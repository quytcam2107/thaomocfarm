@props(['hideFloatnav' => false])

@if(!($hideFloatnav ?? false))
    <nav class="floatnav" aria-label="Điều hướng nhanh">
        <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'is-active' : '' }}"
            aria-current="{{ request()->is('/') ? 'page' : 'false' }}">
            <span aria-hidden="true">🏠</span>Trang chủ
        </a>
        {{-- <a href="{{ url('/category') }}" class="{{ request()->is('category*') ? 'is-active' : '' }}">
            <span aria-hidden="true">🛍️</span>Mua sắm
        </a> --}}
        <a href="{{ url('/gio-hang') }}" class="{{ request()->is('cart*') ? 'is-active' : '' }}">
            <span aria-hidden="true">🛒</span>Giỏ hàng
            {{-- Badge số lượng giỏ hàng: cart.js cập nhật tất cả phần tử .cart-count
            (mobile ẩn header__acts nên badge floatnav hoạt động thay thế) --}}
            <b class="cart-count js-cart-count" hidden>0</b>
        </a>
    </nav>
@endif