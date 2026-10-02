{{-- Widget sidebar "Có thể bạn sẽ thích": CHỈ hiển thị ảnh + giá gốc (gạch ngang) + giá giảm (+ % off).
Link toàn card dẫn tới trang chi tiết sản phẩm (route web.product.show qua $product['url']).
Dữ liệu: HomeService::buildListingItem — price/old_price là chuỗi format_vnd sẵn. --}}
@props(['product'])

<a class="bsp" href="{{ $product['url'] }}" aria-label="Xem chi tiết {{ $product['name'] }}">
    <span class="bsp__media">
        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" width="96" height="96" loading="lazy">
        @if (($product['discount_percent'] ?? 0) > 0)
            <span class="bsp__off">-{{ $product['discount_percent'] }}%</span>
        @endif
    </span>
    <span class="bsp__info">
        <span class="bsp__name">{{ $product['name'] }}</span>
        <span class="bsp__prices">
            <b class="bsp__price">{{ $product['price'] }}</b>
            @if (!empty($product['old_price']))
                <s class="bsp__old">{{ $product['old_price'] }}</s>
            @endif
        </span>
    </span>
</a>