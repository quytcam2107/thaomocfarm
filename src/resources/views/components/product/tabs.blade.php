@props(['description' => '', 'reviews' => [], 'ratingStats' => [], 'productSlug' => '', 'productName' => ''])

@php
    // Thống kê an toàn: total/by_star luôn tồn tại nhờ ProductDetailFetcher::fetchReviews
    $rvTotal = (int) ($ratingStats['total'] ?? 0);
    $rvAvg = (float) ($ratingStats['avg'] ?? $ratingStats['average'] ?? 0);
    $byStar = $ratingStats['by_star'] ?? [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
@endphp

<div class="tabs">
    <div class="tabs__nav" role="tablist" aria-label="Thông tin sản phẩm">
        <button role="tab" id="tab-desc" aria-controls="panel-desc" aria-selected="true">Mô tả</button>
        <button role="tab" id="tab-review" aria-controls="panel-review" aria-selected="false" tabindex="-1">
            Đánh giá ({{ $rvTotal }})
        </button>
        <button role="tab" id="tab-policy" aria-controls="panel-policy" aria-selected="false" tabindex="-1">
            Chính sách
        </button>
    </div>

    <div class="tabs__panel" id="panel-desc" role="tabpanel" aria-labelledby="tab-desc">
        {!! $description !!}
    </div>

    <div class="tabs__panel" id="panel-review" role="tabpanel" aria-labelledby="tab-review" hidden>
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
                    <small class="rv-form__hint">Đánh giá hiển thị sau khi quản trị duyệt. Chỉ khách đã mua hàng được
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

    <div class="tabs__panel" id="panel-policy" role="tabpanel" aria-labelledby="tab-policy" hidden>
        <p>Đổi trả trong 7 ngày với sản phẩm còn nguyên bao bì hút chân không. Hoàn tiền qua chuyển khoản trong 3–5 ngày
            làm việc. Sản phẩm đã mở gói vì lý do an toàn thực phẩm sẽ không áp dụng đổi trả.</p>
    </div>
</div>