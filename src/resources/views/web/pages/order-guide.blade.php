{{-- =====================================================================
TRANG TĨNH: HƯỚNG DẪN ĐẶT HÀNG
- Nội dung fix cứng 100% trong Blade (không lấy từ controller/CMS).
- $suggestedProducts: 4 sản phẩm bán chạy thật (HomeService cache) — block gợi ý mua nhanh.
===================================================================== --}}
<x-layouts.app title="Hướng dẫn đặt hàng | Mộc Xanh"
    seoDescription="Hướng dẫn đặt hàng tại Mộc Xanh chi tiết 5 bước: chọn món, thêm giỏ, điền thông tin, xác nhận COD và theo dõi đơn. Kèm mẹo đặt hàng nhanh, thời gian giao và câu hỏi thường gặp."
    :hide-catnav="true">

    <div class="container">
        {{-- Breadcrumb dùng markup tĩnh đồng bộ trang Giỏ hàng/Thành công --}}
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('web.home') }}">Trang chủ</a></li>
                <li><span aria-current="page">Hướng dẫn đặt hàng</span></li>
            </ol>
        </nav>

        <header class="sp-hero reveal">
            <p class="sp-hero__eyebrow">🌿 Hỗ trợ khách hàng</p>
            <h1>Hướng dẫn đặt hàng tại Mộc Xanh</h1>
            <p class="sp-hero__lead">
                Chỉ mất khoảng <b>2 phút</b> để hoàn thành một đơn hàng. Đặt theo 5 bước bên dưới, hoặc gọi
                <a class="sp-hero__inline-link" href="tel:0362795897">hotline 0362 795 897</a> — nhân viên sẽ đặt giúp
                bạn.
            </p>
            <p class="sp-hero__updated">Cập nhật ngày 01/10/2026 · Áp dụng cho website & hotline</p>
        </header>

        {{-- ============ 5 BƯỚC ĐẶT HÀNG ============ --}}
        <section class="sp-section reveal" aria-labelledby="stepsTitle">
            <h2 class="sec-title" id="stepsTitle">5 bước đặt hàng</h2>
            <ol class="sp-steps">
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true">1</span>
                    <div class="sp-step__body">
                        <h3>Chọn sản phẩm</h3>
                        <p>
                            Truy cập mục <a href="{{ route('web.products.index') }}">Tất cả sản phẩm</a> hoặc chọn
                            danh mục ở menu đầu trang: thịt gác bếp, gia vị Tây Bắc, trà hoa thảo mộc, combo quà tặng.
                            Bấm vào sản phẩm để xem ảnh thật, mô tả, đánh giá và tồn kho.
                        </p>
                    </div>
                </li>
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true">2</span>
                    <div class="sp-step__body">
                        <h3>Thêm vào giỏ hàng</h3>
                        <p>
                            Ở trang chi tiết, chọn khối lượng/quy cách (nếu có), nhập số lượng rồi bấm
                            <b>Thêm vào giỏ</b>. Muốn mua ngay lập tức, bấm <b>Mua ngay</b> — hệ thống tự thêm giỏ và
                            chuyển thẳng tới trang thanh toán. Số hiện trên icon giỏ hàng ở đầu trang là tổng sản phẩm
                            bạn đang có.
                        </p>
                    </div>
                </li>
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true">3</span>
                    <div class="sp-step__body">
                        <h3>Kiểm tra giỏ & áp mã giảm giá</h3>
                        <p>
                            Mở <a href="{{ route('web.cart.index') }}">Giỏ hàng</a> để kiểm tra lại sản phẩm, tăng/giảm
                            số lượng hoặc xóa món không cần. Nếu có mã giảm giá (coupon), nhập mã vào ô
                            <b>Mã giảm giá</b> rồi bấm <b>Áp dụng</b> — giá trị giảm hiển thị ngay trong phần tóm tắt.
                        </p>
                    </div>
                </li>
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true">4</span>
                    <div class="sp-step__body">
                        <h3>Điền thông tin & thanh toán khi nhận hàng (COD)</h3>
                        <p>
                            Bấm <b>Thanh toán</b>, điền họ tên, số điện thoại và địa chỉ nhận hàng (chọn đúng tỉnh/thành
                            → quận/huyện → phường/xã để tính phí ship chính xác). Ghi chú thêm nếu bạn muốn, ví dụ
                            "giao giờ hành chính". Hình thức mặc định là <b>COD</b>: bạn chỉ trả tiền khi đã nhận và
                            kiểm
                            tra hàng.
                        </p>
                    </div>
                </li>
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true">5</span>
                    <div class="sp-step__body">
                        <h3>Xác nhận đơn & chờ liên hệ</h3>
                        <p>
                            Sau khi bấm <b>Đặt hàng</b>, màn hình "Đặt hàng thành công" hiển thị mã đơn của bạn. Trong
                            vòng <b>30 phút – 2 giờ</b> làm việc, nhân viên Mộc Xanh sẽ gọi lại xác nhận đơn, chốt thời
                            gian giao và hướng dẫn kiểm tra hàng khi nhận.
                        </p>
                    </div>
                </li>
            </ol>
        </section>

        {{-- ============ MẸO ĐẶT HÀNG NHANH ============ --}}
        <section class="sp-section reveal" aria-labelledby="tipsTitle">
            <h2 class="sec-title" id="tipsTitle">Mẹo đặt hàng nhanh</h2>
            <ul class="sp-tips">
                <li class="sp-tip">
                    <span class="sp-tip__icon" aria-hidden="true">🎟️</span>
                    <p><b>Săn mã giảm giá trước khi thanh toán.</b> Trang chủ và trang giỏ hàng luôn hiển thị các coupon
                        đang phát hành — sao chép mã rồi dán vào ô Mã giảm giá là xong.</p>
                </li>
                <li class="sp-tip">
                    <span class="sp-tip__icon" aria-hidden="true">⚡</span>
                    <p><b>Dùng nút "Mua ngay"</b> nếu bạn chỉ mua 1 món: bỏ qua bước trung gian, tới thẳng form thanh
                        toán.</p>
                </li>
                <li class="sp-tip">
                    <span class="sp-tip__icon" aria-hidden="true">📞</span>
                    <p><b>Số điện thoại chính xác</b> giúp shipper gọi giao thuận tiện và tránh hoàn đơn. Nên để máy
                        nghe được trong khung giờ giao.</p>
                </li>
                <li class="sp-tip">
                    <span class="sp-tip__icon" aria-hidden="true">📝</span>
                    <p><b>Ghi chú địa chỉ rõ ràng</b> (tên tòa/ngõ/ngách, mốc thời gian nhận) giúp giao đúng lần đầu,
                        hạn chế thất lạc.</p>
                </li>
                <li class="sp-tip">
                    <span class="sp-tip__icon" aria-hidden="true">🎁</span>
                    <p><b>Mua combo quà tặng</b> vừa tiết kiệm hơn mua lẻ, vừa có sẵn hộp quà — nhắn trong ghi chú nếu
                        bạn cần xuất hóa đơn hoặc viết thiệp.</p>
                </li>
            </ul>
        </section>

        {{-- ============ GIAO HÀNG & THANH TOÁN ============ --}}
        <section class="sp-section reveal" aria-labelledby="shipTitle">
            <h2 class="sec-title" id="shipTitle">Giao hàng & thanh toán</h2>
            <div class="sp-info-grid">
                <article class="sp-info-card">
                    <h3>🚚 Thời gian giao dự kiến</h3>
                    <ul>
                        <li><b>Nội thành Hà Nội / TP.HCM:</b> 1–2 ngày</li>
                        <li><b>Tỉnh/thành khác:</b> 2–4 ngày</li>
                        <li><b>Vùng sâu, vùng xa:</b> 4–7 ngày</li>
                    </ul>
                    <p class="sp-info-card__note">Đơn sau 17h hoặc đặt cuối tuần được xử lý sáng ngày làm việc kế tiếp.
                    </p>
                </article>
                <article class="sp-info-card">
                    <h3>💵 Hình thức thanh toán</h3>
                    <ul>
                        <li><b>COD:</b> thanh toán khi nhận hàng (mặc định)</li>
                        <li><b>Chuyển khoản:</b> hỗ trợ theo yêu cầu — bấm "Thanh toán" rồi chọn chuyển khoản khi nhân
                            viên gọi xác nhận</li>
                    </ul>
                    <p class="sp-info-card__note">Phí ship hiển thị chính xác ở bước thanh toán; đơn đạt giá trị miễn
                        phí
                        sẽ được ghi "Miễn phí vận chuyển".</p>
                </article>
                <article class="sp-info-card">
                    <h3>📦 Kiểm tra hàng khi nhận</h3>
                    <ul>
                        <li>Kiểm tra đủ số lượng, bao bì nguyên vẹn trước khi thanh toán</li>
                        <li>Hàng hở/méo/bẩm mốc do vận chuyển → từ chối nhận và báo ngay cho Mộc Xanh</li>
                        <li>Quay video mở hộp để được hỗ trợ đổi trả nhanh nhất</li>
                    </ul>
                    <p class="sp-info-card__note">Chi tiết xem tại <a href="{{ route('web.page.return-policy') }}">Chính
                            sách đổi trả</a>.</p>
                </article>
            </div>
        </section>

        {{-- ============ CÂU HỎI THƯỜNG GẶP ============ --}}
        <section class="sp-section reveal" aria-labelledby="faqTitle">
            <h2 class="sec-title" id="faqTitle">Câu hỏi thường gặp</h2>
            <div class="sp-faq">
                <details class="sp-faq__item">
                    <summary>Tôi có thể đặt hàng không tạo tài khoản không?</summary>
                    <div class="sp-faq__answer">
                        <p>Được. Mộc Xanh cho phép đặt khách vãng lai: chỉ cần họ tên, số điện thoại và địa chỉ. Mọi
                            thông tin đều được xử lý bảo mật theo <a href="{{ route('web.page.privacy') }}">Chính sách
                                bảo mật</a>.</p>
                    </div>
                </details>
                <details class="sp-faq__item">
                    <summary>Lỗi "giỏ hàng trống" khi bấm Thanh toán thì làm sao?</summary>
                    <div class="sp-faq__answer">
                        <p>Nguyên nhân phổ biến: bạn xóa hết sản phẩm trong giỏ, hoặc phiên trình duyệt quá lâu khiến
                            giỏ chưa lưu. Hãy tải lại trang, thêm lại sản phẩm và thử bấm Mua ngay. Nếu vẫn lỗi, gọi
                            hotline để được đặt hộ.</p>
                    </div>
                </details>
                <details class="sp-faq__item">
                    <summary>Mã giảm giá không áp dụng được?</summary>
                    <div class="sp-faq__answer">
                        <p>Kiểm tra: (1) mã còn hạn, (2) đơn đạt giá trị tối thiểu ghi trên mã, (3) nhập đúng chữ in
                            hoa, không thừa khoảng trắng. Mỗi đơn dùng được 1 mã; một số mã không cộng dồn với giá flash
                            sale.</p>
                    </div>
                </details>
                <details class="sp-faq__item">
                    <summary>Tôi muốn thay đổi/sửa đơn sau khi đặt?</summary>
                    <div class="sp-faq__answer">
                        <p>Gọi <a href="tel:0362795897">0362 795 897</a> và đọc mã đơn càng sớm càng tốt. Đơn chưa bàn
                            giao cho vận chuyển sẽ được sửa miễn phí (địa chỉ, số lượng, ghi chú).</p>
                    </div>
                </details>
                <details class="sp-faq__item">
                    <summary>Đặt số lượng lớn để biếu/tặng doanh nghiệp?</summary>
                    <div class="sp-faq__answer">
                        <p>Hotline/email báo giá sỉ, chiết khấu theo số lượng, xuất hóa đơn VAT và gói quà theo yêu
                            cầu.</p>
                    </div>
                </details>
            </div>
        </section>

        {{-- ============ GỢI Ý MUA NHANH (dữ liệu DB thật qua HomeService cache) ============ --}}
        @if (!empty($suggestedProducts))
            <section class="sp-section sp-section--last reveal" aria-labelledby="suggestTitle">
                <h2 class="sec-title" id="suggestTitle">Sản phẩm bán chạy — đặt ngay kẻo lỡ</h2>
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
            <h2 id="ctaTitle">Vẫn cần hỗ trợ thêm?</h2>
            <p>Đội ngũ Mộc Xanh phản hồi trong vài phút trong giờ làm việc (8:00–21:00, kể cả thứ Bảy).</p>
            <div class="sp-cta__actions">
                <a class="btn btn--leaf" href="tel:0362795897">📞 Gọi 0362 795 897</a>
                <a class="btn btn--ghost" href="mailto:thaomocfarm@gmail.com">✉️ Gửi email</a>
                <a class="btn btn--ghost" href="{{ route('web.products.index') }}">🛒 Xem sản phẩm</a>
            </div>
        </section>
    </div>
</x-layouts.app>