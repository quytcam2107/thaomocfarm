<footer class="footer">
    <div class="container footer__grid">
        <div>
            <p class="footer__brand">🌿 Thảo Mộc Farm</p>
            <p class="footer__desc">Đặc sản Tây Bắc & trà hoa thảo mộc nguyên chất, thu mua trực tiếp từ vùng trồng Sơn
                La, Điện Biên, Lào Cai.</p>
        </div>
        <nav aria-label="Hỗ trợ khách hàng">
            <h4>Hỗ trợ</h4>
            <a href="#">Hướng dẫn đặt hàng</a>
            <a href="#">Chính sách đổi trả</a>
            <a href="#">Chính sách bảo mật</a>
            <a href="#">Điều khoản</a>
        </nav>
        <nav aria-label="Danh mục">
            <h4>Danh mục</h4>
            <a href="{{ url('/category/thit-gac-bep') }}">Thịt gác bếp</a>
            <a href="{{ url('/category/gia-vi-tay-bac') }}">Gia vị Tây Bắc</a>
            <a href="{{ url('/category/tra-hoa') }}">Trà hoa thảo dược</a>
            <a href="{{ url('/category/combo-qua-tang') }}">Combo quà tặng</a>
        </nav>
        <div>
            <h4>Liên hệ</h4>
            <p>📍 789 Đường Quang Trung, Hà Đông, Hà Nội</p>
            <p>📞 <a href="tel:0362795897">0362 795 897</a></p>
            <p>✉️ <a href="mailto:thaomocfarm@gmail.com">thaomocfarm@gmail.com</a></p>
        </div>
    </div>
    <p class="footer__bottom container">© <span id="year">{{ date('Y') }}</span> Thảo Mộc Farm · Bản quyền thuộc về Thảo
        Mộc Farm</p>
</footer>