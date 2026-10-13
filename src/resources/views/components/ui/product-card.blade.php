@props([
    'url' => '#',
    'image' => '',
    'name' => 'Sản phẩm',
    'price' => 0,
    'oldPrice' => null,
    'discount' => null,
    'rating' => 5.0,
    'sold' => 0,
    'productId' => 0,
    'variantId' => 0,
])

<article class="pcard">
    <div class="pcard__media">
        <a href="{{ $url }}" aria-label="Xem {{ $name }}">
            {{-- NEW TOOLTIP: data-tip trên img -> hover ảnh hiện tên sản phẩm --}}
            <img class="pcard__img" src="{{ asset($image) }}" alt="{{ $name }}" width="600" height="600" loading="lazy"
                data-tip="{{ $name }}">
        </a>

        @if($discount)
            <span class="pcard__flag pcard__flag--hot">-{{ $discount }}%</span>
        @endif

        {{-- <button class="pcard__fav" aria-label="Thêm {{ $name }} vào yêu thích" aria-pressed="false">♡</button> --}}
    </div>

    <div class="pcard__body">
        {{-- NEW TOOLTIP: tên bị line-clamp 2 dòng -> hover hiện full tên --}}
        <h3 class="pcard__name" data-tip="{{ $name }}"><a href="{{ $url }}">{{ $name }}</a></h3>
        <p class="pcard__rate">
            <span class="stars" aria-label="{{ $rating }} trên 5 sao">★★★★★</span>
            {{ $rating }} · {{ $sold }} đã bán
        </p>
        <div class="pcard__buy">
            <p class="pcard__price">
                <b>{{ is_int($price) ? number_format($price) : $price }}</b>
                @if($oldPrice)
                    <s>{{ is_int($oldPrice) ? number_format($oldPrice) : $oldPrice }}</s>
                @endif
            </p>
            {{-- NEW TOOLTIP: data-tip trên button -> hover (kể cả vào icon svg bên trong)
            hiện "Thêm sản phẩm vào giỏ hàng"; delegate mouseover của app-tooltip.js
            resolve qua closest('[data-tip]') nên icon svg con vẫn ăn tooltip. --}}
            <button class="pcard__add add-cart" type="button" data-product-id="{{ $productId }}"
                data-variant-id="{{ $variantId }}" data-name="{{ $name }}" aria-label="Thêm {{ $name }} vào giỏ"
                data-tip="Thêm sản phẩm vào giỏ hàng"><img class="pcard__add-icon"
                    src="{{ asset('assets/images/svg/cart-add.svg') }}" alt="Thêm giỏ hàng" width="20"
                    height="20"></button>
        </div>
    </div>
</article>