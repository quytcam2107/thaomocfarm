<x-layouts.app title="Thanh toán đơn hàng | Mộc Xanh"
    seoDescription="Thanh toán đơn hàng đặc sản Tây Bắc an toàn, bảo mật." :hide-catnav="true" :hide-floatnav="true">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('web.home') }}">Trang chủ</a></li>
                <li><a href="{{ route('web.cart.index') }}">Giỏ hàng</a></li>
                <li><span aria-current="page">Thanh toán</span></li>
            </ol>
        </nav>
        <div class="page-head">
            <h1 style="text-align: center;">Thông Tin Thanh Toán</h1>
        </div>

        @if(session('error'))
            <div class="alert alert--error"
                style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                {{ session('error') }}
            </div>
        @endif

        <form id="checkoutForm" class="co-layout" action="{{ route('web.checkout.store') }}" method="POST" novalidate>
            @csrf
            <div>
                <fieldset class="fs">
                    <legend>1. Thông tin liên hệ</legend>
                    <div class="form-grid form-grid--2">
                        <p class="field">
                            <label for="name">Họ và tên <span class="req">*</span></label>
                            <input id="name" name="name" autocomplete="name" required placeholder="Nguyễn Văn A"
                                value="{{ old('name', auth()->user()?->name) }}">
                            @error('name') <small class="error" style="color: #dc2626;">{{ $message }}</small> @enderror
                        </p>
                        <p class="field">
                            <label for="phone">Số điện thoại <span class="req">*</span></label>
                            <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required
                                pattern="[0-9]{9,11}" placeholder="09xx xxx xxx"
                                value="{{ old('phone', auth()->user()?->phone) }}">
                            <small class="hint">Dùng để giao hàng liên hệ với bạn</small>
                            @error('phone') <small class="error" style="color: #dc2626;">{{ $message }}</small>
                            @enderror
                        </p>
                        <p class="field form-grid--2-full">
                            <label for="email">Email (nhận mã đơn)</label>
                            <input id="email" name="email" type="email" autocomplete="email" placeholder="ban@email.com"
                                value="{{ old('email', auth()->user()?->email) }}">
                            @error('email') <small class="error" style="color: #dc2626;">{{ $message }}</small>
                            @enderror
                        </p>
                    </div>
                </fieldset>

                <fieldset class="fs">
                    <legend>2. Địa chỉ nhận hàng</legend>
                    <div class="form-grid form-grid--2">
                        <p class="field">
                            <label for="province">Tỉnh / thành <span class="req">*</span></label>
                            <select id="province" name="province" autocomplete="address-level1" required>
                                <option value="">— Chọn —</option>
                                <option value="Hồ Chí Minh" {{ old('province') == 'Hồ Chí Minh' ? 'selected' : '' }}>TP.
                                    Hồ Chí Minh</option>
                                <option value="Hà Nội" {{ old('province') == 'Hà Nội' ? 'selected' : '' }}>Hà Nội</option>
                                <option value="Đà Nẵng" {{ old('province') == 'Đà Nẵng' ? 'selected' : '' }}>Đà Nẵng
                                </option>
                                <option value="Sơn La" {{ old('province') == 'Sơn La' ? 'selected' : '' }}>Sơn La</option>
                            </select>
                            @error('province') <small class="error" style="color: #dc2626;">{{ $message }}</small>
                            @enderror
                        </p>
                        <p class="field">
                            <label for="district">Quận / huyện <span class="req">*</span></label>
                            <select id="district" name="district" autocomplete="address-level2" required>
                                <option value="">— Chọn —</option>
                                <option value="Quận 3" {{ old('district') == 'Quận 3' ? 'selected' : '' }}>Quận 3</option>
                                <option value="Phú Nhuận" {{ old('district') == 'Phú Nhuận' ? 'selected' : '' }}>Phú Nhuận
                                </option>
                                <option value="Thủ Đức" {{ old('district') == 'Thủ Đức' ? 'selected' : '' }}>Thủ Đức
                                </option>
                            </select>
                            @error('district') <small class="error" style="color: #dc2626;">{{ $message }}</small>
                            @enderror
                        </p>
                        <p class="field form-grid--2-full">
                            <label for="address">Địa chỉ chi tiết <span class="req">*</span></label>
                            <input id="address" name="address" autocomplete="street-address" required
                                placeholder="Số nhà, tên đường, phường" value="{{ old('address') }}">
                            @error('address') <small class="error" style="color: #dc2626;">{{ $message }}</small>
                            @enderror
                        </p>
                    </div>
                </fieldset>

                <fieldset class="fs">
                    <legend>3. Phương thức vận chuyển</legend>
                    {{-- Radio khớp đúng whitelist in:fast,standard của CheckoutRequest; checked lấy từ $shipping_method
                    --}}
                    <label class="radio-card">
                        <input type="radio" name="shipping_method" value="fast" {{ old('shipping_method', $shipping_method) == 'fast' ? 'checked' : '' }}>
                        <span><b>Giao nhanh 2h</b> (nội thành Hà Nội) —
                            {{ number_format((int) config('thaomoc.shipping.fast_fee', 30000)) }}₫ · kèm túi giữ lạnh
                            cho món gác
                            bếp</span>
                    </label>
                    <label class="radio-card">
                        <input type="radio" name="shipping_method" value="standard" {{ old('shipping_method', $shipping_method) == 'standard' ? 'checked' : '' }}>
                        <span><b>Giao tiêu chuẩn</b> (2–4 ngày toàn quốc) —
                            {{ number_format((int) config('thaomoc.shipping.default_fee', 30000)) }}₫, miễn phí đơn từ
                            {{ number_format($free_shipping_threshold) }}₫</span>
                    </label>
                    @error('shipping_method') <small class="error" style="color: #dc2626;">{{ $message }}</small>
                    @enderror
                </fieldset>

                <fieldset class="fs">
                    <legend>4. Phương thức thanh toán</legend>
                    <label class="radio-card">
                        <input type="radio" name="payment_method" value="cod" checked>
                        <span><b>COD</b> — nhận hàng rồi thanh toán</span>
                    </label>
                    @error('payment_method') <small class="error" style="color: #dc2626;">{{ $message }}</small>
                    @enderror
                </fieldset>

                <fieldset class="fs">
                    <legend>5. Ghi chú</legend>
                    <p class="field">
                        <label for="note">Lời nhắn cho shop</label>
                        <textarea id="note" name="note"
                            placeholder="Ví dụ: giao giờ hành chính, gói quà giúp mình…">{{ old('note') }}</textarea>
                    </p>
                </fieldset>
            </div>

            <aside class="summary" aria-label="Tóm tắt đơn hàng">
                <h2>Đơn hàng của bạn</h2>
                <ul class="co-mini">
                    @foreach ($items as $item)
                        <li>
                            <img src="{{ $item['image'] }}" alt="{{ $item['product_name'] }}" width="44" height="44"
                                loading="lazy" onerror="this.src='{{ asset('assets/images/placeholder.svg') }}'">
                            {{ $item['product_name'] }} × {{ $item['qty'] }} <b>{{ number_format($item['subtotal']) }}₫</b>
                        </li>
                    @endforeach
                </ul>

                @if($appliedCoupon)
                    <div class="coupon-applied" style="margin-bottom: 12px;">
                        <span>🎟️ Mã <strong>{{ $appliedCoupon['code'] }}</strong>
                            @if($appliedCoupon['type'] === 'shipping')
                                — Miễn phí vận chuyển
                            @else
                                −{{ number_format($appliedCoupon['discount']) }}₫
                            @endif
                        </span>
                    </div>
                @endif

                <p class="sum-row">
                    <span>Tạm tính</span>
                    <span>{{ number_format($subtotal) }}₫</span>
                </p>
                @if($discount > 0 && $appliedCoupon && $appliedCoupon['type'] !== 'shipping')
                    <p class="sum-row sum-row--discount">
                        <span>Giảm giá ({{ $appliedCoupon['code'] }})</span>
                        <span>−{{ number_format($discount) }}₫</span>
                    </p>
                @endif
                <p class="sum-row">
                    <span>Phí vận chuyển</span>
                    <span>{{ $shipping_fee === 0 ? 'Miễn phí' : number_format($shipping_fee) . '₫' }}</span>
                </p>
                <p class="sum-row total">
                    <span>Tổng cộng</span>
                    <span>{{ number_format($total) }}₫</span>
                </p>
                <button class="btn btn--clay btn--block" type="submit">✅ Đặt hàng</button>
                <p class="hint sum-row--center">Bằng việc đặt hàng, bạn đồng ý với điều khoản sử dụng.</p>
            </aside>
        </form>
    </div>
</x-layouts.app>