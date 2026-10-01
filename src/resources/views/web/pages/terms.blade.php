{{-- =====================================================================
TRANG TĨNH: ĐIỀU KHOẢN SỬ DỤNG
- Nội dung fix cứng 100% trong Blade, controller không trả dữ liệu.
===================================================================== --}}
<x-layouts.app title="Điều khoản sử dụng | Mộc Xanh"
    seoDescription="Điều khoản sử dụng website Mộc Xanh: phạm vi áp dụng, tài khoản, quy tắc đặt hàng và thanh toán COD, sở hữu trí tuệ, giới hạn trách nhiệm, xử lý vi phạm và luật áp dụng."
    :hide-catnav="true">

    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('web.home') }}">Trang chủ</a></li>
                <li><span aria-current="page">Điều khoản sử dụng</span></li>
            </ol>
        </nav>

        <header class="sp-hero reveal">
            <p class="sp-hero__eyebrow">🌿 Hỗ trợ khách hàng</p>
            <h1>Điều khoản sử dụng</h1>
            <p class="sp-hero__lead">
                Bằng việc truy cập và đặt hàng trên website Mộc Xanh, bạn đồng ý tuân thủ các điều khoản dưới đây.
                Vui lòng đọc kỹ — đặc biệt mục đặt hàng, thanh toán và giới hạn trách nhiệm.
            </p>
            <p class="sp-hero__updated">Cập nhật ngày 01/10/2026 · Phiên bản 1.0</p>
        </header>

        {{-- ============ 1. PHẠM VI ÁP DỤNG ============ --}}
        <section class="sp-section reveal" aria-labelledby="t1">
            <h2 class="sec-title" id="t1">1. Phạm vi áp dụng</h2>
            <ul class="sp-bullets">
                <li>Áp dụng cho mọi người dùng website (khách vãng lai và khách đã từng đặt hàng).</li>
                <li>Đơn vị quản lý: Mộc Xanh — Thảo mộc & đặc sản Tây Bắc, địa chỉ 789 Đường Quang Trung, Hà Đông, Hà
                    Nội.</li>
                <li>Mộc Xanh có thể cập nhật điều khoản; phiên bản mới hiển thị công khai trên trang này kèm ngày cập
                    nhật.</li>
            </ul>
        </section>

        {{-- ============ 2. TÀI KHOẢN & THÔNG TIN ============ --}}
        <section class="sp-section reveal" aria-labelledby="t2">
            <h2 class="sec-title" id="t2">2. Tài khoản & thông tin người dùng</h2>
            <ul class="sp-bullets">
                <li><b>Bạn cam kết:</b> cung cấp họ tên, số điện thoại, địa chỉ chính xác và thuộc về mình khi đặt
                    hàng.</li>
                <li>Không giả mạo danh tính bên khác, không tạo đơn ảo gây phiền nhiễu hoạt động bán hàng.</li>
                <li>Tự bảo mật thiết bị dùng để truy cập website; chịu trách nhiệm với giao dịch phát sinh từ thiết bị
                    của mình.</li>
            </ul>
        </section>

        {{-- ============ 3. ĐẶT HÀNG & THANH TOÁN ============ --}}
        <section class="sp-section reveal" aria-labelledby="t3">
            <h2 class="sec-title" id="t3">3. Đặt hàng, giá cả & thanh toán</h2>
            <ul class="sp-bullets">
                <li>Giá hiển thị bằng <b>VND</b>, đã gồm thuế theo quy định tại thời điểm đặt; phí vận chuyển tính ở
                    bước thanh toán.</li>
                <li>Flash sale/coupon có số lượng và thời hạn; hết điều kiện, hệ thống tự hoàn giá niêm yết.</li>
                <li>Hình thức mặc định: <b>COD</b> — thanh toán khi nhận. Chuyển khoản áp dụng theo thỏa thuận với nhân
                    viên xác nhận đơn.</li>
                <li>Đơn được coi là hợp lệ sau khi nhân viên gọi xác nhận; đơn nhiều lần không nghe máy hoặc từ chối
                    nhận có thể bị tạm ngưng phục vụ.</li>
            </ul>
        </section>

        {{-- ============ 4. GIAO HÀNG ============ --}}
        <section class="sp-section reveal" aria-labelledby="t4">
            <h2 class="sec-title" id="t4">4. Giao hàng</h2>
            <ul class="sp-bullets">
                <li>Thời gian giao tham khảo theo <a href="{{ route('web.page.order-guide') }}">Hướng dẫn đặt hàng</a>;
                    sự kiện bất khả kháng (thiên tai, giãn cách, lỗi mạng vận chuyển) được thông báo sớm nhất có thể.
                </li>
                <li>Khách kiểm tra hàng trước khi thanh toán; khiếu nại tình trạng hàng nên gửi trong vòng 24 giờ sau
                    khi nhận kèm ảnh/video.</li>
                <li>Việc đổi trả tuân theo <a href="{{ route('web.page.return-policy') }}">Chính sách đổi trả</a>.</li>
            </ul>
        </section>

        {{-- ============ 5. NỘI DUNG & SỞ HỮU TRÍ TUỆ ============ --}}
        <section class="sp-section reveal" aria-labelledby="t5">
            <h2 class="sec-title" id="t5">5. Nội dung & sở hữu trí tuệ</h2>
            <ul class="sp-bullets">
                <li>Toàn bộ hình ảnh, bài viết, thương hiệu "Mộc Xanh" thuộc quyền của chúng tôi hoặc nguồn được phép
                    sử dụng.</li>
                <li>Không sao chép, đăng lại vì mục đích thương mại khi chưa được chấp thuận bằng văn bản.</li>
                <li>Đánh giá của khách trên trang sản phẩm: không chứa ngôn từ thù ghét, thông tin cá nhân bên thứ ba
                    hoặc nội dung vi phạm pháp luật; Mộc Xanh có quyền gỡ bình luận vi phạm.</li>
            </ul>
        </section>

        {{-- ============ 6. GIỚI HẠN TRÁCH NHIỆM ============ --}}
        <section class="sp-section reveal" aria-labelledby="t6">
            <h2 class="sec-title" id="t6">6. Giới hạn trách nhiệm</h2>
            <ul class="sp-bullets">
                <li>Website cung cấp thông tin mô tả, hướng dẫn sử dụng mang tính tham khảo; phản ứng cơ địa mỗi người
                    có thể khác nhau với thực phẩm/thảo mộc.</li>
                <li>Người dùng có bệnh nền, phụ nữ mang thai hoặc đang điều trị nên tham vấn chuyên gia y tế trước khi
                    dùng sản phẩm thảo dược.</li>
                <li>Mộc Xanh chịu trách nhiệm trong phạm vi pháp luật Việt Nam và tối đa bằng giá trị đơn hàng liên
                    quan, trừ trường hợp luật định khác.</li>
                <li>Chúng tôi không bảo đảm website hoạt động liên tục 100%; sự cố kỹ thuật sẽ được khắc phục và thông
                    báo.</li>
            </ul>
        </section>

        {{-- ============ 7. XỬ LÝ VI PHẠM ============ --}}
        <section class="sp-section reveal" aria-labelledby="t7">
            <h2 class="sec-title" id="t7">7. Chấm dứt & xử lý vi phạm</h2>
            <ul class="sp-bullets">
                <li>Đơn đặt trùng lặp bất thường, thanh toán gian lận, lợi dụng chính sách đổi trả → tạm giữ/gỡ ưu đãi
                    và từ chối phục vụ có thời hạn.</li>
                <li>Tranh chấp phát sinh ngoài thỏa thuận được đưa ra cơ quan có thẩm quyền tại Việt Nam giải quyết.
                </li>
            </ul>
        </section>

        {{-- ============ 8. LUẬT ÁP DỤNG & LIÊN HỆ ============ --}}
        <section class="sp-section sp-section--last reveal" aria-labelledby="t8">
            <h2 class="sec-title" id="t8">8. Luật áp dụng & liên hệ</h2>
            <div class="sp-card-note">
                <ul class="sp-bullets">
                    <li>Luật áp dụng: pháp luật Việt Nam (Bao gồm Bộ luật Dân sự 2015, Luật Bảo vệ quyền lợi người tiêu
                        dùng, Nghị định về thương mại điện tử).</li>
                    <li>Góp ý về điều khoản: hotline <a href="tel:0362795897">0362 795 897</a> hoặc email
                        <a href="mailto:thaomocfarm@gmail.com">thaomocfarm@gmail.com</a>.
                    </li>
                </ul>
            </div>
        </section>

        {{-- ============ CTA ============ --}}
        <section class="sp-cta reveal" aria-labelledby="ctaTitle">
            <h2 id="ctaTitle">Sẵn sàng trải nghiệm đặc sản Tây Bắc?</h2>
            <p>Đọc xong điều khoản, bạn có thể bắt đầu ngay với hướng dẫn đặt hàng 5 bước.</p>
            <div class="sp-cta__actions">
                <a class="btn btn--leaf" href="{{ route('web.page.order-guide') }}">🛒 Hướng dẫn đặt hàng</a>
                <a class="btn btn--ghost" href="{{ route('web.products.index') }}">🌿 Xem tất cả sản phẩm</a>
                <a class="btn btn--ghost" href="{{ route('web.page.privacy') }}">🔒 Chính sách bảo mật</a>
            </div>
        </section>
    </div>
</x-layouts.app>