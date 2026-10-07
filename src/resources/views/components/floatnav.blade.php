{{-- =====================================================================
FLOATNAV — Thanh điều hướng nhanh dính đáy (chỉ mobile; desktop ẩn qua CSS)
Menu mới: Gọi điện · Zalo · Flash sale · Giỏ hàng
- Mọi icon là SVG link từ public/assets/images/svg (không còn emoji).
- Hotline/Zalo fix cứng 0362 795 897 (khớp topbar header + trang Liên hệ).
- Giữ hợp đồng JS: badge .cart-count/.js-cart-count (cart-badge.js);
  app-core.js bắt click a[data-fn="flash"] để cuộn mượt tới section #flash.
===================================================================== --}}
@props(['hideFloatnav' => false])

@if(!($hideFloatnav ?? false))
    <nav class="floatnav" aria-label="Điều hướng nhanh">
        {{-- 1. Gọi điện: tel: mở ứng dụng gọi trên di động --}}
        <a href="tel:0362795897" aria-label="Gọi điện tư vấn 0362 795 897">
            <img src="{{ asset('assets/images/svg/icon-phone.svg') }}" alt="" width="20" height="20"
                aria-hidden="true">Gọi điện
        </a>

        {{-- 2. Zalo: chat trực tiếp với số hotline (zalo.me/<sdt>) --}}
        <a href="https://zalo.me/0362795897" target="_blank" rel="noopener noreferrer"
            aria-label="Nhắn Zalo cho Mộc Xanh">
            <img src="{{ asset('assets/images/svg/zalo.png') }}" alt="" width="20" height="20"
                aria-hidden="true">Zalo
        </a>

        {{-- 3. Flash sale: ở home -> JS cuộn tới #flash; trang khác -> về /#flash --}}
        <a href="{{ url('/') }}#flash" data-fn="flash"
            class="{{ request()->is('/') ? 'is-active' : '' }}"
            {{ request()->is('/') ? 'aria-current="page"' : '' }}>
            <img src="{{ asset('assets/images/svg/icon-fire.svg') }}" alt="" width="20" height="20"
                aria-hidden="true">Ưu đãi
        </a>

        {{-- 4. Giỏ hàng: badge .cart-count do cart-badge.js cập nhật (mobile ẩn
        header__acts nên badge floatnav hoạt động thay thế) --}}
        <a href="{{ route('web.cart.index') }}" class="{{ request()->is('gio-hang*') ? 'is-active' : '' }}">
            <img src="{{ asset('assets/images/svg/icon-cart.svg') }}" alt="" width="20" height="20"
                aria-hidden="true">Giỏ hàng
            <b class="cart-count js-cart-count" hidden>0</b>
        </a>
    </nav>
@endif