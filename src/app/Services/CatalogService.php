<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\CategoryViewDTO;
use App\DTOs\ProductViewDTO;
use App\DTOs\RelatedProductDTO;
use App\DTOs\ReviewViewDTO;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Enums\ProductStatus;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class CatalogService
{
    public const CATEGORY_PER_PAGE = 12;

    /** Các lựa chọn sắp xếp hợp lệ ở trang danh mục. */
    public const SORT_OPTIONS = [
        'bestsell' => 'Bán chạy nhất',
        'newest' => 'Mới nhất',
        'price_asc' => 'Giá thấp → cao',
        'price_desc' => 'Giá cao → thấp',
    ];

    /** Các khoảng giá lọc được (đơn vị VND, max = null nghĩa là không chặn trên). */
    public const PRICE_RANGES = [
        ['key' => '0-100', 'min' => 0, 'max' => 100000],
        ['key' => '100-250', 'min' => 100000, 'max' => 250000],
        ['key' => '250-', 'min' => 250000, 'max' => null],
    ];

    /** Nội dung SEO text cố định theo slug danh mục (h2, đoạn văn, h3, danh sách mẹo). */
    public const CATEGORY_SEO_TEXT = [
        'thao-moc' => [
            'heading' => 'Thảo mộc Tây Bắc có gì đặc biệt?',
            'paragraph' => 'Tam thất, giảo cổ lam, táo đỏ… được trồng trên đồi núi cao Hà Giang, Lai Châu, sương mù quanh năm, thổ nhưỡng giàu khoáng nên dược chất tích tụ đậm hơn vùng xuôi. Thảo mộc thu hái đúng mùa, sơ chế thủ công, phơi khô tự nhiên, không tẩm sulphur, không chất bảo quản.',
            'subheading' => 'Chọn thảo mộc chuẩn',
            'tips' => [
                'Củ và quả khô ráo, màu tự nhiên, không mốc trắng hay đốm lạ.',
                'Mùi thơm dịu đặc trưng, không hắc mùi hoá chất.',
                'Đóng gói hút ẩm kín, có nhãn vùng trồng và hạn dùng rõ ràng.',
            ],
        ],
        'tra-hoa-thao-moc' => [
            'heading' => 'Trà hoa thảo mộc của Thảo Mộc Farm có gì đặc biệt?',
            'paragraph' => 'Hoa cúc, hoa sâm, nụ tam thất… được thu hái thủ công vào sáng sớm khi sương chưa tan, giữ trọn hương thơm thanh khiết và dưỡng chất tự nhiên. Sự kết hợp giữa hoa tươi đồi núi cao và thảo mộc vùng cao mang đến sắc trà trong trẻo, vị ngọt hậu sâu, giúp thư giãn tinh thần và thanh lọc cơ thể mỗi ngày.',
            'subheading' => 'Chọn trà hoa chuẩn',
            'tips' => [
                'Cánh hoa và nụ còn nguyên vẹn, màu sắc tươi sáng tự nhiên, không bị xỉn hay xơ dại.',
                'Hương thơm thanh dịu dễ chịu khi mở túi, không có mùi hôi mốc hay hắc nồng.',
                'Nước trà sau khi hãm trong trong, không đục, vị thanh ngọt tự nhiên không đắng gắt.',
            ],
        ],
        'thit-gac-bep' => [
            'heading' => 'Thịt gác bếp Tây Bắc có gì đặc biệt?',
            'paragraph' => 'Thịt trâu, thịt lợn bản sau khi tẩm ướp mắc khén, hạt dổi, ớt rừng sẽ được treo trên gác bếp, hun bằng khói củi suốt 48 giờ. Lớp khói tạo màng bảo vệ tự nhiên giúp thịt khô dần, đậm vị và bảo quản được lâu mà không cần chất bảo quản.',
            'subheading' => 'Chọn thịt gác bếp ngon',
            'tips' => [
                'Thớ thịt khô ráo, màu nâu đen óng, không mốc trắng.',
                'Mùi khói thơm dịu, không khét gắt.',
                'Bao bì hút chân không, có ngày đóng gói và hạn dùng rõ ràng.',
            ],
        ],
        'gia-vi-tay-bac' => [
            'heading' => 'Gia vị Tây Bắc vì sao khó lẫn?',
            'paragraph' => 'Mắc khén, hạt dổi, ớt rừng thu hái từ rừng tự nhiên và vườn đồi Sơn La, Điện Biên, phơi nắng núi rồi rang thơm thủ công. Chính bộ ba mắc khén, hạt dổi, ớt rừng tạo nên vị tê thơm, cay ấm rất riêng mà gia vị công nghiệp không thay thế được.',
            'subheading' => 'Chọn gia vị chuẩn rừng',
            'tips' => [
                'Hạt khô đều, thơm nồng khi vò nhẹ, không ẩm mốc.',
                'Màu tự nhiên sẫm nhẹ, không phẩm màu nhuộm.',
                'Lọ thuỷ tinh hoặc túi zip kín, ghi rõ ngày rang đóng gói.',
            ],
        ],
        'mat-ong' => [
            'heading' => 'Mật ong Tây Bắc quý ở điểm nào?',
            'paragraph' => 'Ong rừng hút mật từ hoa bạc hà, hoa nhãn, hoa vải trên cao nguyên đá Mèo Vạc, Hà Giang nên mật sánh đậm, hậu thơm mát đặc trưng. Mật khai thác thủ công theo mùa hoa, quay lọc thô, không pha đường, không đun nấu nên giữ trọn enzyme và hương hoa bản địa.',
            'subheading' => 'Nhận biết mật ong nguyên chất',
            'tips' => [
                'Mật sánh, mùi thơm hoa nhẹ, nếm có hậu chua thanh rất nhẹ.',
                'Nhỏ giọt lên giấy thấm không loang nước nhanh.',
                'Chai thuỷ tinh sạch; lắng đọng tự nhiên ở đáy là hiện tượng bình thường.',
            ],
        ],
    ];

    /**
     * Lấy chi tiết sản phẩm theo slug.
     * Cache array thuần (không cache object) để tránh lỗi unserialize.
     * Convert sang DTO sau khi đọc từ cache.
     */
    public function getProductDetail(string $slug): array
    {
        $cacheKey = "product_detail:{$slug}";
        $ttl = (int) config('thaomoc.cache.catalog.product_detail', 300);

        $cached = remember_group('catalog', $cacheKey, $ttl, function () use ($slug) {
            return $this->fetchProductDetailFromDB($slug);
        });

        return $this->hydrateProductDetail($cached);
    }

    /**
     * Lấy dữ liệu từ DB, trả về array thuần (không có object).
     */
    private function fetchProductDetailFromDB(string $slug): array
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
     * Convert array đã cache sang DTO objects.
     */
    private function hydrateProductDetail(array $cached): array
    {
        $categoryDTO = $cached['category'] ? new CategoryViewDTO(
            name: $cached['category']['name'],
            url: $cached['category']['url'],
        ) : null;

        $productDTO = new ProductViewDTO(
            id: $cached['product']['id'],
            name: $cached['product']['name'],
            sku: $cached['product']['sku'],
            subtitle: $cached['product']['subtitle'],
            description: $cached['product']['description'],
            price: $cached['product']['price'],
            old_price: $cached['product']['old_price'],
            discount_percent: $cached['product']['discount_percent'],
            avg_rating: $cached['product']['avg_rating'],
            review_count: $cached['product']['review_count'],
            sold_count: $cached['product']['sold_count'],
            stock: $cached['product']['stock'],
            image: $cached['product']['image'],
            meta_description: $cached['product']['meta_description'],
            category: $categoryDTO,
        );

        $relatedDTOs = array_map(fn($r) => new RelatedProductDTO(
            url: $r['url'],
            image: $r['image'],
            name: $r['name'],
            price: $r['price'],
            old_price: $r['old_price'],
            discount_percent: $r['discount_percent'],
            avg_rating: $r['avg_rating'],
            sold_count: $r['sold_count'],
        ), $cached['relatedProducts']);

        $reviewData = $this->getProductReviews($cached['product_id']);

        return [
            'product' => $productDTO,
            'images' => $cached['images'],
            'variants' => $cached['variants'],
            'breadcrumbs' => $cached['breadcrumbs'],
            'relatedProducts' => $relatedDTOs,
            'reviews' => $reviewData['reviews'],
            'ratingStats' => $reviewData['stats'],
        ];
    }

    /**
     * Lấy reviews và rating stats.
     */
    public function getProductReviews(int $productId): array
    {
        $cacheKey = "product_reviews:{$productId}";
        $ttl = 600;

        $cached = remember_group('review', $cacheKey, $ttl, function () use ($productId) {
            return $this->fetchReviewsFromDB($productId);
        });

        $reviewDTOs = array_map(fn($r) => new ReviewViewDTO(
            customer: $r['customer'],
            rating: $r['rating'],
            content: $r['content'],
            created_at: $r['created_at'],
        ), $cached['reviews']);

        return [
            'reviews' => $reviewDTOs,
            'stats' => $cached['stats'],
        ];
    }

    /**
     * Lấy reviews từ DB, trả về array thuần.
     */
    private function fetchReviewsFromDB(int $productId): array
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

    /**
     * Lấy toàn bộ dữ liệu trang danh mục sản phẩm.
     */
    public function getCategoryShow(string $slug, array $rawFilters): ?array
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->select(['id', 'parent_id', 'name', 'slug', 'description'])
            ->first();

        if ($category === null) {
            return null;
        }

        $filters = $this->normalizeCategoryFilters($rawFilters);

        $children = Category::query()
            ->where('parent_id', $category->id)
            ->where('status', 'active')
            ->withCount([
                'products' => function (Builder $q): void {
                    $q->where('status', ProductStatus::ACTIVE->value);
                }
            ])
            ->select(['id', 'parent_id', 'name', 'slug', 'sort_order'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $scopeIds = $filters['cats'] !== []
            ? $filters['cats']
            : array_merge([$category->id], $children->pluck('id')->all());

        $query = Product::query()
            ->whereIn('category_id', $scopeIds)
            ->where('status', ProductStatus::ACTIVE->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with([
                'coverImage' => function (Relation $r): void {
                    $r->select([
                        'product_images.id',
                        'product_images.product_id',
                        'product_images.path',
                        'product_images.thumb_path',
                    ]);
                }
            ])
            ->select(['id', 'category_id', 'slug', 'name', 'price_min', 'compare_price', 'rating_avg', 'sold_count']);

        if ($filters['prices'] !== []) {
            $query->where(function (Builder $q) use ($filters): void {
                foreach ($filters['prices'] as $key) {
                    $range = collect(self::PRICE_RANGES)->firstWhere('key', $key);
                    if ($range === null)
                        continue;
                    if ($range['max'] === null) {
                        $q->orWhere('price_min', '>=', $range['min']);
                    } else {
                        $q->orWhereBetween('price_min', [$range['min'], $range['max']]);
                    }
                }
            });
        }

        if ($filters['rating'] !== null) {
            $query->where('rating_avg', '>=', $filters['rating']);
        }

        $query = match ($filters['sort']) {
            'newest' => $query->orderByDesc('published_at')->orderByDesc('id'),
            'price_asc' => $query->orderBy('price_min')->orderByDesc('id'),
            'price_desc' => $query->orderByDesc('price_min')->orderByDesc('id'),
            default => $query->orderByDesc('sold_count')->orderByDesc('id'),
        };

        $paginator = $query->paginate(self::CATEGORY_PER_PAGE, ['*'], 'page', $filters['page']);

        $products = $paginator->getCollection()
            ->map(fn(Product $p): array => $this->toCategoryCard($p))
            ->all();

        $parent = $category->parent_id !== null
            ? Category::query()->where('id', $category->parent_id)->where('status', 'active')->select(['id', 'name', 'slug'])->first()
            : null;

        $schemaCrumbs = [['label' => 'Trang chủ', 'url' => route('web.home')]];
        if ($parent !== null) {
            $schemaCrumbs[] = ['label' => $parent->name, 'url' => route('web.category.show', ['slug' => $parent->slug])];
        }
        $schemaCrumbs[] = ['label' => $category->name, 'url' => route('web.category.show', ['slug' => $category->slug])];

        $breadcrumbs = $schemaCrumbs;
        $breadcrumbs[count($breadcrumbs) - 1]['url'] = null;

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_map(
                static fn(int $i, array $c): array => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $c['label'],
                    'item' => $c['url'],
                ],
                array_keys($schemaCrumbs),
                $schemaCrumbs
            )),
        ];

        return [
            'category' => [
                'id' => (int) $category->id,
                'name' => (string) $category->name,
                'slug' => (string) $category->slug,
                'description' => $category->description !== null ? (string) $category->description : null,
            ],
            'breadcrumbs' => $breadcrumbs,
            'breadcrumb_schema' => $breadcrumbSchema,
            'children' => $children->map(fn(Category $c): array => [
                'id' => (int) $c->id,
                'name' => (string) $c->name,
                'slug' => (string) $c->slug,
                'count' => (int) $c->products_count,
            ])->all(),
            'products' => $products,
            'meta' => [
                'total' => (int) $paginator->total(),
                'per_page' => (int) $paginator->perPage(),
                'current_page' => (int) $paginator->currentPage(),
                'last_page' => (int) $paginator->lastPage(),
                'from' => (int) ($paginator->firstItem() ?? 0),
                'to' => (int) ($paginator->lastItem() ?? 0),
            ],
            'filters' => $filters,
            'priceRanges' => self::PRICE_RANGES,
            'sortOptions' => self::SORT_OPTIONS,
            'seoText' => $this->categorySeoText($category),
        ];
    }

    /**
     * Lấy toàn bộ dữ liệu trang TẤT CẢ SẢN PHẨM.
     * Tái sử dụng logic lọc, sắp xếp và cấu trúc array thuần giống getCategoryShow.
     */
    public function getAllProducts(array $rawFilters): array
    {
        $filters = $this->normalizeCategoryFilters($rawFilters);

        $query = Product::query()
            ->where('status', ProductStatus::ACTIVE->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with([
                'coverImage' => function (Relation $r): void {
                    $r->select([
                        'product_images.id',
                        'product_images.product_id',
                        'product_images.path',
                        'product_images.thumb_path',
                    ]);
                }
            ])
            ->select(['id', 'category_id', 'slug', 'name', 'price_min', 'compare_price', 'rating_avg', 'sold_count']);

        if ($filters['prices'] !== []) {
            $query->where(function (Builder $q) use ($filters): void {
                foreach ($filters['prices'] as $key) {
                    $range = collect(self::PRICE_RANGES)->firstWhere('key', $key);
                    if ($range === null)
                        continue;
                    if ($range['max'] === null) {
                        $q->orWhere('price_min', '>=', $range['min']);
                    } else {
                        $q->orWhereBetween('price_min', [$range['min'], $range['max']]);
                    }
                }
            });
        }

        if ($filters['rating'] !== null) {
            $query->where('rating_avg', '>=', $filters['rating']);
        }

        $query = match ($filters['sort']) {
            'newest' => $query->orderByDesc('published_at')->orderByDesc('id'),
            'price_asc' => $query->orderBy('price_min')->orderByDesc('id'),
            'price_desc' => $query->orderByDesc('price_min')->orderByDesc('id'),
            default => $query->orderByDesc('sold_count')->orderByDesc('id'),
        };

        $paginator = $query->paginate(8, ['*'], 'page', $filters['page']);

        $products = $paginator->getCollection()
            ->map(fn(Product $p): array => $this->toCategoryCard($p))
            ->all();

        $schemaCrumbs = [
            ['label' => 'Trang chủ', 'url' => route('web.home')],
            ['label' => 'Tất cả sản phẩm', 'url' => route('web.products.index')],
        ];

        $breadcrumbs = $schemaCrumbs;
        $breadcrumbs[count($breadcrumbs) - 1]['url'] = null;

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_map(
                static fn(int $i, array $c): array => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $c['label'],
                    'item' => $c['url'],
                ],
                array_keys($schemaCrumbs),
                $schemaCrumbs
            )),
        ];

        return [
            'pageTitle' => 'Tất cả sản phẩm',
            'pageDescription' => 'Khám phá toàn bộ đặc sản Tây Bắc chính gốc từ Thảo Mộc Farm: thịt gác bếp, thảo mộc, gia vị rừng, mật ong hoa bạc hà. Cam kết chất lượng, giao hàng toàn quốc.',
            'breadcrumbs' => $breadcrumbs,
            'breadcrumb_schema' => $breadcrumbSchema,
            'children' => [], // Không có danh mục con ở trang này
            'products' => $products,
            'meta' => [
                'total' => (int) $paginator->total(),
                'per_page' => (int) $paginator->perPage(),
                'current_page' => (int) $paginator->currentPage(),
                'last_page' => (int) $paginator->lastPage(),
                'from' => (int) ($paginator->firstItem() ?? 0),
                'to' => (int) ($paginator->lastItem() ?? 0),
            ],
            'filters' => $filters,
            'priceRanges' => self::PRICE_RANGES,
            'sortOptions' => self::SORT_OPTIONS,
            'seoText' => [
                'heading' => 'Tinh hoa đặc sản Tây Bắc',
                'paragraph' => 'Thảo Mộc Farm tự hào mang đến bộ sưu tập đầy đủ các đặc sản vùng cao Tây Bắc. Từ những miếng thịt trâu gác bếp thấm đẫm khói bếp, thảo mộc quý hiếm trên đỉnh Hoàng Liên Sơn, đến gia vị mắc khén hạt dổi thơm lừng và mật ong hoa bạc hà ngọt lành. Tất cả được tuyển chọn kỹ lưỡng, sơ chế thủ công và đóng gói cẩn thận để giữ trọn hương vị bản địa.',
                'subheading' => 'Cam kết từ Thảo Mộc Farm',
                'tips' => [
                    'Nguồn gốc rõ ràng, thu hái và chế biến trực tiếp từ bà con vùng cao.',
                    'Nói không với chất bảo quản, phẩm màu hay hóa chất độc hại.',
                    'Đóng gói hút chân không, bảo quản chuẩn, giao hàng tận nơi toàn quốc.',
                ],
            ],
        ];
    }

    /**
     * Chuẩn hoá bộ lọc danh mục về dạng an toàn, đủ key mặc định.
     */
    private function normalizeCategoryFilters(array $raw): array
    {
        $sort = isset($raw['sort']) && array_key_exists($raw['sort'], self::SORT_OPTIONS)
            ? (string) $raw['sort']
            : 'bestsell';

        $prices = array_values(array_intersect(
            (array) ($raw['price'] ?? []),
            array_column(self::PRICE_RANGES, 'key')
        ));

        $rating = isset($raw['rating']) && $raw['rating'] !== null && $raw['rating'] !== ''
            ? (int) $raw['rating']
            : null;
        if ($rating !== null && !in_array($rating, [3, 4, 5], true)) {
            $rating = null;
        }

        $cats = array_values(array_unique(array_map('intval', (array) ($raw['cat'] ?? []))));

        $page = max(1, (int) ($raw['page'] ?? 1));

        return [
            'sort' => $sort,
            'prices' => $prices,
            'rating' => $rating,
            'cats' => $cats,
            'page' => $page,
        ];
    }

    /**
     * Lấy nội dung SEO text theo slug danh mục.
     */
    private function categorySeoText(Category $category): array
    {
        $seo = self::CATEGORY_SEO_TEXT[$category->slug] ?? null;

        if ($seo !== null) {
            return $seo;
        }

        return [
            'heading' => 'Về ' . $category->name,
            'paragraph' => $category->description !== null ? trim((string) $category->description) : null,
            'subheading' => null,
            'tips' => [],
        ];
    }

    /**
     * Chuyển 1 product thành array thuần đúng contract của x-ui.product-card.
     */
    private function toCategoryCard(Product $p): array
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
            'image' => $this->categoryProductImageUrl($p),
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
    private function categoryProductImageUrl(Product $p): string
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
    /**
     * Tìm kiếm sản phẩm theo từ khoá
     */
    public function searchProducts(string $keyword, array $rawFilters = [], ?int $limit = null): array
    {
        $keyword = trim($keyword);
        $filters = $this->normalizeCategoryFilters($rawFilters);

        $query = Product::query()
            ->where('status', ProductStatus::ACTIVE->value)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->search($keyword)
            ->with([
                'coverImage' => function (Relation $r): void {
                    $r->select([
                        'product_images.id',
                        'product_images.product_id',
                        'product_images.path',
                        'product_images.thumb_path',
                    ]);
                }
            ])
            ->select(['id', 'category_id', 'slug', 'name', 'price_min', 'compare_price', 'rating_avg', 'sold_count']);

        if ($filters['prices'] !== []) {
            $query->where(function (Builder $q) use ($filters): void {
                foreach ($filters['prices'] as $key) {
                    $range = collect(self::PRICE_RANGES)->firstWhere('key', $key);
                    if ($range === null)
                        continue;
                    if ($range['max'] === null) {
                        $q->orWhere('price_min', '>=', $range['min']);
                    } else {
                        $q->orWhereBetween('price_min', [$range['min'], $range['max']]);
                    }
                }
            });
        }

        if ($filters['rating'] !== null) {
            $query->where('rating_avg', '>=', $filters['rating']);
        }

        $query = match ($filters['sort']) {
            'newest' => $query->orderByDesc('published_at')->orderByDesc('id'),
            'price_asc' => $query->orderBy('price_min')->orderByDesc('id'),
            'price_desc' => $query->orderByDesc('price_min')->orderByDesc('id'),
            default => $query->orderByDesc('sold_count')->orderByDesc('id'),
        };

        if ($limit !== null && $limit > 0) {
            $products = $query->limit($limit)->get()
                ->map(fn(Product $p): array => $this->toCategoryCard($p))
                ->all();

            return [
                'products' => $products,
                'meta' => ['total' => count($products)],
            ];
        }

        $paginator = $query->paginate(8, ['*'], 'page', $filters['page']);

        $products = $paginator->getCollection()
            ->map(fn(Product $p): array => $this->toCategoryCard($p))
            ->all();

        $schemaCrumbs = [
            ['label' => 'Trang chủ', 'url' => route('web.home')],
            ['label' => 'Tìm kiếm: ' . $keyword, 'url' => route('web.search.index', ['q' => $keyword])],
        ];

        $breadcrumbs = $schemaCrumbs;
        $breadcrumbs[count($breadcrumbs) - 1]['url'] = null;

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_map(
                static fn(int $i, array $c): array => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $c['label'],
                    'item' => $c['url'],
                ],
                array_keys($schemaCrumbs),
                $schemaCrumbs
            )),
        ];

        return [
            'pageTitle' => 'Tìm kiếm: ' . $keyword,
            'pageDescription' => 'Kết quả tìm kiếm "' . $keyword . '" — thảo mộc & đặc sản Tây Bắc chính gốc tại Mộc Xanh.',
            'keyword' => $keyword,
            'breadcrumbs' => $breadcrumbs,
            'breadcrumb_schema' => $breadcrumbSchema,
            'children' => [],
            'products' => $products,
            'meta' => [
                'total' => (int) $paginator->total(),
                'per_page' => (int) $paginator->perPage(),
                'current_page' => (int) $paginator->currentPage(),
                'last_page' => (int) $paginator->lastPage(),
                'from' => (int) ($paginator->firstItem() ?? 0),
                'to' => (int) ($paginator->lastItem() ?? 0),
            ],
            'filters' => $filters,
            'priceRanges' => self::PRICE_RANGES,
            'sortOptions' => self::SORT_OPTIONS,
            'seoText' => [
                'heading' => 'Bạn vừa tìm "' . $keyword . '"',
                'paragraph' => 'Danh sách sản phẩm khớp với từ khoá bạn tìm. Bạn có thể dùng bộ lọc giá, đánh giá hoặc đổi cách sắp xếp để nhanh chóng tìm được sản phẩm phù hợp.',
                'subheading' => null,
                'tips' => [],
            ],
        ];
    }
}