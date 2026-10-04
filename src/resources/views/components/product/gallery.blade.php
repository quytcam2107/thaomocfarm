@props(['images' => [], 'alt' => '', 'shareUrl' => null, 'shareTitle' => '', 'hasFlashSale' => false])

<div class="pd-gallery">
    <div class="pd-stage">
        <img id="pdStageImg" src="{{ asset($images[0] ?? 'images/placeholder.svg') }}" alt="{{ $alt }}" width="800"
            height="800" fetchpriority="high">
    </div>

    {{-- FIX tran ngang: hang thumbs nhieu anh (bang product_images max 6 anh/SP,
    co the tang them) KHONG duoc day giau cot grid. CSS 07-product-detail.css
    da chot: .pd-thumbs { overflow-x:auto + max-width:100% } va
    .pd-gallery / .pd-info { min-width:0 }. Class no-scrollbar (token san co
    trong 01-base.css) an thanh scroll xau xi; cuon bang cam ung/chu bi --}}
    <div class="pd-thumbs no-scrollbar" role="group" aria-label="Ảnh thu nhỏ sản phẩm">
        @forelse($images as $i => $img)
            <button data-full="{{ asset($img) }}" aria-current="{{ $i === 0 ? 'true' : 'false' }}"
                aria-label="Ảnh {{ $i + 1 }}">
                <img src="{{ asset($img) }}" alt="" width="128" height="128" loading="lazy">
            </button>
        @empty
            <button data-full="{{ asset('images/placeholder.svg') }}" aria-current="true">
                <img src="{{ asset('images/placeholder.svg') }}" alt="" width="128" height="128" loading="lazy">
            </button>
        @endforelse
    </div>

    <div class="pd-share" role="group" aria-label="Chia sẻ sản phẩm">

        <button type="button" class="pd-share__btn pd-share__btn--native" data-share-native
            data-share-url="{{ $shareUrl }}" data-share-title="{{ $shareTitle }}">
            <img src="{{ asset('assets/images/svg/share.svg') }}" width="16" height="16" alt="" aria-hidden="true">
        </button>

        <a class="pd-share__btn pd-share__btn--fb" target="_blank" rel="noopener noreferrer"
            href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}"
            aria-label="Chia sẻ lên Facebook">
            <img src="{{ asset('assets/images/svg/facebook.svg') }}" width="20" height="20" alt="" aria-hidden="true">

        </a>

        <a class="pd-share__btn pd-share__btn--zalo" target="_blank" rel="noopener noreferrer"
            href="https://zalo.me/share?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareTitle) }}"
            aria-label="Chia sẻ qua Zalo">
            <img src="{{ asset('assets/images/svg/zalo.png') }}" width="20" height="20" alt="" aria-hidden="true">

        </a>

    </div>
</div>