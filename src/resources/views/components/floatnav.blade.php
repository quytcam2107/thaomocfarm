<nav class="floatnav" aria-label="Điều hướng nhanh">
    <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'is-active' : '' }}"
        aria-current="{{ request()->is('/') ? 'page' : 'false' }}">
        <span aria-hidden="true">🏠</span>Trang chủ
    </a>
    <a href="{{ url('/category') }}" class="{{ request()->is('category*') ? 'is-active' : '' }}">
        <span aria-hidden="true">🛍️</span>Mua sắm
    </a>
    <a href="{{ url('/cart') }}" class="{{ request()->is('cart*') ? 'is-active' : '' }}">
        <span aria-hidden="true">🛒</span>Giỏ hàng
    </a>
</nav>