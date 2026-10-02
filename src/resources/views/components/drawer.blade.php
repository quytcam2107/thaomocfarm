{{-- DRAWER MENU MOBILE: điều hướng chính + danh mục thật từ DB (MenuService) --}}
@php
    $menuService = app(\App\Services\MenuService::class);
    $drawerCategories = $menuService->drawerCategories();

    // Emoji hiển thị cho từng nhóm ngành (fallback 🌿 nếu tên chưa khớp từ khóa)
    $catEmoji = static function (string $name): string {
        $n = mb_strtolower($name, 'UTF-8');
        if (str_contains($n, 'thịt'))
            return '🥩';
        if (str_contains($n, 'gia vị'))
            return '🧂';
        if (str_contains($n, 'trà'))
            return '🍵';
        if (str_contains($n, 'mật ong'))
            return '🍯';
        if (str_contains($n, 'dược') || str_contains($n, 'thảo mộc'))
            return '🌿';
        if (str_contains($n, 'combo') || str_contains($n, 'quà'))
            return '🎁';
        if (str_contains($n, 'hạt'))
            return '🌰';
        return '🌿';
    };

    // 4 nhóm liên kết chính — tất cả trỏ tới route/page THẬT đã tồn tại
    // 'icon' giờ là đường dẫn file SVG (tự thêm vào public/assets/images/svg/)
    $mainLinks = [
        [
            'label' => 'Trang chủ',
            'icon' => 'assets/images/svg/icon-home.svg',
            'url' => route('web.home'),
            'desc' => 'Ưu đãi & sản phẩm nổi bật',
            'match' => fn() => request()->is('/'),
        ],
        [
            'label' => 'Giới thiệu',
            'icon' => 'assets/images/svg/icon-about.svg',
            'url' => route('web.page.about'), // /gioi-thieu
            'desc' => 'Câu chuyện Mộc Xanh',
            'match' => fn() => request()->is('gioi-thieu'),
        ],
        [
            'label' => 'Về chúng tôi',
            'icon' => 'assets/images/svg/icon-contact.svg',
            'url' => route('web.page.contact'), // /lien-he — thông tin cửa hàng, hotline, địa chỉ thật
            'desc' => 'Cửa hàng & liên hệ',
            'match' => fn() => request()->is('lien-he'),
        ],
        [
            'label' => 'Hỗ trợ',
            'icon' => 'assets/images/svg/icon-support.svg',
            'url' => route('web.page.order-guide'), // /huong-dan-dat-hang
            'desc' => 'Đặt hàng, đổi trả, bảo mật',
            'match' => fn() => request()->is('huong-dan-dat-hang', 'chinh-sach-doi-tra', 'chinh-sach-bao-mat', 'dieu-khoan-su-dung'),
        ],
    ];
@endphp

<div class="drawer" id="drawer" role="dialog" aria-modal="true" aria-label="Danh mục sản phẩm">
    <div class="drawer__overlay" data-drawer-close></div>
    <div class="drawer__panel">
        {{-- Header mới: logo + tên thương hiệu + tagline + nút đóng (đã bỏ cart-count khỏi head) --}}
        <div class="drawer__head">
            <span class="drawer__brand">
                <img src="{{ asset('assets/images/logo_thao_moc_farm.png') }}" alt="Logo Mộc Xanh" width="40"
                    height="40">
                <span class="drawer__brand-text">
                    <b>Mộc Xanh</b>
                    <small>Thảo mộc &amp; Đặc sản Tây Bắc</small>
                </span>
            </span>
            <button class="drawer__close" type="button" data-drawer-close aria-label="Đóng danh mục">✕</button>
        </div>

        <nav class="drawer__nav" aria-label="Menu chính">
            {{-- ===== Điều hướng chính ===== --}}
            <p class="drawer__section-title">Khám phá</p>
            <ul class="drawer__links">
                @foreach($mainLinks as $link)
                    <li>
                        <a href="{{ $link['url'] }}" class="{{ $link['match']() ? 'is-active' : '' }}">
                            {{-- Icon SVG từ file (thay cho emoji trước đây) --}}
                            <span class="drawer__link-icon" aria-hidden="true">
                                <img src="{{ asset($link['icon']) }}" alt="" width="20" height="20" loading="lazy">
                            </span>
                            <span class="drawer__link-text">
                                <b>{{ $link['label'] }}</b>
                                <small>{{ $link['desc'] }}</small>
                            </span>
                            <span class="drawer__link-arrow" aria-hidden="true">›</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            {{-- ===== Danh mục thật từ DB — tự ẩn khi rỗng (rule component) ===== --}}
            @if(count($drawerCategories))
                <p class="drawer__section-title">Danh mục sản phẩm</p>
                <ul class="drawer__cats">
                    @foreach($drawerCategories as $cat)
                        <li>
                            <a href="{{ $cat['url'] }}"
                                class="{{ request()->is('danh-muc/' . $cat['slug']) ? 'is-active' : '' }}">
                                <img src="{{ $cat['image'] }}" alt="{{ $cat['name'] }}" width="40" height="40" loading="lazy">
                                <span class="drawer__cat-text">
                                    <b>{{ $catEmoji($cat['name']) }} {{ $cat['name'] }}</b>
                                    <small>{{ $cat['products_count'] }} sản phẩm</small>
                                </span>
                                <span class="drawer__link-arrow" aria-hidden="true">›</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- ===== Tiện ích nhanh — badge giỏ hàng đặt tại dòng "Giỏ hàng" ===== --}}
            <p class="drawer__section-title">Tiện ích</p>
            <ul class="drawer__util">
                <li>
                    <a href="{{ route('web.cart.index') }}">
                        <span class="drawer__link-icon" aria-hidden="true">🛒</span>
                        <span class="drawer__util-label">Giỏ hàng</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('web.search.index') }}">
                        <span class="drawer__link-icon" aria-hidden="true">🔍</span> Tìm kiếm sản phẩm
                    </a>
                </li>
                <li>
                    <a href="tel:0362795897">
                        <span class="drawer__link-icon" aria-hidden="true">📞</span> Hotline 0362 795 897
                    </a>
                </li>
            </ul>
        </nav>

        {{-- Footer drawer: CTA mua sắm --}}
        <div class="drawer__foot">
            <a class="btn btn--leaf drawer__cta" href="{{ route('web.products.index') }}">🛍️ Mua ngay kẻo lỡ</a>
        </div>
    </div>
</div>