@props(['review'])

{{-- Một thẻ đánh giá chuẩn TMĐT: avatar initials (DB không có cột avatar),
sao theo máy lẻ, badge "Đã mua", nút Hữu ích (JS @tm/reviews xử lý).
data-star để khối lọc sao client-side đọc.
NEW: lưới ảnh đánh giá (tối đa 5, src đã là URL tuyệt đối từ Fetcher) và
khối "Phản hồi từ Mộc Xanh" (cột admin_reply) — staff đang login thấy nút
soạn/sửa phản hồi, JS @tm/reviews gọi web.review.reply. --}}
@php
    $rvImgs = array_slice((array) ($review->images ?? []), 0, 5);
    $rvReply = $review->admin_reply ?? null;
    $canReply = auth()->check() && auth()->user()->isStaff();
@endphp
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

    {{-- NEW: ảnh đánh giá — link mở tab mới (đỡ phụ thuộc lightgallery instance
    của gallery sản phẩm; alt ngắn gọn theo tên khách) --}}
    @if($rvImgs !== [])
        <div class="review__images">
            @foreach($rvImgs as $img)
                <a class="review__img-link" href="{{ $img }}" target="_blank" rel="noopener">
                    <img class="review__img" src="{{ $img }}" loading="lazy" alt="Ảnh đánh giá của {{ $review->customer }}">
                </a>
            @endforeach
        </div>
    @endif

    {{-- NEW: phản hồi công khai của Mộc Xanh --}}
    @if($rvReply)
        <div class="review__reply">
            <b>Phản hồi từ Mộc Xanh</b>
            <p>{{ $rvReply }}</p>
        </div>
    @endif

    <div class="review__foot">
        <button type="button" class="rv-helpful js-rv-helpful" data-review-id="{{ $review->id ?? 0 }}"
            aria-label="Đánh giá hữu ích">
            👍 Hữu ích (<span class="rv-helpful__count">{{ $review->helpful_count ?? 0 }}</span>)
        </button>

        {{-- NEW (staff only): soạn/sửa phản hồi cho từng đánh giá --}}
        @if($canReply && ($review->id ?? 0) > 0)
            <button type="button" class="rv-reply-btn js-rv-reply" data-review-id="{{ $review->id }}" aria-expanded="false">
                💬 {{ $rvReply ? 'Sửa phản hồi' : 'Phản hồi' }}
            </button>
            <div class="rv-reply-box" id="rvReplyBox-{{ $review->id }}" hidden>
                <textarea class="rv-reply-textarea js-rv-reply-text" rows="3" maxlength="1000"
                    placeholder="Nội dung phản hồi công khai gửi khách…">{{ $rvReply }}</textarea>
                <div class="rv-reply-actions">
                    <button type="button" class="btn btn--clay btn--sm js-rv-reply-save"
                        data-review-id="{{ $review->id }}">Lưu phản hồi</button>
                    @if($rvReply)
                        <button type="button" class="btn btn--ghost btn--sm js-rv-reply-clear"
                            data-review-id="{{ $review->id }}">Xóa phản hồi</button>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>