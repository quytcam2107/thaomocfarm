@props(['product'])

<div class="buybar">
    <span class="buybar__price">{{ number_format($product->price) }}₫</span>
    <button class="btn btn--leaf add-cart" data-name="{{ $product->name }}" data-product-id="{{ $product->id }}">🛒
        Thêm</button>
    {{-- Buy Now mobile: cùng logic với #buyNow (app.js bind cả 2 id qua delegation) --}}
    <button class="btn btn--clay" id="buyNowMobile" type="button" data-product-id="{{ $product->id }}"
        data-name="{{ $product->name }}">Mua ngay</button>
</div>