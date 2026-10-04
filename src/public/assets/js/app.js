/**
 * APP LOADER — điểm nạp duy nhất từ layout (giữ nguyên tên file app.js).
 * Nguyên tắc tách module:
 *  - core luôn chạy (toast/reveal/count-up — rất nhỏ).
 *  - ui/backtotop/product/search CHỈ được dynamic import khi trang có marker
 *    tương ứng trong DOM -> PDP không tải code search, trang con không tải
 *    code back-to-top... => giảm băng thông, giảm parse/exec, cải thiện LCP/CLS.
 *  - Module là type="module" => thực thi sau khi HTML parse xong (như defer),
 *    không chặn render, không đổi thứ tự so với bản cũ (vốn đặt cuối body).
 * Logic bên trong từng module giữ NGUYÊN BẢN như app.js cũ — chỉ di chuyển vị trí.
 */
import '@tm/core';

const has = sel => !!document.querySelector(sel);

// Drawer burger + clone bộ lọc mobile: chỉ khi có #burgerBtn hoặc cặp filterGroups/filterDrawerBody
if (has('#burgerBtn') || (has('#filterGroups') && has('#filterDrawerBody'))) {
    import('@tm/ui');
}

// Back-to-top: chỉ trang bật :show-back-to-top (hiện là home) render #backToTop
if (has('#backToTop')) {
    import('@tm/backtotop');
}

// Cluster sản phẩm: countdown home/PDP, gallery, share, tabs, Buy Now
if (has('.countdown[data-ends]') || has('[data-pd-flash]') || has('.pd-thumbs')
    || has('[role="tab"]') || has('#buyNow') || has('#buyNowMobile')) {
    import('@tm/product');
}

// Search suggest + spotlight: chỉ khi header có đủ bộ 3 element
if (has('#searchForm') && has('#searchInput') && has('#searchSuggest')) {
    import('@tm/search');
}