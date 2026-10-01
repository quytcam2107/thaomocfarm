<?php

declare(strict_types=1);

namespace App\DTOs;

class RelatedProductDTO
{
    public function __construct(
        // Cần product_id/variant_id để tra deal flash sale & contract .add-cart
        public readonly int $product_id = 0,
        public readonly ?int $variant_id = null,
        public readonly string $url = '#',
        public readonly string $image = '',
        public readonly string $name = '',
        public readonly int $price = 0,
        public readonly ?int $old_price = null,
        public readonly int $discount_percent = 0,
        public readonly float $avg_rating = 5.0,
        public readonly int $sold_count = 0,
    ) {
    }
}