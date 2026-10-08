<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Review\ReviewPhoneMatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;

/**
 * OrderLookupService — tầng nghiệp vụ cho trang "Tra cứu đơn hàng bằng SĐT".
 *
 * Nguyên tắc an toàn dữ liệu:
 *  - CHỈ truy vấn theo customer_phone ĐÃ chuẩn hoá (dùng ReviewPhoneMatcher::normalize
 *    — nguồn sự thật chuẩn hoá SĐT của app, khớp format orders.customer_phone 9..11 số).
 *  - KHÔNG trả email/SĐT đầy đủ của đơn ra view khi chưa xác thực OTP (giai đoạn này
 *    chỉ hiển thị thông tin rút gọn + mask SĐT).
 *  - Không cache kết quả tra cứu (dữ liệu đơn thay đổi theo thời gian thực).
 */
class OrderLookupService
{
    /** Số đơn tối đa lấy cho 1 lần tra cứu (chống phình query/view). */
    private const MAX_ORDERS = 20;

    /**
     * Chuẩn hoá SĐT đầu vào -> chuỗi digit VN hoặc null nếu sai định dạng.
     */
    public function normalizePhone(string $rawPhone): ?string
    {
        return ReviewPhoneMatcher::normalize($rawPhone);
    }

    /**
     * Rate limit theo IP cho hành vi tra cứu (chống dò SĐT hàng loạt).
     * Dùng đúng pattern gọi trực tiếp RateLimiter như CartController/SearchController
     * (project KHÔNG dùng middleware throttle).
     */
    public function hitRateLimit(string $ip): bool
    {
        $limit = (int) config('thaomoc.rate_limits.order_lookup', 10);
        $key = 'order_lookup|' . $ip;

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return false;
        }

        RateLimiter::hit($key, 60);

        return true;
    }

    /**
     * Tìm các đơn gần nhất của một SĐT (kèm items + shipment), mới nhất trước.
     *
     * @return Collection<int, Order>
     */
    public function findOrdersByPhone(string $normalizedPhone): Collection
    {
        return Order::query()
            ->with([
                'items' => fn($q) => $q->select([
                    'id',
                    'order_id',
                    'product_id',
                    'name_snapshot',
                    'sku_snapshot',
                    'image_snapshot',
                    'price',
                    'qty',
                    'subtotal',
                ]),
                'shipment' => fn($q) => $q->select([
                    'id',
                    'order_id',
                    'carrier',
                    'tracking_code',
                    'status',
                    'shipped_at',
                    'delivered_at',
                ]),
            ])
            ->where('customer_phone', $normalizedPhone)
            ->orderByDesc('created_at')
            ->limit(self::MAX_ORDERS)
            ->get();
    }

    /**
     * Map đơn -> dữ liệu hiển thị + timeline tiến trình.
     * Timeline suy từ OrderStatus (bảng order_status_histories đang trống nên
     * KHÔNG dựa vào history; khi có history thì ưu tiên mốc thời gian thật).
     *
     * @return array<string, mixed>
     */
    public function present(Order $order): array
    {
        $status = OrderStatus::tryFrom((string) $order->status) ?? OrderStatus::NEW;

        // Các mốc cố định của quy trình (theo thứ tự nghiệp vụ)
        $steps = [
            ['key' => 'new', 'label' => 'Đơn hàng đã tạo', 'at' => $order->created_at],
            ['key' => 'confirmed', 'label' => 'Nhân viên xác nhận', 'at' => null],
            ['key' => 'packing', 'label' => 'Đang đóng gói', 'at' => null],
            ['key' => 'shipping', 'label' => 'Đang giao hàng', 'at' => $order->shipment?->shipped_at],
            ['key' => 'delivered', 'label' => 'Giao hàng thành công', 'at' => $order->shipment?->delivered_at],
        ];

        // Trạng thái hiện tại nằm ở index nào -> các mốc <= index là "đã đạt"
        $flow = ['new', 'confirmed', 'packing', 'shipping', 'delivered'];
        $currentIndex = array_search($status->value, $flow, true);
        // Đơn hủy / đổi trả: đánh dấu riêng, không có mốc "đã đạt" ngoài bước tạo
        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        $timeline = [];
        foreach ($steps as $i => $step) {
            $timeline[] = [
                'label' => $step['label'],
                'done' => $i <= $currentIndex && !$this->isAbnormal($status),
                'active' => $i === $currentIndex && !$this->isAbnormal($status),
                'time' => $step['at'],
            ];
        }

        $snap = $order->address_snapshot ?? [];

        return [
            'order_number' => $order->order_number,
            'created_at' => $order->created_at,
            'status' => $status->value,
            'status_label' => $status->label(),
            'is_abnormal' => $this->isAbnormal($status),
            'payment_method' => (string) $order->payment_method,
            'payment_status' => (string) $order->payment_status,
            'total' => (int) $order->total,
            'subtotal' => (int) $order->subtotal,
            'discount_amount' => (int) $order->discount_amount,
            'shipping_fee' => (int) $order->shipping_fee,
            'coupon_code' => $order->coupon_code_snapshot,
            'note' => $order->note,
            // Mask SĐT để tránh lộ thông tin trên màn hình công cộng
            'phone_masked' => $this->maskPhone((string) $order->customer_phone),
            'recipient_name' => $this->maskName((string) $order->customer_name),
            'address_line' => implode(', ', array_filter([
                $snap['detail'] ?? '',
                $snap['ward'] ?? '',
                $snap['district'] ?? '',
                $snap['province'] ?? '',
            ], fn($v) => trim((string) $v) !== '')),
            'tracking_code' => $order->shipment?->tracking_code,
            'carrier' => $order->shipment?->carrier,
            'items' => $order->items->map(fn($item) => [
                'name' => (string) $item->name_snapshot,
                'sku' => (string) $item->sku_snapshot,
                'qty' => (int) $item->qty,
                'subtotal' => (int) $item->subtotal,
                'image' => $this->resolveImage($item->image_snapshot),
            ])->all(),
            'timeline' => $timeline,
        ];
    }

    private function isAbnormal(OrderStatus $status): bool
    {
        return in_array($status, [OrderStatus::CANCELLED, OrderStatus::RETURNING], true);
    }

    /**
     * Ảnh snapshot có thể là URL tuyệt đối (http...), path tương đối ('assets/...'),
     * hoặc rỗng -> fallback placeholder (đồng bộ cách xử lý ở checkout-success).
     */
    private function resolveImage(?string $img): string
    {
        if (!empty($img) && str_starts_with((string) $img, 'http')) {
            return (string) $img;
        }

        if (!empty($img)) {
            return asset('assets/images/' . ltrim((string) $img, '/'));
        }

        return asset('assets/images/placeholder.svg');
    }

    /** 0352806324 -> 035***624 */
    private function maskPhone(string $phone): string
    {
        $len = strlen($phone);
        if ($len < 6) {
            return $phone;
        }

        return substr($phone, 0, 3) . '***' . substr($phone, -3);
    }

    /** "Quyết Lưu" -> "Q*** L***" (giữ âm đầu mỗi từ) */
    private function maskName(string $name): string
    {
        $words = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return implode(' ', array_map(
            static fn(string $w): string => mb_substr($w, 0, 1, 'UTF-8') . '***',
            $words
        ));
    }
}