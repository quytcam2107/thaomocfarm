@props([
    'product',
    // Mảng stats thật từ ProductDetailFetcher::fetchReviews() (cache group 'review'):
    // ['avg' => ?float, 'average' => float, 'total' => int, 'by_star' => [...]]
    // Bắt buộc truyền vào — là NGUỒN SỰ THẬT DUY NHẤT cho aggregateRating.
    'ratingStats' => [],
])

@php
    /* =========================================================================
       FIX SEO #1 — image 404:
       $product->image TỪ SERVICE ĐÃ LÀ URL TUYỆT ĐỐI đúng tiền tố
       (.../assets/images/products/...) sau khi ProductDetailFetcher chuẩn hóa.
       Vì vậy KHÔNG bọc asset() ở đây nữa — bọc asset() lên 1 URL tuyệt đối
       là thủ phạm sinh "...//host/https://host/..." hoặc "/products/..." 404.
       Fallback cuối nếu service trả rỗng: placeholder CÓ THẬT trong public/.
       ========================================================================= */
    $schemaImage = (string) ($product->image ?? '');
    if ($schemaImage === '') {
        $schemaImage = asset('assets/images/placeholder.svg');
    }

    /* =========================================================================
       FIX SEO #2 — aggregateRating khi 0 review (Google penalize "Rating spam"):
       Trước đây block luôn emit với ratingValue default 5.0 / reviewCount 0
       (lấy từ cột products.rating_count, không đồng bộ review approved thật).
       Quy tắc mới: CHỈ emit aggregateRating khi số review APPROVED (stats.total)
       > 0; ratingValue lấy trung bình thật (stats.avg), NOT default 5.0.
       Không có review => JSON-LD Product không có aggregateRating => hợp lệ.
       ========================================================================= */
    $reviewTotal = (int) ($ratingStats['total'] ?? 0);
    $ratingAvg = isset($ratingStats['avg']) ? (float) $ratingStats['avg'] : 0.0;
    // Safety: avg null/0 nhưng total > 0 (dữ liệu lạ) -> quay về rating_avg cột SP
    if ($reviewTotal > 0 && $ratingAvg <= 0) {
        $ratingAvg = (float) ($product->avg_rating ?? 0);
    }
    $hasRating = $reviewTotal > 0 && $ratingAvg > 0;

    $productSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'image' => [$schemaImage],
        'description' => $product->meta_description,
        'sku' => $product->sku,
        'brand' => ['@type' => 'Brand', 'name' => 'Mộc Xanh'],
        'offers' => [
            '@type' => 'Offer',
            'url' => route('web.product.show', $product->slug),
            'priceCurrency' => 'VND',
            'price' => (int) $product->price,
            'availability' => $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'seller' => ['@type' => 'Organization', 'name' => 'Mộc Xanh'],
        ],
    ];

    // CHỈ gắn aggregateRating khi thật sự có review đã duyệt (tránh Google penalize)
    if ($hasRating) {
        $productSchema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => number_format($ratingAvg, 1, '.', ''),
            'reviewCount' => $reviewTotal,
            'bestRating' => 5,
            'worstRating' => 1,
        ];
    }

    /* =========================================================================
       FIX SEO #3 — BreadcrumbList: item CUỐI không được có 'item' trỏ '#'
       (bản cũ đẩy '#') và Google khuyến nghị item cuối là trang hiện tại.
       Dùng url()->current() cho breadcrumb cuối để tự kiểm (self-reference hợp lệ).
       ========================================================================= */
    $crumbs = [
        ['name' => 'Trang chủ', 'item' => url('/')],
    ];
    if (!empty($product->category)) {
        $crumbs[] = ['name' => $product->category->name, 'item' => $product->category->url];
    }
    $crumbs[] = ['name' => $product->name, 'item' => route('web.product.show', $product->slug)];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array_values(array_map(
            static fn(int $i, array $c): array => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $c['name'],
                'item' => $c['item'],
            ],
            array_keys($crumbs),
            $crumbs
        )),
    ];

    $jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;
@endphp

<script type="application/ld+json">{!! json_encode($productSchema, $jsonFlags) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, $jsonFlags) !!}</script>