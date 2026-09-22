<?php

declare(strict_types=1);

namespace App\DTOs;

class RelatedProductDTO
{
    public function __construct(
        public readonly string $url,
        public readonly string $image,
        public readonly string $name,
        public readonly int $price,
        public readonly ?int $old_price,
        public readonly int $discount_percent,
        public readonly float $avg_rating,
        public readonly int $sold_count,
    ) {
    }
}