(function () {
    'use strict';

    console.log('[Cart] Initializing cart functionality...');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const cartBadge = document.querySelector('#cartBadge');

    console.log('[Cart] CSRF Token:', csrfToken ? 'Found' : 'NOT FOUND');
    console.log('[Cart] Cart Badge:', cartBadge ? 'Found' : 'NOT FOUND');

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        if (!toast) {
            console.error('[Cart] Toast element not found');
            return;
        }

        console.log('[Cart] Showing toast:', message);
        toast.textContent = message;
        toast.className = `toast toast--${type} is-visible`;

        setTimeout(() => {
            toast.classList.remove('is-visible');
        }, 3000);
    }

    function updateCartBadge(count) {
        console.log('[Cart] Updating badge to:', count);
        if (cartBadge) {
            cartBadge.textContent = count;
            cartBadge.style.animation = 'badge-pulse 0.3s ease-in-out';
            setTimeout(() => {
                cartBadge.style.animation = '';
            }, 300);
        }
    }

    async function addToCart(productId, variantId, productName) {
        console.log('[Cart] Adding to cart:', { productId, variantId, productName });

        if (!csrfToken) {
            console.error('[Cart] CSRF token not found');
            showToast('Lỗi: Không tìm thấy CSRF token', 'error');
            return;
        }

        try {
            const response = await fetch('/gio-hang/them', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    variant_id: variantId,
                    qty: 1,
                }),
            });

            console.log('[Cart] Response status:', response.status);

            const data = await response.json();
            console.log('[Cart] Response data:', data);

            if (data.success) {
                updateCartBadge(data.cartCount);
                showToast(`Đã thêm "${productName}" vào giỏ hàng`, 'success');
            } else {
                console.error('[Cart] Add to cart failed:', data.message);
                showToast(data.message || 'Không thể thêm vào giỏ hàng', 'error');
            }
        } catch (error) {
            console.error('[Cart] Error:', error);
            showToast('Có lỗi xảy ra khi thêm vào giỏ hàng', 'error');
        }
    }

    // Event delegation cho tất cả buttons .add-cart
    document.addEventListener('click', function (e) {
        console.log('[Cart] Click event:', e.target);

        const button = e.target.closest('.add-cart');
        console.log('[Cart] Found add-cart button:', button);

        if (!button) return;

        e.preventDefault();
        e.stopPropagation();

        const productId = parseInt(button.dataset.productId);
        const variantId = parseInt(button.dataset.variantId);
        const productName = button.dataset.name || 'Sản phẩm';

        console.log('[Cart] Button data:', {
            productId,
            variantId,
            productName,
            dataset: button.dataset
        });

        if (!productId || !variantId) {
            console.error('[Cart] Missing product_id or variant_id');
            showToast('Không tìm thấy thông tin sản phẩm', 'error');
            return;
        }

        addToCart(productId, variantId, productName);
    });

    async function fetchCartCount() {
        console.log('[Cart] Fetching cart count...');
        try {
            const response = await fetch('/gio-hang/count', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();
            console.log('[Cart] Cart count:', data.count);
            updateCartBadge(data.count);
        } catch (error) {
            console.error('[Cart] Error fetching cart count:', error);
        }
    }

    // Khởi tạo khi DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            console.log('[Cart] DOM loaded, initializing...');
            fetchCartCount();

            // Debug: Kiểm tra tất cả buttons add-cart
            const addCartButtons = document.querySelectorAll('.add-cart');
            console.log('[Cart] Found add-cart buttons:', addCartButtons.length);
            addCartButtons.forEach((btn, index) => {
                console.log(`[Cart] Button ${index}:`, {
                    productId: btn.dataset.productId,
                    variantId: btn.dataset.variantId,
                    name: btn.dataset.name
                });
            });
        });
    } else {
        console.log('[Cart] DOM already loaded, initializing...');
        fetchCartCount();
    }
})();