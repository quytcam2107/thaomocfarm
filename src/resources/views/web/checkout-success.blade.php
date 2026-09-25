<x-layouts.app title="Đặt hàng thành công | Thảo Mộc Farm"
    seoDescription="Cảm ơn bạn đã đặt hàng tại Thảo Mộc Farm. Theo dõi đơn hàng và liên hệ hỗ trợ.">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('web.home') }}">Trang chủ</a></li>
                <li><a href="{{ route('web.cart.index') }}">Giỏ hàng</a></li>
                <li><span aria-current="page">Đặt hàng thành công</span></li>
            </ol>
        </nav>

        <section class="success">
            {{-- HERO --}}
            <div class="success__hero reveal is-in">
                <div class="success__check" aria-hidden="true">
                    <span class="success__ring"></span>
                    <img class="success__icon" src="{{ asset('assets/images/success-check.svg') }}"
                        alt="Đặt hàng thành công" width="72" height="72"
                        onerror="this.onerror=null;this.src='{{ asset('assets/images/success-check-fallback.png') }}';">
                </div>
                <h1 class="success__title">Đặt hàng thành công!</h1>
                <p class="success__lead">
                    Cảm ơn bạn đã tin tưởng <strong>Thảo Mộc Farm</strong>.
                    Đơn hàng <strong class="success__order-code">{{ $order->order_number }}</strong>
                    đã được ghi nhận và sẽ được xác nhận trong ít phút.
                </p>
            </div>

            {{-- LAYOUT 2 CỘT --}}
            <div class="success__grid">
                {{-- LEFT: Thông tin đơn --}}
                <div class="success__main">
                    <article class="s-card">
                        <header class="s-card__head">
                            <h2 class="s-card__title">
                                <span class="s-card__badge">📦</span>
                                Thông tin giao hàng
                            </h2>
                            <span class="s-card__status">Chờ xác nhận</span>
                        </header>

                        <dl class="s-card__rows">
                            <div class="s-card__row">
                                <dt>Người nhận</dt>
                                <dd>{{ $order->customer_name }}</dd>
                            </div>
                            <div class="s-card__row">
                                <dt>Số điện thoại</dt>
                                <dd><a href="tel:{{ $order->customer_phone }}">{{ $order->customer_phone }}</a></dd>
                            </div>
                            @if($order->customer_email)
                                <div class="s-card__row">
                                    <dt>Email</dt>
                                    <dd><a href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a></dd>
                                </div>
                            @endif
                            <div class="s-card__row">
                                <dt>Địa chỉ</dt>
                                <dd>
                                    {{ $order->address_snapshot['detail'] ?? '' }},
                                    {{ $order->address_snapshot['ward'] ?? '' }}
                                    {{ $order->address_snapshot['district'] ?? '' }},
                                    {{ $order->address_snapshot['province'] ?? '' }}
                                </dd>
                            </div>
                            @if($order->note)
                                <div class="s-card__row">
                                    <dt>Ghi chú</dt>
                                    <dd>{{ $order->note }}</dd>
                                </div>
                            @endif
                        </dl>
                    </article>

                    {{-- TIMELINE --}}
                    <article class="s-card s-card--timeline">
                        <h2 class="s-card__title">
                            <span class="s-card__badge">🚚</span>
                            Tiến trình đơn hàng
                        </h2>
                        <ol class="timeline">
                            <li class="timeline__step is-active">
                                <span class="timeline__dot"></span>
                                <div class="timeline__body">
                                    <b>Đơn hàng đã tạo</b>
                                    <small>{{ $order->created_at->format('H:i - d/m/Y') }}</small>
                                </div>
                            </li>
                            <li class="timeline__step">
                                <span class="timeline__dot"></span>
                                <div class="timeline__body">
                                    <b>Nhân viên xác nhận</b>
                                    <small>Đang chờ liên hệ</small>
                                </div>
                            </li>
                            <li class="timeline__step">
                                <span class="timeline__dot"></span>
                                <div class="timeline__body">
                                    <b>Đóng gói & giao hàng</b>
                                    <small>Dự kiến 2–4 ngày</small>
                                </div>
                            </li>
                            <li class="timeline__step">
                                <span class="timeline__dot"></span>
                                <div class="timeline__body">
                                    <b>Giao hàng thành công</b>
                                    <small>Thanh toán COD</small>
                                </div>
                            </li>
                        </ol>
                    </article>
                </div>

                {{-- RIGHT: Tóm tắt đơn --}}
                <aside class="success__side">
                    <article class="s-card s-card--summary">
                        <h2 class="s-card__title">Tóm tắt đơn hàng</h2>

                        <ul class="s-card__items">
                            @foreach($order->items as $item)
                                <li class="s-item">
                                    <div class="s-item__img">
                                        @php
                                            $img = $item->image_snapshot;
                                            if (!empty($img) && !str_starts_with((string) $img, 'http')) {
                                                $img = asset('assets/images/' . ltrim((string) $img, '/'));
                                            }
                                            if (empty($img))
                                                $img = asset('assets/images/placeholder.svg');
                                        @endphp
                                        <img src="{{ $img }}" alt="{{ $item->name_snapshot }}"
                                            onerror="this.src='{{ asset('assets/images/placeholder.svg') }}'">
                                    </div>
                                    <div class="s-item__info">
                                        <p class="s-item__name">{{ $item->name_snapshot }}</p>
                                        <small>Số lượng: {{ $item->qty }}</small>
                                    </div>
                                    <b class="s-item__price">{{ number_format($item->subtotal) }}₫</b>
                                </li>
                            @endforeach
                        </ul>

                        <div class="s-card__totals">
                            <div class="s-card__line">
                                <span>Tạm tính</span>
                                <span>{{ number_format($order->subtotal) }}₫</span>
                            </div>
                            <div class="s-card__line">
                                <span>Phí vận chuyển</span>
                                <span>{{ $order->shipping_fee === 0 ? 'Miễn phí' : number_format($order->shipping_fee) . '₫' }}</span>
                            </div>
                            @if($order->discount_amount > 0)
                                <div class="s-card__line s-card__line--discount">
                                    <span>Giảm giá</span>
                                    <span>-{{ number_format($order->discount_amount) }}₫</span>
                                </div>
                            @endif
                            <div class="s-card__line s-card__line--total">
                                <span>Tổng thanh toán</span>
                                <b>{{ number_format($order->total) }}₫</b>
                            </div>
                            <p class="s-card__method">
                                💵 Thanh toán khi nhận hàng (COD)
                            </p>
                        </div>
                    </article>
                    <div class="success__actions">
                        <a href="{{ route('web.home') }}" class="btn btn--leaf">🛒 Tiếp tục mua sắm</a>
                        {{-- <a href="{{ route('web.order-tracking.index') }}" class="btn btn--ghost">🔍 Tra cứu đơn
                            hàng</a> --}}
                    </div>
                    <div class="success__support">
                        <h3>Bạn cần hỗ trợ?</h3>
                        <p>Đội ngũ Thảo Mộc Farm luôn sẵn sàng đồng hành cùng bạn.</p>
                        <div class="success__support-cta">
                            <a class="success__support-link" href="tel:0362795897">
                                <span aria-hidden="true">📞</span>
                                <div>
                                    <b>Hotline</b>
                                    <small>0362 795 897</small>
                                </div>
                            </a>
                            <a class="success__support-link" href="mailto:thaomocfarm@gmail.com">
                                <span aria-hidden="true">✉️</span>
                                <div>
                                    <b>Email</b>
                                    <small>thaomocfarm@gmail.com</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </aside>

            </div>

        </section>
    </div>
</x-layouts.app>