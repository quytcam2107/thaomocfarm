<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ShipmentCarrier;
use App\Enums\StockMovementType;
use App\Models\CartItem;
use App\Models\CouponUsage;
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
     */
    public function getCheckoutData(int $cartId, string $shippingMethod = 'standard'): array
    {
        $cartDetails = $this->cartService->getCartDetails($cartId);

        if (empty($cartDetails['items'])) {
            return [];
        }

        $subtotal = $cartDetails['subtotal'];
        $shippingFee = $this->calculateShippingFee($shippingMethod, $subtotal);

        $couponCode = session('applied_coupon_code');
        $discountAmount = 0;
        $couponId = null;

        if ($couponCode) {
            $couponData = $this->couponService->applyCoupon($couponCode, $subtotal, auth()->id());
            if (is_array($couponData) && isset($couponData['discount_amount'])) {
                $discountAmount = (int) $couponData['discount_amount'];
                $couponId = $couponData['coupon_id'] ?? null;
            } else {
                session()->forget('applied_coupon_code');
            }
        }

        $total = $subtotal + $shippingFee - $discountAmount;
        if ($total < 0)
            $total = 0;

        return [
            'items' => $cartDetails['items'],
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'shipping_method' => $shippingMethod,
            'discount_amount' => $discountAmount,
            'coupon_code' => $couponCode,
            'total' => $total,
            'free_shipping_threshold' => config('thaomoc.shipping.free_threshold', 300000),
        ];
    }

    /**
     * Tính phí vận chuyển dựa theo phương thức chọn từ UI.
     */
    public function calculateShippingFee(string $method, int $subtotal): int
    {
        if ($method === 'fast') {
            return 30000;
        }

        // standard
        $threshold = config('thaomoc.shipping.free_threshold', 300000);
        if ($subtotal >= $threshold) {
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

        $subtotal = $cartDetails['subtotal'];
        $shippingMethod = $validated['shipping_method'] ?? 'standard';
        $shippingFee = $this->calculateShippingFee($shippingMethod, $subtotal);

        $couponCode = session('applied_coupon_code');
        $discountAmount = 0;
        $couponId = null;

        if ($couponCode) {
            $couponData = $this->couponService->applyCoupon($couponCode, $subtotal, auth()->id());
            if (is_array($couponData) && isset($couponData['discount_amount'])) {
                $discountAmount = (int) $couponData['discount_amount'];
                $couponId = $couponData['coupon_id'] ?? null;
            }
        }

        $total = $subtotal + $shippingFee - $discountAmount;
        if ($total < 0)
            $total = 0;

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

        return DB::transaction(function () use ($cartId, $cartDetails, $validated, $subtotal, $shippingFee, $discountAmount, $couponId, $total, $addressSnapshot, $orderNumber) {
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

                // Conditional UPDATE nguyên tử: Chốt tồn kho
                $affected = ProductVariant::where('id', $variant->id)
                    ->where('stock', '>=', $item['qty'])
                    ->update(['stock' => DB::raw('stock - ' . (int) $item['qty'])]);

                if ($affected === 0) {
                    throw new \Exception("Sản phẩm {$item['product_name']} không đủ tồn kho hoặc đã cháy hàng.");
                }

                // Record stock movement
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
                    'price' => $variant->price,
                    'qty' => $item['qty'],
                    'subtotal' => $variant->price * $item['qty'],
                ]);
            }

            // Tạo Shipment
            Shipment::create([
                'order_id' => $order->id,
                'carrier' => ShipmentCarrier::INTERNAL->value,
                'fee' => $shippingFee,
                'status' => 'pending',
            ]);

            // Record coupon usage
            if ($couponId) {
                $this->couponService->recordUsage($couponId, auth()->id(), $order->id, $discountAmount);
            }

            // Clear cart
            CartItem::where('cart_id', $cartId)->delete();
            session()->forget('applied_coupon_code');

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