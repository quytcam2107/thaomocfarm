@props(['products' => null])
<section class="container herbal-tea reveal" aria-labelledby="herbalTeaTitle">
    <h2 class="sec-title" id="herbalTeaTitle">Trà hoa thảo mộc</h2>
    <div class="product-grid">
        @if($products && count($products))
            @foreach($products as $product)
                {{-- FIX: service trả giá là SỐ RAW (đã gồm flash sale) => format tại view --}}
                <x-ui.product-card :url="$product['url']" :image="$product['image']" :name="$product['name']"
                    :price="$product['price'] ?? null"
                    :oldPrice="$product['old_price'] ?? null"
                    :discount="$product['discount_percent']"
                    :rating="$product['rating_avg']" :sold="$product['sold_count']"
                    :productId="$product['product_id']" :variantId="$product['variant_id']" />
            @endforeach
        @endif
    </div>
</section>