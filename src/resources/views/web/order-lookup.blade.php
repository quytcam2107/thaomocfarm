{{-- =====================================================================
TRANG TRA CỨU ĐƠN HÀNG BẰNG SỐ ĐIỆN THOẠI (thay vai trò nút Đăng nhập)
- Form GET submit về chính route web.order-lookup.index (?phone=...).
- Server render toàn bộ kết quả (không AJAX) -> hoạt động cả khi tắt JS.
- Class prefix: ol- (order lookup). CSS: partials/16-order-tracking.css.
===================================================================== --}}
<x-layouts.app title="Tra cứu đơn hàng | Mộc Xanh"
    seoDescription="Nhập số điện thoại đã dùng khi đặt hàng để tra cứu trạng thái, tiến trình giao hàng và mã vận đơn của mọi đơn hàng tại Mộc Xanh."
    :hide-catnav="true">

    <div class="container">
        {{-- Breadcrumb đồng bộ trang tĩnh khác --}}
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('web.home') }}">Trang chủ</a></li>
                <li><span aria-current="page">Tra cứu đơn hàng</span></li>
            </ol>
        </nav>

        <section class="ol-hero">
            <p class="ol-hero__eyebrow">🧾 Hỗ trợ khách hàng</p>
            <h1>Tra cứu đơn hàng</h1>
            <p class="ol-hero__lead">
                Nhập <b>số điện thoại bạn đã dùng khi đặt hàng</b> để xem tình trạng và tiến trình
                của tất cả đơn. Không cần đăng nhập tài khoản.
            </p>

            {{-- Form tra cứu: GET, input chỉ nhận digit (JS lọc thêm, server vẫn chuẩn hóa lại) --}}
            <form class="ol-form" id="orderLookupForm" method="GET" action="{{ route('web.order-lookup.index') }}"
                role="search" autocomplete="off">
                <div class="ol-field">
                    <label class="sr-only" for="ol-phone">Số điện thoại đặt hàng</label>
                    <input class="ol-input" type="tel" id="ol-phone" name="phone" inputmode="numeric"
                        pattern="[0-9\s+.\-]{9,15}" maxlength="15" placeholder="VD: 0352806324"
                        value="{{ $inputPhone }}" required>
                </div>
                <button class="btn btn--leaf ol-submit" type="submit">
                    🔍 Tra cứu đơn
                </button>
            </form>

            <p class="ol-hint">
                💡 Cần hỗ trợ? Gọi <a href="tel:0362795897">hotline 0362 795 897</a> hoặc
                <a href="{{ route('web.page.order-guide') }}">xem hướng dẫn đặt hàng</a>.
            </p>
        </section>

        {{-- Báo lỗi / cảnh báo (validate fail, không tìm thấy, rate limit) --}}
        @if($error)
            <div class="ol-alert" role="alert">⚠️ {{ $error }}</div>
        @endif

        {{-- DANH SÁCH ĐƠN --}}
        @if(count($orders) > 0)
            <p class="ol-count">Tìm thấy <b>{{ count($orders) }}</b> đơn hàng cho số
                <b>{{ $inputPhone }}</b>
            </p>

            <div class="ol-list">
                @foreach($orders as $o)
                    <article class="ol-card {{ $o['is_abnormal'] ? 'ol-card--abnormal' : '' }}">
                        <header class="ol-card__head">
                            <div>
                                <p class="ol-card__no">Mã đơn: <b>{{ $o['order_number'] }}</b></p>
                                <p class="ol-card__date">Đặt ngày
                                    {{ \Carbon\Carbon::parse($o['created_at'])->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <span class="ol-badge ol-badge--{{ $o['status'] }}">{{ $o['status_label'] }}</span>
                        </header>

                        <div class="ol-card__grid">
                            {{-- Cột trái: người nhận + địa chỉ + vận chuyển --}}
                            <div class="ol-card__info">
                                <p class="ol-line"><span>Người nhận</span>{{ $o['recipient_name'] }}</p>
                                <p class="ol-line"><span>SĐT</span>{{ $o['phone_masked'] }}</p>
                                @if($o['address_line'])
                                    <p class="ol-line"><span>Địa chỉ</span>{{ $o['address_line'] }}</p>
                                @endif
                                @if($o['tracking_code'])
                                    <p class="ol-line"><span>Vận đơn</span><code>{{ $o['tracking_code'] }}</code></p>
                                @endif
                                @if($o['note'])
                                    <p class="ol-line"><span>Ghi chú</span>{{ $o['note'] }}</p>
                                @endif
                            </div>

                            {{-- Cột phải: tóm tắt tiền --}}
                            <div class="ol-card__money">
                                <p class="ol-money__line"><span>Tạm tính</span>{{ number_format($o['subtotal']) }}₫</p>
                                @if($o['discount_amount'] > 0)
                                    <p class="ol-money__line ol-money__line--discount">
                                        <span>Giảm giá{{ $o['coupon_code'] ? ' (' . $o['coupon_code'] . ')' : '' }}</span>
                                        -{{ number_format($o['discount_amount']) }}₫
                                    </p>
                                @endif
                                <p class="ol-money__line">
                                    <span>Phí ship</span>
                                    {{ $o['shipping_fee'] === 0 ? 'Miễn phí' : number_format($o['shipping_fee']) . '₫' }}
                                </p>
                                <p class="ol-money__total"><span>Tổng</span><b>{{ number_format($o['total']) }}₫</b></p>
                                <p class="ol-money__method">
                                    {{ $o['payment_method'] === 'cod' ? '💵 Thu hộ (COD)' : '🏦 Chuyển khoản' }}
                                    · {{ $o['payment_status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                                </p>
                            </div>
                        </div>

                        {{-- Sản phẩm --}}
                        <ul class="ol-items">
                            @foreach($o['items'] as $item)
                                <li class="ol-item">
                                    <span class="ol-item__img">
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" loading="lazy"
                                            onerror="this.src='{{ asset('assets/images/placeholder.svg') }}'">
                                    </span>
                                    <span class="ol-item__name">{{ $item['name'] }}
                                        <small>x{{ $item['qty'] }} · {{ $item['sku'] }}</small>
                                    </span>
                                    <b class="ol-item__price">{{ number_format($item['subtotal']) }}₫</b>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Timeline tiến trình --}}
                        <ol class="ol-steps">
                            @foreach($o['timeline'] as $step)
                                <li class="ol-step {{ $step['done'] ? 'is-done' : '' }} {{ $step['active'] ? 'is-active' : '' }}">
                                    <span class="ol-step__dot" aria-hidden="true"></span>
                                    <span class="ol-step__body">
                                        <b>{{ $step['label'] }}</b>
                                        @if($step['done'] && $step['time'])
                                            <small>{{ \Carbon\Carbon::parse($step['time'])->format('d/m/Y H:i') }}</small>
                                        @elseif($step['active'])
                                            <small>Đang xử lý…</small>
                                        @else
                                            <small>Chưa tới</small>
                                        @endif
                                    </span>
                                </li>
                            @endforeach
                        </ol>
                        @if($o['is_abnormal'])
                            <p class="ol-card__note">
                                Đơn này đang ở trạng thái <b>{{ $o['status_label'] }}</b>.
                                Liên hệ hotline <a href="tel:0362795897">0362 795 897</a> để được hỗ trợ chi tiết.
                            </p>
                        @endif
                    </article>
                @endforeach
            </div>
        @elseif(!$error)
            {{-- Màn chờ chưa tra cứu gì --}}
            <div class="ol-empty">
                <p class="ol-empty__icon" aria-hidden="true">📦</p>
                <h2>Chưa có tra cứu nào</h2>
                <p>Nhập số điện thoại của bạn ở ô trên để xem toàn bộ đơn hàng đã đặt tại Mộc Xanh.</p>
            </div>
        @endif
    </div>
</x-layouts.app>