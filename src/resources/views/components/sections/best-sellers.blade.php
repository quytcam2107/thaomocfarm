@props(['products' => []])

@if(!empty($products))
    <section class="section section--best" aria-labelledby="bestHeading">
        <div class="container">
            <header class="section__head">
                <div>
                    <p class="section__eyebrow">🔥 Được yêu thích nhất</p>
                    <h2 class="section__title" id="bestHeading">Bán chạy tuần này</h2>
                </div>
            </header>

            <div class="product-grid">
                @foreach($products as $product)
                    <x-ui.product-card :url="$product['url']" :image="$product['image']" :name="$product['name']"
                        :price="$product['price']" :oldPrice="$product['old_price']" :discount="$product['discount_percent']"
                        :rating="$product['rating_avg']" :sold="format_number_compact($product['sold_count'])"
                        :productId="$product['product_id']" :variantId="$product['variant_id']" />
                @endforeach
            </div>
        </div>
    </section>
@endif