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
     * FIX HIỂN THỊ CẢ KHI HẾT PHIÊN: bỏ điều kiện end_at > now() — promotion
     * status=active vẫn trả deal; trạng thái hết hạn do is_ended quyết (view).
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
                ->orderBy('end_at')
                ->first(['id']);

            if ($promotion === null) {
                return [];
            }

            return PromotionProduct::query()
                ->where('promotion_id', $promotion->id)
                ->orderBy('sort_order')
                ->get(['id', 'product_id', 'product_variant_id', 'flash_price', 'discount_percent', 'qty_total', 'qty_sold', 'per_user_limit'])
                ->map(static fn(PromotionProduct $pp): array => [
                    'product_id' => (int) $pp->product_id,
                    'variant_id' => $pp->product_variant_id !== null ? (int) $pp->product_variant_id : null,
                    'flash_price' => (int) $pp->flash_price,
                    'discount_percent' => (int) $pp->discount_percent,
                    'final_price' => 0,
                    'qty_total' => (int) $pp->qty_total,
                    'qty_sold' => (int) $pp->qty_sold,
                    'per_user_limit' => (int) $pp->per_user_limit,
                    'sort_order' => (int) $pp->sort_order,
                ])
                ->all();
        });
    }

    /**
     * Deal theo product_id (map nhanh cho listing/PDP).
     *
     * @return array<int, array<string, mixed>>
     */
    public function dealsByProduct(): array
    {
        $map = [];

        foreach ($this->currentDeals() as $deal) {
            $map[(int) $deal['product_id']] = $deal;
        }

        return $map;
    }

    /**
     * Deal của 1 sản phẩm (ưu tiên đúng variant nếu deal gắn variant).
     *
     * @return array<string, mixed>|null
     */
    public function dealFor(int $productId, ?int $variantId, bool $isDefaultVariant = true): ?array
    {
        $deal = $this->dealsByProduct()[$productId] ?? null;

        if ($deal === null) {
            return null;
        }

        // Deal gắn variant cụ thể mà sản phẩm đang chọn variant khác => bỏ qua
        if ($deal['variant_id'] !== null && $variantId !== null && (int) $deal['variant_id'] !== $variantId) {
            return null;
        }

        return $deal;
    }

    /**
     * Giá flash sale của 1 sản phẩm/variant — null nếu không có deal hợp lệ.
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
     * Metadata phiên flash_sale cho section FLASH SALE (home + PDP).
     *
     * FIX COUNTDOWN (yêu cầu mới): thời gian đếm NGƯỢC KHÔNG LẤY TỪ DATABASE nữa.
     *   - JS (public/assets/js/product/countdown.js) tự tính "giây còn lại tới
     *     nửa đêm 00:00:00" theo giờ máy người xem => luôn chạy 23:59:59 -> 00:00:00
     *     và lặp lại hằng ngày.
     *   - Vì vậy ở đây KHÔNG query end_at, KHÔNG clamp 24h, KHÔNG tính mốc theo now().
     *     ends_at_unix chỉ còn vai trò SEED ban đầu + giữ đúng contract Blade/JS
     *     (marker .countdown[data-ends] / [data-pd-flash] để app.js nạp module).
     *   - Giá trị seed = nửa đêm kế tiếp theo APP_TZ, chỉ để HTML không nhấp nháy
     *     "00:00:00" trước khi JS chạy; JS sẽ ghi đè bằng giờ máy người xem.
     *   - DB vẫn được đọc ĐÚNG 1 lần để biết "có phiên flash sale nào đang chạy
     *     hay không" (quyết định section/block có hiện) — không phục vụ phép tính giờ.
     *
     * @return array{promotion_id:int,promotion_name:string,ends_at_unix:int,is_ended:bool}|null
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
                ->orderBy('end_at')
                ->first(['id', 'name', 'end_at']);

            if ($promotion === null) {
                return null;
            }

            // LOGIC MỚI: mốc đếm = NỬA ĐÊM TIẾP THEO (00:00:00 ngày mai) theo APP_TZ,
            // không phụ thuộc end_at của phiên và không bị cache làm "đóng băng" số giờ.
            $midnightUnix = now()->timezone(config('app.timezone', 'UTC'))
                ->addDay()
                ->startOfDay()
                ->getTimestamp();

            return [
                'promotion_id' => (int) $promotion->id,
                'promotion_name' => (string) $promotion->name,
                'ends_at_unix' => $midnightUnix,
                'is_ended' => false, // countdown luôn là chu kỳ 24h/ngày => label "Kết thúc sau"
            ];
        });
    }

    /**
     * Block flash sale cho PDP — trả null khi sản phẩm KHÔNG thuộc deal đang
     * chạy (component x-product.flash-block tự ẩn => UI cũ không đổi).
     *
     * Lưu ý thiết kế: KHÔNG xuất số "ngày" — countdown PDP đếm GIỜ:PHÚT:GIÂY
     * giống hệt trang home (JS tự tính từ đồng hồ hiện tại tới nửa đêm).
     *
     * @return array{name:string,ends_at_unix:int,is_ended:bool,discount_percent:int,saved_amount:int,slots_left:int,sold_percent:int,per_user_limit:int}|null
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
            'is_ended' => $meta['is_ended'],
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
     * FIX AN TOÀN DATA MỚI: hàm này CHỈ ghi đè các key giá (price/old_price/
     * discount_percent) trên bản sao mảng — các key khác của $cached['product']
     * (đặc biệt là 'specs' vừa thêm cho khối "Thông số sản phẩm") được PHP copy
     * theo value semantics nên vẫn còn nguyên. Không cần sửa gì thêm, giữ comment
     * để ai sau đừng "tối ưu" bằng cách rebuild mảng product từ đầu.
     *
     * @param array<string, mixed> $cached
     * @return array<string, mixed>
     */
    public function applyToDetailArray(array $cached): array
    {
        $pricing = $this->priceFor(
            (int) $cached['product_id'],
            isset($cached['variant']['id']) ? (int) $cached['variant']['id'] : null,
            (int) ($cached['product']['price'] ?? 0),
            (int) ($cached['product']['old_price'] ?? 0)
        );

        if ($pricing !== null) {
            $cached['product']['price'] = $pricing['price'];
            $cached['product']['old_price'] = $pricing['original_price'];
            $cached['product']['discount_percent'] = $pricing['discount_percent'];
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