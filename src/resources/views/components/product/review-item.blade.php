@props(['review'])

<div class="review">
    <p class="review__head">
        <span class="review__avatar">
            <img src="{{ asset($review->avatar ?? 'images/placeholder.svg') }}" alt="{{ $review->user_name }}"
                width="72" height="72" loading="lazy">
        </span>
        <b>{{ $review->user_name }}</b>
        <span class="stars">★★★★★</span>
    </p>
    <p>{{ $review->content }}</p>
</div>