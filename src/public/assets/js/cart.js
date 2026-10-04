/**
 * CART LOADER — giữ nguyên tên file cart.js để tương thích mọi tham chiếu cũ.
 * - cart-badge.js: LUÔN nạp (define window.updateCartCount — contract mà inline
 *   scripts của products/category/search + Buy Now kiểm tra typeof trước khi gọi;
 *   đồng thời GET /gio-hang/count khởi tạo badge khi tải trang).
 * - cart-add.js: chỉ nạp khi trang có nút .add-cart (tiết kiệm JS cho các trang
 *   không bán hàng trực tiếp như checkout/blog/policy).
 * Logic trong 2 module giữ NGUYÊN BẢN như cart.js cũ.
 */
import '@tm/cart-badge';

if (document.querySelector('.add-cart')) {
    import('@tm/cart-add');
}