<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\PromotionProduct;
use App\Services\Promotion\FlashSalePriceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * HomeService — các block trang chủ (danh mục nổi bật, flash sale, coupon,
 * bán chạy, trà hoa). Ngưỡng/số lượng fix cứng đã tách sang config/home.php;
 * public API và kết quả giữ nguyên 100%.
 *
 * FIX GIÁ: block flash sale và 2 block listing đều đi qua
 * FlashSalePriceService => giá bán = flash_price sau khi trừ discount_percent.
 */
class HomeService
{
    public function __construct(
        private readonly CouponService $couponService,
        private readonly FlashSalePriceService $flashPricing
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
     * Block flash sale trang chủ.
     * - Chọn promotion flash_sale active + trong khung giờ
     * - Eager load product + coverImage + defaultVariant (không N+1)
     * - Cột select của relation ofMany phải định danh tên bảng (tránh lỗi 1052)
     * - GIÁ BÁN = flash_price sau khi áp discount_percent
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
                ->first(['id', 'end_at']); // name + end_at unix lấy fresh từ currentPromotionMeta() bên dưới

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

            /*
             * FIX ĐỒNG BỘ HOME <-> PDP:
             * metadata phiên (ends_at/ends_at_unix/promotion_name) KHÔNG lấy từ bản
             * query riêng trong block cache 'home' (TTL 5') nữa — vì admin sửa end_at
             * thì home hiển thị mốc cũ trong khi PDP đã đổi mốc mới. Nay đọc qua
             * FlashSalePriceService::currentPromotionMeta() (cache nhóm 'catalog',
             * CÙNG điều kiện chọn phiên với PDP: type=flash_sale, status=active,
             * start_at<=now<end_at, ưu tiên end_at gần nhất) => 2 màn luôn dùng
             * chung một mốc kết thúc. Danh sách items vẫn cache 'home' như cũ.
             */
            $meta = $this->flashPricing->currentPromotionMeta();

            // Hết phiên giữa lúc còn bản cache 'home' cũ => coi như không có deal
            if ($meta === null || $meta['promotion_id'] !== (int) $promotion->id) {
                return null;
            }

            return [
                'promotion_id' => (int) $promotion->id,
                'promotion_name' => $meta['promotion_name'],
                'ends_at' => $promotion->end_at->toIso8601String(),
                'ends_at_unix' => $meta['ends_at_unix'],
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
     * Map 1 dòng promotion_products sang mảng hiển thị cho view.
     *
     * FIX LOGIC GIÁ: flash_price là GIÁ GỐC của deal; giá bán bắt buộc là
     * flash_price sau khi trừ discount_percent (trước đây đưa raw flash_price
     * ra UI nên thẻ không hề giảm theo %).
     *
     * @return array<string, mixed>
     */
    private function mapFlashItem(PromotionProduct $pp): array
    {
        $product = $pp->product;

        // Giá niêm yết hiện hành (default variant, fallback price_min)
        $basePrice = $product->defaultVariant !== null
            ? (int) $product->defaultVariant->price
            : (int) $product->price_min;

        // % giảm: BẮT BUỘC dùng discount_percent admin nhập; chỉ suy ra khi = 0
        $discount = (int) $pp->discount_percent > 0
            ? (int) $pp->discount_percent
            : (int) round((1 - (int) $pp->flash_price / max(1, $basePrice)) * 100);

        $discount = max(0, min(99, $discount));

        // Giá gốc của deal = flash_price (fallback giá niêm yết nếu data thiếu)
        $originalPrice = (int) $pp->flash_price > 0 ? (int) $pp->flash_price : $basePrice;

        // GIÁ BÁN SAU GIẢM theo discount_percent
        $finalPrice = (int) round($originalPrice * (100 - $discount) / 100);

        // Không để giá sale vượt giá niêm yết
        if ($basePrice > 0 && $finalPrice > $basePrice) {
            $finalPrice = $basePrice;
        }

        $imageUrl = $product->coverImage
            ? asset('assets/images/' . $product->coverImage->path)
            : asset('images/product-default.svg');

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
            // --- Số RAW (đã quy đổi theo discount_percent) ---
            'flash_price' => $finalPrice,
            'original_price' => $originalPrice,
            'saved_amount' => max(0, $originalPrice - $finalPrice),
            // --- Chuỗi format cho view ---
            'flash_price_formatted' => format_vnd($finalPrice),
            'original_price_formatted' => format_vnd($originalPrice),
            'saved_amount_formatted' => format_vnd(max(0, $originalPrice - $finalPrice)),
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
     * - FIX: bỏ `$p->flash_price` (bảng products KHÔNG có cột này) — giá deal
     *   lấy qua FlashSalePriceService::priceFor()
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

            return $products
                ->map(fn(Product $p): array => $this->buildListingItem($p))
                ->filter(static fn(array $item): bool => $item['variant_id'] > 0)
                ->values()
                ->all();
        });
    }

    /**
     * Khối "Trà hoa thảo mộc": sản phẩm thuộc danh mục Trà hoa thảo mộc.
     * - Lọc theo category slug 'tra-hoa-thao-moc'
     * - 1 query chính + eager load ảnh bìa và biến thể mặc định
     * - FIX: dùng chung buildListingItem() => giá flash sale áp thống nhất
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

            return $products
                ->map(fn(Product $p): array => $this->buildListingItem($p))
                ->filter(static fn(array $item): bool => $item['variant_id'] > 0)
                ->values()
                ->all();
        });
    }

    /**
     * Map 1 Product (đã eager coverImage/defaultVariant) sang item listing,
     * CÓ áp giá flash sale nếu sản phẩm đang tham gia deal.
     *
     * @return array<string, mixed>
     */
    private function buildListingItem(Product $p): array
    {
        $variantId = (int) ($p->defaultVariant?->id ?? 0);
        $price = $p->defaultVariant !== null ? (int) $p->defaultVariant->price : (int) $p->price_min;

        $oldPrice = $p->compare_price !== null && (int) $p->compare_price > $price
            ? (int) $p->compare_price
            : null;
        $discount = $oldPrice !== null
            ? (int) round((($oldPrice - $price) / max(1, $oldPrice)) * 100)
            : 0;

        // Flash sale ghi đè giá niêm yết (đồng bộ % với block flash + PDP + giỏ hàng)
        $flash = $this->flashPricing->priceFor((int) $p->id, $variantId > 0 ? $variantId : null, $price, $price);

        if ($flash !== null) {
            $price = $flash['price'];
            $oldPrice = $flash['original_price'];
            $discount = $flash['discount_percent'];
        }

        return [
            'id' => (int) $p->id,
            'product_id' => (int) $p->id,
            'variant_id' => $variantId,
            'name' => $p->name,
            'url' => route('web.product.show', $p->slug),
            'image' => $p->coverImage?->thumb_path
                ?? ($p->coverImage ? asset('assets/images/' . $p->coverImage->path) : asset('images/product-default.svg')),
            'rating_avg' => number_format((float) $p->rating_avg, 1, '.', ''),
            'sold_count' => (int) $p->sold_count,
            'flash_price' => $flash !== null ? format_vnd($price) : null,
            'price' => format_vnd($price),
            'old_price' => $oldPrice !== null ? format_vnd($oldPrice) : null,
            'discount_percent' => $discount,
            'is_flash_sale' => $flash !== null,
        ];
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