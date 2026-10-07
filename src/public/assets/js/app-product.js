/**
 * APP PRODUCT — ĐIỂM VÀO CLUSTER SẢN PHẨM (giữ nguyên tên file + key importmap "@tm/product").
 * File 830 dòng cũ đã được TÁCH THÀNH 8 MODULE CON trong public/assets/js/product/,
 * mỗi module một chức năng, logic GIỮ NGUYÊN BẢN (chỉ thêm dòng import cần dùng):
 *   1. countdown.js            — đếm flash sale H:M:S (home .countdown[data-ends] + PDP [data-pd-flash])
 *   2. gallery.js              — thumbs + nút prev/next + vuốt đổi ảnh + hiệu ứng slide 2 lớp .pd-stage__fx
 *   3. lightgallery.js         — phóng to ảnh bằng lightgallery.js (đọc window.__pdGallerySuppressClick do gallery.js ghi)
 *   4. share.js                — nút chia sẻ native + copy link ([data-share-native]/[data-copy-url])
 *   5. tabs.js                 — tabs ARIA ([role="tab"])
 *   6. spec-sync.js            — đồng bộ bảng "Quy cách & giá bán" theo khối lượng đang chọn
 *   7. buy-now.js              — #buyNow/#buyNowMobile → POST /gio-hang/mua-ngay
 *   8. description-collapse.js — "Xem thêm mô tả" tab #panel-desc
 * Lưu ý ES modules: MỌI import được hoist & thực thi TRƯỚC thân file — nghĩa là toàn bộ
 * code bên dưới chạy SAU cùng nhóm module con, đúng như vị trí comment trong file cũ.
 * Loader app.js vẫn dispatch theo marker cũ (.countdown[data-ends]/[data-pd-flash]/.pd-thumbs/
 * [role="tab"]/#buyNow/#buyNowMobile) → không đổi layout, không đổi contract JS.
 */
import './product/countdown.js';
import './product/gallery.js';
import './product/lightgallery.js';
import './product/share.js';
import './product/tabs.js';
import './product/spec-sync.js';
import './product/buy-now.js';
import './product/description-collapse.js';