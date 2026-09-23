@props(['products' => null])

@if($products && !empty($products['items']))
    <section class="section section--flash" aria-labelledby="flashHeading">
        <div class="container">
            <header class="section__head">
                <div>
                    <p class="section__eyebrow">⏰ Giá siêu hời</p>
                    <h2 class="section__title" id="flashHeading">Flash Sale hôm nay</h2>
                </div>
                <div class="countdown" data-ends="{{ $products['ends_at'] }}" aria-live="polite">
                    <span class="countdown__label">Kết thúc sau</span>
                    <span class="countdown__time" data-hours>00</span>:
                    <span class="countdown__time" data-minutes>00</span>:
                    <span class="countdown__time" data-seconds>00</span>
                </div>
            </header>

            <div class="product-grid">
                @foreach($products['items'] as $product)
                    <x-ui.product-card :url="$product['url']" :image="$product['image']" :name="$product['name']"
                        :price="$product['flash_price_formatted']" :oldPrice="$product['original_price_formatted']"
                        :discount="$product['discount_percent']" :rating="$product['rating_avg']" :sold="$product['sold_text']"
                        :productId="$product['product_id']" :variantId="$product['variant_id']" />
                @endforeach
            </div>

            <footer class="section__foot">
                <a class="btn btn--outline" href="{{ route('web.home') }}">Xem tất cả ưu đãi →</a>
            </footer>
        </div>
    </section>
@endif