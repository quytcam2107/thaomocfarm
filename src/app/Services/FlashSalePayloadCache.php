<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Promotion;
use App\Models\PromotionProduct;
use App\Services\Promotion\FlashSalePriceService;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;
use Throwable;

/**
 * FlashSalePayloadCache — tầng CACHE FILE cho 2 endpoint JSON Flash Sale:
 *   GET /flash-sale                (web.flash-sale.home)
 *   GET /flash-sale/san-pham/{id}  (web.flash-sale.product)
 *
 * CHIẾN LƯỢC "cache-first, refresh-on-expire" (lưu 3 phút, client gọi lên
 * kiểm tra tới hạn thì refresh — đúng yêu cầu):
 *   - Payload JSON serialize sẵn, TTL = config('thaomoc.cache.flash_live.ttl', 180)
 *     (180s = 3 phút, khớp chu kỳ poll của public/assets/js/flash-live.js).
 *   - CÒN HẠN  => đọc thẳng file cache, 0 query DB => response < 100ms.
 *   - HẾT HẠN  => chính request này build lại từ DB và trả dữ liệu MỚI NHẤT,
 *     đồng thời ghi cache 3 phút cho các request sau; request ĐỒNG THỜI khác
 *     nhận bản @stale ngay lập tức (chống stampede, client không bao giờ chờ).
 *   - PDP cache theo TỪNG product_id vì qty_sold/slots_left đổi liên tục.
 *
 * BẪY: key gắn prefix version nhóm 'flashlive'. Driver file không hỗ trợ tags
 * => muốn xóa ngay khi admin sửa deal phải gọi clear() (bump version).
 */
class FlashSalePayloadCache
{
    /** Nhóm cache riêng của payload Flash Sale. */
    private const GROUP = 'flashlive';

    /** Key payload rail trang chủ — dùng chung cho mọi client. */
    private const KEY_HOME = 'flash_sale_home_payload';

    /** Tiền tố key payload 1 sản phẩm trên PDP. */
    private const KEY_PDP_PREFIX = 'flash_sale_pdp_';

    public function __construct(
        private readonly HomeService $homeService,
        private readonly FlashSalePriceService $flashPricing,
    ) {
    }

    /** JSON payload section FLASH SALE trang chủ (đã cache file) + cờ trạng thái. */
    public function homeJson(): string
    {
        return $this->remember(self::KEY_HOME, fn(): array => $this->buildHome());
    }

    /** JSON payload block flash sale của 1 sản phẩm trên PDP (đã cache file). */
    public function pdpJson(int $productId): string
    {
        return $this->remember(
            self::KEY_PDP_PREFIX . $productId,
            fn(): array => $this->buildPdp($productId)
        );
    }

    /** Trạng thái lần đọc gần nhất: HIT / REFRESH / STALE (phục vụ header debug). */
    public function lastState(): string
    {
        return $this->state;
    }

    private string $state = 'HIT';

    /**
     * Xóa toàn bộ payload Flash Sale (rail home + mọi PDP đã cache).
     * Gọi sau khi admin thêm/sửa/xóa promotion_products để JS poll lần kế
     * lấy ngay dữ liệu mới thay vì chờ hết TTL 3 phút.
     */
    public function clear(): void
    {
        bump_group_version(self::GROUP); // mọi key v{n}:... cũ vô hiệu tức thì
    }

    /* ============================ LÕI CACHE ============================ */

    /**
     * Đọc payload từ cache file; hết hạn thì chính request này rebuild
     * (refresh-on-expire), request đồng thời nhận bản stale để không chặn UI.
     */
    private function remember(string $key, callable $builder): string
    {
        $ttl = (int) config('thaomoc.cache.flash_live.ttl', 180);

        try {
            // 1) Cache còn hạn -> chỉ 2 phép đọc file (version + payload) => <100ms
            $cached = remember_group_shared(self::GROUP, $key);

            if ($cached['hit'] === true && is_string($cached['raw'])) {
                $this->state = 'HIT';

                return $cached['raw'];
            }

            if ($cached['stale'] === true && is_string($cached['raw'])) {
                // Có request khác đang rebuild -> trả bản cũ ngay, không đánh DB lần 2
                $this->state = 'STALE';

                return $cached['raw'];
            }

            // 2) Hết hạn / cold start -> build từ DB (nhánh chậm ~500ms, tối đa
            //    1 lần / 3 phút cho mỗi key nhờ marker chống stampede)
            $this->state = 'REFRESH';
            $payload = $this->encode($builder);
            put_group_shared(self::GROUP, $key, $payload, $ttl);

            return $payload;
        } catch (Throwable) {
            // Cache/DB lỗi đột xuất: build trực tiếp lần cuối; vẫn lỗi -> payload rỗng
            // đúng contract để JS ẩn section thay vì báo đỏ.
            try {
                return $this->encode($builder);
            } catch (Throwable) {
                return '{"active":false,"items":[]}';
            }
        }
    }

