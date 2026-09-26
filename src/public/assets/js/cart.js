(function () {
    'use strict';

    console.log('[Cart] Initializing cart functionality...');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const cartBadge = document.querySelector('#cartBadge');

    // Biến để tránh spam click và quản lý timeout của toast
    let isProcessing = false;
    let toastTimeout = null;

    console.log('[Cart] CSRF Token:', csrfToken ? 'Found' : 'NOT FOUND');
    console.log('[Cart] Cart Badge:', cartBadge ? 'Found' : 'NOT FOUND');

    /**
     * Hiển thị thông báo toast cho người dùng
     * @param {string} message - Nội dung thông báo
     * @param {string} type - Loại thông báo ('success', 'error', 'warning')
     */
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        if (!toast) {
            console.error('[Cart] Toast element not found');
            return;
        }

        // Xóa timeout cũ nếu có để tránh chồng lấn
        if (toastTimeout) {
            clearTimeout(toastTimeout);
        }

        console.log('[Cart] Showing toast:', message);
        toast.textContent = message;
        // Reset classes và thêm class mới
        toast.className = `toast toast--${type} is-visible`;

        toastTimeout = setTimeout(() => {
            toast.classList.remove('is-visible');
        }, 3000);
    }

    /**
     * Cập nhật số lượng trên badge giỏ hàng ở header
     * @param {number} count - Tổng số lượng sản phẩm trong giỏ
     */
    function updateCartBadge(count) {
        // Đảm bảo count là số hợp lệ, nếu null/undefined thì về 0
        const safeCount = Number(count) || 0;
        console.log('[Cart] Updating badge to:', safeCount);

        if (cartBadge) {
            cartBadge.textContent = safeCount;

            // Ẩn badge nếu count = 0 (tùy UI, nếu muốn hiện số 0 thì bỏ dòng if này)
            if (safeCount === 0) {
                cartBadge.style.display = 'none';
            } else {
                cartBadge.style.display = ''; // Reset về display mặc định (flex/inline-block)

                // Restart animation
                cartBadge.style.animation = 'none';
                // Force reflow để restart animation
                void cartBadge.offsetWidth;
                cartBadge.style.animation = 'badge-pulse 0.3s ease-in-out';
            }
        }
    }

    /**
     * Gửi request thêm sản phẩm vào giỏ hàng
     * @param {HTMLElement} buttonElement - Nút bấm được click
     * @param {number} productId - ID sản phẩm
     * @param {number} variantId - ID biến thể
     * @param {string} productName - Tên sản phẩm (để hiển thị toast)
     * @param {number} qty - Số lượng thêm vào
     */
    async function addToCart(buttonElement, productId, variantId, productName, qty = 1) {
        if (isProcessing) return; // Chặn spam click

        console.log('[Cart] Adding to cart:', { productId, variantId, qty, productName });

        if (!csrfToken) {
            console.error('[Cart] CSRF token not found');
            showToast('Lỗi hệ thống: Không tìm thấy CSRF token', 'error');
            return;
        }

        // UI Loading state
        isProcessing = true;
        const originalBtnText = buttonElement.innerHTML;
        buttonElement.disabled = true;
        // buttonElement.innerHTML = '<span class="inline-block animate-spin mr-2">⟳</span> Đang thêm...';

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
                    qty: qty,
                }),
            });

            // Kiểm tra HTTP status trước khi parse JSON
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

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
            showToast('Có lỗi xảy ra khi thêm vào giỏ hàng. Vui lòng thử lại.', 'error');
        } finally {
            // Reset UI state
            isProcessing = false;
            buttonElement.disabled = false;
            buttonElement.innerHTML = originalBtnText;
        }
    }

    // Event delegation cho tất cả buttons .add-cart
    document.addEventListener('click', function (e) {
        const button = e.target.closest('.add-cart');
        if (!button) return;

        e.preventDefault();
        // Không dùng e.stopPropagation() để tránh chặn sự kiện click của parent (nếu có)

        // 1. Lấy Product ID (luôn có trong dataset của button)
        const productId = parseInt(button.dataset.productId, 10);
        const productName = button.dataset.name || 'Sản phẩm';

        // 2. Lấy Variant ID: 
        // Ưu tiên tìm input radio đang được check trên PDP (Trang chi tiết)
        // Nếu không có (ở trang chủ/danh mục) thì lấy từ dataset của button
        let variantId = parseInt(button.dataset.variantId, 10);

        if (isNaN(variantId)) {
            // Tìm radio button variant đang được chọn
            const selectedVariant = document.querySelector('input[name="variant_id"]:checked') ||
                document.querySelector('input[name="variant"]:checked');
            if (selectedVariant) {
                variantId = parseInt(selectedVariant.value, 10);
            }
        }

        // 3. Lấy số lượng (Qty): 
        // Ưu tiên tìm input qty trên PDP, mặc định là 1
        let qty = 1;
        const qtyInput = document.querySelector('input[name="qty"]');
        if (qtyInput) {
            qty = parseInt(qtyInput.value, 10) || 1;
            // Đảm bảo số lượng không nhỏ hơn 1
            if (qty < 1) qty = 1;
        }

        // 4. Validate dữ liệu trước khi gửi
        if (isNaN(productId)) {
            console.error('[Cart] Missing product_id', button.dataset);
            showToast('Không tìm thấy mã sản phẩm', 'error');
            return;
        }
        
        if (isNaN(variantId)) {
            console.error('[Cart] Missing variant_id', button.dataset);
            showToast('Vui lòng chọn quy cách/phiên bản sản phẩm', 'error');

            // UX Bonus: Tự động scroll hoặc highlight khu vực chọn biến thể nếu có
            const variantSection = document.querySelector('.variant-selector, .variant-options');
            if (variantSection) {
                variantSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                // Thêm hiệu ứng nhấp nháy nhẹ (cần class animate-pulse của Tailwind)
                variantSection.classList.add('animate-pulse', 'ring-2', 'ring-red-400', 'rounded-lg');
                setTimeout(() => {
                    variantSection.classList.remove('animate-pulse', 'ring-2', 'ring-red-400', 'rounded-lg');
                }, 1500);
            }
            return;
        }
            
        // 5. Gọi hàm thêm vào giỏ
        addToCart(button, productId, variantId, productName, qty);
    });

    /**
     * Lấy tổng số lượng sản phẩm trong giỏ hàng khi tải trang
     */
    async function fetchCartCount() {
        console.log('[Cart] Fetching cart count...');
        try {
            const response = await fetch('/gio-hang/count', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('[Cart] Cart count:', data.count);
            updateCartBadge(data.count);
        } catch (error) {
            console.warn('[Cart] Error fetching cart count (Route may not exist or server error):', error);
            // Fallback: Ẩn badge hoặc set về 0 nếu không lấy được
            updateCartBadge(0);
        }
    }

    // Khởi tạo khi DOM ready
    function init() {
        console.log('[Cart] DOM loaded, initializing...');
        fetchCartCount();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();