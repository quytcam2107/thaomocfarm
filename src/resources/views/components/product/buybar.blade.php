@props(['product'])

<div class="buybar">
    <span class="buybar__price">{{ number_format($product->price) }}₫</span>
    <button class="btn btn--leaf add-cart" data-name="{{ $product->name }}" data-id="{{ $product->id }}">🛒
        Thêm</button>
    <button class="btn btn--clay" id="buyNowMobile" data-id="{{ $product->id }}">Mua ngay</button>
</div>