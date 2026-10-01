<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

/*
 * ProductQueryFilters — bộ lọc/sắp xếp dùng chung cho CatalogService
 * (tách từ CatalogService để service chính gọn).
 * Logic giữ nguyên 100%: normalize filters + apply giá/rating/sort.
 */
trait ProductQueryFilters
{
    /**
     * Chuẩn hoá bộ lọc danh mục về dạng an toàn, đủ key mặc định.
     */
    private function normalizeCategoryFilters(array $raw): array
    {
        $sortOptions = (array) config('catalog.sort_options');
        $priceKeys = array_column((array) config('catalog.price_ranges'), 'key');

        $sort = isset($raw['sort']) && array_key_exists($raw['sort'], $sortOptions)
            ? (string) $raw['sort']
            : 'bestsell';

        $prices = array_values(array_intersect(
            (array) ($raw['price'] ?? []),
            $priceKeys
        ));

        $rating = isset($raw['rating']) && $raw['rating'] !== null && $raw['rating'] !== ''
            ? (int) $raw['rating']
            : null;
        if ($rating !== null && !in_array($rating, (array) config('catalog.rating_filters', [3, 4, 5]), true)) {
            $rating = null;
        }

        $cats = array_values(array_unique(array_map('intval', (array) ($raw['cat'] ?? []))));

        $page = max(1, (int) ($raw['page'] ?? 1));

        return [
            'sort' => $sort,
            'prices' => $prices,
            'rating' => $rating,
            'cats' => $cats,
            'page' => $page,
        ];
    }

    /**
     * Áp bộ lọc giá + rating + sắp xếp vào query sản phẩm (dùng chung mọi trang listing).
     *
     * @param Builder<Product> $query
     * @param array{sort: string, prices: list<string>, rating: int|null} $filters
     * @return Builder<Product>
     */
    private function applyListingFilters(Builder $query, array $filters): Builder
    {
        // Lọc khoảng giá: OR giữa các range hợp lệ (max = null => không chặn trên)
        if ($filters['prices'] !== []) {
            $ranges = (array) config('catalog.price_ranges');

            $query->where(function (Builder $q) use ($filters, $ranges): void {
                foreach ($filters['prices'] as $key) {
                    $range = collect($ranges)->firstWhere('key', $key);
                    if ($range === null)
                        continue;
                    if ($range['max'] === null) {
                        $q->orWhere('price_min', '>=', $range['min']);
                    } else {
                        $q->orWhereBetween('price_min', [$range['min'], $range['max']]);
                    }
                }
            });
        }

        if ($filters['rating'] !== null) {
            $query->where('rating_avg', '>=', $filters['rating']);
        }

        // Sắp xếp theo key đã được normalize ở trên
        return match ($filters['sort']) {
            'newest' => $query->orderByDesc('published_at')->orderByDesc('id'),
            'price_asc' => $query->orderBy('price_min')->orderByDesc('id'),
            'price_desc' => $query->orderByDesc('price_min')->orderByDesc('id'),
            default => $query->orderByDesc('sold_count')->orderByDesc('id'),
        };
    }
}