@props(['products' => null])
<section class="container best reveal" aria-labelledby="bestTitle">
    <h2 class="sec-title" id="bestTitle">Bán chạy tuần này</h2>
    <div class="product-grid">
        @if($products && count($products))
            @foreach($products as $p)
                <x-ui.product-card :url="$p['url']" :image="$p['image']" :name="$p['name']" :price="$p['price']"
                    :oldPrice="$p['old_price']" :discount="$p['discount_percent']" :rating="$p['rating_avg']" :sold="$p['sold_count']" />
            @endforeach
        @endif
    </div>
</section>