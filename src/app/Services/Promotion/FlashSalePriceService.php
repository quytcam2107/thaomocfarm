<?php

declare(strict_types=1);

namespace App\Services\Promotion;

use App\Enums\PromotionStatus;
use App\Enums\PromotionType;
use App\Models\Promotion;
use App\Models\PromotionProduct;
use Illuminate\Support\Facades\DB;

/**
 * FlashSalePriceService — nguồn sự thật DUY NHẤT cho giá flash sale.
 *
 * Quy ước khớp dữ liệu thật (ai-database/tables/promotion_products.md):
 *   - flash_price      = GIÁ GỐC của deal (giá gạch ngang)
 *   - discount_percent = % giảm áp TRÊN flash_price
 *   - GIÁ BÁN = round(flash_price * (100 - discount_percent) / 100)
 *     ví dụ thật: 800.000 -22% => 624.000 | 150.000 -35% => 97.500
 *                 115.000 -20% => 92.000  | 115.000 -18% => 94.300
 *   - discount_percent = 0 => tự suy % từ flash_price so với giá niêm yết
 *
 * Cache nhóm "catalog" (driver file, không dùng Cache::tags) — sau khi admin
 * sửa deal phải gọi flush() để giá đổi ngay.
 */
class FlashSalePriceService
{
    /**
     * Danh sách deal flash_sale đang chạy.
     *
     * @return list<array{product_id:int,variant_id:int|null,flash_price:int,discount_percent:int,final_price:int,qty_total:int,qty_sold:int,per_user_limit:int,sort_order:int}>
     */
    public function currentDeals(): array
    {
        $ttl = (int) config('thaomoc.cache.catalog.flash_sale', 300);

        return remember_group('catalog', 'flash_sale_deals', $ttl, function (): array {
            /** @var Promotion|null $promotion */
            $promotion = Promotion::query()
                ->where('type', PromotionType::FLASH_SALE->value)
                ->where('status', PromotionStatus::ACTIVE->value)
                ->where('start_at', '<=', now())
                ->where('end_at', '>', now())
                ->orderBy('end_at')
                ->first(['id']);

            if ($promotion === null) {
                return [];
            }

            return PromotionProduct::query()
                ->where('promotion_id', $promotion->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get([
                    'id',
                    'product_id',
                    'product_variant_id',
                    'flash_price',
                    'discount_percent',
                    'qty_total',
                    'qty_sold',
                    'per_user_limit',
                    'sort_order'
                ])
                ->map(fn(PromotionProduct $pp): array => [
                    'product_id' => (int) $pp->product_id,
                    'variant_id' => $pp->product_variant_id !== null ? (int) $pp->product_variant_id : null,
                    'flash_price' => (int) $pp->flash_price,
                    'discount_percent' => (int) $pp->discount_percent,
                    'final_price' => $this->applyPercent((int) $pp->flash_price, (int) $pp->discount_percent),
                    'qty_total' => (int) $pp->qty_total,
                    'qty_sold' => (int) $pp->qty_sold,
                    'per_user_limit' => (int) $pp->per_user_limit,
                    'sort_order' => (int) $pp->sort_order,
                ])
                ->all();
        });
    }

    /**
     * Map product_id => deal (1 sản phẩm lấy deal đầu tiên theo sort_order).
     *
     * @return array<int, array<string, mixed>>
     */
    public function dealsByProduct(): array
    {
        $map = [];

        foreach ($this->currentDeals() as $deal) {
            $map[$deal['product_id']] ??= $deal;
        }

        return $map;
    }

    /** Deal áp cho cặp (product, variant); null nếu không thuộc flash sale. */
    public function dealFor(int $productId, ?int $variantId, bool $isDefaultVariant = true): ?array
    {
        $deal = $this->dealsByProduct()[$productId] ?? null;

        if ($deal === null) {
            return null;
        }

        // Data thật: cả 4 deal đều product_variant_id = NULL => áp cho default variant
        if ($deal['variant_id'] !== null) {
            return ($variantId !== null && $deal['variant_id'] === $variantId) ? $deal : null;
        }

        return $isDefaultVariant ? $deal : null;
    }

    /**
     * Giá bán + giá gốc + % giảm cho 1 sản phẩm/biến thể.
     * Trả null khi không có deal => caller giữ giá niêm yết.
     *
     * @return array{price:int,original_price:int,discount_percent:int}|null
     */
    public function priceFor(int $productId, ?int $variantId, int $basePrice, int $originalBase = 0, bool $isDefaultVariant = true): ?array
    {
        $deal = $this->dealFor($productId, $variantId, $isDefaultVariant);

        if ($deal === null) {
            return null;
        }

        $original = $deal['flash_price'] > 0 ? $deal['flash_price'] : $basePrice;
        $percent = $this->percentOf($deal, $basePrice, $original);
        $final = $this->applyPercent($original, $percent);

        // An toàn: giá deal không được cao hơn giá niêm yết
        if ($basePrice > 0 && $final >= $basePrice) {
            return null;
        }

        return [
            'price' => $final,
            'original_price' => $original,
            'discount_percent' => $percent,
        ];
    }

    /**
     * Metadata phiên flash_sale đang chạy cho PDP (countdown + tên chương trình).
     * Cùng điều kiện chọn phiên với HomeService::flashSale() (type=flash_sale,
     * status=active, start_at<=now<end_at, ưu tiên end_at gần nhất) => PDP và
     * trang home luôn đếm ngược về CÙNG một mốc thời gian.
     *
     * @return array{promotion_id:int,promotion_name:string,ends_at_unix:int}|null
     */
    public function currentPromotionMeta(): ?array
    {
        $ttl = (int) config('thaomoc.cache.catalog.flash_sale', 300);

        return remember_group('catalog', 'flash_sale_meta', $ttl, function (): ?array {
            /** @var Promotion|null $promotion */
            $promotion = Promotion::query()
                ->where('type', PromotionType::FLASH_SALE->value)
                ->where('status', PromotionStatus::ACTIVE->value)
                ->where('start_at', '<=', now())
                ->where('end_at', '>', now())
                ->orderBy('end_at')
                ->first(['id', 'name', 'end_at']);

            if ($promotion === null) {
                return null;
            }

            return [
                'promotion_id' => (int) $promotion->id,
                'promotion_name' => (string) $promotion->name,
                'ends_at_unix' => $promotion->end_at->getTimestamp(),
            ];
        });
    }

    /**
     * Block flash sale cho PDP — trả null khi sản phẩm KHÔNG thuộc deal đang
     * chạy (component x-product.flash-block tự ẩn => UI cũ không đổi).
     *
     * Lưu ý thiết kế: KHÔNG xuất số "ngày" — countdown PDP đếm GIỜ:PHÚT:GIÂY
     * giống hệt trang home (JS tự quy đổi phần dư thành giờ, không cần biết
     * end_at cách bao nhiêu ngày).
     *
     * @return array{name:string,ends_at_unix:int,discount_percent:int,saved_amount:int,slots_left:int,sold_percent:int,per_user_limit:int}|null
     */
    public function pdpBlockFor(int $productId): ?array
    {
        $meta = $this->currentPromotionMeta();
        $deal = $this->dealsByProduct()[$productId] ?? null;

        if ($meta === null || $deal === null) {
            return null;
        }

        // Đồng bộ công thức giá với applyToDetailArray(): flash_price = giá gốc deal
        $original = $deal['flash_price'] > 0 ? (int) $deal['flash_price'] : 0;
        $percent = $this->percentOf($deal, $original, $original);
        $final = $this->applyPercent($original, $percent);

        $qtyTotal = (int) $deal['qty_total'];
        $qtySold = (int) $deal['qty_sold'];

        return [
            'name' => $meta['promotion_name'],
            'ends_at_unix' => $meta['ends_at_unix'],
            'discount_percent' => min(99, max(0, $percent)),
            'saved_amount' => max(0, $original - $final),
            'slots_left' => max(0, $qtyTotal - $qtySold),
            'sold_percent' => $qtyTotal > 0 ? (int) round($qtySold * 100 / $qtyTotal) : 0,
            'per_user_limit' => (int) $deal['per_user_limit'],
        ];
    }

    /**
     * Áp giá flash sale lên mảng thô PDP (chạy NGOÀI cache => giá luôn tươi).
     *
     * @param array<string, mixed> $cached
     * @return array<string, mixed>
     */
    public function applyToDetailArray(array $cached): array
    {
        $deals = $this->dealsByProduct();

        if ($deals === []) {
            return $cached;
        }

        $productId = (int) $cached['product_id'];

        // 1) Sản phẩm chính
        if (isset($deals[$productId])) {
            $deal = $deals[$productId];
            $base = (int) $cached['product']['price'];
            $original = $deal['flash_price'] > 0 ? $deal['flash_price'] : $base;
            $percent = $this->percentOf($deal, $base, $original);
            $final = $this->applyPercent($original, $percent);

            if ($base <= 0 || $final < $base) {
                $cached['product']['old_price'] = $original;
                $cached['product']['price'] = $final;
                $cached['product']['discount_percent'] = $percent;

                // Đồng bộ biến thể mặc định để pill/radio + buybar hiển thị đúng giá deal
                foreach ($cached['variants'] as $i => $v) {
                    if (!empty($v['selected'])) {
                        $cached['variants'][$i]['old_price'] = $original;
                        $cached['variants'][$i]['price'] = $final;
                    }
                }
            }
        }

        // 2) Sản phẩm liên quan
        foreach ($cached['relatedProducts'] as $i => $r) {
            $pid = (int) ($r['product_id'] ?? 0);

            if ($pid === 0 || !isset($deals[$pid])) {
                continue;
            }

            $deal = $deals[$pid];
            $base = (int) $r['price'];
            $original = $deal['flash_price'] > 0 ? $deal['flash_price'] : $base;
            $percent = $this->percentOf($deal, $base, $original);
            $final = $this->applyPercent($original, $percent);

            if ($base > 0 && $final < $base) {
                $cached['relatedProducts'][$i]['old_price'] = $original;
                $cached['relatedProducts'][$i]['price'] = $final;
                $cached['relatedProducts'][$i]['discount_percent'] = $percent;
            }
        }

        return $cached;
    }

    /** product_id đang có deal (dùng cho scope/UI "danh mục flash sale"). */
    public function productIdsWithDeals(): array
    {
        return array_keys($this->dealsByProduct());
    }

    /** category_id của các sản phẩm đang tham gia flash sale (product_id => category_id). */
    public function categoryIdsOfDeals(): array
    {
        $productIds = $this->productIdsWithDeals();

        if ($productIds === []) {
            return [];
        }

        return DB::table('products')
            ->whereIn('id', $productIds)
            ->pluck('category_id', 'id')
            ->map(static fn($v): int => (int) $v)
            ->all();
    }

    /** Xóa cache deal (gọi sau khi admin thêm/sửa/xóa promotion_products). */
    public function flush(): void
    {
        bump_group_version('catalog');
    }

    /** % giảm của deal; = 0 thì suy ra từ flash_price so với giá cơ sở. */
    private function percentOf(array $deal, int $basePrice, int $original): int
    {
        $percent = (int) ($deal['discount_percent'] ?? 0);

        if ($percent > 0) {
            return min(99, $percent);
        }

        if ($original <= 0 || $basePrice <= 0 || $original >= $basePrice) {
            return 0;
        }

        return max(0, min(99, (int) round((1 - $original / $basePrice) * 100)));
    }

    /** Công thức cốt lõi: giá bán = round(gốc * (100 - %) / 100). */
    private function applyPercent(int $originalPrice, int $percent): int
    {
        if ($originalPrice <= 0 || $percent <= 0) {
            return $originalPrice;
        }

        return (int) round($originalPrice * (100 - min(99, max(0, $percent))) / 100);
    }
}