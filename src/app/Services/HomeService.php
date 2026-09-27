<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Promotion;
use App\Models\PromotionProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Enums\ProductStatus;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HomeService
{
    public function __construct(
        private readonly CouponService $couponService
    ) {
    }

    /** Số danh mục nổi bật tối đa hiển thị ở block trang chủ */
    private const FEATURED_LIMIT = 8;

    /** TTL cache của block danh mục nổi bật (10 phút) */
    private const FEATURED_TTL = 600;

    /** Số sản phẩm flash sale tối đa hiển thị ở block trang chủ */
    private const FLASH_LIMIT = 8;

    /** TTL cache block flash sale (giây) – ngắn hơn vì deal đổi thường xuyên */
    private const FLASH_TTL = 300;

    /** Số mã giảm giá tối đa hiển thị ở block trang chủ */
    private const COUPONS_LIMIT = 8;

    /** TTL cache block mã giảm giá (10 phút) */
    private const COUPONS_TTL = 600;

    /** Số sản phẩm bán chạy tối đa hiển thị */
    private const BEST_SELLERS_LIMIT = 4;

    /** TTL cache block bán chạy (10 phút) */
    private const BEST_SELLERS_TTL = 600;

    /** Số sản phẩm Trà hoa thảo mộc tối đa hiển thị ở block trang chủ */
    private const HERBAL_TEA_LIMIT = 8;

    /** TTL cache block Trà hoa thảo mộc (10 phút) */
    private const HERBAL_TEA_TTL = 600;

    /**
     * Danh sách danh mục nổi bật kèm số sản phẩm đang bán.
     * - 1 query duy nhất nhờ withCount (subquery), tuyệt đối không N+1
     * - Cache theo nhóm "home" qua remember_group()
     *
     * @return array<int, array{id: int, name: string, slug: string, image: string, products_count: int, url: string}>
     */
    public function featuredCategories(): array
    {
        return remember_group('home', 'featured_categories', self::FEATURED_TTL, function (): array {
            return Category::query()
                ->where('status', 'active')
                ->where('is_featured', true)
                ->withCount([
                    // Đếm riêng sản phẩm đang active, alias thẳng vào attribute
                    'products as products_count' => fn(Builder $q): Builder => $q->active(),
                ])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(self::FEATURED_LIMIT)
                ->get(['id', 'name', 'slug', 'icon', 'sort_order'])
                ->map(fn(Category $category): array => [
                    'id' => (int) $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => $category->icon
                        ? asset('assets/images/categories/' . $category->icon)
                        : asset('assets/images/category-default.svg'),
                    'products_count' => (int) $category->products_count,
                    'url' => route('web.category.show', ['slug' => $category->slug]),
                ])
                ->all();
        });
    }

    /**
     * Block flash sale "Giá siêu hời" đang chạy.
     * - Chọn promotion flash_sale active + trong khung giờ
     * - Eager load product + coverImage + defaultVariant (không N+1)
     * - Cột select của relation ofMany phải định danh tên bảng (tránh lỗi 1052)
     * - Trả về null nếu không có deal => view tự ẩn block
     *
     * @return array{promotion_id: int, promotion_name: string, ends_at: string, ends_at_unix: int, items: array<int, array<string, mixed>>}|null
     */
    public function flashSale(): ?array
    {
        return remember_group('home', 'flash_sale', self::FLASH_TTL, function (): ?array {
            /** @var Promotion|null $promotion */
            $promotion = Promotion::query()
                ->where('type', 'flash_sale')
                ->where('status', 'active')
                ->where('start_at', '<=', now())
                ->where('end_at', '>', now())
                ->orderBy('end_at')
                ->first(['id', 'name', 'end_at']);

            if ($promotion === null) {
                return null;
            }

            $items = PromotionProduct::query()
                ->where('promotion_id', $promotion->id)
                ->with([
                    'product' => function (Relation $query): void {
                        $query
                            ->where('status', 'active')
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
                                'defaultVariant' => function (Relation $q): void {
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
                ->limit(self::FLASH_LIMIT)
                ->get(['id', 'product_id', 'product_variant_id', 'flash_price', 'discount_percent', 'qty_total', 'qty_sold', 'sort_order'])
                ->filter(fn(PromotionProduct $pp): bool => $pp->product !== null
                    && ($pp->product_variant_id !== null || $pp->product->defaultVariant !== null))
                ->map(fn(PromotionProduct $pp): array => $this->mapFlashItem($pp))
                ->values()
                ->all();

            if ($items === []) {
                return null;
            }
            return [
                'promotion_id' => (int) $promotion->id,
                'promotion_name' => $promotion->name,
                'ends_at' => $promotion->end_at->toIso8601String(),
                'ends_at_unix' => $promotion->end_at->getTimestamp(),
                'items' => $items,
            ];
        });
    }

    /**
     * Map 1 dòng promotion_products sang mảng hiển thị cho view
     *
     * @return array<string, mixed>
     */
    private function mapFlashItem(PromotionProduct $pp): array
    {
        $product = $pp->product;

        // % giảm: ưu tiên giá trị admin nhập, thiếu thì tự tính từ giá gốc
        $discount = $pp->discount_percent > 0
            ? (int) $pp->discount_percent
            : (int) round((1 - $pp->flash_price / max(1, (int) $product->price_min)) * 100);
        $imageUrl = $product->coverImage
            ? asset('assets/images/' . $product->coverImage->path)
            : asset('images/product-default.svg');

        return [
            'id' => (int) $pp->id,
            'product_id' => (int) $product->id,
            'variant_id' => (int) ($pp->product_variant_id ?? $product->defaultVariant?->id),
            'name' => $product->name,
            'url' => route('web.product.show', ['slug' => $product->slug]),
            'image' => $imageUrl,
            'rating_avg' => number_format((float) $product->rating_avg, 1, '.', ''),
            // 'sold_text' => format_number_compact((int) $product->sold_count),
            'sold_text' => (int) $product->sold_count,
            'discount_percent' => $discount,
            'flash_price_formatted' => format_vnd((int) $pp->flash_price),
            'original_price_formatted' => format_vnd((int) $product->price_min),
            'slots_left' => $pp->slotsLeft(),
            'sold_percent' => $pp->soldPercent(),
        ];
    }

    /**
     * Block mã giảm giá trang chủ: lấy coupon active thật từ bảng coupons.
     * - Delegate cho CouponService::getPublicCoupons() (mảng thuần, không object)
     * - Cache nhóm "home" qua remember_group(); bump bằng thaomoc:bump-cache home
     * - Trả [] khi không có mã => component tự ẩn cả section
     *
     * @return array<int, array{code: string, desc: string, minOrder: string, exp: string}>
     */
    public function homeCoupons(): array
    {
        return remember_group('home', 'coupons', self::COUPONS_TTL, function (): array {
            return $this->couponService->getPublicCoupons(self::COUPONS_LIMIT);
        });
    }

    /**
     * Khối "Bán chạy tuần này": top sản phẩm active sắp theo sold_count.
     * - 1 query chính + 1 eager load ảnh bìa (ofMany định danh bảng)
     * - Cache array thuần (không object) để tránh lỗi unserialize
     *
     * @return array<int, array{id: int, name: string, url: string, image: string, rating_avg: string, sold_count: int, price: int, old_price: int|null, discount_percent: int, product_id: int, variant_id: int}>
     */
    public function bestSellers(): array
    {
        return remember_group('home', 'best_sellers', self::BEST_SELLERS_TTL, function (): array {
            $products = Product::query()
                ->select(['id', 'slug', 'name', 'price_min', 'compare_price', 'sold_count', 'rating_avg'])
                ->where('status', ProductStatus::ACTIVE->value)
                ->with([
                    'coverImage' => function (HasOne $query): void {
                        $query->select([
                            'product_images.id',
                            'product_images.product_id',
                            'product_images.path',
                            'product_images.thumb_path',
                        ]);
                    },
                    'defaultVariant' => function (Relation $query): void {
                        $query->select([
                            'product_variants.id',
                            'product_variants.product_id',
                            'product_variants.price',
                            'product_variants.stock',
                        ]);
                    }
                ])
                ->orderByDesc('sold_count')
                ->limit(self::BEST_SELLERS_LIMIT)
                ->get();

            return $products->map(static function (Product $p): array {
                $price = (int) $p->price_min;
                $oldPrice = $p->compare_price !== null && (int) $p->compare_price > $price
                    ? (int) $p->compare_price
                    : null;
                $discount = $oldPrice !== null
                    ? (int) round((($oldPrice - $price) / max(1, $oldPrice)) * 100)
                    : 0;

                return [
                    'id' => (int) $p->id,
                    'product_id' => (int) $p->id,
                    'variant_id' => (int) ($p->defaultVariant?->id ?? 0),
                    'name' => $p->name,
                    'url' => route('web.product.show', $p->slug),
                    'image' => $p->coverImage?->thumb_path ?? ($p->coverImage ? asset('assets/images/' . $p->coverImage->path) : asset('images/product-default.svg')),
                    'rating_avg' => number_format((float) $p->rating_avg, 1, '.', ''),
                    'sold_count' => (int) $p->sold_count,
                    'flash_price' => format_vnd((int) $p->flash_price),
                    'price' => format_vnd((int) $p->price_min),
                    'old_price' => format_vnd((int)$oldPrice),
                    'discount_percent' => $discount,
                ];
            })->filter(fn($item) => $item['variant_id'] > 0)->values()->all();
        });
    }

    /**
     * Khối "Trà hoa thảo mộc": sản phẩm thuộc danh mục Trà hoa thảo mộc.
     * - Lọc theo category slug 'tra-hoa-thao-moc'
     * - 1 query chính + eager load ảnh bìa và biến thể mặc định
     * - Cache array thuần (không object)
     *
     * @return array<int, array<string, mixed>>
     */
    public function herbalTea(): array
    {
        return remember_group('home', 'herbal_tea', self::HERBAL_TEA_TTL, function (): array {
            $category = Category::query()->where('slug', 'tra-hoa-thao-moc')->first(['id']);

            if ($category === null) {
                return [];
            }

            $products = Product::query()
                ->select(['id', 'slug', 'name', 'price_min', 'compare_price', 'sold_count', 'rating_avg'])
                ->where('status', ProductStatus::ACTIVE->value)
                ->where('category_id', $category->id)
                ->with([
                    'coverImage' => function (HasOne $query): void {
                        $query->select([
                            'product_images.id',
                            'product_images.product_id',
                            'product_images.path',
                            'product_images.thumb_path',
                        ]);
                    },
                    'defaultVariant' => function (Relation $query): void {
                        $query->select([
                            'product_variants.id',
                            'product_variants.product_id',
                            'product_variants.price',
                            'product_variants.stock',
                        ]);
                    }
                ])
                ->orderByDesc('sold_count')
                ->limit(self::HERBAL_TEA_LIMIT)
                ->get();

            return $products->map(static function (Product $p): array {
                $price = (int) $p->price_min;
                $oldPrice = $p->compare_price !== null && (int) $p->compare_price > $price
                    ? (int) $p->compare_price
                    : null;
                $discount = $oldPrice !== null
                    ? (int) round((($oldPrice - $price) / max(1, $oldPrice)) * 100)
                    : 0;

                return [
                    'id' => (int) $p->id,
                    'product_id' => (int) $p->id,
                    'variant_id' => (int) ($p->defaultVariant?->id ?? 0),
                    'name' => $p->name,
                    'url' => route('web.product.show', $p->slug),
                    'image' => $p->coverImage?->thumb_path ?? ($p->coverImage ? asset('assets/images/' . $p->coverImage->path) : asset('images/product-default.svg')),
                    'rating_avg' => number_format((float) $p->rating_avg, 1, '.', ''),
                    'sold_count' => (int) $p->sold_count,
                    'price' => format_vnd((int) $p->price_min),
                    'old_price' => format_vnd($oldPrice),
                    'discount_percent' => $discount,
                ];
            })->filter(fn($item) => $item['variant_id'] > 0)->values()->all();
        });
    }
}