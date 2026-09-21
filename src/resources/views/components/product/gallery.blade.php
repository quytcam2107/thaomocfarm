@props(['images' => [], 'alt' => ''])

<div class="pd-gallery">
    <div class="pd-stage">
        <img id="pdStageImg" src="{{ asset($images[0] ?? 'images/placeholder.svg') }}" alt="{{ $alt }}" width="800"
            height="800" fetchpriority="high">
    </div>

    <div class="pd-thumbs" role="group" aria-label="Ảnh thu nhỏ sản phẩm">
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
</div>