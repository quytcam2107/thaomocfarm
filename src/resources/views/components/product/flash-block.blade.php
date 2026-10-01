@props(['product'])

{{-- Block Flash Sale + Countdown cho trang chi tiết sản phẩm. --}}
{{-- REUSE giao diện trang chủ: dùng lại đúng class .countdown/.cd của 04-home.css --}}
{{-- => countdown GIỜ:PHÚT:GIÂY y hệt home, không hiển thị số "ngày" (end_at DB có thể rất xa). --}}
@php
    $fs = $product->flashSale ?? null;
@endphp

@if($fs)
    <div class="pd-flash" data-pd-flash data-ends="{{ $fs['ends_at_unix'] }}" role="region"
        aria-label="Chương trình flash sale {{ $fs['name'] }}">

        <div class="pd-flash__head">
            <span class="pd-flash__badge">⚡ FLASH SALE</span>
            <strong class="pd-flash__name">{{ $fs['name'] }}</strong>

            {{-- Countdown tới end_at của phiên — cùng cấu trúc markup với trang home --}}
            <p class="countdown pd-flash__cd" role="timer" aria-live="polite">
                Kết thúc sau
                <b class="cd" id="pdCdH">00</b>:<b class="cd" id="pdCdM">00</b>:<b class="cd" id="pdCdS">00</b>
            </p>
        </div>

        <div class="pd-flash__body">
            {{-- Tiến độ slot đã bán (dùng lại bar của card flash trang chủ) --}}
            @if($fs['sold_percent'] > 0)
                <div class="flash__prog" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                    aria-valuenow="{{ $fs['sold_percent'] }}" aria-label="Tiến độ bán deal">
                    <span class="flash__prog-bar {{ $fs['sold_percent'] >= 70 ? 'is-hot' : '' }}"
                        style="width: {{ min(100, max(5, $fs['sold_percent'])) }}%;"></span>
                </div>
                <p class="flash__prog-meta">
                    <b>{{ $fs['sold_percent'] }}% đã bán</b>
                    @if($fs['slots_left'] > 0)
                        <small>Còn {{ number_format($fs['slots_left'], 0, ',', '.') }} slot</small>
                    @else
                        <small>Hết slot</small>
                    @endif
                </p>
            @endif

            <ul class="pd-flash__facts">
                @if($fs['discount_percent'] > 0)
                    <li>🔥 Giảm <b>-{{ $fs['discount_percent'] }}%</b>
                        @if($fs['saved_amount'] > 0)
                            — tiết kiệm {{ number_format($fs['saved_amount'], 0, ',', '.') }}₫
                        @endif
                    </li>
                @endif
                {{-- @if($fs['per_user_limit'] > 0)
                    <li>🛡 Giới hạn {{ $fs['per_user_limit'] }} sản phẩm / khách</li>
                @endif --}}
            </ul>
        </div>
    </div>
@endif