@props(['images' => [], 'thumbs' => [], 'alt' => '', 'shareUrl' => null, 'shareTitle' => '', 'hasFlashSale' => false])

{{-- images/thumbs đã là URL tuyệt đối do ProductDetailHydrator chuẩn hóa (asset() 1 lần
duy nhất ở tầng service) -> KHÔNG bọc asset() nữa, tránh URL đúp http://host/http://... --}}
<div class="pd-gallery">
    {{-- Khung .pd-stage là vùng NHẬN VUỐT (touch + chuột) để đổi ảnh — xem
    app-product.js (Pointer Events) và 07-product-detail.css (touch-action:pan-y,
    hiệu ứng chuyển mượt theo hướng chạy trên 2 lớp phủ .pd-stage__fx bên dưới). --}}
    <div class="pd-stage">
        {{-- FIX VUỐT BẰNG CHUỘT: draggable="false" + unselectable chặn HTML5 native
        image drag — thủ phạm cắt pointermove giữa chừng khiến vuốt chuột không đủ
        ngưỡng đổi ảnh (xem app-product.js SWIPE_MIN_X). --}}
        <img id="pdStageImg" src="{{ $images[0] ?? asset('images/placeholder.svg') }}" alt="{{ $alt }}" width="800"
            height="800" fetchpriority="high" draggable="false" unselectable="on">

        {{-- NEW (chống giật + slide theo hướng): 2 lớp phủ hiệu ứng, CHỈ dùng khi có >1 ảnh.
        .pd-stage__fx--back : app-product.js put ảnh CŨ vào đây rồi mờ dần ra (khung khỏi trống trắng)
        .pd-stage__fx--front: app-product.js put ảnh MỚI vào đây, trượt vào từ mép trái/phải tùy hướng
        pointer-events:none trong CSS => không chắn vùng vuốt/nút prev-next. --}}
        @if(count($images) > 1)
            <div class="pd-stage__fx pd-stage__fx--back" data-pd-fx="back" aria-hidden="true"></div>
            <div class="pd-stage__fx pd-stage__fx--front" data-pd-fx="front" aria-hidden="true"></div>
        @endif

        {{-- Nút prev/next TRÊN ẢNH TO: overlay 2 mép, chỉ hiện khi có >1 ảnh.
        data-pd-nav-zone="stage": JS bỏ qua setPointerCapture khi pointerdown
        rơi vào nút này, nhờ vậy button nhận được click bình thường.
        NEW data-pd-edge="first|last": JS disabled nút prev khi đang xem ẢNH ĐẦU
        và disabled nút next khi đang xem ẢNH CUỐI (không còn wrap-around ở nút).
        Trạng thái đầu trang do server render sẵn (ảnh 1 -> prev disabled);
        app-product.js cập nhật lại sau mỗi lần đổi ảnh. --}}
        @if(count($images) > 1)
            <button type="button" class="pd-nav pd-nav--prev" data-pd-nav="-1" data-pd-nav-zone="stage" data-pd-edge="first"
                aria-label="Ảnh trước" draggable="false" disabled>
                <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
                    <path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
            <button type="button" class="pd-nav pd-nav--next" data-pd-nav="1" data-pd-nav-zone="stage" data-pd-edge="last"
                aria-label="Ảnh sau" draggable="false">
                <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
                    <path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
            <span class="pd-counter" id="pdCounter">1/{{ count($images) }}</span>
        @endif
    </div>

    {{-- FIX tran ngang: hang thumbs nhieu anh (bang product_images max 6 anh/SP,
    co the tang them) KHONG duoc day giau cot grid. CSS 07-product-detail.css
    da chot: .pd-thumbs { overflow-x:auto + max-width:100% } va
    .pd-gallery / .pd-info { min-width:0 }. Class no-scrollbar (token san co
    trong 01-base.css) an thanh scroll xau xi; cuon bang cam ung/chu bi.
    2 nut prev/next nho hai ben hang thumbs (data-pd-nav): render khi >1 anh
    nhung MAC DINH AN (hidden). Chi hien khi hang thumbs THAT SU TRA NGANG
    (scrollWidth > clientWidth) — app-product.js quyet dinh bang ResizeObserver,
    dung duoc tren moi kich man hinh --}}
    <div class="pd-thumbs-wrap">
        {{-- data-thumbs-nav: danh dau cap nut cua rieng hang thumbs de JS notate --}}
        <span class="pd-thumbs-nav" data-thumbs-nav hidden>
            {{-- data-pd-edge="first": prev hàng thumbs cũng disabled khi đang ở ảnh đầu --}}
            <button type="button" class="pd-nav pd-nav--sm pd-nav--sm-prev" data-pd-nav="-1" data-pd-nav-zone="thumbs"
                data-pd-edge="first" aria-label="Ảnh trước" draggable="false" disabled>
                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                    <path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        </span>

        <div class="pd-thumbs no-scrollbar" role="group" aria-label="Ảnh thu nhỏ sản phẩm">
            @forelse($images as $i => $img)
                <button type="button" data-full="{{ $img }}" data-index="{{ $i }}"
                    aria-current="{{ $i === 0 ? 'true' : 'false' }}" aria-label="Ảnh {{ $i + 1 }}">
                    <img src="{{ $thumbs[$i] ?? $img }}" alt="" width="128" height="128" loading="lazy" draggable="false">
                </button>
            @empty
                <button type="button" data-full="{{ asset('images/placeholder.svg') }}" data-index="0" aria-current="true">
                    <img src="{{ asset('images/placeholder.svg') }}" alt="" width="128" height="128" loading="lazy"
                        draggable="false">
                </button>
            @endforelse
        </div>

        <span class="pd-thumbs-nav" data-thumbs-nav hidden>
            <button type="button" class="pd-nav pd-nav--sm pd-nav--sm-next" data-pd-nav="1" data-pd-nav-zone="thumbs"
                data-pd-edge="last" aria-label="Ảnh sau" draggable="false">
                <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                    <path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        </span>
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