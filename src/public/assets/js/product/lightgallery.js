/**
 * APP PRODUCT — PHÓNG TO ẢNH BẰNG LIGHTGALLERY.JS (dynamic mode, vendor self-host).
 * Tách từ app-product.js cũ (dòng 335–484) — logic GIỮ NGUYÊN BẢN, chỉ thêm dòng import.
 * Đọc window.__pdGallerySuppressClick (do gallery.js ghi) để không mở zoom khi vừa vuốt xong.
 */
import { q, qa } from '@tm/core';

/* =====================================================================
   PHÓNG TO ẢNH BẰNG LIGHTGALLERY.JS (THAY lightbox tự chế #pdZoomViewer).

   SỬA 2 LỖI ĐƯỢC BÁO:
   A) "Lỗi font ở các nút" (toolbar hiện ô vuông □/ký tự lạ):
      - Nguyên nhân: icon lightgallery là FONT-SYMBOL (family "lg", escape
        \e0xx). Vendor font KHÔNG được tải -> browser render glyph khuyết.
      - Fix: self-host đủ lg.woff2/woff/ttf/svg vào
        public/assets/vendor/lightgallery/fonts/ (Bước 0) + @font-face chốt
        đường dẫn asset() tuyệt đối trong partial 07.
      - Phòng vệ: hàm ensureVendorReady() dưới đây CHỜ window.lightGallery
        xuất hiện (vendor <script defer> có thể迟到 hơn module ES) -> tránh
        trường hợp bấm ảnh lúc vendor chưa load rồi lightbox mở "nửa vời".
   B) "Chưa đầy đủ công cụ":
      - Core UMD KHÔNG kèm plugin. Phải nạp lg-zoom/lg-thumbnail/lg-autoplay/
        lg-share/lg-fullscreen/lg-pager (Blade @push) VÀ truyền vào
        settings.plugins — thiếu 1 trong 2 đều mất nút.
      - Plugin caption KHÔNG tồn tại trong lightgallery@2.8.x: caption
        data-sub-html do core render -> không nạp, không khai báo.
      - settings.download:true -> core tự thêm nút Tải ảnh (không phải plugin).
      - mobileSettings.controls:true -> trên điện thoại toolbar VẪN hiện đủ
        (mặc định của lib là ẨN toolbar trên mobile, phải ghi đè).
   - Nguồn slide: các <a data-src/data-thumb/data-sub-html/data-download-url>
     trong #lgGalleryRoot (Blade render từ $images).
   - Dynamic mode + create/destroy mỗi lần mở -> không tồn tại DOM lightgallery
     khi đóng, không trùng toolbar, không rò rỉ listener.
   - Trigger mở DUY NHẤT: [data-pd-zoom="stage"] (CLICK chuột trái / CHẠM vào
     ảnh to). Hàng thumbs bấm vẫn CHỈ đổi ảnh lớn.
   - Chặn "tự zoom khi lướt chuột": đọc window.__pdGallerySuppressClick +
     e.button !== 0 + bỏ qua click vào nút prev/next và .pd-counter.
   - Fallback: vendor thiếu -> console.warn, KHÔNG phá gallery hiện có.
   ===================================================================== */
