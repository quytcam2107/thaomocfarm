<x-layouts.app title="Giỏ hàng của bạn | Thảo Mộc Farm" seoDescription="Xem và thanh toán giỏ hàng đặc sản Tây Bắc"
    :hide-floatnav="true">
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
            <div class="alert alert--error"
                style="background: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert--success"
                style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                {{ session('success') }}
            </div>
        @endif

        <div class="cart-layout">
            {{-- ============ BLOCK MA GIAM GIA ============ --}}
            @if(count($availableCoupons) > 0)
                <section class="coupon-strip" aria-label="Mã giảm giá đang phát hành">
                    <div class="coupon-strip__head">
                        <h2 class="coupon-strip__title">🎟️ Mã giảm giá</h2>
                        <span class="coupon-strip__hint">Bấm "Sao chép" rồi dán vào ô mã ở khung Tóm tắt đơn hàng. Mã
                            đang áp dụng sẽ được tô đậm.</span>
                    </div>
                    <div class="coupon-strip__grid">
                        @foreach($availableCoupons as $coupon)
                            <div class="cpn-wrap @if($coupon['applied']) cpn-wrap--applied @endif">
                                <x-ui.coupon-card :code="$coupon['code']" :desc="$coupon['desc']"
                                    :min-order="$coupon['minOrder']" :exp="$coupon['exp']" :applied="$coupon['applied']" />
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="cart-main">
                @if(count($cartItems) > 0)
                    <div class="cart-list" id="cartList">
                        @foreach($cartItems as $item)
                            <article class="cart-item" data-item-id="{{ $item['id'] }}" data-price="{{ $item['price'] }}">
                                <span class="cart-item__media">
                                    <a href="{{ $item['url'] }}">
                                        <img src="{{ $item['image'] }}" alt="{{ $item['product_name'] }}" width="200"
                                            height="200" loading="lazy"
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
                                            <input type="number" inputmode="numeric" min="1" max="{{ $item['stock'] }}"
                                                value="{{ $item['qty'] }}" aria-label="Số lượng {{ $item['product_name'] }}">
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
                    <div class="coupon-input" data-applied-code="{{ $appliedCoupon['code'] ?? '' }}">
                        <input id="couponCode" placeholder="Nhập mã giảm giá" aria-label="Mã giảm giá"
                            value="{{ $appliedCoupon['code'] ?? '' }}">
                        <button class="btn btn--ghost" id="applyCoupon">Áp dụng</button>
                    </div>
                    <div id="couponMsg" class="coupon-alert" role="status" aria-live="polite"></div>

                    @if($appliedCoupon)
                        <div class="coupon-applied">
                            <span>🎟️ Mã <strong>{{ $appliedCoupon['code'] }}</strong>
                                −{{ number_format($appliedCoupon['discount']) }}₫</span>
                            <button type="button" class="coupon-applied__remove" id="removeCoupon">Gỡ mã</button>
                        </div>
                    @endif

                    @if($appliedCoupon && $appliedCoupon['type'] === 'shipping')
                        <p class="freeship-note success">
                            🎉 Mã {{ $appliedCoupon['code'] }} miễn phí vận chuyển cho đơn này!
                        </p>
                    @elseif($discountedSubtotal < $freeShippingThreshold)
                        <p class="freeship-note">
                            🎁 Thêm <strong>{{ number_format($freeShippingThreshold - $discountedSubtotal) }}₫</strong> để
                            được miễn phí vận chuyển
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

    {{-- ============ STICKY BOTTOM BAR (mobile only) ============
    Luôn hiện ở đáy màn hình khi giỏ có sản phẩm:
    - Tổng tiền + tên mã đang áp (nếu có)
    - Nút 🎟️ mở drawer
    - Nút Thanh toán (CTA chính)
    Desktop (>=768px) ẩn bằng CSS.
    --}}
    @if(count($cartItems) > 0)
        <div class="cart-bar" id="cartBar" role="region" aria-label="Tóm tắt giỏ hàng">
            <div class="cart-bar__left">
                <div class="cart-bar__total">
                    <span class="cart-bar__label">Tổng</span>
                    <span class="cart-bar__amount">{{ number_format($total) }}₫</span>
                </div>
                @if($appliedCoupon)
                    <span class="cart-bar__applied">🎟️ {{ $appliedCoupon['code'] }}</span>
                @endif
            </div>
            <div class="cart-bar__right">
                <button class="cart-bar__coupon" id="openCartDrawer" type="button" aria-label="Mở áp mã giảm giá"
                    title="Áp mã & xem chi tiết">
                    🎟️
                </button>
                <a class="btn btn--clay cart-bar__checkout" href="{{ route('web.checkout.index') }}">
                    Thanh toán
                </a>
            </div>
        </div>

        {{-- ============ DRAWER ÁP MÃ (mobile only) ============
        Slide từ dưới lên, chứa:
        - Ô nhập mã + nút Áp dụng
        - Chip mã đang áp (nếu có)
        - Danh sách ticket coupon
        - Tóm tắt chi tiết (tạm tính / giảm / ship / tổng)
        - Nút Thanh toán
        Desktop (>=768px) ẩn bằng CSS.
        --}}
        <div class="cart-drawer" id="cartDrawer" aria-hidden="true" aria-labelledby="cartDrawerTitle">
            <div class="cart-drawer__overlay" data-close-drawer></div>
            <div class="cart-drawer__panel" role="dialog" aria-modal="true">
                <header class="cart-drawer__head">
                    <h2 id="cartDrawerTitle">Áp mã & Tóm tắt</h2>
                    <button class="cart-drawer__close" data-close-drawer type="button" aria-label="Đóng">✕</button>
                </header>

                <div class="cart-drawer__body">
                    <section class="cart-drawer__section">
                        <h3 class="cart-drawer__subtitle">Nhập mã giảm giá</h3>
                        <div class="coupon-input" data-applied-code="{{ $appliedCoupon['code'] ?? '' }}">
                            <input id="drawerCouponCode" placeholder="VD: SALE9K" aria-label="Mã giảm giá"
                                value="{{ $appliedCoupon['code'] ?? '' }}">
                            <button class="btn btn--ghost" id="drawerApplyCoupon">Áp dụng</button>
                        </div>
                        <div id="drawerCouponMsg" class="coupon-alert" role="status" aria-live="polite"></div>

                        @if($appliedCoupon)
                            <div class="coupon-applied">
                                <span>🎟️ Mã <strong>{{ $appliedCoupon['code'] }}</strong>
                                    −{{ number_format($appliedCoupon['discount']) }}₫</span>
                                <button type="button" class="coupon-applied__remove" id="drawerRemoveCoupon">Gỡ</button>
                            </div>
                        @endif
                    </section>

                    @if(count($availableCoupons) > 0)
                        <section class="cart-drawer__section">
                            <h3 class="cart-drawer__subtitle">🎟️ Mã đang có</h3>
                            <div class="cart-drawer__coupon-list">
                                @foreach($availableCoupons as $coupon)
                                    <div class="cpn-wrap @if($coupon['applied']) cpn-wrap--applied @endif">
                                        <x-ui.coupon-card :code="$coupon['code']" :desc="$coupon['desc']"
                                            :min-order="$coupon['minOrder']" :exp="$coupon['exp']" :applied="$coupon['applied']" />
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="cart-drawer__section">
                        <h3 class="cart-drawer__subtitle">Tóm tắt đơn hàng</h3>
                        <div class="cart-drawer__summary">
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
                        </div>
                    </section>

                    <a class="btn btn--clay btn--block cart-drawer__checkout" href="{{ route('web.checkout.index') }}">
                        Thanh toán ngay — {{ number_format($total) }}₫
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div id="couponToast" class="coupon-toast" role="status"></div>

    @push('scripts')
            <script>
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                // Thêm class body khi có sticky bar (chỉ có tác dụng trên mobile nhờ CSS)
                if (document.getElementById('cartBar')) {
                    document.body.classList.add('has-cart-bar');
                }

                // ================= SYNC NÚT "ÁP DỤNG" =================
                // Enable khi input là mã KHÁC mã đang áp dụng.
                // Disable khi: input rỗng, hoặc input bằng mã đang áp dụng.
                function syncApplyButton(inputId, btnId) {
                    const input = document.getElementById(inputId);
                    const btn = document.getElementById(btnId);
                    if (!input || !btn) return;

                    const wrap = input.closest('.coupon-input');
                    const appliedCode = ((wrap && wrap.dataset.appliedCode) || '').toUpperCase();

                    const handler = () => {
                        const val = (input.value || '').trim().toUpperCase();
                        // Enable khi: có giá trị nhập VÀ khác mã đang áp dụng
                        btn.disabled = val === '' || val === appliedCode;
                    };

                    // Bind sự kiện gõ/xoá/paste thủ công
                    input.addEventListener('input', handler);
                    input.addEventListener('change', handler);

                    // Chạy lần đầu khi trang load
                    handler();
                }

                syncApplyButton('couponCode', 'applyCoupon');
                syncApplyButton('drawerCouponCode', 'drawerApplyCoupon');

                document.addEventListener('click', function (e) {
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

                    // ==== Nút "Sao chép" của ticket mã giảm giá (dùng chung cho cả strip desktop & drawer mobile) ====
                    const copyBtn = e.target.closest('.copy-btn');
                    if (copyBtn && !copyBtn.disabled) {
                        const code = (copyBtn.dataset.code || '').trim().toUpperCase();
                        if (!code) return;
                        copyCouponCode(code);
                    }
                });

                document.addEventListener('change', function (e) {
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

                // ================= COUPON (dùng chung cho desktop + drawer) =================

                let couponToastTimer = null;

                function showCouponToast(message) {
                    const toast = document.getElementById('couponToast');
                    if (!toast) return;
                    toast.textContent = message;
                    toast.classList.add('coupon-toast--show');
                    if (couponToastTimer) clearTimeout(couponToastTimer);
                    couponToastTimer = setTimeout(() => toast.classList.remove('coupon-toast--show'), 2200);
                }

                /**
                 * Điền mã vừa copy vào input đang active:
                 * - Drawer mở => điền vào #drawerCouponCode
                 * - Drawer đóng => điền vào #couponCode (desktop sidebar)
                 *
                 * QUAN TRỌNG: sau khi gán input.value = code bằng JS,
                 * phải dispatch 'input' event để syncApplyButton chạy lại.
                 */
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
                    const drawer = document.getElementById('cartDrawer');
                    const inputId = (drawer && drawer.classList.contains('is-open'))
                        ? 'drawerCouponCode'
                        : 'couponCode';
                    const input = document.getElementById(inputId);
                    if (input) {
                        input.value = code;
                        // FIRE EVENT: để syncApplyButton biết input đã thay đổi và enable nút "Áp dụng"
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.focus();
                        input.select();
                    }
                    showCouponToast('Đã sao chép mã ' + code + ' — bấm "Áp dụng" để dùng');
                }

                /**
        * Áp mã: dùng chung cho cả desktop (#couponCode) và drawer (#drawerCouponCode).
        * Hiển thị thông báo dạng alert box có icon (không còn text trần).
        */
                async function applyCouponCode(inputId, msgId) {
                    const input = document.getElementById(inputId);
                    const msgEl = document.getElementById(msgId);
                    if (!input || !msgEl) return;

                    const code = (input.value || '').trim().toUpperCase();
                    // Reset trạng thái alert
                    msgEl.className = 'coupon-alert';
                    msgEl.innerHTML = '';

                    if (!code) {
                        showAlert(msgEl, 'Vui lòng nhập mã giảm giá.', 'error');
                        return;
                    }

                    // Show loading state
                    showAlert(msgEl, 'Đang kiểm tra mã...', 'loading');

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
                            showAlert(msgEl, data.message || 'Áp mã thành công!', 'success');
                            // Delay 600ms để user thấy thông báo success trước khi reload
                            setTimeout(() => location.reload(), 600);
                        } else {
                            showAlert(msgEl, data.message || 'Không thể áp dụng mã này.', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showAlert(msgEl, 'Có lỗi xảy ra khi kết nối đến server.', 'error');
                    }
                }

                /**
                 * Render alert box với icon và message.
                 * type: 'error' | 'success' | 'loading'
                 */
                function showAlert(el, message, type) {
                    const icons = {
                        error: '<span class="coupon-alert__icon">⚠️</span>',
                        success: '<span class="coupon-alert__icon">✓</span>',
                        loading: '<span class="coupon-alert__icon coupon-alert__icon--spin">⏳</span>',
                    };
                    el.className = `coupon-alert coupon-alert--${type}`;
                    el.innerHTML = `${icons[type] || ''}<span class="coupon-alert__text">${message}</span>`;
                }

                /**
                 * Gỡ mã đang áp (chung cho desktop & drawer).
                 */
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

                // Bind desktop
                document.getElementById('applyCoupon')?.addEventListener('click', () => applyCouponCode('couponCode', 'couponMsg'));
                document.getElementById('removeCoupon')?.addEventListener('click', removeAppliedCoupon);
                document.getElementById('couponCode')?.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        applyCouponCode('couponCode', 'couponMsg');
                    }
                });

                // Bind drawer
                document.getElementById('drawerApplyCoupon')?.addEventListener('click', () => applyCouponCode('drawerCouponCode', 'drawerCouponMsg'));
                document.getElementById('drawerRemoveCoupon')?.addEventListener('click', removeAppliedCoupon);
                document.getElementById('drawerCouponCode')?.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        applyCouponCode('drawerCouponCode', 'drawerCouponMsg');
                    }
                });

                // ================= CART DRAWER (mobile) =================
                function openCartDrawer() {
                    const drawer = document.getElementById('cartDrawer');
                    if (!drawer) return;
                    drawer.classList.add('is-open');
                    drawer.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('is-locked');
                    // Focus vào input mã để khách gõ ngay
                    setTimeout(() => {
                        document.getElementById('drawerCouponCode')?.focus();
                    }, 200);
                }

                function closeCartDrawer() {
                    const drawer = document.getElementById('cartDrawer');
                    if (!drawer) return;
                    drawer.classList.remove('is-open');
                    drawer.setAttribute('aria-hidden', 'true');
                    document.body.classList.remove('is-locked');
                }

                document.getElementById('openCartDrawer')?.addEventListener('click', openCartDrawer);

                // Delegated close (nút X + backdrop)
                document.getElementById('cartDrawer')?.addEventListener('click', function (e) {
                    if (e.target.closest('[data-close-drawer]')) {
                        closeCartDrawer();
                    }
                });

                // ESC đóng drawer
                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') {
                        closeCartDrawer();
                    }
                });

                // Qty +/- debounce effect
                document.querySelectorAll('[data-step]').forEach(btn => {
                    btn.addEventListener('click', function () {
                        this.disabled = true;
                        setTimeout(() => { this.disabled = false; }, 500);
                    });
                });
            </script>
    @endpush
</x-layouts.app>