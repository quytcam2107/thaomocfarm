<x-layouts.app title="Giỏ hàng của bạn | Thảo Mộc Farm" seoDescription="Xem và thanh toán giỏ hàng đặc sản Tây Bắc">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('web.home') }}">Trang chủ</a></li>
                <li><span aria-current="page">Giỏ hàng ({{ $totalQty }})</span></li>
            </ol>
        </nav>
        <div class="page-head">
            <h1>Giỏ hàng của bạn</h1>
        </div>

        @if(session('error'))
            <div class="alert alert--error" style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert--success" style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                {{ session('success') }}
            </div>
        @endif

        {{-- ============ BLOCK MA GIAM GIA (dung component cpn co san) ============ --}}
        @if(count($availableCoupons) > 0)
            <section class="coupon-strip" aria-label="Mã giảm giá đang phát hành">
                <div class="coupon-strip__head">
                    <h2 class="coupon-strip__title">🎟️ Mã giảm giá</h2>
                    <span class="coupon-strip__hint">Bấm “Sao chép” rồi dán vào ô bên phải. Mã đang áp dụng sẽ được viền xanh.</span>
                </div>
                <div class="coupon-strip__grid">
                    @foreach($availableCoupons as $coupon)
                        <div class="cpn-wrap @if($coupon['applied']) cpn-wrap--applied @endif">
                            <x-ui.coupon-card
                                :code="$coupon['code']"
                                :desc="$coupon['desc']"
                                :min-order="$coupon['minOrder']"
                                :exp="$coupon['exp']"
                            />
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="cart-layout">
            <div>
                @if(count($cartItems) > 0)
                    <div class="cart-list" id="cartList">
                        @foreach($cartItems as $item)
                            <article class="cart-item" data-item-id="{{ $item['id'] }}" data-price="{{ $item['price'] }}">
                                <span class="cart-item__media">
                                    <a href="{{ $item['url'] }}">
                                        <img src="{{ $item['image'] }}" alt="{{ $item['product_name'] }}" width="200" height="200" loading="lazy"
                                             onerror="this.src='{{ asset('assets/images/placeholder.svg') }}'">
                                    </a>
                                </span>
                                <div class="cart-item__content">
                                    <h2 class="cart-item__name">
                                        <a href="{{ $item['url'] }}">{{ $item['product_name'] }}</a>
                                    </h2>
                                    @if($item['variant_label'])
                                        <p class="cart-item__variant">Phân loại: {{ $item['variant_label'] }}</p>
                                    @endif
                                    @if($item['price'])
                                        <p class="cart-item__price">Giá: {{ $item['price'] }}</p>
                                    @endif
                                    <div class="cart-item__row">
                                        <div class="qty" data-qty>
                                            <button type="button" data-step="-1" aria-label="Giảm số lượng" {{ $item['qty'] <= 1 ? 'disabled' : '' }}>−</button>
                                            <input type="number" inputmode="numeric" min="1" max="{{ $item['stock'] }}" value="{{ $item['qty'] }}"
                                                aria-label="Số lượng {{ $item['product_name'] }}">
                                            <button type="button" data-step="1" aria-label="Tăng số lượng" {{ $item['qty'] >= $item['stock'] ? 'disabled' : '' }}>+</button>
                                        </div>
                                        <span class="cart-item__total">{{ number_format($item['subtotal']) }}₫</span>
                                        <button class="remove-btn" data-remove>Xoá</button>
                                    </div>
                                    @if($item['qty'] >= $item['stock'])
                                        <small class="cart-item__stock-warning">Đã đạt số lượng tối đa trong kho</small>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="empty" id="cartEmpty">
                        <span style="font-size: 5rem;">🧺</span>
                        <p>Giỏ hàng của bạn đang trống</p>
                        <a class="btn btn--leaf" href="{{ route('web.home') }}">Mua sắm ngay</a>
                    </div>
                @endif
            </div>

            @if(count($cartItems) > 0)
                <aside class="summary" aria-label="Tóm tắt đơn hàng">
                    <h2>Tóm tắt đơn hàng</h2>
                    <div class="coupon-input">
                        <input id="couponCode" placeholder="Nhập mã giảm giá" aria-label="Mã giảm giá"
                               value="{{ $appliedCoupon['code'] ?? '' }}">
                        <button class="btn btn--ghost" id="applyCoupon" @if($appliedCoupon) disabled @endif>Áp dụng</button>
                    </div>
                    <p id="couponMsg" class="coupon-msg" role="status"></p>

                    @if($appliedCoupon)
                        <div class="coupon-applied">
                            <span>🎟️ Mã <strong>{{ $appliedCoupon['code'] }}</strong> −{{ number_format($appliedCoupon['discount']) }}₫</span>
                            <button type="button" class="coupon-applied__remove" id="removeCoupon">Gỡ mã</button>
                        </div>
                    @endif

                    @if($appliedCoupon && $appliedCoupon['type'] === 'shipping')
                        <p class="freeship-note success">
                            🎉 Mã {{ $appliedCoupon['code'] }} miễn phí vận chuyển cho đơn này!
                        </p>
                    @elseif($discountedSubtotal < $freeShippingThreshold)
                        <p class="freeship-note">
                            🎁 Thêm <strong>{{ number_format($freeShippingThreshold - $discountedSubtotal) }}₫</strong> để được miễn phí vận chuyển
                        </p>
                    @else
                        <p class="freeship-note success">
                            🎉 Đơn của bạn được FREESHIP!
                        </p>
                    @endif

                    <p class="sum-row">
                        <span>Tạm tính</span>
                        <span>{{ number_format($subtotal) }}₫</span>
                    </p>
                    @if($discount > 0)
                        <p class="sum-row sum-row--discount">
                            <span>Giảm giá ({{ $appliedCoupon['code'] ?? 'mã' }})</span>
                            <span>−{{ number_format($discount) }}₫</span>
                        </p>
                    @endif
                    <p class="sum-row">
                        <span>Phí vận chuyển</span>
                        <span>{{ $shippingFee === 0 ? 'Miễn phí' : number_format($shippingFee) . '₫' }}</span>
                    </p>
                    <p class="sum-row total">
                        <span>Tổng cộng</span>
                        <span>{{ number_format($total) }}₫</span>
                    </p>
                    <a class="btn btn--clay btn--block" href="{{ route('web.checkout.index') }}">Thanh toán ngay</a>
                    <p class="sum-row sum-row--center">
                        <a class="remove-btn" href="{{ route('web.home') }}">← Tiếp tục mua sắm</a>
                    </p>
                </aside>
            @endif
        </div>
    </div>

    <div id="couponToast" class="coupon-toast" role="status"></div>

    @push('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        document.addEventListener('click', function(e) {
            const stepBtn = e.target.closest('[data-step]');
            if (stepBtn) {
                const qtyContainer = stepBtn.closest('.qty');
                const input = qtyContainer.querySelector('input');
                const step = parseInt(stepBtn.dataset.step);
                let currentValue = parseInt(input.value);
                const min = parseInt(input.min);
                const max = parseInt(input.max);

                let newValue = currentValue + step;
                if (newValue < min) newValue = min;
                if (newValue > max) newValue = max;

                input.value = newValue;
                updateQty(input.closest('.cart-item').dataset.itemId, newValue);
            }

            const removeBtn = e.target.closest('[data-remove]');
            if (removeBtn) {
                if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
                    const itemId = removeBtn.closest('.cart-item').dataset.itemId;
                    removeItem(itemId);
                }
            }

            // ==== Nút "Sao chép" của ticket mã giảm giá (data-code trên .copy-btn) ====
            const copyBtn = e.target.closest('.copy-btn');
            if (copyBtn) {
                const code = (copyBtn.dataset.code || '').trim().toUpperCase();
                if (!code) return;
                copyCouponCode(code);
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target.matches('.qty input')) {
                let value = parseInt(e.target.value);
                const min = parseInt(e.target.min);
                const max = parseInt(e.target.max);
                if (isNaN(value) || value < min) value = min;
                if (value > max) value = max;
                e.target.value = value;
                updateQty(e.target.closest('.cart-item').dataset.itemId, value);
            }
        });

        async function updateQty(itemId, qty) {
            try {
                const response = await fetch(`/gio-hang/cap-nhat/${itemId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ qty: qty }),
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi cập nhật số lượng');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi kết nối đến server');
            }
        }

        async function removeItem(itemId) {
            try {
                const response = await fetch(`/gio-hang/xoa/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Không thể xóa sản phẩm');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xóa sản phẩm');
            }
        }

        // ================= COUPON =================

        let couponToastTimer = null;

        function showCouponToast(message) {
            const toast = document.getElementById('couponToast');
            if (!toast) return;
            toast.textContent = message;
            toast.classList.add('coupon-toast--show');
            if (couponToastTimer) clearTimeout(couponToastTimer);
            couponToastTimer = setTimeout(() => toast.classList.remove('coupon-toast--show'), 2200);
        }

        async function copyCouponCode(code) {
            try {
                await navigator.clipboard.writeText(code);
            } catch (err) {
                const ta = document.createElement('textarea');
                ta.value = code;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); } catch (e2) { /* ignore */ }
                document.body.removeChild(ta);
            }
            const input = document.getElementById('couponCode');
            if (input && !input.disabled) {
                input.value = code;
                input.focus();
                input.select();
            }
            showCouponToast('Đã sao chép mã ' + code + ' — bấm “Áp dụng” để dùng');
        }

        async function applyCouponCode() {
            const input = document.getElementById('couponCode');
            const msgEl = document.getElementById('couponMsg');
            if (!input || !msgEl) return;

            const code = (input.value || '').trim().toUpperCase();
            msgEl.textContent = '';
            msgEl.className = 'coupon-msg';

            if (!code) {
                msgEl.textContent = 'Vui lòng nhập mã giảm giá.';
                msgEl.classList.add('coupon-msg--error');
                return;
            }

            try {
                const response = await fetch(`{{ route('web.cart.coupon.apply') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ code: code }),
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    location.reload();
                } else {
                    msgEl.textContent = data.message || 'Không thể áp dụng mã này.';
                    msgEl.classList.add('coupon-msg--error');
                }
            } catch (error) {
                console.error('Error:', error);
                msgEl.textContent = 'Có lỗi xảy ra khi kết nối đến server.';
                msgEl.classList.add('coupon-msg--error');
            }
        }

        async function removeAppliedCoupon() {
            try {
                const response = await fetch(`{{ route('web.cart.coupon.remove') }}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    showCouponToast('Không thể gỡ mã, vui lòng thử lại');
                }
            } catch (error) {
                console.error('Error:', error);
                showCouponToast('Có lỗi xảy ra khi kết nối đến server');
            }
        }

        document.getElementById('applyCoupon')?.addEventListener('click', applyCouponCode);
        document.getElementById('removeCoupon')?.addEventListener('click', removeAppliedCoupon);
        document.getElementById('couponCode')?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                applyCouponCode();
            }
        });

        document.querySelectorAll('[data-step]').forEach(btn => {
            btn.addEventListener('click', function() {
                this.disabled = true;
                setTimeout(() => { this.disabled = false; }, 500);
            });
        });
    </script>
    @endpush
</x-layouts.app>