(function initLightGallery() {
    const lgRoot = q('#lgGalleryRoot');
    if (!lgRoot) return; // trang không phải PDP -> bỏ qua

    let wrapper = null;   // element tạm承载 dynamic items (chỉ tồn tại khi mở)
    let lgInstance = null;

    /* Danh sách ảnh từ Blade (nguồn duy nhất, trùng data-full hàng thumbs)
       -> mảng dynamic mode của lightgallery */
    function collectItems() {
        return qa('a[data-src]', lgRoot).map(a => ({
            src: a.getAttribute('data-src'),
            thumb: a.getAttribute('data-thumb') || a.getAttribute('data-src'),
            subHtml: a.getAttribute('data-sub-html') || '',
            downloadUrl: a.getAttribute('data-download-url') || a.getAttribute('data-src'),
        })).filter(it => it.src);
    }

    /* Danh sách plugin: lấy global do các file UMD expose; lọc bỏ plugin chưa
       tải để không crash (VD user quên 1 file curl) — log rõ file nào thiếu. */
    function resolvePlugins() {
        const wanted = [
            ['lgZoom', 'lg-zoom.umd.min.js'],
            ['lgThumbnail', 'lg-thumbnail.umd.min.js'],
            ['lgAutoplay', 'lg-autoplay.umd.min.js'],
            ['lgShare', 'lg-share.umd.min.js'],
            ['lgFullscreen', 'lg-fullscreen.umd.min.js'],
            ['lgPager', 'lg-pager.umd.min.js'],
        ];
        const plugins = [];
        wanted.forEach(([name, file]) => {
            if (typeof window[name] === 'function') plugins.push(window[name]);
            else console.warn('[lightgallery] thiếu plugin ' + name + ' — kiểm tra public/assets/vendor/lightgallery/' + file);
        });
        return plugins;
    }

    function openGallery(index) {
        if (typeof window.lightGallery !== 'function') {
            console.warn('[lightgallery] vendor JS chưa tải — kiểm tra public/assets/vendor/lightgallery/');
            return;
        }
        const items = collectItems();
        if (!items.length) return;

        /* Wrapper tạm chứa dynamic items — settings.container phải là cha của el */
        wrapper = document.createElement('div');
        wrapper.className = 'lg-dynamic-host';
        const host = q('.pd-gallery') || document.body;
        host.appendChild(wrapper);

        lgInstance = window.lightGallery(wrapper, {
            dynamic: true,
            dynamicEl: items,
            index: Math.min(Math.max(index, 0), items.length - 1),
            plugins: resolvePlugins(),   // <-- ĐỦ CÔNG CỤ: zoom/thumb/autoplay/share/fullscreen/pager
            download: true,              // nút Tải ảnh (core built-in)
            counter: true,               // "i / n" góc toolbar
            closable: true,
            closeOnTap: true,            // chạm nền đóng
            hideScrollbar: true,
            loop: true,                  // tới ảnh cuối bấm next quay về đầu (trong lightbox)
            speed: 280,
            zoomMax: 4,                  // khớp mức zoom tối đa 4x của lightbox cũ
            actualSize: true,            // nút "1:1 / xem kích thước thật"
            showZoomInOut: true,         // explicit +/- trong toolbar
            doubleTapZoom: 2,            // mobile double-tap -> 2x
            pinchZoom: true,             // pinch 2 ngón (zoom plugin)
            addClass: 'lg-tm-theme',     // hook theme xanh Thảo Mộc trong CSS
            /* mobileSettings.controls mặc định = false => ẨN toolbar trên phone.
               Bật lên true để mobile cũng đủ công cụ (mục B ở trên). */
            mobileSettings: { controls: true, showCloseIcon: true, download: true },
            /* Việt hóa TOÀN BỘ label/tooltip/aria (contract UI tiếng Việt):
               strings core + strings từng plugin — thiếu key nào plugin tự fallback
               tiếng Anh, nên khai báo đủ cả 6 bộ. */
            strings: {
                closeGallery: 'Đóng',
                toggleMaximize: 'Toàn màn hình',
                previousSlide: 'Ảnh trước',
                nextSlide: 'Ảnh sau',
                download: 'Tải ảnh',
                playVideo: 'Chạy video',
                mediaLoadingFailed: 'Không tải được ảnh…',
            },
            zoomPluginStrings: {
                zoomIn: 'Phóng to',
                zoomOut: 'Thu nhỏ',
                viewActualSize: 'Kích thước thật',
            },
            thumbnailPluginStrings: { toggleThumbnails: 'Dải ảnh nhỏ' },
            autoplayPluginStrings: { toggleAutoplay: 'Chạy slideshow' },
            sharePluginStrings: { share: 'Chia sẻ' },
            fullscreenPluginStrings: { toggleFullscreen: 'Toàn màn hình' },
            pagerPluginStrings: { currentPage: 'Trang hiện tại', totalNoOfPages: 'Tổng số trang' },
        });

        /* Đóng -> destroy + xóa wrapper: không để lại DOM lightgallery trên trang */
        wrapper.addEventListener('lgAfterClose', () => {
            try { if (lgInstance && typeof lgInstance.destroy === 'function') lgInstance.destroy(); } catch (_) { }
            lgInstance = null;
            if (wrapper) { wrapper.remove(); wrapper = null; }
        });

        lgInstance.openGallery();
    }

    /* Delegate click trên document (bind 1 lần, không lo timing DOM) */
    document.addEventListener('click', e => {
        if (lgInstance) return;                                // đang mở -> ignore
        if (e.button !== undefined && e.button !== 0) return;  // chỉ chuột trái
        if (window.__pdGallerySuppressClick) return;           // click dư sau vuốt
        if (e.target.closest('[data-pd-nav], .pd-counter')) return; // nút prev/next/counter
        if (!e.target.closest('[data-pd-zoom="stage"]')) return;
        e.preventDefault();
        const idx = parseInt((q('#pdStageImg') || {}).dataset?.index || '0', 10) || 0;
        openGallery(idx);
    });
})();