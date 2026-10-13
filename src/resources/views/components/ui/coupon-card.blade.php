@props(['code' => '', 'desc' => '', 'minOrder' => '', 'exp' => '', 'applied' => false])

<article class="cpn @if($applied) cpn--applied @endif">
    <div class="cpn__left">
        <b>{{ $code }}</b>
        <small>{{ $minOrder }}</small>
    </div>
    {{-- NEW TOOLTIP: data-tip trên .cpn__body (cha của .cpn__desc) -> hover vùng
    thân coupon luôn hiện full mô tả; .cpn__desc cũng mang data-tip để bắt chính
    xác phần tử bị line-clamp. Nội dung gán bằng textContent trong JS -> an toàn. --}}
    <div class="cpn__body" data-tip="{{ $desc }}">
        <p class="cpn__desc" data-tip="{{ $desc }}">{{ $desc }}</p>
        <div class="cpn__foot">
            <span class="cpn__exp">{{ $exp }}</span>
            {{-- NEW TOOLTIP: nút copy — hover hiện hướng dẫn; disabled thì JS bỏ qua?
            Không: vẫn show tip tĩnh "Sao chép mã vào clipboard" là hint hợp lệ;
            riêng state applied đổi text thành "Đang áp dụng". --}}
            <button class="btn btn--sm copy-btn @if($applied) btn--leaf cpn__btn-applied @else btn--clay @endif"
                data-code="{{ $code }}" @if($applied) disabled @endif
                data-tip="{{ $applied ? 'Mã đang được áp dụng' : 'Sao chép mã vào clipboard' }}">
                @if($applied)
                    ✓ Đang áp dụng
                @else
                    Sao chép
                @endif
            </button>
        </div>
    </div>
</article>