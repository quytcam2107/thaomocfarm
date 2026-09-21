@props(['description' => '', 'reviews' => [], 'ratingStats' => []])

<div class="tabs">
    <div class="tabs__nav" role="tablist" aria-label="Thông tin sản phẩm">
        <button role="tab" id="tab-desc" aria-controls="panel-desc" aria-selected="true">Mô tả</button>
        <button role="tab" id="tab-review" aria-controls="panel-review" aria-selected="false" tabindex="-1">
            Đánh giá ({{ count($reviews) }})
        </button>
        <button role="tab" id="tab-policy" aria-controls="panel-policy" aria-selected="false" tabindex="-1">
            Chính sách
        </button>
    </div>

    <div class="tabs__panel" id="panel-desc" role="tabpanel" aria-labelledby="tab-desc">
        {!! $description !!}
    </div>

    <div class="tabs__panel" id="panel-review" role="tabpanel" aria-labelledby="tab-review" hidden>
        <p class="review-sum">
            <b>{{ $ratingStats['avg'] ?? 5.0 }}</b>
            <span class="stars">★★★★★</span>
            · {{ count($reviews) }} đánh giá
        </p>
        @forelse($reviews as $review)
            <x-product.review-item :review="$review" />
        @empty
            <p>Chưa có đánh giá nào cho sản phẩm này.</p>
        @endforelse
    </div>

    <div class="tabs__panel" id="panel-policy" role="tabpanel" aria-labelledby="tab-policy" hidden>
        <p>Đổi trả trong 7 ngày với sản phẩm còn nguyên bao bì hút chân không. Hoàn tiền qua chuyển khoản trong 3–5 ngày
            làm việc. Sản phẩm đã mở gói vì lý do an toàn thực phẩm sẽ không áp dụng đổi trả.</p>
    </div>
</div>