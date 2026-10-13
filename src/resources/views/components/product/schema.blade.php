@props(['product'])

@php
    $productSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'image' => [asset($product->image)],
        'description' => $product->meta_description,
        'sku' => $product->sku,
        'brand' => ['@type' => 'Brand', 'name' => 'Mộc Xanh'],
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => $product->avg_rating ?? 5.0,
            'reviewCount' => $product->review_count ?? 0,
        ],
        'offers' => [
            '@type' => 'Offer',
            'url' => url()->current(),
            'priceCurrency' => 'VND',
            'price' => $product->price,
            'availability' => $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'seller' => ['@type' => 'Organization', 'name' => 'Mộc Xanh'],
        ],
    ];
    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => url('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $product->category->name ?? 'Danh mục', 'item' => $product->category->url ?? '#'],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $product->name],
        ],
    ];
    $jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;
@endphp

<script type="application/ld+json">{!! json_encode($productSchema, $jsonFlags) !!}</script>
<script type="application/ld+json">{!! json_encode($breadcrumbSchema, $jsonFlags) !!}</script>
