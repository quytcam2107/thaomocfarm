<?php

declare(strict_types=1);

namespace App\Services\Review;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/*
 * ReviewPhoneMatcher — tầng đối chiếu SỐ ĐIỆN THOẠI của khách đánh giá với
 * SĐT người nhận trên đơn hàng (orders.customer_phone, NOT NULL, format thật
 * 9–11 số: xem ai-database/tables/orders.md + regex CheckoutRequest).
 *
 * Lý do chuẩn hóa: khách có thể gõ "035 280 6324", "+84 3528 06324" hay
 * "84352806324" trong khi DB lưu "0352806324". Ta quy về dạng Việt Nam
 * 0xxxxxxxxx rồi so sánh exact + LIKE chèn '%' giữa các chữ số để chịu được
 * khoảng trắng/ký tự lạ trong dữ liệu cũ.
 */
class ReviewPhoneMatcher
{
    /**
     * Chuẩn hóa SĐT về dạng VN "0"+9 chữ số; sai độ dài (9..11) → null.
     */
    public static function normalize(?string $phone): ?string
    {
        $trimmed = trim((string) $phone);
        // Có dấu '+' = dạng quốc tế (+84..., 0084...) — phân biệt với số nội địa bắt đầu 84
        $hasPlus = str_starts_with($trimmed, '+');
        $digits = preg_replace('/\D/', '', $trimmed) ?? '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2); // 0084xxxxxxxx -> 84xxxxxxxx
        }

        if (!str_starts_with($digits, '0') && ($hasPlus || str_starts_with($digits, '84'))) {
            // +84xxxxxxxxx hoặc 84xxxxxxxxx -> 0xxxxxxxxx
            $rest = str_starts_with($digits, '84') ? substr($digits, 2) : $digits;
            $digits = '0' . $rest;
        }

        // Độ dài hợp lệ theo CheckoutRequest: 9..11 chữ số
        $len = strlen($digits);
        if ($len < 9 || $len > 11) {
            return null;
        }

        return $digits;
    }

    /**
     * Tồn tại đơn GIAO THÀNH CÔNG (status=delivered) chứa sản phẩm và khớp
     * SĐT đã chuẩn hóa? Trả về bản ghi orders gần nhất hoặc null.
     *
     * @return object{id: int, order_number: string}|null
     */
    public static function findDeliveredOrder(int $productId, string $normalizedPhone): ?object
    {
        $likePattern = self::buildLikePattern($normalizedPhone);

        $order = DB::table('orders')
            ->select(['id', 'order_number'])
            ->where('status', 'delivered')
            ->whereExists(function ($q) use ($productId): void {
                $q->select(DB::raw(1))
                    ->from('order_items')
                    ->whereColumn('order_items.order_id', 'orders.id')
                    ->where('order_items.product_id', $productId);
            })
            ->where(function ($q) use ($normalizedPhone, $likePattern): void {
                // Khớp trực tiếp OR khớp mẫu số-chẵn-lẻ (chống DB cũ giãn số)
                $q->where('customer_phone', $normalizedPhone);
                if ($likePattern !== null) {
                    $q->orWhere('customer_phone', 'LIKE', $likePattern);
                }
            })
            ->orderByDesc('created_at')
            ->first();

        return $order ?: null;
    }

    /**
     * Mồi LIKE chèn '%' giữa các chữ số ("0352806324" -> "0%3%5%2%8%0%6%3%2%4").
     * Chuỗi đầu vào chỉ còn chữ số nên không cần escape ký tự đặc biệt.
     */
    private static function buildLikePattern(string $phone): ?string
    {
        if (strlen($phone) < 9) {
            return null;
        }

        return $phone[0] . '%' . implode('%', str_split(substr($phone, 1)));
    }

    /**
     * Sinh tên file ảnh review: rv_{yyyymmdd}_{16 hex}.{ext} — chống trùng,
     * đuôi đã được ReviewService whitelist trước khi gọi.
     */
    public static function makeFileName(UploadedFile $file): string
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: (string) $file->guessExtension());
        if ($ext === '') {
            $ext = 'jpg';
        }

        return 'rv_' . now()->format('Ymd') . '_' . Str::lower(Str::random(16)) . '.' . $ext;
    }
}