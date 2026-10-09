@props(['description' => '', 'reviews' => [], 'ratingStats' => [], 'productSlug' => '', 'productName' => '', 'product' => null, 'variants' => [], 'category' => null, 'productSpecs' => []])

@php
    // Thống kê an toàn: total/by_star luôn tồn tại nhờ ProductDetailFetcher::fetchReviews
    $rvTotal = (int) ($ratingStats['total'] ?? 0);
    $rvAvg = (float) ($ratingStats['avg'] ?? $ratingStats['average'] ?? 0);
    $byStar = $ratingStats['by_star'] ?? [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];

    /* YÊU CẦU MỚI: tab "Chính sách" đã XÓA khỏi nav + panel.
       Tab "Đánh giá" đẩy xuống DƯỚI 2 tab "Mô tả" / "Chi tiết sản phẩm":
       .tabs đổi layout grid 1 cột (xem 07d-pdp-tabs.css) -> nav (2 tab) nằm
       trên, khối đánh giá (.rv-zone) là hàng thứ 2, LUÔN hiển thị bên dưới
       mà không cần bấm tab. Panel review bỏ role=tabpanel để khỏi bị JS
       tabs ARIA ([role="tab"]) ẩn/hiện theo tab. */

    /* NEW SPECS: "Thông số sản phẩm" giờ đọc JSON từ DB (products.specs_json)
       qua ProductDetailHydrator::buildSpecRows() -> $product->specs
       (list ['label','value']). Nguồn ưu tiên: prop :product-specs (nếu view
       truyền tường minh), fallback DTO. Các dòng kỹ thuật cũ (SKU / kho / đã bán /
       đánh giá) chuyển xuống khối riêng "$techSpecs" để KHÔNG lẫn với thông số
       nhà sản phẩm — vẫn giữ đủ thông tin như trước. */
    $dbSpecs = $productSpecs !== [] ? $productSpecs : (array) ($product->specs ?? []);

    $techSpecs = [];
    if ($product) {
        $techSpecs[] = ['label' => 'Mã sản phẩm (SKU)', 'value' => (string) ($product->sku ?? '')];
        if (!empty($product->subtitle)) {
            $techSpecs[] = ['label' => 'Mô tả ngắn', 'value' => (string) $product->subtitle];
        }
        if ($category) {
            $techSpecs[] = ['label' => 'Danh mục', 'value' => (string) ($category['name'] ?? ''), 'url' => (string) ($category['url'] ?? '')];
        }
        // $techSpecs[] = ['label' => 'Tình trạng kho', 'value' => ((int) ($product->stock ?? 0) > 0) ? 'Còn hàng (' . number_format((int) $product->stock) . ' sản phẩm)' : 'Tạm hết hàng'];
        $techSpecs[] = ['label' => 'Đã bán', 'value' => number_format((int) ($product->sold_count ?? 0)) . ' sản phẩm'];
        $techSpecs[] = ['label' => 'Đánh giá', 'value' => number_format($rvAvg, 1) . '/5 (' . $rvTotal . ' đánh giá)'];
    }
@endphp

