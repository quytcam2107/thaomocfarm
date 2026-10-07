<?php

declare(strict_types=1);

namespace App\Services\Catalog;

use App\DTOs\CategoryViewDTO;
use App\DTOs\ProductViewDTO;
use App\DTOs\RelatedProductDTO;
use App\DTOs\ReviewViewDTO;
use App\Services\Promotion\FlashSalePriceService;

/*
 * ProductDetailHydrator — convert array thuần đã cache sang DTO cho view
 * (tách từ CatalogService::hydrateProductDetail).
 */
class ProductDetailHydrator
{
    /**
     * Bảng ánh xạ key JSON trong products.specs_json -> nhãn hiển thị.
     * THỨ TỰ mảng = thứ tự dòng trong bảng "Thông số sản phẩm".
     * Field mới chỉ cần thêm 1 dòng ở đây (Fetcher/Hydrator/View không đổi).
     */
    private const SPEC_LABELS = [
        'thuong_hieu' => 'Thương hiệu',
        'xuat_xu' => 'Xuất xứ',
        'han_su_dung' => 'Hạn sử dụng',
        'thanh_phan' => 'Thành phần',
        'huong_vi' => 'Hương vị',
        'bao_quan' => 'Hướng dẫn bảo quản',
        'cong_dung' => 'Công dụng sản phẩm',
        'cach_dung' => 'Hướng dẫn sử dụng',
        'san_xuat' => 'Tên tổ chức chịu trách nhiệm sản xuất',
        'phan_phoi' => 'Phân phối độc quyền',
        'lien_he' => 'Thông tin liên hệ',
    ];

    /**
     * @param array<string, mixed> $cached Mảng thô từ ProductDetailFetcher::fetch()
     * @param array{reviews: list<array<string, mixed>|ReviewViewDTO>, stats: array<string, mixed>} $reviewData
     * @param FlashSalePriceService|null $flashPricing NEW: gắn block flash sale (countdown) cho PDP
     * @return array<string, mixed>
     */
    public static function hydrate(array $cached, array $reviewData, ?FlashSalePriceService $flashPricing = null): array
    {
        /* Chuẩn hóa path/URL ảnh gallery ra URL tuyệt đối — idempotent, chạy an toàn
           trên CẢ cache cũ lẫn cache mới (tránh lỗi "Undefined variable $imageThumbs"
           khi cache 'catalog' còn giữ định dạng list string URL của deploy trước):
           - Path tương đối (vd 'products/a.jpg') -> bọc assets/images/ như convention cũ.
           - URL tuyệt đối (http(s):// hoặc bắt đầu bằng '/') -> giữ nguyên, KHÔNG bọc
             đúp asset() (bọc đúp sẽ sinh URL sai kiểu http://host/http://host/...). */
        $imageUrl = static function (string $p): string {
            if ($p === '') {
                return asset('images/placeholder.svg');
            }
            if (preg_match('#^https?://#i', $p) || str_starts_with($p, '/')) {
                return $p;
            }
            return asset('assets/images/' . ltrim($p, '/'));
        };

        $categoryDTO = $cached['category'] ? new CategoryViewDTO(
            name: $cached['category']['name'],
            url: $cached['category']['url'],
        ) : null;

        // NEW: block flash sale cho PDP — null nếu SP không thuộc deal => component tự ẩn
        $flashBlock = $flashPricing?->pdpBlockFor((int) $cached['product_id']);

        /* NEW: decode thông số sản phẩm từ JSON trong DB.
           - Fetcher đã trả sẵn $cached['product']['specs'] (array phẳng key=>string).
           - Cache CŨ còn TTL (sinh trước deploy) không có key 'specs' -> tự đọc
             fallback các key dạng chuỗi JSON thô ($cached['product']['specs_json'])
             để trang không lỗi và vẫn hiện đúng dữ liệu sau khi bump cache.
           - Kết quả: list ['label' => ..., 'value' => ...] theo đúng thứ tự SPEC_LABELS,
             bỏ qua field trống; field lạ (không có trong map) vẫn được giữ cuối bảng. */
        $specRows = self::buildSpecRows($cached['product'] ?? []);

        $productDTO = new ProductViewDTO(
            id: $cached['product']['id'],
            slug: (string) ($cached['product']['slug'] ?? ''),
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
            flashSale: $flashBlock,
            specs: $specRows,
        );

        $relatedDTOs = array_map(fn($r) => new RelatedProductDTO(
            product_id: (int) ($r['product_id'] ?? 0),
            variant_id: isset($r['variant_id']) ? (int) $r['variant_id'] : null,
            url: $r['url'],
            image: $r['image'],
            name: $r['name'],
            price: $r['price'],
            old_price: $r['old_price'],
            discount_percent: $r['discount_percent'],
            avg_rating: $r['avg_rating'],
            sold_count: $r['sold_count'],
        ), $cached['relatedProducts']);

        // Reviews luôn đọc mới qua group cache 'review' riêng — không nằm trong cache 'catalog'.
        // FIX "Cannot use object of type App\DTOs\ReviewViewDTO as array": dùng helper
        // fromArray() + is_array() nên hàm này an toàn với CẢ 2 dạng đầu vào:
        //   - list array thuần (chuẩn mới — CatalogService::getProductReviews trả raw)
        //   - list ReviewViewDTO (cache/deploy cũ còn TTL hoặc call-site ngoài còn truyền DTO)
        $reviewDTOs = array_map(
            [ReviewViewDTO::class, 'fromArray'],
            $reviewData['reviews'] ?? []
        );

        /* Normalize ảnh gallery: list chứa object {full, thumb, alt} (bản mới) hoặc
           list URL string trần (cache cũ còn TTL sau deploy). Tách riêng 2 list URL
           tuyệt đối cho view. Fallback an toàn: nếu mục thiếu key 'thumb' -> thumb = full,
           và nếu mọi mục đều không có thumb -> $galleryThumb = $galleryFull, đảm bảo
           view KHÔNG BAO GIỜ nhận $imageThumbs rỗng (tránh Undefined/blank thumb). */
        $galleryFull = [];
        $galleryThumb = [];
        foreach (($cached['images'] ?? []) as $img) {
            if (is_array($img)) {
                $full = (string) ($img['full'] ?? '');
                $thumb = (string) ($img['thumb'] ?? $full);
            } else {
                $full = (string) $img;
                $thumb = $full;
            }
            if ($full === '' && $thumb === '') {
                continue; // ảnh thiếu path -> bỏ qua, tránh render src rỗng
            }
            $galleryFull[] = $imageUrl($full);
            $galleryThumb[] = $imageUrl($thumb !== '' ? $thumb : $full);
        }
        if (count($galleryThumb) !== count($galleryFull)) {
            $galleryThumb = $galleryFull; // chốt fallback 1-1 theo index
        }

        return [
            'product' => $productDTO,
            'images' => $galleryFull,
            'imageThumbs' => $galleryThumb,
            'variants' => $cached['variants'],
            'breadcrumbs' => $cached['breadcrumbs'],
            'relatedProducts' => $relatedDTOs,
            'reviews' => $reviewDTOs,
            'ratingStats' => $reviewData['stats'],
        ];
    }

