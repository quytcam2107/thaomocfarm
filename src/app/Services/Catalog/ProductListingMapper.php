<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Models\Product;
use App\Services\Promotion\FlashSalePriceService;

/*
 * ProductListingMapper — map 1 model Product thành array thuần đúng
 * contract của x-ui.product-card (tách từ CatalogService::toCategoryCard).
 *
 * FIX: sản phẩm thuộc flash sale phải hiển thị GIÁ ĐÃ GIẢM theo
 * discount_percent (nguồn sự thật: FlashSalePriceService).
 */
class ProductListingMapper
{
    /**
     * @param FlashSalePriceService|null $pricing null => giữ giá niêm yết (unit test/legacy call-site)
     * @return array<string, mixed>
     */
    public static function toCard(Product $p, ?FlashSalePriceService $pricing = null): array
    {
        $variantId = $p->relationLoaded('defaultVariant') && $p->defaultVariant !== null
            ? (int) $p->defaultVariant->id
            : null;

        // Giá cơ sở: ưu tiên default variant, fallback price_min
        $price = $variantId !== null && $p->relationLoaded('defaultVariant')
            ? (int) $p->defaultVariant->price
            : (int) $p->price_min;

        $old = ($p->compare_price !== null && (int) $p->compare_price > $price)
            ? (int) $p->compare_price
            : null;

        $discount = $old !== null
            ? (int) round((($old - $price) / max(1, $old)) * 100)
            : 0;

        $isFlash = false;

        if ($pricing !== null) {
            $flash = $pricing->priceFor((int) $p->id, $variantId, $price, $price);

            if ($flash !== null) {
                $price = $flash['price'];
                $old = $flash['original_price'];
                $discount = $flash['discount_percent'];
                $isFlash = true;
            }
        }

        return [
            'id' => (int) $p->id,
            'url' => route('web.product.show', ['slug' => $p->slug]),
            'image' => self::coverImageUrl($p),
            'name' => (string) $p->name,
            'price' => format_vnd($price),
            'oldPrice' => $old !== null ? format_vnd((int) $old) : null,
            'discount' => $discount,
            'rating' => (float) $p->rating_avg,
            'sold' => (int) $p->sold_count,
            // Contract .add-cart: product-card nhận productId/variantId
            'product_id' => (int) $p->id,
            'variant_id' => $variantId ?? 0,
            'is_flash_sale' => $isFlash,
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