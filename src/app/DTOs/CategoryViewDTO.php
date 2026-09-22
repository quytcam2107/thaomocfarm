<?php

declare(strict_types=1);

namespace App\DTOs;

class CategoryViewDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
    ) {}
}