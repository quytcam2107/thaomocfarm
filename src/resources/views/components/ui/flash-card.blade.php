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
    'savedAmount' => null,    // Số VND tiết kiệm (int) hoặc chuỗi "52.500₫" — chỉ tính từ discount_percent
])

@php
    // Map tone -> class màu badge (chỉ dùng selector có sẵn trong 04-home.css)
    $toneClass = match ($urgentTone) {
        'hot' => 'is-hot',
        'deep' => 'is-deep',
        'sell' => 'is-sell',
        default => 'is-new',
    };

    // Bỏ hậu tố ₫ để JS/inline script dễ đọc con số (contract .pcard__price b)
    $rawNumber = static function ($value): ?string {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_int($value) || is_float($value)) {
            return number_format((float) $value, 0, ',', '.');
        }
        $digits = preg_replace('/[^\d]/u', '', (string) $value);
        return $digits !== '' ? number_format((int) $digits, 0, ',', '.') : null;
    };

    $priceText = $rawNumber($price);
    $oldText = $rawNumber($oldPrice);

    // FIX LỆCH #1: chỉ hiện dòng "Tiết kiệm" khi thực sự có tiền giảm (> 0),
    // tránh render thẻ rỗng làm .pcard__buy cao bất thường giữa các card.
    $savedValue = (int) preg_replace('/[^\d]/u', '', (string) ($savedAmount ?? '0'));
    $savedText = $savedValue > 0 ? $rawNumber($savedValue) : null;
@endphp

<article class="pcard pcard--flash">
    <div class="pcard__media">
        {{-- Badge hook: nằm CHÍNH GIỮA mép trên ảnh; flag "-X%" ở góc trái
        (xử lý responsive bằng container query trong 14-flash-sale.css) --}}
        @if($urgentText)
            <span class="flash__urgent {{ $toneClass }}">{{ $urgentText }}</span>
        @endif

        <a href="{{ $url }}" aria-label="Xem {{ $name }}">
            <img class="pcard__img" src="{{ asset($image) }}" alt="{{ $name }}" width="600" height="600" loading="lazy">
        </a>

        @if($discount)
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
                    <small>Còn {{ number_format((int) $slotsLeft, 0, ',', '.') }} slot</small>
                @endif
            </p>
        @endif

        {{-- Text social proof: "Đã bán 200 sản phẩm hôm nay" --}}
        @if($soldTextToday)
            <p class="flash__sold">{{ $soldTextToday }}</p>
        @endif

        {{-- FIX LỆCH #2: .flash__save KHÔNG nằm trong .pcard__buy nữa.
        .pcard__buy là flex row (price | nút add) -> nhét thêm 1 <p> vào sẽ
            tạo flex item thứ 3, chữ bị dồn giữa và lệch khỏi giá.
            Chuyển dòng tiết kiệm lên block riêng ngay trên .pcard__buy. --}}
            @if($savedText)
                <p class="flash__save">
                    {{-- <i>: nhãn chữ, <b>: con số — CSS tự ẩn <i> ở màn rất hẹp --}}
                                <i class="flash__save-label">Tiết kiệm</i>
                                <b class="flash__save-value">{{ $savedText }}₫</b>
                </p>
            @endif

        <div class="pcard__buy">
            <p class="pcard__price">
                <b>{{ $priceText }}₫</b>
                @if($oldText)
                    <s>{{ $oldText }}₫</s>
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