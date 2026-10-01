<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ReviewViewDTO;
use App\Models\Category;
use App\Models\Product;
use App\Services\Catalog\BreadcrumbBuilder;
use App\Services\Catalog\ProductDetailFetcher;
use App\Services\Catalog\ProductDetailHydrator;
use App\Services\Catalog\ProductListingMapper;
use App\Services\Catalog\ProductQueryFilters;
use App\Services\Promotion\FlashSalePriceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * CatalogService — facade mỏng của tầng danh mục.
 * Logic nặng đã tách sang App\Services\Catalog\* + config/catalog.php
 * + data/catalog-seo.json; public API và kết quả giữ nguyên 100%.
 *
 * FIX: lưới sản phẩm (danh mục / tất cả SP / tìm kiếm) và PDP đều áp giá
 * flash sale qua FlashSalePriceService => đồng bộ % với block trang chủ.
 */
class CatalogService
{
    use ProductQueryFilters;

    /*
     * Constants cũ giữ lại để KHÔNG phá các call-site đang tham chiếu
     * CatalogService::SORT_OPTIONS / PRICE_RANGES / CATEGORY_PER_PAGE.
     * Nguồn sự thật duy nhất là config/catalog.php.
     */
    public const CATEGORY_PER_PAGE = 12;
    public const SORT_OPTIONS = [
        'bestsell' => 'Bán chạy nhất',
        'newest' => 'Mới nhất',
        'price_asc' => 'Giá thấp → cao',
        'price_desc' => 'Giá cao → thấp',
    ];
    public const PRICE_RANGES = [
        ['key' => '0-100', 'min' => 0, 'max' => 100000],
        ['key' => '100-250', 'min' => 100000, 'max' => 250000],
        ['key' => '250-', 'min' => 250000, 'max' => null],
    ];

    public function __construct(
        private readonly FlashSalePriceService $flashPricing
    ) {
    }

    /**
     * Lấy chi tiết sản phẩm theo slug.
     * Cache array thuần (không cache object) để tránh lỗi unserialize.
     * Convert sang DTO sau khi đọc từ cache.
     *
     * Lưu ý: mảng thô được cache CHƯA áp flash sale (để không "đóng băng" giá
     * deal suốt TTL); giá deal được áp MỖI request ngay sau khi đọc cache.
     */
    public function getProductDetail(string $slug): array
    {
        $cacheKey = "product_detail:{$slug}";
        $ttl = (int) config('thaomoc.cache.catalog.product_detail', 300);

        $cached = remember_group('catalog', $cacheKey, $ttl, function () use ($slug) {
            return ProductDetailFetcher::fetch($slug);
        });

        // FIX: áp giá flash sale (nếu có) cho PDP + related — chạy NGOÀI cache
        $cached = $this->flashPricing->applyToDetailArray($cached);

        return ProductDetailHydrator::hydrate($cached, $this->getProductReviews((int) $cached['product_id']), $this->flashPricing);
    }

