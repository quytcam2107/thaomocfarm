/**
 * CART ADD — tách từ cart.js cũ, logic giữ nguyên 100%.
 * Loader cart.js chỉ nạp module này khi trang CÓ nút .add-cart
 * (trang thanh toán/giới thiệu/blog... không tải đoạn code này).
 */
import { getCartBadges, updateCartBadge, showToast } from '@tm/cart-badge';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

console.log('[Cart] CSRF Token:', csrfToken ? 'Found' : 'NOT FOUND');
console.log('[Cart] Cart Badges:', getCartBadges().length, 'found');

// Biến để tránh spam click
let isProcessing = false;

/**
 * Gửi request thêm sản phẩm vào giỏ hàng
 */
async function addToCart(buttonElement, productId, variantId, productName, qty = 1) {
    if (isProcessing) return; // Chặn spam click

    console.log('[Cart] Adding to cart:', { productId, variantId, qty, productName });

    if (!csrfToken) {
        console.error('[Cart] CSRF token not found');
        showToast('Lỗi hệ thống: Không tìm thấy CSRF token');
        return;
    }

    // UI Loading state
    isProcessing = true;
    const originalBtnText = buttonElement.innerHTML;
    buttonElement.disabled = true;

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
            showToast(`Đã thêm "${productName}" vào giỏ hàng`);
        } else {
            console.error('[Cart] Add to cart failed:', data.message);
            showToast(data.message || 'Không thể thêm vào giỏ hàng');
        }
    } catch (error) {
        console.error('[Cart] Error:', error);
        showToast('Có lỗi xảy ra khi thêm vào giỏ hàng. Vui lòng thử lại.');
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

    // Các trang products/category/search đã có handler riêng (không dùng cart.js flow)
    if (button.dataset.productId && isNaN(parseInt(button.dataset.variantId, 10)) &&
        !document.querySelector('input[name="variant_id"]:checked') &&
        !document.querySelector('input[name="variant"]:checked') &&
        button.closest('.product-grid')) {
        return; // Để script inline của trang đó xử lý
    }

    e.preventDefault();

    // 1. Lấy Product ID
    const productId = parseInt(button.dataset.productId, 10);
    const productName = button.dataset.name || 'Sản phẩm';

    // 2. Lấy Variant ID: ưu tiên radio checked trên PDP, fallback dataset
    let variantId = parseInt(button.dataset.variantId, 10);

    if (isNaN(variantId)) {
        const selectedVariant = document.querySelector('input[name="variant_id"]:checked') ||
            document.querySelector('input[name="variant"]:checked');
        if (selectedVariant) {
            variantId = parseInt(selectedVariant.value, 10);
        }
    }

    // 3. Lấy số lượng (Qty): ưu tiên input qty trên PDP, mặc định 1
    let qty = 1;
    const qtyInput = document.querySelector('input[name="qty"]');
    if (qtyInput) {
        qty = parseInt(qtyInput.value, 10) || 1;
        if (qty < 1) qty = 1;
    }

    // 4. Validate dữ liệu trước khi gửi
    if (isNaN(productId)) {
        console.error('[Cart] Missing product_id', button.dataset);
        showToast('Không tìm thấy mã sản phẩm');
        return;
    }

    if (isNaN(variantId)) {
        console.error('[Cart] Missing variant_id', button.dataset);
        showToast('Vui lòng chọn quy cách/phiên bản sản phẩm');

        const variantSection = document.querySelector('.variant-selector, .variant-options');
        if (variantSection) {
            variantSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
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