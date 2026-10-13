{{-- =====================================================================
FLOAT-DOCK — Cụm 2 nút nổi "Gọi điện · Zalo" ở góc dưới phải (chỉ desktop)
- Mobile (<768px): floatnav đã có sẵn 2 ô Gọi điện/Zalo => CSS ẩn .float-dock
    (display:none từ partials/12-layout-extras.css), tránh treo 2 nút giữa màn hình.
    Riêng trang chi tiết sản phẩm (body.has-buybar): buybar đáy screen cũng đã có
    2 nút Gọi điện/Zalo => 12-layout-extras.css tiếp tục ẩn dock ở mobile (PDP).
    - Desktop (>=768px): floatnav bị CSS display:none => dock thay thế 2 chức năng
    liên hệ này. Xếp DỌC: Gọi điện ở TRÊN, Zalo ở DƯỚI; riêng nút back-to-top
    do app-backtotop.js điều khiển (ẩn ở đầu trang, hiện khi vuốt xuống qua ngưỡng)
    và được CSS neo ngay DƯỚI dock (bottom 132px > dock chiếm tới mốc 122px).
    - Hotline/Zalo fix cứng 0362 795 897 (khớp topbar header + floatnav + trang Liên hệ).
    ===================================================================== --}}
    <nav class="float-dock" aria-label="Liên hệ nhanh">
        {{-- 1. Gọi điện: tel: mở ứng dụng gọi --}}
        <a href="tel:0362795897" class="fd-phone" aria-label="Gọi điện tư vấn 0362 795 897">
            <img src="{{ asset('assets/images/svg/icon-phone.svg') }}" alt="" width="20" height="20" aria-hidden="true">
            {{-- Tooltip chữ: chỉ hiện khi hover ở tablet/desktop (>=768px) --}}
            <span class="tooltip-text" aria-hidden="true">Gọi ngay cho chúng tôi</span>
        </a>

        {{-- 2. Zalo: chat trực tiếp với số hotline (zalo.me/<sdt>) --}}
            <a href="https://zalo.me/0362795897" class="fd-zalo" target="_blank" rel="noopener noreferrer"
                aria-label="Nhắn Zalo cho Mộc Xanh">
                <img src="{{ asset('assets/images/svg/zalo.png') }}" alt="" width="20" height="20" aria-hidden="true">
                {{-- Tooltip chữ: chỉ hiện khi hover ở tablet/desktop (>=768px) --}}
                <span class="tooltip-text" aria-hidden="true">Chat với chúng tôi qua zalo</span>
            </a>
    </nav>