    /**
     * NEW: gộp JSON specs từ DB thành list ['label','value'] cho Blade.
     * Ưu tiên key 'specs' (Fetcher bản mới); nếu thiếu (cache cũ) thì decode
     * 'specs_json' (chuỗi JSON hoặc array). Không bao giờ ném exception khi
     * JSON hỏng — coi như chưa có thông số.
     *
     * @param array<string, mixed> $product
     * @return list<array{label: string, value: string}>
     */
    private static function buildSpecRows(array $product): array
    {
        $raw = $product['specs'] ?? null;

        if (!is_array($raw) || $raw === []) {
            $fallback = $product['specs_json'] ?? null;
            if (is_string($fallback) && $fallback !== '') {
                $decoded = json_decode($fallback, true);
                $raw = is_array($decoded) ? $decoded : [];
            } elseif (is_array($fallback)) {
                $raw = $fallback;
            } else {
                $raw = [];
            }
        }

        // Chuẩn hóa key/value về chuỗi (chống value là array/null do dữ liệu lạ)
        $normalized = [];
        foreach ($raw as $k => $v) {
            if (is_scalar($v)) {
                $normalized[(string) $k] = trim((string) $v);
            }
        }

        $rows = [];
        $usedKeys = [];
        foreach (self::SPEC_LABELS as $key => $label) {
            $value = $normalized[$key] ?? '';
            if ($value === '') {
                continue; // field trống -> không render dòng rỗng
            }
            $rows[] = ['label' => $label, 'value' => $value];
            $usedKeys[$key] = true;
        }

        // Field extra admin tự thêm ngoài 11 field chuẩn -> hiển thị cuối bảng
        foreach ($normalized as $key => $value) {
            if ($value === '' || isset(self::SPEC_LABELS[$key]) || isset($usedKeys[$key])) {
                continue;
            }
            $rows[] = ['label' => $key, 'value' => $value];
        }

        return $rows;
    }
}