<?php

declare(strict_types=1);

namespace App\DTOs;

/*
 * ReviewViewDTO — 1 đánh giá hiển thị trên PDP (khối x-product.tabs).
 * FIX "Cannot use object of type App\DTOs\ReviewViewDTO as array":
 * DTO implements ArrayAccess để mọi call-site truy cập bằng CẢ 2 kiểu
 * ($review['rating'] lẫn $review->rating) — dữ liệu gốc từ
 * ProductDetailFetcher::fetchReviews() là array thuần nên offset luôn tồn tại.
 */
class ReviewViewDTO implements \ArrayAccess
{
    public function __construct(
        public readonly string $customer,
        public readonly int $rating,
        public readonly string $content,
        public readonly string $created_at,
        // Cột THẬT của bảng reviews (tinyint(1) -> bool)
        public readonly bool $is_verified = false,
        // NEW cho UI đánh giá: id (nút Hữu ích), avatar chữ cái, lượt helpful
        public readonly int $id = 0,
        public readonly string $initials = '?',
        public readonly int $helpful_count = 0,
        // NEW: ảnh đánh giá — list URL tuyệt đối (Fetcher đã bọc asset())
        public readonly array $images = [],
        // NEW: phản hồi công khai của Mộc Xanh (cột text admin_reply)
        public readonly ?string $admin_reply = null,
    ) {
    }

    /**
     * Alias cho view cũ (review-item.blade.php dùng $review->user_name).
     * Không có cột avatar trong DB => fallback ảnh placeholder do view xử lý.
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'user_name' => $this->customer,
            default => null,
        };
    }

    /**
     * Builder an toàn kiểu "vừa mảng vừa object" — FIX lỗi
     * "Cannot use object of type App\DTOs\ReviewViewDTO as array":
     * nhận 1 bản ghi ở dạng array thuần (chuẩn cache của tầng Catalog) HOẶC
     * chính một ReviewViewDTO cũ (cache/deploy trước còn sót) và luôn trả về
     * DTO hợp lệ với đủ key mới (id/initials/helpful_count có fallback).
     *
     * @param array<string, mixed>|self $row
     */
    public static function fromArray(array|self $row): self
    {
        // Đã là DTO -> trả nguyên bản (không map lại bằng cú pháp mảng)
        if ($row instanceof self) {
            return $row;
        }

        return new self(
            customer: (string) ($row['customer'] ?? 'Ẩn danh'),
            rating: (int) ($row['rating'] ?? 0),
            content: (string) ($row['content'] ?? ''),
            created_at: (string) ($row['created_at'] ?? ''),
            is_verified: (bool) ($row['is_verified'] ?? false),
            // Key mới — cache 'review' cũ còn TTL có thể thiếu -> fallback an toàn
            id: (int) ($row['id'] ?? 0),
            initials: (string) ($row['initials'] ?? '?'),
            helpful_count: (int) ($row['helpful_count'] ?? 0),
            // NEW: ảnh + phản hồi admin — cache cũ thiếu key -> mặc định rỗng/null
            images: array_values((array) ($row['images'] ?? [])),
            admin_reply: isset($row['admin_reply']) && $row['admin_reply'] !== '' ? (string) $row['admin_reply'] : null,
        );
    }

    /* ===== ArrayAccess: cho phép đọc/isset theo cú pháp mảng ===== */

    public function offsetExists(mixed $offset): bool
    {
        return property_exists($this, (string) $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        // Trả về đúng giá trị thuộc tính; key lạ -> null (giống hành vi __get cũ)
        return property_exists($this, (string) $offset) ? $this->{$offset} : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        // DTO readonly — chỉ ghi khi chưa khởi tạo (tránh fatal khi clone/unserialize cũ)
        if (!property_exists($this, (string) $offset)) {
            return;
        }
        if (!isset($this->{$offset})) {
            $this->{$offset} = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        // Không hỗ trợ unset trên DTO readonly — giữ im lặng an toàn
    }
}