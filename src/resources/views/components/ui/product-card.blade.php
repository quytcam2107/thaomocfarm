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
            <img class="pcard__img" src="{{ asset($image) }}" alt="{{ $name }}" width="600" height="600" loading="lazy">
        </a>

        @if($discount)
            <span class="pcard__flag pcard__flag--hot">-{{ $discount }}%</span>
        @endif

        <button class="pcard__fav" aria-label="Thêm {{ $name }} vào yêu thích" aria-pressed="false">♡</button>
    </div>

    <div class="pcard__body">
        <h3 class="pcard__name"><a href="{{ $url }}">{{ $name }}</a></h3>
        <p class="pcard__rate">
            <span class="stars" aria-label="{{ $rating }} trên 5 sao">★★★★★</span>
            {{ $rating }} · {{ $sold }} đã bán
        </p>
        <div class="pcard__buy">
            <p class="pcard__price">
                <b>{{ is_int($price) ? number_format($price) : $price }}₫</b>
                @if($oldPrice)
                    <s>{{ is_int($oldPrice) ? number_format($oldPrice) : $oldPrice }}₫</s>
                @endif
            </p>
            <button class="pcard__add add-cart" type="button" data-product-id="{{ $productId }}"
                data-variant-id="{{ $variantId }}" data-name="{{ $name }}"
                aria-label="Thêm {{ $name }} vào giỏ">+</button>
        </div>
    </div>
</article>