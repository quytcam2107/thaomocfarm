@props(['product'])

<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Product",
    "name": "{{ $product->name }}",
    "image": ["{{ asset($product->image) }}"],
    "description": "{{ $product->meta_description }}",
    "sku": "{{ $product->sku }}",
    "brand": {
        "@@type": "Brand",
        "name": "Mộc Xanh"
    },
    "aggregateRating": {
        "@@type": "AggregateRating",
        "ratingValue": "{{ $product->avg_rating ?? '5.0' }}",
        "reviewCount": "{{ $product->review_count ?? 0 }}"
    },
    "offers": {
        "@@type": "Offer",
        "url": "{{ url()->current() }}",
        "priceCurrency": "VND",
        "price": "{{ $product->price }}",
        "availability": "{{ $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
        "seller": {
            "@@type": "Organization",
            "name": "Mộc Xanh"
        }
    }
}
</script>

<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        {"@@type": "ListItem", "position": 1, "name": "Trang chủ", "item": "{{ url('/') }}"},
        {"@@type": "ListItem", "position": 2, "name": "{{ $product->category->name ?? 'Danh mục' }}", "item": "{{ $product->category->url ?? '#' }}"},
        {"@@type": "ListItem", "position": 3, "name": "{{ $product->name }}"}
    ]
}
</script>