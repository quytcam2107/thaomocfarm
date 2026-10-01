{{-- =====================================================================
TRANG TĨNH: CHÍNH SÁCH ĐỔI TRẢ
- Nội dung fix cứng 100% trong Blade.
- $suggestedProducts: gợi ý sản phẩm trà hoa (HomeService cache).
===================================================================== --}}
<x-layouts.app title="Chính sách đổi trả | Mộc Xanh"
    seoDescription="Chính sách đổi trả Mộc Xanh: đổi trong 7 ngày với lỗi sản xuất/hư hỏng do vận chuyển, điều kiện hàng đổi trả, quy trình 4 bước và thời gian hoàn tiền."
    :hide-catnav="true">

    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('web.home') }}">Trang chủ</a></li>
                <li><span aria-current="page">Chính sách đổi trả</span></li>
            </ol>
        </nav>

        <header class="sp-hero reveal">
            <p class="sp-hero__eyebrow">🌿 Hỗ trợ khách hàng</p>
            <h1>Chính sách đổi trả</h1>
            <p class="sp-hero__lead">
                Mộc Xanh cam kết đổi trả minh bạch, nhanh gọn để bạn luôn yên tâm khi mua đặc sản và thảo mộc.
                Tóm tắt: <b>đổi trong 7 ngày</b> nếu hàng lỗi hoặc hư hỏng do vận chuyển, <b>hoàn tiền trong 3–5
                    ngày</b>
                sau khi nhận lại hàng.
            </p>
            <p class="sp-hero__updated">Cập nhật ngày 01/10/2026 · Áp dụng cho mọi đơn trên website & hotline</p>
        </header>

        {{-- ============ CÁC TRƯỜNG HỢP ĐƯỢC ĐỔI TRẢ ============ --}}
        <section class="sp-section reveal" aria-labelledby="caseTitle">
            <h2 class="sec-title" id="caseTitle">Các trường hợp được đổi trả</h2>
            <ul class="sp-checklist sp-checklist--yes">
                <li>Hàng<b>lỗi từ nhà sản xuất</b>: ẩm mốc, có sâu mọt, mùi lạ khác mô tả, hạn sử dụng in sai.</li>
                <li>Hàng <b>hư hỏng do vận chuyển</b>: bao bì rách/nứt, vơi số lượng, biến dạng so với ảnh chốt đơn.
                </li>
                <li>Mộc Xanh <b>giao nhầm</b> sản phẩm, nhầm quy cách/khối lượng so với đơn đã xác nhận.</li>
                <li>Giao <b>trễ quá 3 ngày làm việc</b> so với lịch hẹn đã chốt qua điện thoại (áp dụng hoàn phí ship).
                </li>
            </ul>
        </section>

        {{-- ============ TRƯỜNG HỢP KHÔNG ÁP DỤNG ============ --}}
        <section class="sp-section reveal" aria-labelledby="naTitle">
            <h2 class="sec-title" id="naTitle">Trường hợp không áp dụng đổi trả</h2>
            <ul class="sp-checklist sp-checklist--no">
                <li>Sản phẩm <b>đã mở bao bì và dùng một phần</b> (đặc thù thực phẩm), trừ khi lỗi được ghi nhận theo
                    mục trên.</li>
                <li>Khách thay đổi sở thích cá nhân sau khi nhận (với nhóm thực phẩm/trà); các mặt hàng phụ kiện, hộp
                    quà còn nguyên vẹn vẫn hỗ trợ đổi trong 7 ngày.</li>
                <li>Hao hụt, xuống cấp do <b>bảo quản sai</b> phía khách: để nơi ẩm, nắng trực tiếp, mở kín không đúng
                    cách.</li>
                <li>Quá thời hạn 7 ngày kể từ khi đơn trạng thái "Giao thành công".</li>
            </ul>
        </section>

        {{-- ============ ĐIỀU KIỆN HÀNG ĐỔI TRẢ ============ --}}
        <section class="sp-section reveal" aria-labelledby="condTitle">
            <h2 class="sec-title" id="condTitle">Điều kiện hàng gửi đổi trả</h2>
            <div class="sp-card-note">
                <ul class="sp-bullets">
                    <li>Còn <b>mã đơn hàng</b> (xem ở màn hình đặt hàng thành công hoặc tin nhắn xác nhận).</li>
                    <li>Gửi kèm <b>ảnh/video</b> tình trạng lỗi — bằng chứng rõ giúp duyệt nhanh trong 24 giờ.</li>
                    <li>Giữ bao bì gốc, tem/seal; với quà tặng kèm (nếu có) gửi lại đầy đủ.</li>
                </ul>
            </div>
        </section>

        {{-- ============ QUY TRÌNH 4 BƯỚC ============ --}}
        <section class="sp-section reveal" aria-labelledby="procTitle">
            <h2 class="sec-title" id="procTitle">Quy trình đổi trả 4 bước</h2>
            <ol class="sp-steps">
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true">1</span>
                    <div class="sp-step__body">
                        <h3>Liên hệ & cung cấp thông tin</h3>
                        <p>Gọi <a href="tel:0362795897">0362 795 897</a> hoặc email
                            <a href="mailto:thaomocfarm@gmail.com">thaomocfarm@gmail.com</a>, đọc mã đơn + mô tả lỗi +
                            gửi ảnh/video. Thời gian phản hồi yêu cầu: trong 24 giờ làm việc.
                        </p>
                    </div>
                </li>
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true">2</span>
                    <div class="sp-step__body">
                        <h3>Duyệt yêu cầu & hướng dẫn gửi hàng</h3>
                        <p>Nếu thuộc diện đổi trả, nhân viên gửi biên bản duyệt qua Zalo/SMS kèm địa chỉ nhận. Hàng lỗi
                            do nhà sản xuất/vận chuyển: Mộc Xanh chịu phí hai chiều; trường hợp khác: khách chịu phí
                            gửi về.</p>
                    </div>
                </li>
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true">3</span>
                    <div class="sp-step__body">
                        <h3>Kiểm tra hàng nhận lại</h3>
                        <p>Kho kiểm trong 1–2 ngày làm việc. Hàng đạt điều kiện sẽ được đổi ngay sản phẩm cùng loại
                            hoặc quy đổi giá trị sang món khác theo yêu cầu.</p>
                    </div>
                </li>
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true">4</span>
                    <div class="sp-step__body">
                        <h3>Hoàn tiền / đổi hàng</h3>
                        <p>Đổi hàng: gửi đi trong 1–2 ngày sau khi kiểm đạt. Hoàn tiền (COD): chuyển khoản trong
                            <b>3–5 ngày làm việc</b>; hoàn bằng voucher nếu khách chọn, giá trị cộng thêm 5%.
                        </p>
                    </div>
                </li>
            </ol>
        </section>

        {{-- ============ BẢNG THỜI GIAN XỬ LÝ ============ --}}
        <section class="sp-section reveal" aria-labelledby="timeTitle">
            <h2 class="sec-title" id="timeTitle">Thời gian xử lý</h2>
            <div class="sp-table-wrap">
                <table class="sp-table">
                    <thead>
                        <tr>
                            <th scope="col">Giai đoạn</th>
                            <th scope="col">Thời gian cam kết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Phản hồi yêu cầu đổi trả</td>
                            <td>Trong 24 giờ làm việc</td>
                        </tr>
                        <tr>
                            <td>Kiểm hàng nhận lại</td>
                            <td>1–2 ngày làm việc</td>
                        </tr>
                        <tr>
                            <td>Gửi hàng đổi mới</td>
                            <td>1–2 ngày sau khi kiểm đạt</td>
                        </tr>
                        <tr>
                            <td>Hoàn tiền chuyển khoản</td>
                            <td>3–5 ngày làm việc</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ============ LƯU Ý BẢO QUẢN ============ --}}
        <section class="sp-section reveal" aria-labelledby="careTitle">
            <h2 class="sec-title" id="careTitle">Lưu ý để tránh phải đổi trả</h2>
            <ul class="sp-tips">
                <li class="sp-tip">
                    <span class="sp-tip__icon" aria-hidden="true">🍵</span>
                    <p><b>Trà hoa & thảo mộc:</b> bảo quản nơi khô ráo, kín gió; đậy kín sau mỗi lần dùng để giữ hương
                        và tránh ẩm.</p>
                </li>
                <li class="sp-tip">
                    <span class="sp-tip__icon" aria-hidden="true">🥩</span>
                    <p><b>Thịt gác bếp & gia vị:</b> ngăn mát tủ lạnh nếu dùng lâu hơn 2 tuần; bọc kín, tránh lẫn mùi.
                    </p>
                </li>
                <li class="sp-tip">
                    <span class="sp-tip__icon" aria-hidden="true">📦</span>
                    <p><b>Khai hàng cẩn thận:</b> quay video mở hộp là căn cứ nhanh nhất để hai bên thống kê mức độ hư
                        hỏng và xử lý quyền lợi của bạn.</p>
                </li>
            </ul>
        </section>

        {{-- ============ GỢI Ý MUA NHANH ============ --}}
        @if (!empty($suggestedProducts))
            <section class="sp-section sp-section--last reveal" aria-labelledby="suggestTitle">
                <h2 class="sec-title" id="suggestTitle">Trà hoa bán chạy — An tâm đổi trả theo chính sách này</h2>
                <div class="product-grid">
                    @foreach ($suggestedProducts as $product)
                        <x-ui.product-card :url="$product['url']" :image="$product['image']" :name="$product['name']"
                            :price="$product['price']" :oldPrice="$product['old_price']"
                            :discount="$product['discount_percent']" :rating="$product['rating_avg']"
                            :sold="$product['sold_count']" :productId="$product['product_id']"
                            :variantId="$product['variant_id']" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ============ CTA LIÊN HỆ ============ --}}
        <section class="sp-cta reveal" aria-labelledby="ctaTitle">
            <h2 id="ctaTitle">Cần đổi trả ngay?</h2>
            <p>Chuẩn bị mã đơn và ảnh tình trạng hàng, chúng tôi duyệt trong 24 giờ làm việc.</p>
            <div class="sp-cta__actions">
                <a class="btn btn--leaf" href="tel:0362795897">📞 Hotline 0362 795 897</a>
                <a class="btn btn--ghost" href="mailto:thaomocfarm@gmail.com">✉️ Gửi ảnh qua email</a>
                <a class="btn btn--ghost" href="{{ route('web.page.order-guide') }}">🛒 Hướng dẫn đặt hàng</a>
            </div>
        </section>
    </div>
</x-layouts.app>