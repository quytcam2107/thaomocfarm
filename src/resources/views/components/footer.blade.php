<footer class="footer">
    <div class="container footer__grid">
        <div>
            <p class="footer__brand">🌿 Mộc Xanh</p>
            <p class="footer__desc">Đặc sản Tây Bắc & trà hoa thảo mộc nguyên chất, thu mua trực tiếp từ vùng trồng Sơn
                La, Điện Biên, Lào Cai.</p>
        </div>
        <nav aria-label="Hỗ trợ khách hàng">
            <h4>Hỗ trợ</h4>
            <a href="{{ route('web.page.order-guide') }}">Hướng dẫn đặt hàng</a>
            <a href="{{ route('web.page.return-policy') }}">Chính sách đổi trả</a>
            <a href="{{ route('web.page.privacy') }}">Chính sách bảo mật</a>
            <a href="{{ route('web.page.terms') }}">Điều khoản</a>
        </nav>
        <nav aria-label="Danh mục">
            <h4>Danh mục</h4>
            <a href="{{ url('/thit-gac-bep') }}">Thịt gác bếp</a>
            <a href="{{ url('/gia-vi-tay-bac') }}">Gia vị Tây Bắc</a>
            <a href="{{ url('/tra-hoa') }}">Trà hoa thảo dược</a>
            <a href="{{ url('/combo-qua-tang') }}">Combo quà tặng</a>
        </nav>
        <div>
            <h4>Liên hệ</h4>
            <p>📍 789 Đường Quang Trung, Hà Đông, Hà Nội</p>
            <p>📞 <a href="tel:0362795897">0362 795 897</a></p>
            <p>✉️ <a href="mailto:thaomocfarm@gmail.com">thaomocfarm@gmail.com</a></p>
        </div>
    </div>
    <p class="footer__bottom container">© <span id="year">{{ date('Y') }}</span> Mộc Xanh · Bản quyền thuộc về Mộc Xanh
    </p>
</footer>