<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Promotion;
use App\Models\PromotionProduct;
use App\Services\Promotion\FlashSalePriceService;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * FlashSaleRenderService — tầng dữ liệu Flash Sale cho cơ chế RENDER MỚI:
 * server chỉ xuất "khung" (Blade), JS tự gọi route web.flash-sale.data
 * MỖI 3 PHÚT một lần để lấy dữ liệu section home + block PDP.
 *
 * Tái sử dụng tối đa logic cũ của HomeService (giá, hook text, tiến độ slot)
 * bằng cách gọi mapFlashItem() qua Closure::bind — KHÔNG refactor HomeService.
 *
 * Nguồn sự thật giá vẫn là FlashSalePriceService (flash_price = giá gốc deal,
 * discount_percent áp trên flash_price). Cache nhóm 'catalog' TTL 300s nên mỗi
 * lần JS poll (180s) nhiều vòng sẽ lấy dữ liệu mới sau TTL — đúng ý "3 phút cập nhật 1 lần".
 */
class FlashSaleRenderService
{
    public function __construct(
        private readonly HomeService $homeService,
        private readonly FlashSalePriceService $flashPricing,
    ) {
    }

    /**
     * Payload section FLASH SALE trang chủ (JS render lại rail + head + stats).
     *
     * @return array{active: bool, promotion_name: string, ends_at_unix: int, sold_today: int, urgent_count: int, items: array<int, array<string, mixed>>}
     */
    public function homePayload(): array
    {
        /** @var Promotion|null $promotion */
        $promotion = Promotion::query()
            ->where('type', 'flash_sale')
            ->where('status', 'active')
            ->where('start_at', '<=', now())
            ->orderBy('end_at')
            ->first(['id']);

        if ($promotion === null) {
            return ['active' => false, 'items' => []];
        }

        // Eager load y hệt HomeService::flashSale() để mapFlashItem() chạy đúng
        $products = PromotionProduct::query()
            ->where('promotion_id', $promotion->id)
            ->with([
                'product' => function (Relation $query): void {
                    $query
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
            ->limit((int) config('home.limits.flash_sale', 8))
            ->get(['id', 'product_id', 'product_variant_id', 'flash_price', 'discount_percent', 'qty_total', 'qty_sold', 'sort_order'])
            ->filter(fn(PromotionProduct $pp): bool => $pp->product !== null
                && ($pp->product_variant_id !== null || $pp->product->defaultVariant !== null));

        // Gọi hàm riêng mapFlashItem() của HomeService mà không sửa file đó
        $mapItem = \Closure::bind(
            fn(PromotionProduct $pp): array => $this->mapFlashItem($pp),
            $this->homeService,
            HomeService::class
        );

        $items = $products->map(fn(PromotionProduct $pp): array => $mapItem($pp))->values()->all();

        if ($items === []) {
            return ['active' => false, 'items' => []];
        }

        $meta = $this->flashPricing->currentPromotionMeta();

        return [
            'active' => true,
            'promotion_name' => (string) ($meta['promotion_name'] ?? 'Flash Sale'),
            'ends_at_unix' => (int) ($meta['ends_at_unix'] ?? 0),
            'sold_today' => array_sum(array_column($items, 'qty_sold')),
            'urgent_count' => count(array_filter($items, static fn(array $i): bool => (bool) $i['is_urgent'])),
            'items' => $items,
        ];
    }

    /**
     * Payload block Flash Sale trên PDP — active=false khi SP không thuộc deal
     * (JS khi đó ẩn hẳn .pd-flash, đồng hành vi Blade @if($fs) cũ).
     *
     * @return array{active: bool, name?: string, ends_at_unix?: int, discount_percent?: int, saved_amount?: int, slots_left?: int, sold_percent?: int, price?: int, original_price?: int}
     */
    public function pdpPayload(int $productId): array
    {
        $block = $this->flashPricing->pdpBlockFor($productId);

        if ($block === null) {
            return ['active' => false];
        }

        // Giá sau giảm để JS cập nhật .pd-price (đồng bộ với applyToDetailArray)
        $original = (int) ($block['saved_amount'] ?? 0);
        $deal = $this->flashPricing->dealsByProduct()[$productId] ?? [];
        $flashPrice = (int) ($deal['flash_price'] ?? 0);
        $percent = (int) ($block['discount_percent'] ?? 0);
        $final = (int) round($flashPrice * (100 - $percent) / 100);

        return [
            'active' => true,
            'name' => $block['name'],
            'ends_at_unix' => $block['ends_at_unix'],
            'is_ended' => $block['is_ended'],
            'discount_percent' => $percent,
            'saved_amount' => $block['saved_amount'],
            'slots_left' => $block['slots_left'],
            'sold_percent' => $block['sold_percent'],
            'price' => $final,
            'original_price' => $flashPrice,
        ];
    }
}