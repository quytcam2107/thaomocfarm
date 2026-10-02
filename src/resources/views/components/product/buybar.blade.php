@props(['product'])

<div class="buybar">
    <span class="buybar__price">{{ number_format($product->price) }}₫</span>
    <button class="btn btn--leaf add-cart" data-name="{{ $product->name }}"
        data-product-id="{{ $product->id }}">Thêm</button>
    <button class="btn btn--clay" id="buyNowMobile" type="button" data-product-id="{{ $product->id }}"
        data-name="{{ $product->name }}">
        <span class="btn__main">Mua ngay</span>
    </button>
</div>