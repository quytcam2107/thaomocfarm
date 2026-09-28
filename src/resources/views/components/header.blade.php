@props([
    'hideCatnav' => false,
])
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
            <img class="brand__logo" src="{{ asset('assets/images/logo_thao_moc_farm.png') }}" alt="Logo Mộc Xanh"
                width="50" height="50">
            <span class="brand__text">Mộc Xanh<small>Thảo mộc & Đặc sản Tây Bắc</small></span>
        </a>

        {{-- Form Search --}}
        <form class="search" id="searchForm" role="search" action="{{ route('web.search.index') }}" autocomplete="off">
            <input type="search" name="q" id="searchInput" placeholder="Tìm củ tam thất, trà hoa, táo đỏ..."
                value="{{ request('q') }}" aria-label="Tìm sản phẩm" autocomplete="off">
            <button type="submit" aria-label="Tìm kiếm">🔍</button>

            {{-- Dropdown gợi ý --}}
            <div class="search__suggest" id="searchSuggest" role="listbox" aria-label="Gợi ý sản phẩm" hidden></div>
        </form>

        <div class="header__acts">
            @auth
                <a class="act" href="{{ url('/account') }}" aria-label="Tài khoản">
                    <img src="{{ asset('assets/images/svg/icon-user.svg') }}" alt="" width="20" height="20">
                    <span>{{ Auth::user()->name ?? 'Tài khoản' }}</span>
                </a>
            @else
                <a class="act" href="{{ url('/login') }}" aria-label="Đăng nhập">
                    <img src="{{ asset('assets/images/svg/icon-user.svg') }}" alt="" width="20" height="20">
                    <span>Đăng nhập</span>
                </a>
            @endauth

            <a class="act" href="{{ url('/gio-hang') }}" aria-label="Giỏ hàng">
                <img src="{{ asset('assets/images/svg/icon-cart.svg') }}" alt="" width="20" height="20">
                <span>Giỏ hàng</span>
                <b class="cart-count" id="cartBadge">0</b>
            </a>
        </div>
    </div>

    @unless($hideCatnav)
        <nav class="catnav" aria-label="Danh mục sản phẩm">
            <div class="container catnav__in no-scrollbar">
                <a href="{{ url('/thao-moc') }}">Thảo mộc & Dược liệu</a>
                <a href="{{ url('/tra-hoa-thao-moc') }}">Trà hoa Thảo Mộc</a>
                <a href="{{ url('/gia-vi-tay-bac') }}">Gia vị Tây Bắc</a>
                <a href="{{ url('/thit-gac-bep') }}">Thịt gác bếp</a>
                <a class="is-hot" href="{{ url('/deal-hot') }}">🔥 Deal hot</a>
            </div>
        </nav>
    @endunless
</header>