@props(['product'])

<div class="buybar">
    <span class="buybar__price">{{ number_format($product->price) }}₫</span>
    <button class="btn btn--leaf add-cart" data-name="{{ $product->name }}" data-product-id="{{ $product->id }}">🛒
        Thêm</button>
    {{-- Buy Now mobile: cùng logic với #buyNow (app.js bind cả 2 id qua delegation).
    NEW: thêm dòng phụ "Gọi điện và giao tận nơi" dưới text "Mua ngay" (event.stopPropagation
    để bấm link hotline không kích hoạt buyNow()). --}}
    <button class="btn btn--clay" id="buyNowMobile" type="button" data-product-id="{{ $product->id }}"
        data-name="{{ $product->name }}">
        <span class="btn__main">Mua ngay</span>
        <a class="btn__sub" href="#" onclick="event.stopPropagation()"
            aria-label="Gọi điện và giao tận nơi qua hotline 0362 795 897">Gọi điện xác nhận và giao tận nơi</a>
    </button>
</div>