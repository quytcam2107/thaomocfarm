@props(['product'])

{{-- =====================================================================
BUYBAR (thanh mua hàng dính đáy trang chi tiết SP — mobile-first, ẩn >=1024px)
Layout mới 5 nút (trái -> phải): Gọi điện · Zalo · Giỏ hàng · Thêm vào giỏ · Mua ngay
- Không còn .buybar__price (giá đã có trong .pd-price phía trên).
- Hotline/Zalo fix cứng 0362 795 897 (khớp topbar + floatnav + float-dock).
- Giữ nguyên contract JS:
+ .add-cart -> cart-add.js (POST /gio-hang/them)
+ #buyNowMobile -> product/buy-now.js (POST /gio-hang/mua-ngay)
+ .cart-count/.js-cart-count -> cart-badge.js cập nhật số lượng realtime
- Icon dùng SVG/PNG tự hosted (không emoji) -> không lỗi font "tofu".
CSS: public/assets/css/partials/07i-pdp-buybar.css
===================================================================== --}}
<div class="buybar">
    {{-- 1. Gọi điện: tel: mở ứng dụng gọi trên di động --}}
    <a class="buybar__iconbtn" href="tel:0362795897" aria-label="Gọi điện tư vấn 0362 795 897">
        <img src="{{ asset('assets/images/svg/icon-phone.svg') }}" alt="" width="20" height="20" aria-hidden="true">
        <span>Gọi điện</span>
    </a>

    {{-- 2. Zalo: chat trực tiếp với số hotline (zalo.me/<sdt>) --}}
        <a class="buybar__iconbtn buybar__iconbtn--zalo" href="https://zalo.me/0362795897" target="_blank"
            rel="noopener noreferrer" aria-label="Nhắn Zalo cho Mộc Xanh">
            <img src="{{ asset('assets/images/svg/zalo.png') }}" alt="" width="20" height="20" aria-hidden="true">
            <span>Zalo</span>
        </a>

        {{-- 3. Giỏ hàng: link tới /gio-hang + badge .cart-count (contract cart-badge.js) --}}
        <a class="buybar__cart" href="{{ route('web.cart.index') }}" aria-label="Xem giỏ hàng">
            <img src="{{ asset('assets/images/svg/icon-cart.svg') }}" alt="" width="20" height="20" aria-hidden="true">
            <span>Giỏ hàng</span>
            <b class="cart-count js-cart-count buybar__badge" hidden>0</b>
        </a>

        {{-- 4. Thêm vào giỏ: cart-add.js đọc data-product-id + radio variant + qty --}}
        <button class="btn btn--leaf add-cart" type="button" data-name="{{ $product->name }}"
            data-product-id="{{ $product->id }}">
            <span>Thêm vào giỏ</span>
        </button>

        {{-- 5. Mua ngay: buy-now.js đọc data-product-id + variant checked + qty rồi
        POST /gio-hang/mua-ngay -> redirect /thanh-toan. Khi loading JS thay
        textContent = 'Đang xử lý…' nên bên trong chỉ đặt 1 span text thuần. --}}
        <button class="btn btn--clay" id="buyNowMobile" type="button" data-product-id="{{ $product->id }}"
            data-name="{{ $product->name }}">
            <span class="btn__main">Mua ngay</span>
        </button>
</div>