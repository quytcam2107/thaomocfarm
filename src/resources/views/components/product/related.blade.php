@props(['products' => collect()])

<section aria-labelledby="relTitle">
    <h2 class="sec-title" id="relTitle">Sản Phẩm Thường Mua Cùng</h2>
    <div class="product-grid">
        @forelse($products as $product)
            <x-ui.product-card :url="$product->url" :image="$product->image" :name="$product->name"
                :price="number_format($product->price) . '₫'" :oldPrice="$product->old_price ? number_format($product->old_price) . '₫' : null" :discount="$product->discount_percent"
                :rating="$product->avg_rating ?? '5.0'" :sold="number_format($product->sold_count ?? 0)" />
        @empty
            <p>Hiện chưa có sản phẩm liên quan.</p>
        @endforelse
    </div>
</section>