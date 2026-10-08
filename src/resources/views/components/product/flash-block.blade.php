@props(['product'])

{{-- Block Flash Sale + Countdown cho trang chi tiết sản phẩm. --}}
{{-- REUSE giao diện trang chủ: dùng lại đúng class .countdown/.cd của 14-flash-sale.css. --}}
{{-- CƠ CHẾ RENDER MỚI: Blade chỉ in khung + giá trị seed lần đầu; flash-live.js
gọi web.flash-sale.product mỗi 3 phút để cập nhật tên phiên, tiến độ slot,
% giảm, số tiền tiết kiệm và GIÁ (.pd-price). data-pd-flash + data-ends vẫn
là marker cho app.js dynamic import @tm/product (countdown.js đếm tới nửa đêm). --}}
@php
    $fs = $product->flashSale ?? null;
@endphp
<div class="pd-flash {{ $fs ? '' : 'is-ended' }}" data-pd-flash data-flash-pdp
    data-flash-url="{{ route('web.flash-sale.product', ['id' => $product->id]) }}"
    data-ends="{{ $fs['ends_at_unix'] ?? now()->addDay()->startOfDay()->timestamp }}" role="region"
    aria-label="Chương trình flash sale {{ $fs['name'] ?? '' }}">

    <div class="pd-flash__head">
        <span class="pd-flash__badge">⚡ FLASH SALE</span>
        <strong class="pd-flash__name" data-fsp-name>{{ $fs['name'] ?? 'Flash Sale' }}</strong>

        {{-- Countdown nửa đêm — cùng engine + cùng công thức với trang home --}}
        <p class="countdown pd-flash__cd" role="timer" aria-live="polite">
            Kết thúc sau
            <b class="cd" id="pdCdH">--</b>:<b class="cd" id="pdCdM">--</b>:<b class="cd" id="pdCdS">--</b>
        </p>
    </div>

    <div class="pd-flash__body">
        {{-- Tiến độ slot đã bán (dùng lại bar của card flash trang chủ) --}}
        <div class="flash__prog" role="progressbar" aria-valuemin="0" aria-valuemax="100"
            aria-valuenow="{{ $fs['sold_percent'] ?? 0 }}" aria-label="Tiến độ bán deal" data-fsp-prog-wrap
            @if(($fs['sold_percent'] ?? 0) <= 0) hidden @endif>
            <span class="flash__prog-bar {{ ($fs['sold_percent'] ?? 0) >= 70 ? 'is-hot' : '' }}" data-fsp-prog-bar
                style="width: {{ min(100, max(5, (int) ($fs['sold_percent'] ?? 0))) }}%;"></span>
        </div>
        <p class="flash__prog-meta" data-fsp-prog-meta @if(($fs['sold_percent'] ?? 0) <= 0) hidden @endif>
            <b data-fsp-percent>{{ $fs['sold_percent'] ?? 0 }}% đã bán</b>
            @if(($fs['slots_left'] ?? 0) > 0)
                <small data-fsp-slots>Còn {{ number_format((int) $fs['slots_left'], 0, ',', '.') }} slot</small>
            @else
                <small data-fsp-slots>Hết slot</small>
            @endif
        </p>

        <ul class="pd-flash__facts">
            <li data-fsp-discount @if(($fs['discount_percent'] ?? 0) <= 0) hidden @endif>
                🔥 Giảm <b>-{{ $fs['discount_percent'] ?? 0 }}%</b>
                <span data-fsp-saved>@if(($fs['saved_amount'] ?? 0) > 0)
                    — tiết kiệm {{ number_format((int) $fs['saved_amount'], 0, ',', '.') }}₫
                @endif</span>
            </li>
        </ul>
    </div>
</div>