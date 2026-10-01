@props(['products' => null])
<section class="container best reveal" aria-labelledby="bestTitle">
    <h2 class="sec-title" id="bestTitle">Sản phẩm nổi bật - Bán chạy</h2>
    <div class="product-grid">
        @if($products && count($products))
            @foreach($products as $product)
                {{-- FIX: bỏ :flashPrice (x-ui.product-card không khai báo prop này) --}}
                <x-ui.product-card :url="$product['url']" :image="$product['image']" :name="$product['name']"
                    :price="$product['price']" :oldPrice="$product['old_price']" :discount="$product['discount_percent']"
                    :rating="$product['rating_avg']" :sold="$product['sold_count']" :productId="$product['product_id']"
                    :variantId="$product['variant_id']" />
            @endforeach
        @endif
    </div>
</section>