<div class="tabs">
    <div class="tabs__nav" role="tablist" aria-label="Thông tin sản phẩm">
        <button role="tab" id="tab-desc" aria-controls="panel-desc" aria-selected="true">Mô tả sản phẩm</button>
        <button role="tab" id="tab-spec" aria-controls="panel-spec" aria-selected="false" tabindex="-1">Chi tiết sản
            phẩm</button>
    </div>

    <div class="tabs__panel" id="panel-desc" role="tabpanel" aria-labelledby="tab-desc">
        {{-- NEW XEM THÊM MÔ TẢ: description là longtext HTML rất dài (bảng biểu, FAQ
        <details>) -> bọc trong .desc-collapse để CSS clamp max-height + lớp fade;
            data-desc-toggle là hook cho app-product.js (tự ẩn nút khi nội dung ngắn).
            Body collapse mang id để aria-controls trỏ đúng (accessibility). --}}
            <div class="desc-collapse is-collapsed" data-desc-collapse>
                <div class="desc-collapse__body" id="descCollapseBody" data-desc-body>
                    {{-- FIX CMS IMG: description trong DB có thể chứa literal
                    "{{ asset('...') }}" — Blade KHÔNG compile chuỗi data khi
                    xuất bằng {!! !!} => browser in nguyên văn, ảnh không hiện.
                    render_cms_html(): compile Blade expression + sanitize
                    whitelist chống XSS (helper tại app/Support/helpers.php) --}}
                    {!! render_cms_html((string) $description) !!}
                </div>

                <div class="desc-collapse__foot" data-desc-foot hidden>
                    <button type="button" class="desc-more" data-desc-toggle aria-expanded="false"
                        aria-controls="descCollapseBody">
                        <span class="desc-more__label">Xem thêm mô tả sản phẩm</span>
                        <span class="desc-more__icon" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
    </div>

    {{-- NEW TAB CHI TIẾT SẢN PHẨM: bảng quy cách (từ product_variants thật
    qua $variants của ProductDetailFetcher) + "Thông số sản phẩm" đọc JSON DB
    + khối thông tin kỹ thuật. Component tự ẩn từng khối khi data rỗng.

    FIX SPEC-TAG: mỗi <tr> mang data-variant-id (= $v['value'] = id variant,
        trùng value của radio name="variant_id" trong x-product.info) để JS
        app-product.js (@tm/product) DI CHUYỂN .spec-tag "Đang chọn" sang đúng
        dòng khi khách đổi khối lượng. Trước đây tag chỉ render 1 lần theo
        is_default (ProductDetailFetcher set cứng 'selected') -> chọn khối lượng
        khác tag vẫn đứng ở dòng default. --}}
        <div class="tabs__panel" id="panel-spec" role="tabpanel" aria-labelledby="tab-spec" hidden>
            @if(count($variants))
                        <h3 class="spec-title">Quy cách &amp; giá bán</h3>
                        {{-- SỬA YÊU CẦU MỚI: BỎ CỘT "Giá niêm yết" — bảng còn 2 cột
                        "Quy cách" | "Giá bán". Giá niêm yết (compare_price / flash_price
                        do FlashSalePriceService ghi đè vào old_price) giờ gạch ngang
                        <s> đặt NGAY TRONG Ô Giá bán của dòng ĐANG CHỌN, chỉ khi nó cao
                            hơn giá bán. data-price / data-old-price trên
                <tr> giữ nguyên để
                    app-product.js vẽ lại ô giá khi khách đổi khối lượng. --}}
                    <div class="spec-table-wrap">
                        <table class="spec-table" data-spec-table>
                            <thead>
                                <tr>
                                    <th scope="col">Quy cách</th>
                                    <th scope="col">Giá bán</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($variants as $v)
                                    @php $vSelected = !empty($v['selected']); @endphp
                                    <tr data-variant-id="{{ $v['value'] }}" data-price="{{ (int) $v['price'] }}"
                                        data-old-price="{{ (int) ($v['old_price'] ?? 0) }}" @if($vSelected) class="is-selected" @endif>
                                        <td>{{ $v['label'] }}@if($vSelected) <span class="spec-tag">Đang chọn</span>@endif
                                        </td>
                                        <td><b>{{ number_format((int) $v['price']) }}₫</b>@if($vSelected && !empty($v['old_price']) && (int) $v['old_price'] > (int) $v['price'])
                                        <s>{{ number_format((int) $v['old_price']) }}₫</s>@endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
            @endif

        {{-- NEW: THÔNG SỐ SẢN PHẨM TỪ DATABASE (products.specs_json) — 11 field:
        Thương hiệu / Xuất xứ / Hạn sử dụng / Thành phần / Hương vị / Hướng dẫn
        bảo quản / Công dụng / Hướng dẫn sử dụng / Tổ chức sản xuất / Phân phối
        độc quyền / Thông tin liên hệ. Value escape mặc định của Blade ({{ }}) —
        an toàn XSS vì nội dung do admin nhập. --}}
        @if(count($dbSpecs))
            <h3 class="spec-title">Thông số sản phẩm</h3>
            <dl class="spec-list spec-list--db">
                @foreach($dbSpecs as $spec)
                    <div class="spec-row">
                        <dt>{{ $spec['label'] }}</dt>
                        <dd>{{ $spec['value'] }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif

        @if(count($techSpecs))
            <h3 class="spec-title">Thông tin mua hàng</h3>
            <dl class="spec-list">
                @foreach($techSpecs as $spec)
                    <div class="spec-row">
                        <dt>{{ $spec['label'] }}</dt>
                        <dd>
                            @if(!empty($spec['url']))
                                <a href="{{ $spec['url'] }}">{{ $spec['value'] }}</a>
                            @else
                                {{ $spec['value'] }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
        @endif
</div>

{{-- ĐÁNH GIÁ ĐẨY XUỐNG DƯỚI: khối .rv-zone đặt NGOÀI khu vực tabpanel, thành
hàng thứ 3 của grid .tabs (sau nav + panel) -> luôn nằm dưới 2 tab. Giữ nguyên
họ selector .rv-* và marker .rv-zone để app-review.js (@tm/review) hoạt động
không đổi (data-product-slug, #reviewList, #rvForm, lọc sao…). --}}
<div class="tabs__review">
    <div class="tabs__review-head">
        <h3>Đánh giá sản phẩm <span class="tabs__review-count">({{ $rvTotal }})</span></h3>
    </div>

    {{-- KHỐI ĐÁNH GIÁ CHUẨN TMĐT: summary + lọc sao + form + danh sách.
    Data đổ vào #reviewList để JS module @tm/reviews lọc client-side. --}}
    <div class="rv-zone" data-product-slug="{{ $productSlug }}">

        {{-- 1) Tóm tắt điểm: score to + thang phân bố 5→1 sao (bấm để lọc) --}}
        <div class="rv-summary">
            <div class="rv-summary__score">
                <b>{{ number_format($rvAvg, 1) }}</b>
                <span class="stars"
                    aria-label="{{ $rvAvg }} trên 5 sao">{{ str_repeat('★', (int) round($rvAvg)) }}{{ str_repeat('☆', 5 - (int) round($rvAvg)) }}</span>
                <small>{{ $rvTotal }} đánh giá</small>
            </div>
            <div class="rv-summary__bars">
                @foreach([5, 4, 3, 2, 1] as $star)
                    @php $cnt = (int) ($byStar[$star] ?? 0);
                    $pct = $rvTotal > 0 ? (int) round($cnt / $rvTotal * 100) : 0; @endphp
                    <button type="button" class="rv-bar js-rv-filter" data-star="{{ $star }}"
                        aria-label="Lọc {{ $star }} sao">
                        <span class="rv-bar__label">{{ $star }}<i class="rv-bar__star">★</i></span>
                        <span class="rv-bar__track"><i class="rv-bar__fill" style="width: {{ $pct }}%"></i></span>
                        <span class="rv-bar__count">{{ $cnt }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- 2) Nút mở form + FORM đánh giá.
        FIX SAO KHÔNG CHỌN ĐƯỢC: input rating đổi sang type="hidden" và đặt
        NGOÀI div .rv-rateyo — rateyo render con SVG vào trong div này, nếu
        input nằm cạnh/trong box sẽ bị che/dispatch hỏng. Widget bind qua
        data-rateyo-* + JS @tm/reviews (API rateyo 2.3.4: rating/numStars/onSet). --}}
        <div class="rv-form-toggle-row">
            <button type="button" class="btn btn--leaf rv-form-toggle" id="rvOpenForm" aria-expanded="false"
                aria-controls="rvForm">
                ✍️ Viết đánh giá của bạn
            </button>
        </div>

        <form id="rvForm" class="rv-form" hidden novalidate enctype="multipart/form-data"
            action="{{ route('web.product.reviews.store', $productSlug) }}" method="POST">
            @csrf
            {{-- Input value sao: hidden ngoài widget, JS onSet ghi vào đây.
            FIX BUG "The rating field must be an integer": hidden mang
            name="rating" + value="0" để formData gốc LUÔN chứa một giá trị
            nguyên hợp lệ (rule integer không bao giờ nhận chuỗi rỗng/"NaN");
            JS setRating() cập nhật value của nó mỗi lần chấm sao. --}}
            <input type="hidden" class="rv-rating" name="rating" value="0">
            {{-- Radio fallback 1..5 (KHÔNG đặt name="rating" — tránh trùng key
            multipart làm Laravel nhận rating thành MẢNG và fail rule integer);
            JS đọc .rv-rating-radio:checked để biết số sao khách chọn --}}
            <div class="rv-rating-fallback" aria-hidden="true">
                @foreach([1, 2, 3, 4, 5] as $starVal)
                    <input type="radio" class="rv-rating-radio" value="{{ $starVal }}" id="rvStar{{ $starVal }}"
                        tabindex="-1">
                @endforeach
            </div>
            <div class="rv-form__row">
                <label class="rv-form__label">Chấm điểm của bạn <em>*</em></label>
                <div class="rv-rateyo" id="rvRateyo" title="Chọn từ 1 đến 5 sao"></div>
                <output class="rv-form__score" id="rvScoreText">Chưa chọn sao</output>
            </div>

            <div class="rv-form__grid">
                <div class="rv-form__row">
                    <label class="rv-form__label" for="rvName">Tên hiển thị <em>*</em></label>
                    <input id="rvName" name="name" type="text" maxlength="100" placeholder="VD: Nguyễn Văn A"
                        autocomplete="name">
                </div>
                {{-- FIX YÊU CẦU MỚI: SĐT bắt buộc (đối chiếu đơn đã giao), email KHÔNG bắt buộc --}}
                <div class="rv-form__row">
                    <label class="rv-form__label" for="rvPhone">Số điện thoại đã đặt hàng <em>*</em></label>
                    <input id="rvPhone" name="phone" type="tel" maxlength="15" inputmode="tel"
                        placeholder="VD: 0352806324" autocomplete="tel">
                </div>
                <div class="rv-form__row">
                    <label class="rv-form__label" for="rvEmail">Email (không bắt buộc)</label>
                    <input id="rvEmail" name="email" type="email" maxlength="150" placeholder="ban@email.com"
                        autocomplete="email">
                </div>
            </div>

            <div class="rv-form__row">
                <label class="rv-form__label" for="rvContent">Nhận xét <em>*</em></label>
                <textarea id="rvContent" name="content" rows="4" minlength="10" maxlength="1000"
                    placeholder="Chia sẻ cảm nhận của bạn về chất lượng, hương vị, đóng gói… (tối thiểu 10 ký tự)"></textarea>
                <small class="rv-form__hint">Đánh giá hiển thị sau khi quản trị duyệt. Chỉ khách đã mua hàng
                    được
                    đánh giá (đối chiếu bằng số điện thoại).</small>
            </div>

            {{-- NEW: chọn ảnh đánh giá — tối đa 5, preview grid (JS @tm/reviews quản lý) --}}
            <div class="rv-form__row">
                <label class="rv-form__label" for="rvImages">Ảnh đánh giá (tối đa 5)</label>
                <input id="rvImages" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple
                    class="rv-img-input">
                <small class="rv-form__hint">JPG, PNG hoặc WEBP — mỗi ảnh tối đa 2MB.</small>
                <div class="rv-img-previews" id="rvImgPreviews" aria-live="polite"></div>
            </div>

            <p class="rv-form__error" id="rvError" role="alert" hidden></p>

            <div class="rv-form__actions">
                <button type="submit" class="btn btn--clay" id="rvSubmit">
                    <span class="btn__main">Gửi đánh giá</span>
                </button>
                <button type="button" class="btn btn--ghost" id="rvCancel">Hủy</button>
            </div>
        </form>

        {{-- 3) Thanh lọc kết quả (hiện khi bấm vào 1 mốc sao) --}}
        <div class="rv-filterbar" id="rvFilterBar" hidden>
            <span>Đang xem: <b id="rvFilterLabel"></b></span>
            <button type="button" class="rv-filterbar__clear" id="rvFilterClear">Xem tất cả ✕</button>
        </div>

        {{-- 4) Danh sách đánh giá — data-* để JS module lọc + nút Helpful --}}
        <div class="rv-list" id="reviewList">
            @forelse($reviews as $review)
                <x-product.review-item :review="$review" />
            @empty
                <p class="rv-empty">Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên!</p>
            @endforelse
        </div>
    </div>
</div>
</div>