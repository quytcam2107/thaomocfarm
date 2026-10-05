@props(['review'])

{{-- Một thẻ đánh giá chuẩn TMĐT: avatar initials (DB không có cột avatar),
sao theo máy lẻ, badge "Đã mua", nút Hữu ích (JS @tm/reviews xử lý).
data-star để khối lọc sao client-side đọc. --}}
<div class="review" data-star="{{ $review->rating }}">
    <div class="review__head">
        <span class="review__avatar" aria-hidden="true">{{ $review->initials ?? '?' }}</span>
        <div class="review__who">
            <b>{{ $review->customer }}</b>
            <span class="review__stars" aria-label="{{ $review->rating }} trên 5 sao">
                @for($i = 1; $i <= 5; $i++)
                    <i class="rv-star {{ $i <= $review->rating ? 'is-on' : '' }}">★</i>
                @endfor
            </span>
        </div>
        @if($review->is_verified)
            <span class="review__verified" title="Khách đã mua sản phẩm này">✔ Đã mua</span>
        @endif
        @if($review->created_at)
            <span class="review__date">{{ $review->created_at }}</span>
        @endif
    </div>

    <p class="review__content">{{ $review->content }}</p>

    <div class="review__foot">
        <button type="button" class="rv-helpful js-rv-helpful" data-review-id="{{ $review->id ?? 0 }}"
            aria-label="Đánh giá hữu ích">
            👍 Hữu ích (<span class="rv-helpful__count">{{ $review->helpful_count ?? 0 }}</span>)
        </button>
    </div>
</div>