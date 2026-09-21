<header class="site-header">
    <div class="topbar">
        <div class="container topbar__in">
            <p>🚚 Miễn phí vận chuyển cho đơn từ 300K 🚀 Giao nhanh 2h nội thành</p>
            <nav class="topbar__nav" aria-label="Liên kết nhanh">
                <a href="tel:0362795897">📞 0362 795 897</a>
                <a href="#">🧾 Tra cứu đơn</a>
                <a href="#">📍 Cửa hàng</a>
            </nav>
        </div>
    </div>
    <div class="container header__main">
        <button class="burger" id="burgerBtn" aria-label="Mở danh mục" aria-controls="drawer" aria-expanded="false">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round">
                <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <a class="brand" href="{{ url('/') }}">
            <img class="brand__logo" src="{{ asset('assets/images/logo_thao_moc_farm.png') }}" alt="Logo Thảo mộc Farm"
                width="50" height="50">
            <span class="brand__text">Thảo Mộc Farm<small>Thảo mộc & Đặc sản Tây Bắc</small></span>
        </a>
        <form class="search" role="search" action="{{ url('/tim-kiem') }}">
            <input type="search" name="q" placeholder="Tìm thịt trâu gác bếp, trà hoa, mắc khén…"
                aria-label="Tìm sản phẩm">
            <button type="submit" aria-label="Tìm kiếm">🔍</button>
        </form>
        <div class="header__acts">
            {{-- Dùng @auth thay vì @if để tránh lỗi quên @endif --}}
            @auth
                <a class="act" href="{{ url('/account') }}"
                    aria-label="Tài khoản">👤<span>{{ Auth::user()->name ?? 'Tài khoản' }}</span></a>
            @else
                <a class="act" href="{{ url('/login') }}" aria-label="Đăng nhập">👤<span>Đăng nhập</span></a>
            @endauth

            <a class="act" href="{{ url('/cart') }}" aria-label="Giỏ hàng">🛒<span>Giỏ hàng</span><b class="cart-count"
                    id="cartBadge">0</b></a>
        </div>
    </div>
    <nav class="catnav" aria-label="Danh mục sản phẩm">
        <div class="container catnav__in no-scrollbar">
            <a href="{{ url('/category/thao-moc') }}">🌿 Thảo mộc</a>
            <a href="{{ url('/category/thit-gac-bep') }}">🍖 Thịt gác bếp</a>
            <a href="{{ url('/category/gia-vi-tay-bac') }}">🧂 Gia vị Tây Bắc</a>
            <a href="{{ url('/category/mat-ong') }}">🍯 Mật ong</a>
            <a class="is-hot" href="{{ url('/deal-hot') }}">🔥 Deal hot</a>
        </div>
    </nav>
</header>