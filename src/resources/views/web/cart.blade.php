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
                        <input id="couponCode" placeholder="Nhập mã giảm giá" aria-label="Mã giảm giá">
                        <button class="btn btn--ghost" id="applyCoupon">Áp dụng</button>
                    </div>
                    @if($subtotal < $freeShippingThreshold)
                        <p class="freeship-note">
                            🎁 Thêm <strong>{{ number_format($freeShippingThreshold - $subtotal) }}₫</strong> để được miễn phí vận chuyển
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
                    // Reload trang để cập nhật tổng tiền và phí ship
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
                    // Reload trang sau khi xóa
                    location.reload();
                } else {
                    alert('Không thể xóa sản phẩm');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xóa sản phẩm');
            }
        }

        // Thêm hiệu ứng loading khi click nút +/-
        document.querySelectorAll('[data-step]').forEach(btn => {
            btn.addEventListener('click', function() {
                this.disabled = true;
                setTimeout(() => {
                    this.disabled = false;
                }, 500);
            });
        });
    </script>
    @endpush
</x-layouts.app>