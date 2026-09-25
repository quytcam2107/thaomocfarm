<div class="drawer" id="drawer" role="dialog" aria-modal="true" aria-label="Danh mục sản phẩm">
    <div class="drawer__overlay" data-drawer-close></div>
    <div class="drawer__panel">
        <p class="drawer__head">Thảo Mộc Farm
            <button class="drawer__close" data-drawer-close aria-label="Đóng danh mục">✕</button>
        </p>
        <nav class="drawer__nav" aria-label="Danh mục trong menu">
            <a href="{{ url('/') }}">🏠 Trang chủ</a>
            <a href="{{ url('/category/thit-gac-bep') }}">🍖 Thịt gác bếp</a>
            <a href="{{ url('/category/gia-vi-tay-bac') }}">🧂 Gia vị Tây Bắc</a>
            <a href="{{ url('/category/tra-hoa') }}">🌹 Trà hoa thảo dược</a>
            <a href="{{ url('/category/duoc-lieu') }}">🌿 Dược liệu quý</a>
            <a href="{{ url('/category/mat-ong') }}">🍯 Mật ong & tinh dầu</a>
            <a href="{{ url('/category/hat') }}">🌰 Hạt & nông sản</a>
            <a href="{{ url('/category/combo-qua-tang') }}">🎁 Combo quà tặng</a>
            <a href="{{ url('/tra-cuu-don') }}">🧾 Tra cứu đơn hàng</a>
        </nav>
    </div>
</div>