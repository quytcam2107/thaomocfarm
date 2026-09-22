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
    ) {
    }
}