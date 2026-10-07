<?php

declare(strict_types=1);

namespace App\DTOs;

class ProductViewDTO
{
    public function __construct(
        public readonly int $id,
        // Slug thật của sản phẩm — cần cho route('web.product.show', $slug) (canonical/share URL)
        public readonly string $slug = '',
        public readonly string $name = '',
        public readonly string $sku = '',
        public readonly string $subtitle = '',
        public readonly string $description = '',
        public readonly int $price = 0,
        public readonly ?int $old_price = null,
        public readonly int $discount_percent = 0,
        public readonly float $avg_rating = 5.0,
        public readonly int $review_count = 0,
        public readonly int $sold_count = 0,
        public readonly int $stock = 0,
        public readonly string $image = '',
        public readonly string $meta_description = '',
        public readonly ?CategoryViewDTO $category = null,
        // Block flash sale PDP (null khi SP không thuộc deal => UI giữ nguyên như cũ)
        public readonly ?array $flashSale = null,
        // NEW: danh sách "Thông số sản phẩm" đã decode + map nhãn tiếng Việt từ
        // cột products.specs_json. Mỗi phần tử: ['label' => string, 'value' => string].
        // Rỗng khi sản phẩm chưa nhập thông số => component tự ẩn khối.
        public readonly array $specs = [],
    ) {
    }
}