<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ShipmentCarrier;
use App\Enums\StockMovementType;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Shipment;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CouponService $couponService
    ) {
    }

    /**
     * Lấy dữ liệu để hiển thị trang checkout.
     * Logic tính tiền ĐỒNG BỘ với CartService::getCartSummary():
     *  1) goodsDiscount (fixed/percent) trừ vào eligible subtotal
     *  2) shippingFee tính theo method + threshold
     *  3) type=shipping → shippingDiscount = shippingFee
     *  4) total = discountedSubtotal + shippingFee - shippingDiscount
     */
    public function getCheckoutData(int $cartId, string $shippingMethod = 'standard'): array
    {
        $cartDetails = $this->cartService->getCartDetails($cartId);

        $subtotal = (int) $cartDetails['subtotal'];
        $goodsDiscount = 0;
        $shippingDiscount = 0;
        $applied = null;

        $resolved = $this->couponService->resolveAppliedCoupon($cartId, auth()->id());

        if ($resolved !== null) {
            $applied = ['code' => $resolved['code'], 'type' => $resolved['type']];

            if ($resolved['type'] !== 'shipping') {
                $goodsDiscount = $this->couponService->calculateDiscount($resolved['coupon'], $resolved['eligible'], 0);
            }
        }

        $discountedSubtotal = max(0, $subtotal - $goodsDiscount);
        $shippingFee = $this->calculateShippingFee($shippingMethod, $discountedSubtotal);

        if ($resolved !== null && $resolved['type'] === 'shipping') {
            $shippingDiscount = $shippingFee;
        }

        $total = max(0, $discountedSubtotal + $shippingFee - $shippingDiscount);

        return [
            'items' => $cartDetails['items'],
            'subtotal' => $subtotal,
            'discount' => $goodsDiscount,
            'discounted_subtotal' => $discountedSubtotal,
            'shipping_fee' => $shippingFee,
            'shipping_discount' => $shippingDiscount,
            'total' => $total,
            'total_qty' => (int) $cartDetails['total_qty'],
            'applied' => $applied,
        ];
    }

    /**
     * Phí ship theo phương thức vận chuyển.
     * - standard: miễn phí nếu subtotal >= threshold, còn lại 20.000₫
     * - express: luôn thu phí express
     */
    public function calculateShippingFee(string $method, int $subtotal): int
    {
        if ($method === 'express') {
            return (int) config('thaomoc.shipping.express_fee', 30000);
        }

        if ($subtotal >= (int) config('thaomoc.shipping.free_threshold', 200000)) {
            return 0;
        }

        return 20000;
    }

    /**
     * Xử lý đặt hàng (Tạo đơn, trừ tồn kho nguyên tử, lưu lịch sử).
     */
    public function processCheckout(int $cartId, array $validated): Order
    {
        $cartDetails = $this->cartService->getCartDetails($cartId);
        if (empty($cartDetails['items'])) {
            throw new \Exception('Giỏ hàng đang trống.');
        }

        $subtotal = (int) $cartDetails['subtotal'];
        $userId = auth()->id();
        $shippingMethod = $validated['shipping_method'] ?? 'standard';

        // Resolve coupon lần nữa (tránh race condition)
        $goodsDiscount = 0;
        $shippingDiscount = 0;
        $couponId = null;
        $couponCode = null;

        $resolved = $this->couponService->resolveAppliedCoupon($cartId, $userId);

        if ($resolved !== null) {
            $coupon = $resolved['coupon'];
            $couponId = $coupon->id;
            $couponCode = $resolved['code'];
            $type = $resolved['type'];

            if ($type !== 'shipping') {
                $goodsDiscount = $this->couponService->calculateDiscount($coupon, $resolved['eligible'], 0);
            }
        }

        $discountedSubtotal = max(0, $subtotal - $goodsDiscount);
        $shippingFee = $this->calculateShippingFee($shippingMethod, $discountedSubtotal);

        if ($resolved !== null && $resolved['type'] === 'shipping') {
            $shippingDiscount = $shippingFee;
        }

        $discountAmount = $goodsDiscount + $shippingDiscount;
        $total = max(0, $discountedSubtotal + $shippingFee - $shippingDiscount);

        $addressSnapshot = [
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'province' => $validated['province'],
            'district' => $validated['district'],
            'ward' => $validated['ward'] ?? '',
            'detail' => $validated['address'],
        ];

        $orderNumber = $this->generateOrderNumber();

        return DB::transaction(function () use ($cartId, $cartDetails, $validated, $subtotal, $shippingFee, $discountAmount, $couponId, $couponCode, $total, $addressSnapshot, $orderNumber) {
            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => auth()->id(),
                'customer_name' => $validated['name'],
                'customer_phone' => $validated['phone'],
                'customer_email' => $validated['email'] ?? null,
                'address_snapshot' => $addressSnapshot,
                'note' => $validated['note'] ?? null,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'coupon_code_snapshot' => $couponCode,
                'coupon_id' => $couponId,
                'shipping_fee' => $shippingFee,
                'total' => $total,
                'payment_method' => PaymentMethod::COD->value,
                'payment_status' => OrderPaymentStatus::PENDING->value,
                'status' => OrderStatus::NEW ->value,
            ]);

            // Trừ tồn kho & Tạo Order Items
            foreach ($cartDetails['items'] as $item) {
                $variant = ProductVariant::find($item['variant_id']);
                if (!$variant) {
                    throw new \Exception("Không tìm thấy biến thể sản phẩm {$item['product_name']}.");
                }

                // Conditional UPDATE nguyên tử
                $affected = ProductVariant::where('id', $variant->id)
                    ->where('stock', '>=', $item['qty'])
                    ->update(['stock' => DB::raw('stock - ' . (int) $item['qty'])]);

                if ($affected === 0) {
                    throw new \Exception("Sản phẩm {$item['product_name']} không đủ tồn kho hoặc đã cháy hàng.");
                }

                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'type' => StockMovementType::EXPORT->value,
                    'qty' => -(int) $item['qty'],
                    'ref_type' => 'order',
                    'ref_id' => $order->id,
                    'note' => 'Đặt hàng thành công',
                    'created_by' => auth()->id(),
                ]);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $variant->id,
                    'name_snapshot' => $item['product_name'],
                    'sku_snapshot' => $variant->sku ?? '',
                    'image_snapshot' => $item['image'],
                    // FIX: giá chốt đơn = giá flash sale đã áp discount_percent (nếu có deal)
                    'price' => (int) ($item['unit_price'] ?? $variant->price),
                    'qty' => $item['qty'],
                    'subtotal' => (int) ($item['unit_price'] ?? $variant->price) * $item['qty'],
                ]);
            }

            Shipment::create([
                'order_id' => $order->id,
                'carrier' => ShipmentCarrier::INTERNAL->value,
                'fee' => $shippingFee,
                'status' => 'pending',
            ]);

            // Record coupon usage (gọi method nếu có trong CouponService)
            if ($couponId && method_exists($this->couponService, 'recordUsage')) {
                $this->couponService->recordUsage($couponId, auth()->id(), $order->id, $discountAmount);
            }

            // Clear cart
            CartItem::where('cart_id', $cartId)->delete();

            // Xóa session coupon sau khi đặt hàng thành công
            $this->couponService->removeAppliedCoupon();

            return $order;
        });
    }

    /**
     * Sinh mã đơn hàng TMX-yyyyMMdd-xxxxx
     */
    private function generateOrderNumber(): string
    {
        $prefix = config('thaomoc.order.prefix', 'TMX');
        $date = date('Ymd');
        $length = (int) config('thaomoc.order.random_length', 5);

        do {
            $random = strtoupper(Str::random($length));
            $orderNumber = "{$prefix}-{$date}-{$random}";
            $exists = Order::where('order_number', $orderNumber)->exists();
        } while ($exists);

        return $orderNumber;
    }
}