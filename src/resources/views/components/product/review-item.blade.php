@props(['review'])

<div class="review">
    <p class="review__head">
        <span class="review__avatar">
            {{-- Bảng reviews/users không có cột avatar => placeholder cố định --}}
            <img src="{{ asset('images/placeholder.svg') }}" alt="{{ $review->customer }}" width="72" height="72"
                loading="lazy">
        </span>
        <b>{{ $review->customer }}</b>
        @if($review->is_verified)
            <span class="review__verified" title="Khách đã mua sản phẩm này">✔ Đã mua</span>
        @endif
        <span class="stars"
            aria-label="{{ $review->rating }} trên 5 sao">{{ str_repeat('★', max(1, $review->rating)) }}</span>
    </p>
    <p>{{ $review->content }}</p>
    @if($review->created_at)
        <p class="review__date">{{ $review->created_at }}</p>
    @endif
</div>