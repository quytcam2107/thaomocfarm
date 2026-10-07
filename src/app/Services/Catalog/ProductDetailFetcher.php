<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Relation;

/*
 * ProductDetailFetcher — tầng DB thuần của trang chi tiết sản phẩm
 * (tách từ CatalogService::fetchProductDetailFromDB + fetchReviewsFromDB).
 * BẮT BUỘC trả array thuần (không object Eloquent) để an toàn cho cache file.
 */
class ProductDetailFetcher
{
    /**
     * Lấy toàn bộ dữ liệu thô của 1 sản phẩm theo slug (kèm cả related products).
     *
     * @return array<string, mixed>
     */
    public static function fetch(string $slug): array
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

        // Related: giữ nguyên logic cũ nhưng PHẢI trả product_id + variant_id
        // để FlashSalePriceService tra đúng deal và ghi đè giá ngoài cache.
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
                    'product_id' => (int) $p->id,
                    'variant_id' => $defVar ? (int) $defVar->id : null,
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

        /* NEW: specs_json — cột json bảng products (migration 2026_10_07_000001).
           Eloquent cast 'array' nên $product->specs_json là array|null; ép về array
           thuần để an toàn cache file. Hy sinh 1 vòng normalize: mọi value phải là
           string scalar (chống kiểu bất thường do admin nhập JSON lồng nhau). */
        $specsRaw = is_array($product->specs_json) ? $product->specs_json : [];
        $specsFlat = [];
        foreach ($specsRaw as $k => $v) {
            if (is_scalar($v)) {
                $specsFlat[(string) $k] = trim((string) $v);
            }
        }

        return [
            'product_id' => $product->id,
            'product' => [
                'id' => $product->id,
                'slug' => $product->slug,
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
                // NEW: mảng thông số đã chuẩn hóa (key => string), rỗng khi cột NULL
                'specs' => $specsFlat,
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
     * Lấy reviews + thống kê sao cho 1 sản phẩm (mảng thuần, cache-safe).
     *
     * FIX LỖI 1054: bảng `reviews` KHÔNG có cột `customer_name` (xem
     * ai-database/tables/reviews.md). Tên khách hàng lấy qua quan hệ
     * reviews.user_id -> users.name, eager-load bằng BelongsTo để tránh N+1.
     * UPDATE: migration 2026_10_05_000002 ĐÃ thêm cột customer_name/customer_phone
     * (review guest không còn user_id) — tên hiển thị ưu tiên user.name,
     * fallback customer_name tự khai trên form.
     *
     * NEW (UI đánh giá chuẩn TMĐT): trả thêm id (nút Hữu ích), initials
     * (avatar chữ cái thay ảnh vì DB không có cột avatar), helpful_count
     * (withCount review_votes — chống N+1), images (URL tuyệt đối từ cột json
     * reviews.images), admin_reply (cột text thật — phản hồi của Mộc Xanh).
     *
     * @return array{reviews: list<array<string, mixed>>, stats: array<string, mixed>}
     */
    public static function fetchReviews(int $productId): array
    {
        $reviews = Review::query()
            ->where('reviews.product_id', $productId)
            ->where('reviews.status', 'approved')
            ->with([
                // Chỉ select đúng cột TỒN TẠI: id + name (users.name)
                'user' => function (BelongsTo $q): void {
                    $q->select(['users.id', 'users.name']);
                },
            ])
            ->withCount('votes') // NEW: số lượt "Hữu ích"
            ->orderByDesc('reviews.created_at')
            ->limit(20)
            ->get([
                'reviews.id',
                'reviews.user_id',
                'reviews.rating',
                'reviews.content',
                'reviews.is_verified',
                'reviews.created_at',
                // NEW: danh tính guest + ảnh + phản hồi admin (cột THẬT sau migrate)
                'reviews.customer_name',
                'reviews.images',
                'reviews.admin_reply',
            ]);

        $stats = [
            'avg' => $reviews->count() > 0 ? round((float) $reviews->avg('rating'), 1) : null,
            'average' => $reviews->count() > 0 ? round((float) $reviews->avg('rating'), 1) : 0,
            'total' => $reviews->count(),
            'by_star' => [
                5 => $reviews->where('rating', 5)->count(),
                4 => $reviews->where('rating', 4)->count(),
                3 => $reviews->where('rating', 3)->count(),
                2 => $reviews->where('rating', 2)->count(),
                1 => $reviews->where('rating', 1)->count(),
            ],
        ];

        return [
            'reviews' => $reviews->map(fn(Review $r): array => [
                // 'customer' giữ key cũ cho ReviewViewDTO; thứ tự ưu tiên:
                // user login -> customer_name tự khai -> ẩn danh khi dữ liệu bị xóa
                'customer' => $r->user?->name ?: ($r->customer_name ?: 'Ẩn danh'),
                'id' => (int) $r->id,
                'initials' => self::initials($r->user?->name ?: ($r->customer_name ?: 'A')),
                'helpful_count' => (int) ($r->votes_count ?? 0),
                'rating' => (int) $r->rating,
                'content' => (string) $r->content,
                'is_verified' => (bool) $r->is_verified,
                'created_at' => optional($r->created_at)->format('d/m/Y'),
                // NEW: ảnh review — cột json lưu path tương đối ('assets/images/reviews/x.jpg')
                // -> bọc asset(); ảnh đã là URL tuyệt đối thì giữ nguyên (idempotent)
                'images' => array_map(
                    static fn(string $p): string => preg_match('#^https?://#i', $p) || str_starts_with($p, '/')
                        ? $p
                        : asset(ltrim($p, '/')),
                    array_values((array) ($r->images ?? []))
                ),
                // NEW: phản hồi của Mộc Xanh (null khi chưa reply)
                'admin_reply' => $r->admin_reply !== null && trim((string) $r->admin_reply) !== ''
                    ? (string) $r->admin_reply
                    : null,
            ])->values()->all(),
            'stats' => $stats,
        ];
    }

    /**
     * NEW: chữ cái đầu của 2 từ cuối trong tên (vd "Quyết Lưu" -> "QL")
     * làm avatar text — DB không có cột avatar nên không dùng ảnh.
     */
    private static function initials(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name)) ?: [];
        $parts = array_values(array_filter($parts));
        if ($parts === []) {
            return '?';
        }
        $take = array_slice($parts, -2);
        $out = '';
        foreach ($take as $p) {
            $out .= mb_strtoupper(mb_substr($p, 0, 1));
        }
        return $out;
    }
}