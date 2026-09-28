{{-- Nút "lên đầu trang" (back-to-top) cho các màn hình trang chủ.
- Mặc định ẩn trong CSS; app.js toggle class .is-visible khi cuộn quá 400px.
- Không đặt aria-hidden tĩnh: app.js sẽ set true khi nút ẩn để người dùng bàn phím
/ screen reader không bấm nhầm nút vô hình. --}}
<button type="button" class="back-to-top" id="backToTop" aria-label="Lên đầu trang">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
        <path d="M12 19V5M5 12l7-7 7 7" />
    </svg>
</button>