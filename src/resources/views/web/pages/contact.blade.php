{{-- =====================================================================
TRANG TĨNH: LIÊN HỆ (nhóm "Về chúng tôi" — footer)
- Nội dung fix cứng 100% trong Blade, controller không trả dữ liệu.
===================================================================== --}}
<x-layouts.app title="Liên hệ Mộc Xanh | Hotline 0362 795 897"
    seoDescription="Kênh liên hệ Mộc Xanh: hotline 0362 795 897, email thaomocfarm@gmail.com, địa chỉ 789 Đường Quang Trung, Hà Đông, Hà Nội. Giờ làm việc 8:00–21:00, hỗ trợ đơn COD và đổi trả."
    :hide-catnav="true">

    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('web.home') }}">Trang chủ</a></li>
                <li><span aria-current="page">Liên hệ</span></li>
            </ol>
        </nav>

        <header class="sp-hero reveal">
            <p class="sp-hero__eyebrow">🌿 Về chúng tôi</p>
            <h1>Liên hệ với Mộc Xanh</h1>
            <p class="sp-hero__lead">
                Bạn cần tư vấn chọn món, tra cứu đơn hay hỗ trợ đổi trả? Gọi hotline hoặc gửi email — đội ngũ Mộc Xanh
                trực <b>8:00–21:00 hằng ngày (kể cả thứ Bảy)</b> và phản hồi trong vài phút.
            </p>
            <p class="sp-hero__updated">Hotline <a href="tel:0362795897">0362 795 897</a> · Email
                <a href="mailto:thaomocfarm@gmail.com">thaomocfarm@gmail.com</a>
            </p>
        </header>

        {{-- ============ KÊNH LIÊN HỆ ============ --}}
        <section class="sp-section reveal" aria-labelledby="c1">
            <h2 class="sec-title" id="c1">Kênh liên hệ</h2>
            <div class="sp-info-grid">
                <article class="sp-info-card">
                    <h3>📞 Hotline / Zalo</h3>
                    <ul>
                        <li><b>0362 795 897</b> — nghe máy 8:00–21:00</li>
                        <li>Tư vấn sản phẩm, báo giá sỉ, đặt đơn hộ</li>
                        <li>Khiếu nại tình trạng hàng trong 24h sau khi nhận</li>
                    </ul>
                    <p class="sp-info-card__note">Cách nhanh nhất: gọi và đọc mã đơn (ví dụ MX-0001).</p>
                </article>
                <article class="sp-info-card">
                    <h3>✉️ Email</h3>
                    <ul>
                        <li><b>thaomocfarm@gmail.com</b></li>
                        <li>Phản hồi trong vòng 12 giờ làm việc</li>
                        <li>Kèm ảnh/video và mã đơn nếu cần đổi trả</li>
                    </ul>
                    <p class="sp-info-card__note">Phù hợp trao đổi hợp tác, cung ứng số lượng lớn.</p>
                </article>
                <article class="sp-info-card">
                    <h3>📍 Cửa hàng / Kho</h3>
                    <ul>
                        <li><b>789 Đường Quang Trung, Hà Đông, Hà Nội</b></li>
                        <li>Mở cửa 8:00–21:00, tất cả các ngày</li>
                        <li>Đến xem hàng vui lòng gọi trước 30 phút</li>
                    </ul>
                    <p class="sp-info-card__note">Có chỗ đỗ xe máy; hỗ trợ xuất hóa đơn theo yêu cầu.</p>
                </article>
            </div>
        </section>

        {{-- ============ CHỌN ĐÚNG NGƯỜI ĐỂ NHANH ============ --}}
        <section class="sp-section reveal" aria-labelledby="c2">
            <h2 class="sec-title" id="c2">Nên liên hệ lúc nào?</h2>
            <ol class="sp-steps">
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true"></span>
                    <div class="sp-step__body">
                        <h3>Trước khi đặt hàng</h3>
                        <p>Hỏi về nguồn gốc, vị mặn/bốc, cách dùng và hạn dùng — nhân viên tư vấn chọn đúng món theo
                            khẩu
                            vị và ngân sách của bạn.</p>
                    </div>
                </li>
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true"></span>
                    <div class="sp-step__body">
                        <h3>Vừa đặt xong</h3>
                        <p>Cần sửa địa chỉ, số lượng, ghi chú? Gọi càng sớm càng tốt — đơn chưa bàn giao cho vận chuyển
                            sẽ được sửa miễn phí.</p>
                    </div>
                </li>
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true"></span>
                    <div class="sp-step__body">
                        <h3>Đã nhận hàng</h3>
                        <p>Kiểm tra đủ số lượng, bao bì nguyên vẹn rồi mới thanh toán COD. Hàng hở/méo/bẩm mốc → từ chối
                            nhận và báo ngay cho Mộc Xanh.</p>
                    </div>
                </li>
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true"></span>
                    <div class="sp-step__body">
                        <h3>Muốn đổi trả</h3>
                        <p>Gửi ảnh/video mở hộp kèm mã đơn qua hotline hoặc email; điều kiện chi tiết xem tại <a
                                href="{{ route('web.page.return-policy') }}">Chính sách đổi trả</a>.</p>
                    </div>
                </li>
            </ol>
        </section>

        {{-- ============ GHI CHÚ KHI GỬI THÔNG TIN ============ --}}
        <section class="sp-section reveal" aria-labelledby="c3">
            <h2 class="sec-title" id="c3">Để được xử lý nhanh, hãy cung cấp</h2>
            <ul class="sp-bullets">
                <li><b>Mã đơn hàng</b> và số điện thoại đã dùng khi đặt.</li>
                <li>Mô tả ngắn vấn đề (thiếu hàng, sai sản phẩm, chậm giao…) và mong muốn của bạn.</li>
                <li>Ảnh/video tình trạng sản phẩm & bao bì nếu khiếu nại chất lượng.</li>
                <li>Thông tin cá nhân của bạn chỉ được dùng để xử lý yêu cầu — xem <a
                        href="{{ route('web.page.privacy') }}">Chính sách bảo mật</a>.</li>
            </ul>
        </section>

        {{-- ============ FAQ LIÊN HỆ ============ --}}
        <section class="sp-section reveal" aria-labelledby="faqTitle">
            <h2 class="sec-title" id="faqTitle">Câu hỏi thường gặp</h2>
            <div class="sp-faq">
                <details class="sp-faq__item">
                    <summary>Tôi có thể mua trực tiếp tại cửa hàng không?</summary>
                    <div class="sp-faq__answer">
                        <p>Được. Địa chỉ 789 Đường Quang Trung, Hà Đông, Hà Nội mở cửa 8:00–21:00. Gọi
                            <a href="tel:0362795897">0362 795 897</a> trước 30 phút để chúng tôi chuẩn bị hàng bạn cần
                            xem.
                        </p>
                    </div>
                </details>
                <details class="sp-faq__item">
                    <summary>Mua số lượng lớn / làm đại lý thì liên hệ ai?</summary>
                    <div class="sp-faq__answer">
                        <p>Gửi email <a href="mailto:thaomocfarm@gmail.com">thaomocfarm@gmail.com</a> kèm số lượng dự
                            kiến mỗi tháng và khu vực phân phối. Chúng tôi báo giá sỉ, chính sách công nợ và hồ sơ giấy
                            tờ sản phẩm trong 12 giờ làm việc.</p>
                    </div>
                </details>
                <details class="sp-faq__item">
                    <summary>Gọi ngoài giờ làm việc thì sao?</summary>
                    <div class="sp-faq__answer">
                        <p>Bạn cứ nhắn Zalo/email; tin nhắn được trả lời vào đầu khung giờ 8:00 sáng hôm sau. Đơn gấp
                            trong ngày nên gọi trước 16:00 để kịp xử lý và giao trong ngày làm việc kế tiếp.</p>
                    </div>
                </details>
                <details class="sp-faq__item">
                    <summary>Xin hóa đơn VAT sau khi đặt hàng được không?</summary>
                    <div class="sp-faq__answer">
                        <p>Được. Ghi chú nhu cầu xuất hóa đơn ngay khi đặt, hoặc gửi tên công ty + mã số thuế qua email
                            trong vòng 24 giờ sau khi nhận hàng.</p>
                    </div>
                </details>
            </div>
        </section>

        {{-- ============ CTA ============ --}}
        <section class="sp-cta reveal" aria-labelledby="ctaTitle">
            <h2 id="ctaTitle">Bạn đã sẵn sàng trò chuyện?</h2>
            <p>Chọn kênh tiện nhất — chúng tôi luôn bắt máy trong giờ làm việc.</p>
            <div class="sp-cta__actions">
                <a class="btn btn--leaf" href="tel:0362795897">📞 Gọi 0362 795 897</a>
                <a class="btn btn--ghost" href="mailto:thaomocfarm@gmail.com">✉️ Gửi email</a>
                <a class="btn btn--ghost" href="{{ route('web.page.about') }}">🌿 Về Mộc Xanh</a>
            </div>
        </section>
    </div>
</x-layouts.app>