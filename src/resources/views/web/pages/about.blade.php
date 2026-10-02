{{-- =====================================================================
TRANG TĨNH: GIỚI THIỆU (nhóm "Về chúng tôi" — footer)
- Nội dung fix cứng trong Blade; controller chỉ gửi thêm 4 SP gợi ý.
===================================================================== --}}
<x-layouts.app title="Giới thiệu Mộc Xanh | Thảo mộc & Đặc sản Tây Bắc"
    seoDescription="Mộc Xanh — câu chuyện đưa đặc sản Tây Bắc về phố: thu mua trực tiếp từ vùng trồng Sơn La, Điện Biên, Lào Cai; sản phẩm đạt OCOP 3–4 sao, VietGAP, xưởng sơ chế ISO 22000."
    :hide-catnav="true">

    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('web.home') }}">Trang chủ</a></li>
                <li><span aria-current="page">Giới thiệu</span></li>
            </ol>
        </nav>

        <header class="sp-hero reveal">
            <p class="sp-hero__eyebrow">🌿 Về chúng tôi</p>
            <h1>Chuyện của Mộc Xanh</h1>
            <p class="sp-hero__lead">
                Mộc Xanh bắt đầu từ những chuyến xe ngược núi: chúng tôi đến tận bản xa ở Sơn La, Điện Biên, Lào Cai
                để mua trực thảo mộc, trà hoa và dược liệu từ bà con — không qua trung gian, để
                người làm ra nhận giá xứng đáng và người nhận hàng được đúng vị nguyên bản của Tây Bắc.
            </p>
            <p class="sp-hero__updated">Thành lập tại Hà Nội · Phục vụ hơn 8.800 khách hàng toàn quốc</p>
        </header>

        {{-- ============ 1. SỨ MỆNH ============ --}}
        <section class="sp-section reveal" aria-labelledby="a1">
            <h2 class="sec-title" id="a1">1. Sứ mệnh của chúng tôi</h2>
            <ul class="sp-bullets">
                <li><b>Làm cầu nối tin cậy</b> giữa vùng nguyên liệu núi cao và căn bếp phố thị: mỗi sản phẩm đều truy
                    xuất được nguồn gốc vùng trồng.</li>
                <li><b>Giữ trọn hương vị bản địa</b> — công thức truyền thống của đồng bào được bảo
                    tồn gần như nguyên vẹn.</li>
                <li><b>Sinh kế bền vững cho bà con</b>: thu mua giá công bằng, bao tiêu dài hạn theo mùa vụ, ưu tiên
                    hộ đã đạt chứng nhận.</li>
                <li><b>Minh bạch với khách hàng</b>: công khai tiêu chí chọn lọc, giấy kiểm nghiệm và phản hồi thật từ
                    người đã mua.</li>
            </ul>
        </section>

        {{-- ============ 2. HÀNH TRÌNH ============ --}}
        <section class="sp-section reveal" aria-labelledby="a2">
            <h2 class="sec-title" id="a2">2. Hành trình của Mộc Xanh</h2>
            <ol class="sp-steps">
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true">1</span>
                    <div class="sp-step__body">
                        <h3>Khởi đầu từ một chuyến lên Tây Bắc</h3>
                        <p>Mẻ hàng đầu tiên chỉ có thịt trâu gác bếp và mắc khén rang mang về biếu người thân — và rất
                            nhiều lời hỏi "mua thêm ở đâu?".</p>
                    </div>
                </li>
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true">2</span>
                    <div class="sp-step__body">
                        <h3>Liên kết vùng trồng</h3>
                        <p>Xây dựng quan hệ thu mua trực tiếp với 15 vùng nguyên liệu liên kết tại Sơn La, Điện Biên,
                            Lào Cai; đồng hành cùng các hộ đạt VietGAP và OCOP.</p>
                    </div>
                </li>
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true">3</span>
                    <div class="sp-step__body">
                        <h3>Chuẩn hóa sơ chế & đóng gói</h3>
                        <p>Xưởng sơ chế vận hành theo ISO 22000, từng lô đều kiểm nghiệm định kỳ trước khi lên kệ; hút
                            chân không và ghi rõ hạn dùng trên bao bì.</p>
                    </div>
                </li>
                <li class="sp-step">
                    <span class="sp-step__num" aria-hidden="true">4</span>
                    <div class="sp-step__body">
                        <h3>Thương hiệu Mộc Xanh ra đời</h3>
                        <p>Khách khắp cả nước đặt hàng online, giao COD toàn quốc; đơn đạt giá trị miễn phí được ghi rõ
                            ngay ở bước thanh toán.</p>
                    </div>
                </li>
            </ol>
        </section>

        {{-- ============ 3. CAM KẾT CHẤT LƯỢNG ============ --}}
        <section class="sp-section reveal" aria-labelledby="a3">
            <h2 class="sec-title" id="a3">3. Cam kết chất lượng</h2>
            <div class="sp-info-grid">
                <article class="sp-info-card">
                    <h3>🏅 Nguồn gốc rõ ràng</h3>
                    <ul>
                        <li>Thu mua trực tiếp từ bản, không trôi nổi chợ mối</li>
                        <li>8 sản phẩm đạt OCOP 3–4 sao</li>
                        <li>Vùng trồng trà hoa đạt chuẩn VietGAP</li>
                    </ul>
                    <p class="sp-info-card__note">Mỗi lô hàng kèm hồ sơ vùng trồng và ngày thu hoạch.</p>
                </article>
                <article class="sp-info-card">
                    <h3>🔬 An toàn thực phẩm</h3>
                    <ul>
                        <li>Xưởng sơ chế chuẩn ISO 22000</li>
                        <li>Kiểm nghiệm định kỳ từng lô</li>
                        <li>Không tẩm ướp hóa chất bảo quản ngoài danh mục</li>
                    </ul>
                    <p class="sp-info-card__note">Đầy đủ giấy tờ công bố sản phẩm theo quy định.</p>
                </article>
                <article class="sp-info-card">
                    <h3>💚 Hậu mãi yên tâm</h3>
                    <ul>
                        <li>Đổi trả linh hoạt — xem điều kiện cụ thể</li>
                        <li>Hoàn tiền 100% nếu lỗi do nhà bán</li>
                        <li>Hỗ trợ 8:00–21:00, kể cả thứ Bảy</li>
                    </ul>
                    <p class="sp-info-card__note">Chi tiết tại <a href="{{ route('web.page.return-policy') }}">Chính
                            sách đổi trả</a>.</p>
                </article>
            </div>
        </section>

        {{-- ============ 4. CÁCH CHÚNG TÔI LÀM VIỆC ============ --}}
        <section class="sp-section reveal" aria-labelledby="a4">
            <h2 class="sec-title" id="a4">4. Một sản phẩm đi tới tay bạn như thế nào?</h2>
            <ul class="sp-checklist sp-checklist--yes">
                <li>Chọn lọc tại vùng trồng theo tiêu chí kích cỡ, độ khô, mùi tự nhiên.</li>
                <li>Sơ chế – đóng gói tại xưởng, hút chân không, dán nhãn lô và hạn dùng.</li>
                <li>Kiểm nghiệm mẫu lô trước khi nhập kho.</li>
                <li>Soạn đơn trong ngày, giao COD toàn quốc, khách được kiểm tra hàng khi nhận.</li>
            </ul>
            <ul class="sp-tips" style="margin-top:12px;">
                <li class="sp-tip">
                    <span class="sp-tip__icon" aria-hidden="true">🐂</span>
                    <p><b>Thịt gác bếp:</b> treo trên bếp than củi nhiều tuần theo cách của người Thái — vị khói trầm,
                        không hắc, xé sợi chấm chẳng chéo là "hết ý".</p>
                </li>
                <li class="sp-tip">
                    <span class="sp-tip__icon" aria-hidden="true">🌸</span>
                    <p><b>Trà hoa thảo mộc:</b> hoa cúc chi, hoa hồng… sấy低温 giữ hương, uống dịu, phù hợp ngày nóng
                        hoặc sau bữa ăn nhiều dầu mỡ.</p>
                </li>
                <li class="sp-tip">
                    <span class="sp-tip__icon" aria-hidden="true">🎁</span>
                    <p><b>Combo quà tặng:</b> hộp đẹp, kèm chứng nhận OCOP — biếu Tết, cảm ơn đối tác vừa sang vừa tiết
                        kiệm hơn mua lẻ.</p>
                </li>
            </ul>
        </section>

        {{-- ============ GỢI Ý MUA NHANH ============ --}}
        @if (!empty($suggestedProducts))
            <section class="sp-section reveal" aria-labelledby="suggestTitle">
                <h2 class="sec-title" id="suggestTitle">Sản phẩm bán chạy — thử ngay hôm nay</h2>
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

        {{-- ============ CTA ============ --}}
        <section class="sp-cta reveal" aria-labelledby="ctaTitle">
            <h2 id="ctaTitle">Muốn hiểu Mộc Xanh hơn nữa?</h2>
            <p>Ghé cửa hàng, gọi hotline hoặc bắt đầu với hướng dẫn đặt hàng 5 bước — chúng tôi phản hồi trong vài phút
                giờ làm việc.</p>
            <div class="sp-cta__actions">
                <a class="btn btn--leaf" href="{{ route('web.page.contact') }}">📍 Đến liên hệ với chúng tôi</a>
                <a class="btn btn--ghost" href="{{ route('web.products.index') }}">🌿 Xem tất cả sản phẩm</a>
                <a class="btn btn--ghost" href="{{ route('web.page.order-guide') }}">🛒 Hướng dẫn đặt hàng</a>
            </div>
        </section>
    </div>
</x-layouts.app>