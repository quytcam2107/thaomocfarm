/**
 * CART BADGE — tách từ cart.js cũ, logic giữ nguyên.
 * Chứa CONTRACT QUAN TRỌNG: window.updateCartCount (inline scripts của
 * products/category/search + Buy Now trong app-product.js đều gọi hàm này).
 * Loader cart.js luôn nạp module này trên mọi trang.
 */

// Badge giỏ hàng: chọn theo class .js-cart-count / .cart-count để lấy CẢ badge header__acts
// (desktop), badge floatnav (mobile) lẫn badge "Giỏ hàng" trong drawer__util (mobile).
// Hàm query động để luôn lấy được danh sách mới nhất tại thời điểm cập nhật.
export function getCartBadges() {
    return Array.from(document.querySelectorAll('.js-cart-count, .cart-count'));
}

// Timeout riêng của badge-toast (toast tập trung đã chuyển vào cart.js loader)
let toastTimeout = null;

/**
 * Hiển thị thông báo toast cho người dùng (dùng class .show giống app.js)
 * @param {string} message - Nội dung thông báo
 */
export function showToast(message) {
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
    toast.classList.add('show');

    toastTimeout = setTimeout(() => {
        toast.classList.remove('show');
    }, 1800);
}

/**
 * Cập nhật số lượng trên TẤT CẢ badge giỏ hàng (header__acts + floatnav + drawer tiện ích)
 * @param {number} count - Tổng số lượng sản phẩm trong giỏ
 */
export function updateCartBadge(count) {
    // Đảm bảo count là số hợp lệ, nếu null/undefined thì về 0
    const safeCount = Number(count) || 0;
    console.log('[Cart] Updating badges to:', safeCount);

    getCartBadges().forEach((badge) => {
        badge.textContent = safeCount;

        if (safeCount === 0) {
            // Ẩn badge khi giỏ trống (dùng thuộc tính hidden, có CSS [hidden] hỗ trợ)
            badge.hidden = true;
        } else {
            badge.hidden = false;

            // Restart animation pulse
            badge.style.animation = 'none';
            void badge.offsetWidth; // force reflow
            badge.style.animation = 'badge-pulse 0.3s ease-in-out';
        }
    });
}

// API dùng chung cho các script add-cart inline (products/category/search):
// window.updateCartCount(n) -> cập nhật đồng bộ mọi badge (mobile + desktop + drawer)
window.updateCartCount = updateCartBadge;

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
        updateCartBadge(0);
    }
}

// Module type="module" luôn chạy sau khi HTML parse xong -> gọi trực tiếp
// (giữ hành vi DOMContentLoaded của bản cũ, đồng thời an toàn khi browser back/forward cache)
fetchCartCount();