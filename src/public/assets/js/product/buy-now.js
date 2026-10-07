/**
 * APP PRODUCT — BUY NOW (#buyNow desktop + #buyNowMobile buybar):
 * POST /gio-hang/mua-ngay (kèm X-CSRF-TOKEN) -> window.updateCartCount + redirect /thanh-toan.
 * Tách từ app-product.js cũ (dòng 653–736) — logic GIỮ NGUYÊN BẢN, chỉ thêm dòng import.
 */
import { q, toast } from '@tm/core';

/* =====================================================================
   BUY NOW (#buyNow desktop + #buyNowMobile buybar):
   - Thu thập: product_id (data-product-id), variant_id (radio checked
     name="variant_id" — fallback name="variant"), qty (input[name="qty"])
   - POST /gio-hang/mua-ngay (kèm X-CSRF-TOKEN từ meta[name="csrf-token"])
   - Thành công: cập nhật badge qua window.updateCartCount (contract cart.js)
     rồi redirect tới resp.redirect (/thanh-toan)
   - Dùng event delegation trên document → bind được cả 2 nút, không lo
     timing DOM. Biến isBuying chặn spam click khi đang gửi request.
   ===================================================================== */
const csrfToken = q('meta[name="csrf-token"]')?.content;
let isBuying = false;

async function buyNow(button) {
    if (isBuying) return;

    const productId = parseInt(button.dataset.productId, 10);
    if (isNaN(productId)) { toast('Không tìm thấy mã sản phẩm'); return; }

    // Variant đang chọn trên PDP (khớp convention của cart.js)
    const selectedVariant = q('input[name="variant_id"]:checked') || q('input[name="variant"]:checked');
    const variantId = selectedVariant ? parseInt(selectedVariant.value, 10) : NaN;
    if (isNaN(variantId)) {
        toast('Vui lòng chọn quy cách sản phẩm');
        const pills = q('.pills');
        if (pills) pills.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    // Số lượng từ stepper PDP, clamp >= 1
    const qtyInput = q('input[name="qty"]');
    let qty = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;
    if (qty < 1) qty = 1;

    if (!csrfToken) { toast('Lỗi hệ thống: Không tìm thấy CSRF token'); return; }

    isBuying = true;
    const originalHtml = button.innerHTML;
    button.disabled = true;
    button.textContent = 'Đang xử lý…';

    try {
        const res = await fetch('/gio-hang/mua-ngay', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ product_id: productId, variant_id: variantId, qty }),
        });

        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();

        if (data.success) {
            // Đồng bộ badge giỏ (header + floatnav) trước khi rời trang
            if (typeof window.updateCartCount === 'function') {
                window.updateCartCount(data.cartCount);
            }
            if (data.redirect) {
                window.location.assign(data.redirect);
                return; // giữ nguyên trạng thái disabled tới khi chuyển trang
            }
        } else {
            toast(data.message || 'Không thể mua ngay, vui lòng thử lại');
        }
    } catch (err) {
        console.error('[BuyNow]', err);
        toast('Có lỗi xảy ra, vui lòng thử lại');
    } finally {
        // Chỉ reset khi KHÔNG redirect (lỗi / thiếu URL)
        isBuying = false;
        button.disabled = false;
        button.innerHTML = originalHtml;
    }
}

document.addEventListener('click', e => {
    const btn = e.target.closest('#buyNow, #buyNowMobile');
    if (!btn) return;
    e.preventDefault();
    buyNow(btn);
});