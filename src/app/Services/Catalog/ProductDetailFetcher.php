<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/*
 * ProductDetailFetcher — tầng DB thuần của trang chi tiết sản phẩm
 * (tách từ CatalogService::fetchProductDetailFromDB + fetchReviewsFromDB).
 * BẮT BUỘC trả array thuần (không object Eloquent) để an toàn cho cache file.
 */
class ProductDetailFetcher
{
    /**
     * Lấy toàn bộ dữ liệu thô của 1 sản phẩm theo slug (kèm cả related products).
     *
     * @return array<string, mixed>
     */
    public static function fetch(string $slug): array
    {
        $product = Product::where('slug', $slug)
            ->where('status', 'active')
            ->with([
                'category' => function (BelongsTo $query) {
                    $query->select('id', 'name', 'slug');
                },
                'images' => function (HasMany $query) {
                    $query->orderBy('sort_order')->select('id', 'product_id', 'path', 'thumb_path', 'alt', 'is_cover');
                },
                'variants' => function (HasMany $query) {
                    $query->orderBy('sort_order')->select('id', 'product_id', 'label', 'price', 'compare_price', 'stock', 'is_default');
                },
            ])
            ->firstOrFail();

        $defaultVariant = $product->variants->firstWhere('is_default', true) ?? $product->variants->first();

        $currentPrice = $defaultVariant ? (int) $defaultVariant->price : (int) $product->price_min;
        $currentComparePrice = $defaultVariant && $defaultVariant->compare_price ? (int) $defaultVariant->compare_price : ($product->compare_price ? (int) $product->compare_price : null);
        $currentStock = $defaultVariant ? (int) $defaultVariant->stock : (int) $product->stock_total;

        $discountPercent = 0;
        if ($currentComparePrice && $currentComparePrice > $currentPrice) {
            $discountPercent = (int) round((($currentComparePrice - $currentPrice) / $currentComparePrice) * 100);
        }

        $coverImage = $product->images->firstWhere('is_cover', true)?->path
            ?? $product->images->first()?->path
            ?? 'images/placeholder.svg';

        $images = $product->images->map(fn($img) => asset('assets/images/' . $img->path))->values()->all();

        $variants = $product->variants->map(function ($v) {
            return [
                'label_group' => 'Khối lượng',
                'name' => 'variant_id',
                'value' => (string) $v->id,
                'label' => $v->label,
                'selected' => (bool) $v->is_default,
                'price' => (int) $v->price,
                'old_price' => $v->compare_price ? (int) $v->compare_price : null,
                'stock' => (int) $v->stock,
            ];
        })->values()->all();

        $breadcrumbs = [
            ['label' => 'Trang chủ', 'url' => route('web.home')],
        ];
        if ($product->category) {
            $breadcrumbs[] = [
                'label' => $product->category->name,
                'url' => route('web.category.show', $product->category->slug),
            ];
        }
        $breadcrumbs[] = ['label' => $product->name, 'url' => null];

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->with(['images', 'variants'])
            ->limit(4)
            ->get()
            ->map(function ($p) {
                $coverImg = asset(
                    'assets/images/' . (
                        $p->images->firstWhere('is_cover', true)?->path
                        ?? $p->images->first()?->path
                        ?? 'placeholder.svg'
                    )
                );
                $defVar = $p->variants->firstWhere('is_default', true) ?? $p->variants->first();
                $pr = $defVar ? (int) $defVar->price : (int) $p->price_min;
                $op = $defVar && $defVar->compare_price ? (int) $defVar->compare_price : ($p->compare_price ? (int) $p->compare_price : null);
                $disc = ($op && $op > $pr) ? (int) round((($op - $pr) / $op) * 100) : 0;

                return [
                    'url' => route('web.product.show', $p->slug),
                    'image' => $coverImg,
                    'name' => $p->name,
                    'price' => $pr,
                    'old_price' => $op,
                    'discount_percent' => $disc,
                    'avg_rating' => (float) $p->rating_avg,
                    'sold_count' => (int) $p->sold_count,
                ];
            })
            ->all();

        return [
            'product_id' => $product->id,
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'subtitle' => $product->subtitle ?? '',
                'description' => $product->description ?? '',
                'price' => $currentPrice,
                'old_price' => $currentComparePrice,
                'discount_percent' => $discountPercent,
                'avg_rating' => (float) $product->rating_avg,
                'review_count' => (int) $product->rating_count,
                'sold_count' => (int) $product->sold_count,
                'stock' => $currentStock,
                'image' => $coverImage,
                'meta_description' => $product->seo['description'] ?? $product->subtitle ?? $product->name,
            ],
            'category' => $product->category ? [
                'name' => $product->category->name,
                'url' => route('web.category.show', $product->category->slug),
            ] : null,
            'images' => $images,
            'variants' => $variants,
            'breadcrumbs' => $breadcrumbs,
            'relatedProducts' => $relatedProducts,
        ];
    }

    /**
     * Lấy reviews đã duyệt + thống kê sao của 1 sản phẩm (array thuần).
     *
     * @return array{reviews: list<array<string, mixed>>, stats: array{avg: float, total: int, breakdown: array<int,int>}}
     */
    public static function fetchReviews(int $productId): array
    {
        $reviews = Review::where('product_id', $productId)
            ->where('status', 'approved')
            ->with('user:id,name')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'customer' => $r->user?->name ?? 'Khách',
                'rating' => (int) $r->rating,
                'content' => (string) $r->content,
                'created_at' => $r->created_at->format('d/m/Y'),
            ])
            ->all();

        $stats = Review::where('product_id', $productId)
            ->where('status', 'approved')
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray();

        $totalReviews = (int) array_sum($stats);
        $weightedSum = 0;
        foreach ($stats as $star => $count) {
            $weightedSum += (int) $star * (int) $count;
        }

        return [
            'reviews' => $reviews,
            'stats' => [
                'avg' => $totalReviews > 0 ? round($weightedSum / $totalReviews, 1) : 5.0,
                'total' => $totalReviews,
                'breakdown' => [
                    5 => (int) ($stats[5] ?? 0),
                    4 => (int) ($stats[4] ?? 0),
                    3 => (int) ($stats[3] ?? 0),
                    2 => (int) ($stats[2] ?? 0),
                    1 => (int) ($stats[1] ?? 0),
                ],
            ],
        ];
    }
}