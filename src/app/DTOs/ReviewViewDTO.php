<?php

declare(strict_types=1);

namespace App\DTOs;

class ReviewViewDTO
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
}