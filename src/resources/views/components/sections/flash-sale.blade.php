@props(['products' => null])
{{-- @dd($products) --}}
<section class="flash reveal" id="flash" aria-labelledby="flashTitle">
    <div class="container">
        <div class="flash__head">
            <h2 class="flash__title" id="flashTitle">⚡ {{ $products['promotion_name'] ?? 'Giá siêu hời' }}</h2>
            <p class="countdown" role="timer" aria-live="off">Kết thúc sau
                <b class="cd" id="cdH">00</b>:<b class="cd" id="cdM">00</b>:<b class="cd" id="cdS">00</b>
            </p>
            <a class="flash__all" href="{{ url('/category') }}">Xem tất cả →</a>
        </div>
        <div class="flash__rail no-scrollbar" role="region" aria-label="Deal chớp nhoáng, cuộn ngang" tabindex="0">
            @if($products && count($products['items']))
                @foreach($products['items'] as $p)
                    <x-ui.product-card :url="$p['url']" :image="$p['image']" :name="$p['name']" :price="$p['flash_price_formatted']"
                        :oldPrice="$p['original_price_formatted']" :discount="$p['discount_percent']" :rating="$p['rating_avg']" :sold="$p['sold_text']" />
                @endforeach
            @endif
        </div>
    </div>
</section>