    /** Encode an toàn nhúng HTML (contract cardHtml/renderHome của flash-live.js). */
    private function encode(callable $builder): string
    {
        $payload = json_encode(
            $builder(),
            JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );

        return is_string($payload) ? $payload : '{"active":false,"items":[]}';
    }

    /* =========================== BUILDERS =========================== */

    /**
     * Rail home: TÁI SỬ DỤNG nguyên logic HomeService::flashSale() (query +
     * mapFlashItem + hook text) — ăn theo cache nhóm 'home' TTL 300s sẵn có,
     * normalize về đúng contract payload cũ của FlashSaleRenderService.
     *
     * @return array<string, mixed>
     */
    private function buildHome(): array
    {
        $block = $this->homeService->flashSale();

        if ($block === null || ($block['items'] ?? []) === []) {
            return ['active' => false, 'items' => []];
        }

        return [
            'active' => true,
            'promotion_name' => (string) ($block['promotion_name'] ?? 'Flash Sale'),
            'ends_at_unix' => (int) ($block['ends_at_unix'] ?? 0),
            'sold_today' => (int) ($block['sold_today'] ?? 0),
            'urgent_count' => (int) ($block['urgent_count'] ?? 0),
            'items' => $block['items'],
        ];
    }

    /**
     * PDP: giữ NGUYÊN công thức giá của FlashSalePriceService (nguồn sự thật
     * duy nhất) bằng currentPromotionMeta() (cache 'catalog'); deal lấy qua
     * dealsByProduct() (cùng cache) — không query lặp khi refresh.
     *
     * @return array<string, mixed>
     */
    private function buildPdp(int $productId): array
    {
        $meta = $this->flashPricing->currentPromotionMeta();
        $deal = $this->flashPricing->dealsByProduct()[$productId] ?? null;

        if ($meta === null || $deal === null) {
            return ['active' => false];
        }

        /** @var PromotionProduct|null $pp */
        $pp = PromotionProduct::query()
            ->where('promotion_id', (int) $meta['promotion_id'])
            ->where('product_id', $productId)
            ->with([
                // defaultVariant bắt buộc cho mapFlashItem() (giá niêm yết + variant_id)
                'product' => function (Relation $query) use ($productId): void {
                    $query
                        ->where('id', $productId)
                        ->where('status', ProductStatus::ACTIVE->value)
                        ->select(['id', 'name', 'slug', 'price_min', 'rating_avg', 'rating_count', 'sold_count'])
                        ->with([
                            'coverImage' => function (Relation $q): void {
                                $q->select([
                                    'product_images.id',
                                    'product_images.product_id',
                                    'product_images.path',
                                    'product_images.alt',
                                ]);
                            },
                            'defaultVariant' => function (HasOne $q): void {
                                $q->select([
                                    'product_variants.id',
                                    'product_variants.product_id',
                                    'product_variants.price',
                                ]);
                            },
                        ]);
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first(['id', 'product_id', 'product_variant_id', 'flash_price', 'discount_percent', 'qty_total', 'qty_sold', 'sort_order']);

        if ($pp === null || $pp->product === null) {
            return ['active' => false];
        }

        // Map qua hàm riêng của HomeService (Closure::bind) — đồng nhất card home/PDP
        $mapItem = \Closure::bind(
            fn(PromotionProduct $item): array => $this->mapFlashItem($item),
            $this->homeService,
            HomeService::class
        );

        $item = $mapItem($pp);

        return [
            'active' => true,
            'name' => (string) $meta['promotion_name'],
            'ends_at_unix' => (int) $meta['ends_at_unix'],
            'is_ended' => (bool) ($meta['is_ended'] ?? false),
            'discount_percent' => (int) ($item['discount_percent'] ?? 0),
            'saved_amount' => (int) ($item['saved_amount'] ?? 0),
            'slots_left' => (int) ($item['slots_left'] ?? 0),
            'sold_percent' => (int) ($item['sold_percent'] ?? 0),
            'price' => (int) ($item['flash_price'] ?? 0),          // giá bán sau giảm
            'original_price' => (int) ($item['original_price'] ?? 0), // flash_price = giá gạch ngang
        ];
    }
}