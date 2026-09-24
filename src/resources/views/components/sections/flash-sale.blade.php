@props(['products' => null])

@if($products && !empty($products['items']))
    <section class="flash reveal" id="flash" aria-labelledby="flashTitle">
        <div class="container">
            <div class="flash__head">
                {{-- Giữ UI của B, nhưng dùng text của A --}}
                <h2 class="flash__title" id="flashTitle">⚡ Flash Sale hôm nay</h2>

                {{-- Thêm data-ends của A vào để JS đếm ngược hoạt động đúng --}}
                <p class="countdown" data-ends="{{ $products['ends_at'] }}" role="timer" aria-live="polite">
                    Kết thúc sau
                    <b class="cd" id="cdH">00</b>:<b class="cd" id="cdM">00</b>:<b class="cd" id="cdS">00</b>
                </p>

                {{-- Giữ UI của B, nhưng dùng route của A --}}
                <a class="flash__all" href="{{ route('web.home') }}">Xem tất cả ưu đãi →</a>
            </div>

            <div class="flash__rail no-scrollbar" role="region" aria-label="Deal chớp nhoáng, cuộn ngang" tabindex="0">
                {{-- Dùng điều kiện và biến $product y hệt Code A --}}
                @foreach($products['items'] as $product)
                    <x-ui.product-card :url="$product['url']" :image="$product['image']" :name="$product['name']"
                        :price="$product['flash_price_formatted']" :oldPrice="$product['original_price_formatted']"
                        :discount="$product['discount_percent']" :rating="$product['rating_avg']" :sold="$product['sold_text']"
                        :productId="$product['product_id']" :variantId="$product['variant_id']" />
                @endforeach
            </div>
        </div>
    </section>
@endif