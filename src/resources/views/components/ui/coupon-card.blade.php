@props(['code' => '', 'desc' => '', 'minOrder' => '', 'exp' => '', 'applied' => false])

<article class="cpn @if($applied) cpn--applied @endif">
    <div class="cpn__left">
        <b>{{ $code }}</b>
        <small>{{ $minOrder }}</small>
    </div>
    <div class="cpn__body">
        <p class="cpn__desc">{{ $desc }}</p>
        <div class="cpn__foot">
            <span class="cpn__exp">{{ $exp }}</span>
            <button class="btn btn--sm copy-btn @if($applied) btn--leaf cpn__btn-applied @else btn--clay @endif"
                data-code="{{ $code }}" @if($applied) disabled @endif>
                @if($applied)
                    ✓ Đang áp dụng
                @else
                    Sao chép
                @endif
            </button>
        </div>
    </div>
</article>