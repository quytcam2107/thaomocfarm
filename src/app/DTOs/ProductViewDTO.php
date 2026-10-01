<?php

declare(strict_types=1);

namespace App\DTOs;

class ProductViewDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $sku,
        public readonly string $subtitle,
        public readonly string $description,
        public readonly int $price,
        public readonly ?int $old_price,
        public readonly int $discount_percent,
        public readonly float $avg_rating,
        public readonly int $review_count,
        public readonly int $sold_count,
        public readonly int $stock,
        public readonly string $image,
        public readonly string $meta_description,
        public readonly ?CategoryViewDTO $category,
        // Block flash sale PDP (null khi SP không thuộc deal => UI giữ nguyên như cũ)
        public readonly ?array $flashSale = null,
    ) {
    }
}