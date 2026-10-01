<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PromotionProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * HomeService — các block trang chủ (danh mục nổi bật, flash sale, coupon,
 * bán chạy, trà hoa). Ngưỡng/số lượng fix cứng đã tách sang config/home.php;
 * public API và kết quả giữ nguyên 100%.
 */
class HomeService
{
    public function __construct(
        private readonly CouponService $couponService
    ) {
    }

    /**
     * Danh sách danh mục nổi bật kèm số sản phẩm đang bán.
     * - 1 query duy nhất nhờ withCount (subquery), tuyệt đối không N+1
     * - Cache theo nhóm "home" qua remember_group()
     *
     * @return array<int, array{id: int, name: string, slug: string, image: string, products_count: int, url: string}>
     */
    public function featuredCategories(): array
    {
        return remember_group('home', 'featured_categories', $this->ttl('featured_categories'), function (): array {
            return Category::query()
                ->where('status', 'active')
                ->where('is_featured', true)
                ->withCount([
                    // Đếm riêng sản phẩm đang active, alias thẳng vào attribute
                    'products as products_count' => fn(Builder $q): Builder => $q->active(),
                ])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit($this->limit('featured_categories'))
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
     * @return array{promotion_id: int, promotion_name: string, ends_at: string, ends_at_unix: int, sold_today: int, urgent_count: int, items: array<int, array<string, mixed>>}|null
     */
    public function flashSale(): ?array
    {
        return remember_group('home', 'flash_sale', $this->ttl('flash_sale'), function (): ?array {
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
                ->orderBy('sort_order') // giữ đúng thứ tự sort_order admin cấu hình trong DB
                ->orderBy('id')
                ->limit($this->limit('flash_sale'))
                ->get(['id', 'product_id', 'product_variant_id', 'flash_price', 'discount_percent', 'qty_total', 'qty_sold', 'sort_order'])
                ->filter(fn(PromotionProduct $pp): bool => $pp->product !== null
                    && ($pp->product_variant_id !== null || $pp->product->defaultVariant !== null))
                ->map(fn(PromotionProduct $pp): array => $this->mapFlashItem($pp))
                ->values()
                ->all();

            if ($items === []) {
                return null;
            }

            // Tổng hợp nhanh cho header section: lượt bán hôm nay + số deal sắp cháy
            $soldToday = array_sum(array_column($items, 'qty_sold'));
            $urgentCount = count(array_filter($items, static fn(array $i): bool => $i['is_urgent']));

            return [
                'promotion_id' => (int) $promotion->id,
                'promotion_name' => $promotion->name,
                'ends_at' => $promotion->end_at->toIso8601String(),
                'ends_at_unix' => $promotion->end_at->getTimestamp(),
                'sold_today' => $soldToday,
                'urgent_count' => $urgentCount,
                'items' => $items,
            ];
        });
    }

    /**
     * Chọn text "kích thích mua hàng" gắn trên đầu card deal.
     * - Ưu tiên cảnh báo khan hiếm khi deal đạt ngưỡng đã bán / còn ít slot
     * - Chưa đạt ngưỡng thì fallback theo % giảm sâu -> bán chạy -> deal hot,
     *   đảm bảo card nào cũng có 1 dòng hook thay vì để trống
     *
     * @return array{text: string, tone: string} tone: hot|deep|sell|new dùng cho class màu badge
     */
    private function flashHookText(int $discount, int $soldPercent, int $slotsLeft, int $qtySold): array
    {
        $hooks = (array) config('home.flash_hooks');

        // 1) Sắp cháy hàng: đã bán >= ngưỡng % hoặc còn rất ít slot
        if ($soldPercent >= (int) $hooks['urgent_percent'] || ($slotsLeft > 0 && $slotsLeft <= (int) $hooks['urgent_slots'])) {
            return ['text' => 'Sắp cháy hàng 🔥', 'tone' => 'hot'];
        }

        // 2) Giảm giá sâu: % giảm >= ngưỡng
        if ($discount >= (int) $hooks['deep_discount_percent']) {
            return ['text' => 'Giảm giá sâu -' . $discount . '%', 'tone' => 'deep'];
        }

        // 3) Bán chạy: lượt bán của deal >= ngưỡng
        if ($qtySold >= (int) $hooks['hot_sold_count']) {
            return ['text' => 'Bán chạy ⚡', 'tone' => 'sell'];
        }

        // 4) Dự phòng: luôn có text, không bao giờ trả rỗng
        return ['text' => 'Deal hot hôm nay', 'tone' => 'new'];
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

        $discount = max(0, min(99, $discount));
        $slotsLeft = $pp->slotsLeft();
        $soldPercent = $pp->soldPercent();
        $qtySold = (int) $pp->qty_sold;

        // Text hook trên đầu card + tone màu (badge luôn có, không phụ thuộc điều kiện urgent)
        $hook = $this->flashHookText($discount, $soldPercent, $slotsLeft, $qtySold);
        $isUrgent = $hook['tone'] === 'hot';

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
            'slots_left' => $slotsLeft,
            'sold_percent' => $soldPercent,

            // --- Mắt xích UI flash sale percent (text ví dụ + thanh tiến độ) ---
            'qty_sold' => $qtySold,                                              // lượt bán của deal
            'qty_total' => (int) $pp->qty_total,                                 // tổng slot
            'sold_text_today' => 'Đã bán ' . number_format($qtySold, 0, ',', '.') . ' sản phẩm hôm nay',
            'urgent_text' => $hook['text'],                                      // badge đầu card, luôn có text
            'urgent_tone' => $hook['tone'],                                      // hot|deep|sell|new -> chọn màu badge
            'progress_text' => $soldPercent . '% đã bán',                         // label nhỏ dưới thanh tiến độ
            'is_urgent' => $isUrgent,
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
        return remember_group('home', 'coupons', $this->ttl('coupons'), function (): array {
            return $this->couponService->getPublicCoupons($this->limit('coupons'));
        });
    }

    /**
     * Khối "Bán chạy tuần này": top sản phẩm active sắp theo sold_count.
     * - 1 query chính + 1 eager load ảnh bìa (ofMany định danh bảng)
     * - Cache array thuần (không object) để tránh lỗi unserialize
     *
     * @return array<int, array<string, mixed>>
     */
    public function bestSellers(): array
    {
        return remember_group('home', 'best_sellers', $this->ttl('best_sellers'), function (): array {
            $products = $this->homeProductQuery()
                ->orderByDesc('sold_count')
                ->limit($this->limit('best_sellers'))
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
                    'old_price' => format_vnd((int) $oldPrice),
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
        return remember_group('home', 'herbal_tea', $this->ttl('herbal_tea'), function (): array {
            $category = Category::query()->where('slug', 'tra-hoa-thao-moc')->first(['id']);

            if ($category === null) {
                return [];
            }

            $products = $this->homeProductQuery()
                ->where('category_id', $category->id)
                ->orderByDesc('sold_count')
                ->limit($this->limit('herbal_tea'))
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

    /**
     * Query sản phẩm dùng chung 2 block listing trang chủ (bán chạy + trà hoa):
     * active + eager coverImage/defaultVariant với cột định danh bảng.
     */
    private function homeProductQuery(): Builder
    {
        return Product::query()
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
            ]);
    }

    /** Số item tối đa 1 block (config/home.php → limits). */
    private function limit(string $block): int
    {
        return (int) config('home.limits.' . $block);
    }

    /** TTL cache 1 block (config/home.php → ttl). */
    private function ttl(string $block): int
    {
        return (int) config('home.ttl.' . $block);
    }
}