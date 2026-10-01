<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Models\Product;

/*
 * ProductListingMapper — map 1 model Product thành array thuần đúng
 * contract của x-ui.product-card (tách từ CatalogService::toCategoryCard).
 */
class ProductListingMapper
{
    /**
     * @return array<string, mixed>
     */
    public static function toCard(Product $p): array
    {
        $old = ($p->compare_price !== null && (int) $p->compare_price > (int) $p->price_min)
            ? (int) $p->compare_price
            : null;

        $discount = $old !== null
            ? (int) round((1 - (int) $p->price_min / $old) * 100)
            : 0;

        return [
            'id' => (int) $p->id,
            'url' => route('web.product.show', ['slug' => $p->slug]),
            'image' => self::coverImageUrl($p),
            'name' => (string) $p->name,
            'price' => (int) $p->price_min !== null ? format_vnd((int) $p->price_min) : null,
            'oldPrice' => $old !== null ? format_vnd((int) $old) : null,
            'discount' => $discount,
            'rating' => (float) $p->rating_avg,
            'sold' => (int) $p->sold_count,
        ];
    }

    /**
     * URL ảnh cover của sản phẩm cho trang danh mục.
     */
    private static function coverImageUrl(Product $p): ?string
    {
        $cover = $p->coverImage;

        if ($cover === null) {
            return asset('images/product-default.svg');
        }

        $path = $cover->thumb_path !== null ? (string) $cover->thumb_path : (string) $cover->path;

        return !empty($path)
            ? asset('assets/images/' . $path)
            : null;
    }
}