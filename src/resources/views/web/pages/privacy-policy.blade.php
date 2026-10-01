{{-- =====================================================================
TRANG TĨNH: CHÍNH SÁCH BẢO MẬT
- Nội dung fix cứng 100% trong Blade, controller không trả dữ liệu.
===================================================================== --}}
<x-layouts.app title="Chính sách bảo mật | Mộc Xanh"
    seoDescription="Chính sách bảo mật Mộc Xanh: thông tin thu thập, mục đích sử dụng, thời gian lưu trữ, cookie, đối tác chia sẻ, biện pháp bảo vệ và quyền của khách hàng theo Nghị định 13/2023/NĐ-CP."
    :hide-catnav="true">

    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('web.home') }}">Trang chủ</a></li>
                <li><span aria-current="page">Chính sách bảo mật</span></li>
            </ol>
        </nav>

        <header class="sp-hero reveal">
            <p class="sp-hero__eyebrow">🌿 Hỗ trợ khách hàng</p>
            <h1>Chính sách bảo mật</h1>
            <p class="sp-hero__lead">
                Mộc Xanh chỉ thu thập những thông tin <b>cần thiết để bán hàng và giao hàng cho bạn</b>, không bán hay
                trao đổi dữ liệu khách hàng với bên thứ ba vì mục đích quảng cáo. Chính sách áp dụng từ ngày
                01/10/2026.
            </p>
            <p class="sp-hero__updated">Cập nhật ngày 01/10/2026 · Phù hợp Nghị định 13/2023/NĐ-CP về bảo vệ dữ liệu cá
                nhân</p>
        </header>

        {{-- ============ 1. THÔNG TIN THU THẬP ============ --}}
        <section class="sp-section reveal" aria-labelledby="s1">
            <h2 class="sec-title" id="s1">1. Thông tin chúng tôi thu thập</h2>
            <ul class="sp-bullets">
                <li><b>Khi đặt hàng:</b> họ tên, số điện thoại, email (tùy chọn), địa chỉ nhận hàng, ghi chú đơn.</li>
                <li><b>Khi tương tác:</b> nội dung đánh giá sản phẩm, câu hỏi gửi qua hotline/email/form liên hệ.</li>
                <li><b>Tự động qua kỹ thuật:</b> loại trình duyệt/thiết bị, trang tham chiếu, thống kê truy cập ẩn danh
                    (không định danh cá nhân).</li>
                <li>Mộc Xanh <b>không yêu cầu</b> giấy tờ tùy thân, thông tin tài khoản ngân hàng hay thẻ thanh toán để
                    đặt đơn COD.</li>
            </ul>
        </section>

        {{-- ============ 2. MỤC ĐÍCH SỬ DỤNG ============ --}}
        <section class="sp-section reveal" aria-labelledby="s2">
            <h2 class="sec-title" id="s2">2. Mục đích sử dụng thông tin</h2>
            <ul class="sp-bullets">
                <li>Xác nhận đơn, đóng gói, giao hàng và xử lý đổi trả/bảo hành.</li>
                <li>Chăm sóc sau bán: nhắc đơn, gửi voucher ưu đãi nếu bạn đồng ý nhận.</li>
                <li>Cải thiện website: biết mục nào nhiều người xem để bổ sung sản phẩm phù hợp.</li>
                <li>Nghiệm thu khiếu nại và tuân thủ yêu cầu của cơ quan nhà nước có thẩm quyền.</li>
            </ul>
        </section>

        {{-- ============ 3. THỜI GIAN LƯU TRỮ ============ --}}
        <section class="sp-section reveal" aria-labelledby="s3">
            <h2 class="sec-title" id="s3">3. Thời gian lưu trữ</h2>
            <ul class="sp-bullets">
                <li>Dữ liệu đơn hàng lưu tối đa <b>5 năm</b> theo nghĩa vụ kế toán – thuế.</li>
                <li>Dữ liệu marketing (nếu bạn đăng ký nhận tin) lưu đến khi bạn rút lại sự đồng ý.</li>
                <li>Sau thời hạn trên, thông tin được xóa hoặc ẩn danh hóa khỏi hệ thống.</li>
            </ul>
        </section>

        {{-- ============ 4. COOKIE ============ --}}
        <section class="sp-section reveal" aria-labelledby="s4">
            <h2 class="sec-title" id="s4">4. Cookie & công nghệ tương tự</h2>
            <ul class="sp-bullets">
                <li><b>Cookie phiên làm việc:</b> giữ trạng thái đăng nhập và mã giỏ hàng của bạn; hết hiệu lực khi
                    đóng trình duyệt hoặc sau vài tuần không hoạt động.</li>
                <li>Website hiện dùng cookie kỹ thuật là chính — không theo dõi hành vi để bán quảng cáo.</li>
                <li>Bạn có thể xóa/chặn cookie trong cài đặt trình duyệt; một số chức năng (giỏ hàng) sẽ ngừng hoạt
                    động bình thường.</li>
            </ul>
        </section>

        {{-- ============ 5. BÊN THỨ BA ============ --}}
        <section class="sp-section reveal" aria-labelledby="s5">
            <h2 class="sec-title" id="s5">5. Chia sẻ với bên thứ ba</h2>
            <ul class="sp-bullets">
                <li><b>Đơn vị vận chuyển:</b> chỉ nhận tên, số điện thoại, địa chỉ để giao đơn.</li>
                <li><b>Cơ quan nhà nước:</b> khi có yêu cầu hợp pháp bằng văn bản.</li>
                <li>Không chia sẻ, mua bán dữ liệu khách hàng cho mục đích quảng cáo của bên khác.</li>
            </ul>
        </section>

        {{-- ============ 6. BẢO VỆ THÔNG TIN ============ --}}
        <section class="sp-section reveal" aria-labelledby="s6">
            <h2 class="sec-title" id="s6">6. Biện pháp bảo vệ thông tin</h2>
            <ul class="sp-bullets">
                <li>Toàn bộ website chạy nền tảng Laravel với phòng vệ CSRF/XSS chuẩn và HTTPS.</li>
                <li>Truy cập cơ sở dữ liệu giới hạn theo vai trò; dữ liệu nhạy cảm không hiển thị ở màn hình công khai.
                </li>
                <li>Sao lưu định kỳ; rà soát lỗ hổng khi nâng cấp hệ thống.</li>
                <li>Phía bạn: nên thoát tài khoản trên thiết bị công cộng và không chia sẻ mã OTP/SMS.</li>
            </ul>
        </section>

        {{-- ============ 7. QUYỀN CỦA BẠN ============ --}}
        <section class="sp-section reveal" aria-labelledby="s7">
            <h2 class="sec-title" id="s7">7. Quyền của bạn đối với dữ liệu cá nhân</h2>
            <ul class="sp-bullets">
                <li>Được <b>biết, kiểm tra, chỉnh sửa</b> thông tin của mình.</li>
                <li>Rút lại đồng ý nhận tin bất kỳ lúc nào (làm theo hướng dẫn hủy đăng ký trong email).</li>
                <li>Yêu cầu <b>xóa dữ liệu</b> không còn cần cho nghĩa vụ pháp lý.</li>
                <li>Khiếu nại về xử lý dữ liệu qua hotline/email bên dưới; phản hồi trong 7 ngày làm việc.</li>
            </ul>
        </section>

        {{-- ============ 8. LIÊN HỆ ============ --}}
        <section class="sp-section sp-section--last reveal" aria-labelledby="s8">
            <h2 class="sec-title" id="s8">8. Đầu mối bảo vệ dữ liệu</h2>
            <div class="sp-card-note">
                <ul class="sp-bullets">
                    <li>📍 789 Đường Quang Trung, Hà Đông, Hà Nội</li>
                    <li>📞 Hotline: <a href="tel:0362795897">0362 795 897</a></li>
                    <li>✉️ Email: <a href="mailto:thaomocfarm@gmail.com">thaomocfarm@gmail.com</a></li>
                </ul>
            </div>
        </section>

        {{-- ============ CTA ============ --}}
        <section class="sp-cta reveal" aria-labelledby="ctaTitle">
            <h2 id="ctaTitle">Mua sắm an tâm cùng Mộc Xanh</h2>
            <p>Mọi đơn hàng đều mã hóa phiên giao dịch và chỉ dùng thông tin cho việc giao nhận.</p>
            <div class="sp-cta__actions">
                <a class="btn btn--leaf" href="{{ route('web.products.index') }}">🛒 Xem sản phẩm</a>
                <a class="btn btn--ghost" href="{{ route('web.page.terms') }}">📄 Điều khoản sử dụng</a>
                <a class="btn btn--ghost" href="mailto:thaomocfarm@gmail.com">✉️ Gửi yêu cầu về dữ liệu</a>
            </div>
        </section>
    </div>
</x-layouts.app>