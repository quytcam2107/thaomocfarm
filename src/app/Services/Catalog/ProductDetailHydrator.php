<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\DTOs\CategoryViewDTO;
use App\DTOs\ProductViewDTO;
use App\DTOs\RelatedProductDTO;
use App\DTOs\ReviewViewDTO;
use App\Services\Promotion\FlashSalePriceService;

/*
 * ProductDetailHydrator — convert array thuần đã cache sang DTO cho view
 * (tách từ CatalogService::hydrateProductDetail).
 */
class ProductDetailHydrator
{
    /**
     * @param array<string, mixed> $cached Mảng thô từ ProductDetailFetcher::fetch()
     * @param array{reviews: list<array<string, mixed>>, stats: array<string, mixed>} $reviewData
     * @param FlashSalePriceService|null $flashPricing NEW: gắn block flash sale (countdown) cho PDP
     * @return array<string, mixed>
     */
    public static function hydrate(array $cached, array $reviewData, ?FlashSalePriceService $flashPricing = null): array
    {
        $categoryDTO = $cached['category'] ? new CategoryViewDTO(
            name: $cached['category']['name'],
            url: $cached['category']['url'],
        ) : null;

        // NEW: block flash sale cho PDP — null nếu SP không thuộc deal => component tự ẩn
        $flashBlock = $flashPricing?->pdpBlockFor((int) $cached['product_id']);

        $productDTO = new ProductViewDTO(
            id: $cached['product']['id'],
            name: $cached['product']['name'],
            sku: $cached['product']['sku'],
            subtitle: $cached['product']['subtitle'],
            description: $cached['product']['description'],
            price: $cached['product']['price'],
            old_price: $cached['product']['old_price'],
            discount_percent: $cached['product']['discount_percent'],
            avg_rating: $cached['product']['avg_rating'],
            review_count: $cached['product']['review_count'],
            sold_count: $cached['product']['sold_count'],
            stock: $cached['product']['stock'],
            image: $cached['product']['image'],
            meta_description: $cached['product']['meta_description'],
            category: $categoryDTO,
            flashSale: $flashBlock,
        );

        $relatedDTOs = array_map(fn($r) => new RelatedProductDTO(
            product_id: (int) ($r['product_id'] ?? 0),
            variant_id: isset($r['variant_id']) ? (int) $r['variant_id'] : null,
            url: $r['url'],
            image: $r['image'],
            name: $r['name'],
            price: $r['price'],
            old_price: $r['old_price'],
            discount_percent: $r['discount_percent'],
            avg_rating: $r['avg_rating'],
            sold_count: $r['sold_count'],
        ), $cached['relatedProducts']);

        // Reviews luôn đọc mới qua group cache 'review' riêng — không nằm trong cache 'catalog'
        $reviewDTOs = array_map(fn($r) => new ReviewViewDTO(
            customer: $r['customer'],
            rating: $r['rating'],
            content: $r['content'],
            created_at: $r['created_at'],
            is_verified: (bool) ($r['is_verified'] ?? false),
        ), $reviewData['reviews']);

        return [
            'product' => $productDTO,
            'images' => $cached['images'],
            'variants' => $cached['variants'],
            'breadcrumbs' => $cached['breadcrumbs'],
            'relatedProducts' => $relatedDTOs,
            'reviews' => $reviewDTOs,
            'ratingStats' => $reviewData['stats'],
        ];
    }
}