    /**
     * Lấy reviews và rating stats (hydrate thành ReviewViewDTO).
     */
    public function getProductReviews(int $productId): array
    {
        $cacheKey = "product_reviews:{$productId}";
        $ttl = 600;

        $cached = remember_group('review', $cacheKey, $ttl, function () use ($productId) {
            return ProductDetailFetcher::fetchReviews($productId);
        });

        $reviewDTOs = array_map(fn($r) => new ReviewViewDTO(
            customer: $r['customer'],
            rating: $r['rating'],
            content: $r['content'],
            created_at: $r['created_at'],
            is_verified: (bool) ($r['is_verified'] ?? false),
        ), $cached['reviews']);

        return [
            'reviews' => $reviewDTOs,
            'stats' => $cached['stats'],
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
                    $q->where('status', \App\Enums\ProductStatus::ACTIVE->value);
                }
            ])
            ->select(['id', 'parent_id', 'name', 'slug', 'sort_order'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $scopeIds = $filters['cats'] !== []
            ? $filters['cats']
            : array_merge([$category->id], $children->pluck('id')->all());

        $query = $this->applyListingFilters(
            $this->baseListingQuery()->whereIn('category_id', $scopeIds),
            $filters
        );

        $paginator = $query->paginate((int) config('catalog.per_page', self::CATEGORY_PER_PAGE), ['*'], 'page', $filters['page']);

        $products = $paginator->getCollection()
            ->map(fn(Product $p): array => ProductListingMapper::toCard($p, $this->flashPricing))
            ->all();

        $parent = $category->parent_id !== null
            ? Category::query()->where('id', $category->parent_id)->where('status', 'active')->select(['id', 'name', 'slug'])->first()
            : null;

        $schemaCrumbs = [['label' => 'Trang chủ', 'url' => route('web.home')]];
        if ($parent !== null) {
            $schemaCrumbs[] = ['label' => $parent->name, 'url' => route('web.category.show', ['slug' => $parent->slug])];
        }
        $schemaCrumbs[] = ['label' => $category->name, 'url' => route('web.category.show', ['slug' => $category->slug])];

        return [
            'category' => [
                'id' => (int) $category->id,
                'name' => (string) $category->name,
                'slug' => (string) $category->slug,
                'description' => $category->description !== null ? (string) $category->description : null,
            ],
            'breadcrumbs' => BreadcrumbBuilder::withCurrent($schemaCrumbs),
            'breadcrumb_schema' => BreadcrumbBuilder::schema($schemaCrumbs),
            'children' => $children->map(fn(Category $c): array => [
                'id' => (int) $c->id,
                'name' => (string) $c->name,
                'slug' => (string) $c->slug,
                'count' => (int) $c->products_count,
            ])->all(),
            'products' => $products,
            'meta' => $this->paginatorMeta($paginator),
            'filters' => $filters,
            'priceRanges' => (array) config('catalog.price_ranges'),
            'sortOptions' => (array) config('catalog.sort_options'),
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

        $paginator = $this->applyListingFilters($this->baseListingQuery(), $filters)
            ->paginate((int) config('catalog.all_products_per_page', 8), ['*'], 'page', $filters['page']);

        $products = $paginator->getCollection()
            ->map(fn(Product $p): array => ProductListingMapper::toCard($p, $this->flashPricing))
            ->all();

        $schemaCrumbs = [
            ['label' => 'Trang chủ', 'url' => route('web.home')],
            ['label' => 'Tất cả sản phẩm', 'url' => route('web.products.index')],
        ];

        // Page meta + SEO text đọc từ data/catalog-seo.json (khối all_products_page)
        $static = (array) $this->seoFile()['all_products_page'];

        return [
            'pageTitle' => (string) $static['pageTitle'],
            'pageDescription' => (string) $static['pageDescription'],
            'breadcrumbs' => BreadcrumbBuilder::withCurrent($schemaCrumbs),
            'breadcrumb_schema' => BreadcrumbBuilder::schema($schemaCrumbs),
            'children' => [], // Không có danh mục con ở trang này
            'products' => $products,
            'meta' => $this->paginatorMeta($paginator),
            'filters' => $filters,
            'priceRanges' => (array) config('catalog.price_ranges'),
            'sortOptions' => (array) config('catalog.sort_options'),
            'seoText' => (array) $static['seoText'],
        ];
    }

    /**
     * Tìm kiếm sản phẩm theo từ khoá.
     */
    public function searchProducts(string $keyword, array $rawFilters = [], ?int $limit = null): array
    {
        $keyword = trim($keyword);
        $filters = $this->normalizeCategoryFilters($rawFilters);

        $query = $this->applyListingFilters(
            $this->baseListingQuery()->search($keyword),
            $filters
        );

        // Chế độ giới hạn nhanh (ajax/suggestion): không phân trang, không breadcrumb
        if ($limit !== null && $limit > 0) {
            $products = $query->limit($limit)->get()
                ->map(fn(Product $p): array => ProductListingMapper::toCard($p, $this->flashPricing))
                ->all();

            return [
                'products' => $products,
                'meta' => ['total' => count($products)],
            ];
        }

        $paginator = $query->paginate((int) config('catalog.all_products_per_page', 8), ['*'], 'page', $filters['page']);

        $products = $paginator->getCollection()
            ->map(fn(Product $p): array => ProductListingMapper::toCard($p, $this->flashPricing))
            ->all();

        $schemaCrumbs = [
            ['label' => 'Trang chủ', 'url' => route('web.home')],
            ['label' => 'Tìm kiếm: ' . $keyword, 'url' => route('web.search.index', ['q' => $keyword])],
        ];

        // Text trang tìm kiếm có template "{keyword}" — đọc từ data/catalog-seo.json
        $static = (array) $this->seoFile()['search_page'];

        return [
            'pageTitle' => 'Tìm kiếm: ' . $keyword,
            'pageDescription' => str_replace('{keyword}', $keyword, (string) $static['descriptionTemplate']),
            'keyword' => $keyword,
            'breadcrumbs' => BreadcrumbBuilder::withCurrent($schemaCrumbs),
            'breadcrumb_schema' => BreadcrumbBuilder::schema($schemaCrumbs),
            'children' => [],
            'products' => $products,
            'meta' => $this->paginatorMeta($paginator),
            'filters' => $filters,
            'priceRanges' => (array) config('catalog.price_ranges'),
            'sortOptions' => (array) config('catalog.sort_options'),
            'seoText' => [
                'heading' => str_replace('{keyword}', $keyword, (string) $static['seoText']['headingTemplate']),
                'paragraph' => (string) $static['seoText']['paragraph'],
                'subheading' => $static['seoText']['subheading'] ?? null,
                'tips' => (array) ($static['seoText']['tips'] ?? []),
            ],
        ];
    }

    /**
     * Query gốc chung cho mọi trang listing: active + đã publish + eager coverImage
     * + defaultVariant (cần để đối chiếu giá deal với đúng biến thể bán).
     */
    private function baseListingQuery(): Builder
    {
        return Product::query()
            ->where('status', \App\Enums\ProductStatus::ACTIVE->value)
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
                },
                // FIX: cần defaultVariant để map giá flash sale theo biến thể mặc định
                'defaultVariant' => function (HasOne $r): void {
                    $r->select([
                        'product_variants.id',
                        'product_variants.product_id',
                        'product_variants.price',
                    ]);
                },
            ])
            ->select(['id', 'category_id', 'slug', 'name', 'price_min', 'compare_price', 'rating_avg', 'sold_count']);
    }

    /**
     * Chuẩn hoá meta phân trang (array thuần như contract cũ).
     */
    private function paginatorMeta($paginator): array
    {
        return [
            'total' => (int) $paginator->total(),
            'per_page' => (int) $paginator->perPage(),
            'current_page' => (int) $paginator->currentPage(),
            'last_page' => (int) $paginator->lastPage(),
            'from' => (int) ($paginator->firstItem() ?? 0),
            'to' => (int) ($paginator->lastItem() ?? 0),
        ];
    }

    /**
     * Lấy nội dung SEO text theo slug danh mục (data/catalog-seo.json).
     */
    private function categorySeoText(Category $category): array
    {
        $seo = (array) ($this->seoFile()['seo_texts'][$category->slug] ?? []);

        if ($seo !== []) {
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
     * Đọc (và cache trong 1 request) file JSON chứa toàn bộ SEO text fix cứng.
     */
    private function seoFile(): array
    {
        static $data = null;

        if ($data === null) {
            $path = (string) config('catalog.seo_file');
            $decoded = json_decode((string) @file_get_contents($path), true);
            $data = is_array($decoded) ? $decoded : [];
        }

        return $data;
    }
}