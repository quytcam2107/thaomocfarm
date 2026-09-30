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
    // --- Dữ liệu flash sale (từ HomeService::mapFlashItem) ---
    'soldPercent' => 0,       // % slot đã bán -> độ đầy thanh tiến độ
    'soldTextToday' => null,  // "Đã bán 200 sản phẩm hôm nay"
    'urgentText' => null,     // Text hook đầu card: "Sắp cháy hàng 🔥" / "Giảm giá sâu -40%" / ...
    'urgentTone' => 'new',    // hot|deep|sell|new -> chọn màu badge
    'slotsLeft' => null,      // slot còn lại
    'progressText' => null,   // "78% đã bán"
])

@php
    // Map tone -> class màu badge (chỉ dùng selector có sẵn trong 04-home.css)
    $toneClass = match ($urgentTone) {
        'hot' => 'is-hot',
        'deep' => 'is-deep',
        'sell' => 'is-sell',
        default => 'is-new',
    };
@endphp

<article class="pcard pcard--flash">
    <div class="pcard__media">
        {{-- Badge hook: luôn hiển thị, nằm trên CÙNG và CHÍNH GIỮA thẻ --}}
        @if($urgentText)
            <span class="flash__urgent {{ $toneClass }}">{{ $urgentText }}</span>
        @endif

        <a href="{{ $url }}" aria-label="Xem {{ $name }}">
            <img class="pcard__img" src="{{ asset($image) }}" alt="{{ $name }}" width="600" height="600" loading="lazy">
        </a>

        @if($discount)
            {{-- Flag % giảm giá: giữ đúng selector .pcard__flag của card gốc --}}
            <span class="pcard__flag pcard__flag--hot">-{{ $discount }}%</span>
        @endif
    </div>

    <div class="pcard__body">
        <h3 class="pcard__name"><a href="{{ $url }}">{{ $name }}</a></h3>
        <p class="pcard__rate">
            <span class="stars" aria-label="{{ $rating }} trên 5 sao">★★★★★</span>
            {{ $rating }} · {{ $sold }} đã bán
        </p>

        {{-- Thanh tiến độ đã bán theo % (barfill = width inline từ server, JS không cần đụng tới) --}}
        @if($soldPercent > 0)
            <div class="flash__prog" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                aria-valuenow="{{ $soldPercent }}" aria-label="Tiến độ bán deal {{ $name }}">
                <span class="flash__prog-bar {{ $urgentTone === 'hot' ? 'is-hot' : '' }}"
                    style="width: {{ min(100, max(5, $soldPercent)) }}%;"></span>
            </div>
            <p class="flash__prog-meta">
                <b>{{ $progressText }}</b>
                @if($slotsLeft !== null && $slotsLeft > 0)
                    <small>Còn {{ number_format($slotsLeft, 0, ',', '.') }} slot</small>
                @endif
            </p>
        @endif

        {{-- Text social proof: "Đã bán 200 sản phẩm hôm nay" --}}
        @if($soldTextToday)
            <p class="flash__sold">{{ $soldTextToday }}</p>
        @endif

        <div class="pcard__buy">
            <p class="pcard__price">
                <b>{{ is_int($price) ? number_format($price) : $price }}</b>
                @if($oldPrice)
                    <s>{{ is_int($oldPrice) ? number_format($oldPrice) : $oldPrice }}</s>
                @endif
            </p>
            {{-- Giữ nguyên contract JS: .add-cart + data-product-id/data-variant-id --}}
            <button class="pcard__add add-cart" type="button" data-product-id="{{ $productId }}"
                data-variant-id="{{ $variantId }}" data-name="{{ $name }}" aria-label="Thêm {{ $name }} vào giỏ"><img
                    class="pcard__add-icon" src="{{ asset('assets/images/svg/cart-add.svg') }}" alt="Thêm giỏ hàng"
                    width="20" height="20"></button>
        </div>
    </div>
</article>