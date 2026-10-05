/**
 * APP PRODUCT — countdown flash sale (home + PDP), gallery thumbs + vuốt ảnh,
 * phóng to ảnh bằng LIGHTGALLERY.JS, nút chia sẻ, tabs ARIA, Buy Now.
 * Loader chỉ nạp khi có một trong các marker:
 * .countdown[data-ends] / [data-pd-flash] / .pd-thumbs / [role="tab"] / #buyNow,#buyNowMobile.
 */
import { q, qa, toast } from '@tm/core';

/* =====================================================================
   COUNTDOWN FLASH SALE — HOME + PDP dùng chung 1 engine GIỜ:PHÚT:GIÂY
   - data-ends (unix giây) do SERVER tính tại thời điểm render và đã clamp
     <= 24h: phiên còn chạy quá 24h -> đếm đúng 24h; phiên đã hết hạn ->
     block VẪN hiển thị và đếm vòng 24h từ lúc render.
   - Vì mốc là unix giây server sinh, JS chỉ việc đếm — KHÔNG cần meta
     server-time (đã loại bỏ để không ảnh hưởng SEO).
   - KHÔNG hiển thị ô "ngày": tổng giây còn lại quy hết ra giờ (max 24).
   ===================================================================== */
const pad = n => String(n).padStart(2, '0');

/* Engine đếm H:M:S tới 1 unix timestamp; timer trả về để clear khi cần */
function runCountdown(hEl, mEl, sEl, endsUnix) {
    const tick = () => {
        const s = Math.max(0, Math.floor((endsUnix * 1000 - Date.now()) / 1000));
        hEl.textContent = pad(Math.floor(s / 3600));
        mEl.textContent = pad(Math.floor((s % 3600) / 60));
        sEl.textContent = pad(s % 60);
        if (s <= 0) clearInterval(timer); // dừng ở 00:00:00, vẫn giữ block hiển thị
    };
    tick();
    const timer = setInterval(tick, 1000);
    return timer;
}

/* Countdown trang home: đếm tới mốc data-ends server đã clamp <= 24h */
const cdHome = q('.countdown[data-ends]');
const cdH = q('#cdH'), cdM = q('#cdM'), cdS = q('#cdS');
if (cdH && cdM && cdS && cdHome) {
    const endsUnix = parseInt(cdHome.dataset.ends, 10);
    if (!isNaN(endsUnix) && endsUnix > 0) {
        runCountdown(cdH, cdM, cdS, endsUnix);
    }
}

/* Countdown flash sale trên PDP — CÙNG engine, CÙNG công thức mốc với home */
const pdBox = q('[data-pd-flash]');
if (pdBox) {
    const pdH = q('#pdCdH'), pdM = q('#pdCdM'), pdS = q('#pdCdS');
    const endsUnix = parseInt(pdBox.dataset.ends, 10);
    if (pdH && pdM && pdS && !isNaN(endsUnix) && endsUnix > 0) {
        runCountdown(pdH, pdM, pdS, endsUnix);
    }
}

/* =====================================================================
   GALLERY PDP: thumb click + nút prev/next + VUỐT ĐỂ CHUYỂN ẢNH.

   FIX LỖI "vuốt chuột / click nút trên ảnh to không chuyển ảnh" (3 mắt xích):
   1) Không setPointerCapture khi pointerdown rơi vào [data-pd-nav]: capture
      làm pointerup/truy vết click dồn về .pd-stage -> button con không bao giờ
      nhận click. Nay nút prev/next overlay hoạt động bình thường.
   2) Ngưỡng vuốt hạ 28px -> 12px + cancel native drag (dragstart.preventDefault)
      để chuột kéo trên ảnh không bị HTML5 image-drag cắt mất pointermove.
   3) Chặn click "dư" sau vuốt bằng flag gallerySuppressClick (KHAI BÁO CỤC BỘ
      trong block này) + đồng bộ sang window.__pdGallerySuppressClick để khối
      lightgallery đọc được -> vuốt/lướt chuột trên ảnh to KHÔNG bao giờ mở
      phóng to ("lướt chuột là tự zoom").
   - Desktop: pointerdown chỉ theo dõi vuốt khi nhấn CHUỘT TRÁI (button===0);
     hover thuần không sinh action nào, con trỏ là HÌNH BÀN TAY (không icon
     kính lúp +, không cursor zoom-in). Chỉ CLICK chuột trái thật sự vào ảnh
     to mới mở phóng to.
   - Wrap-around: ẢNH CUỐI vuốt trái -> về ảnh ĐẦU, ẢNH ĐẦU vuốt phải -> ra
     ảnh cuối (chỉ áp dụng cho VUỐT; NÚT prev/next thì stop ở biên).
   - DISABLED Ở BIÊN: nút prev disabled khi đang xem ảnh đầu, nút next disabled
     khi đang xem ảnh cuối. Blade render sẵn prev disabled (trang luôn mở đầu
     ở ảnh 1) để đúng cả trước khi JS chạy; updateNavState() đồng bộ lại sau
     MỌI lần đổi ảnh (nút / thumb / vuốt).
   - Hiệu ứng chuyển ảnh MƯỢT THEO HƯỚNG (FIX GIẬT HÌNH): không chạy animation
     trên #pdStageImg nữa mà trên 2 lớp phủ .pd-stage__fx (Blade render khi >1
     ảnh) — ảnh CŨ đứng lại + mờ dần, ảnh MỚI chờ load xong rồi slide vào từ
     mép trái/phải tùy hướng; keyframes pdImgOut / pdImgInFromLeft/Right trong
     07-product-detail.css. Chi tiết xem khối fxPlay bên dưới.
   - Cuốn thumb đang chọn vào tầm nhìn khi hàng thumbs tràn ngang.
   ===================================================================== */
const stage = q('#pdStageImg');
const thumbs = qa('.pd-thumbs button');
if (stage && thumbs.length) {
    const counter = q('#pdCounter');
    const stageBox = q('.pd-stage');
    const thumbsRow = q('.pd-thumbs');       /* hàng thumbs cuộn ngang */
    const thumbsWrap = q('.pd-thumbs-wrap'); /* cha flex chứa 2 nút prev/next nhỏ */
    let current = parseInt(stage.dataset.index || '0', 10) || 0;
    /* Flag chặn click "dư" phát ra sau một cú vuốt thật (desktop + mobile).
       Đồng bộ sang window.__pdGallerySuppressClick để lightgallery đọc được —
       nhờ đó CLICK DƯ sau vuốt chuột trên ảnh to KHÔNG bao giờ mở phóng to. */
    let gallerySuppressClick = false;
    window.__pdGallerySuppressClick = false;
    const setGallerySuppress = (v) => {
        gallerySuppressClick = v;
        window.__pdGallerySuppressClick = v; // chia sẻ trạng thái cho lightgallery
    };

    /* =================================================================
       2 nút prev/next của HÀNG THUMBS chỉ hiện khi hàng thumbs THỰC SỰ
       tràn ngang (còn ảnh ngoài tầm nhìn -> cần vuốt).
       - Blade render sẵn 2 span [data-thumbs-nav] với attribute `hidden`
         => mặc định ẩn trên MỌI màn hình, kể cả khi JS chưa kịp chạy.
       - updateThumbsNav() đo scrollWidth > clientWidth (+2px dung sai):
         * không tràn -> ẩn 2 nút + data-cols="auto": hàng thumbs co về
           đúng khổ nội dung (không giữ slot nút thừa -> khỏi lệch tâm so
           với khung ảnh to).
         * tràn -> hiện 2 nút + data-cols="fill": hàng thumbs chiếm phần
           còn lại của cột và cuộn trong phạm vi đó.
       - ResizeObserver (fallback: resize/load) => ĐÚNG TRÊN MỌI KÍCH
         THƯỚC MÀN HÌNH: desktop/tablet/mobile, đổi orientation, zoom.
       ================================================================= */
    function updateThumbsNav() {
        if (!thumbsRow || !thumbsWrap) return;
        const overflow = thumbsRow.scrollWidth - thumbsRow.clientWidth > 2;
        qa('[data-thumbs-nav]').forEach(el => el.hidden = !overflow);
        thumbsWrap.dataset.cols = overflow ? 'fill' : 'auto';
    }

    /* Mặc định cols="auto" ngay khi module chạy (trước cả lần đo đầu) — đồng bộ
       với chốt an toàn trong 07-product-detail.css để hàng thumbs không bao
       giờ đẩy giãn cột grid gây scroll ngang toàn trang */
    if (thumbsWrap) thumbsWrap.dataset.cols = 'auto';
    updateThumbsNav();
    if (typeof ResizeObserver !== 'undefined') {
        const ro = new ResizeObserver(updateThumbsNav);
        if (thumbsRow) ro.observe(thumbsRow);
        if (thumbsWrap) ro.observe(thumbsWrap);
    } else {
        window.addEventListener('resize', updateThumbsNav);
        window.addEventListener('load', updateThumbsNav);
    }
    /* Ảnh thumb lazy-load làm chiều rộng nội dung đổi -> đo lại vài nhịp */
    [50, 300, 800].forEach(ms => setTimeout(updateThumbsNav, ms));

    /* =================================================================
       DISABLED NÚT prev/next Ở 2 BIÊN:
       - prev (data-pd-edge="first") disabled khi current === 0 (đang xem ảnh đầu)
       - next (data-pd-edge="last")  disabled khi current >= n-1 (đang xem ảnh cuối)
       - Áp cho CẢ 4 nút: 2 nút overlay trên ảnh to + 2 nút nhỏ cạnh hàng thumbs.
       - Gọi ngay khi module chạy + sau MỌI lần goTo() để trạng thái luôn đúng;
         vì disabled nên trình duyệt tự không phát click -> không cần chặn thêm.
       ================================================================= */
    const navBtns = qa('[data-pd-nav]');
    function updateNavState() {
        const n = thumbs.length;
        navBtns.forEach(btn => {
            const edge = btn.dataset.pdEdge; // 'first' | 'last' | undefined
            if (edge === 'first') btn.disabled = current <= 0;
            else if (edge === 'last') btn.disabled = current >= n - 1;
        });
    }

    /* =================================================================
       (CHỐNG GIẬT + SLIDE THEO HƯỚNG): hiệu ứng chuyển ảnh KHÔNG chạy
       trên #pdStageImg nữa (đó chính là thủ phạm "giật đùng đùng": animation
       bắt đầu ngay khi gán src -> ảnh chưa tải, khung nhảy trắng). Thay vào
       đó dùng 2 lớp phủ .pd-stage__fx do Blade render sẵn khi có >1 ảnh:
       - fxBack : đứng sau, chứa ảnh CŨ — hiện ra ngay tại chỗ rồi mờ dần
                  => khung không bao giờ trống nền trong lúc chờ ảnh mới.
       - fxFront: đứng trước, chứa ảnh MỚI — JS CHỜ img.onload (hoặc complete)
                  rồi mới gắn class is-animating + pd-in-left/pd-in-right để
                  slide vào đúng hướng chuyển (mũi tên trái/vuốt phải = từ mép
                  trái; mũi tên phải/vuốt trái = từ mép phải).
       - Fallback: thiếu layer (SP chỉ có 1 ảnh / CSS-Blade lệch bản) -> đổi
         src trực tiếp như cũ, không animation.
       ================================================================= */
    const FX_MS = 400; // khớp duration keyframes pdImgOut / pdImgInFrom* trong CSS
    const fxBack = q('[data-pd-fx="back"]');
    const fxFront = q('[data-pd-fx="front"]');
    let fxTimer = null;

    /* Gỡ toàn bộ class/ảnh của 2 lớp phủ về trạng thái nghỉ */
    function fxReset() {
        if (!fxBack || !fxFront) return;
        fxFront.classList.remove('is-animating', 'pd-in-left', 'pd-in-right');
        fxBack.classList.remove('is-animating');
        fxBack.replaceChildren();
        fxFront.replaceChildren();
    }

    /* Tạo thẻ <img> trang trí (không SEO, alt rỗng) cho một lớp phủ */
    function makeFxImg(src) {
        const im = document.createElement('img');
        im.src = src;
        im.alt = '';
        im.draggable = false;
        im.setAttribute('unselectable', 'on');
        return im;
    }

    /* Chạy hiệu ứng chuyển từ oldSrc -> newSrc theo dir ('next' sang phải | 'prev' sang trái) */
    function fxPlay(oldSrc, newSrc, dir) {
        if (!fxBack || !fxFront) return;
        clearTimeout(fxTimer);
        fxReset();

        /* Bước 1: ảnh CŨ xuất hiện tức thì ở lớp dưới (khỏi thấy khoảng trắng) */
        fxBack.appendChild(makeFxImg(oldSrc));
        void fxBack.offsetWidth; // reflow nhẹ để chắc chắn lớp back đã render
        fxBack.classList.add('is-animating');

        /* Bước 2: nạp ảnh MỚI vào lớp trên nhưng chưa chạy animation */
        const front = makeFxImg(newSrc);
        fxFront.appendChild(front);

        const play = () => {
            /* Ảnh đã sẵn sàng -> bật slide đúng hướng + fade mềm (CSS keyframes) */
            fxFront.classList.remove('pd-in-left', 'pd-in-right');
            fxFront.classList.add(dir === 'prev' ? 'pd-in-left' : 'pd-in-right', 'is-animating');
            /* Bước 3: hết duration -> dọn lớp phủ, ảnh thật bên dưới đã là ảnh mới */
            fxTimer = setTimeout(fxReset, FX_MS + 60);
        };

        if (front.complete && front.naturalWidth > 0) {
            play(); // cache nóng -> chạy ngay, không delay
        } else {
            front.addEventListener('load', play, { once: true });
            front.addEventListener('error', () => fxTimer = setTimeout(fxReset, FX_MS), { once: true });
        }
    }

    const goTo = (idx, dir = 'next') => {
        const n = thumbs.length;
        const i = ((idx % n) + n) % n; // wrap-around, an toàn cả số âm
        const btn = thumbs[i];
        if (!btn) return;
        const oldSrc = stage.src; // ảnh đang hiển thị — giữ lại cho lớp back
        current = i;
        stage.src = btn.dataset.full;
        stage.dataset.index = String(i);
        thumbs.forEach(b => b.setAttribute('aria-current', String(b === btn)));
        if (counter) counter.textContent = `${i + 1}/${n}`;
        /* Bật/tắt 2 nút prev/next theo vị trí ảnh hiện tại */
        updateNavState();
        /* FIX GIẬT HÌNH: hiệu ứng chuyển mượt THEO HƯỚNG chạy trên 2 lớp phủ
           .pd-stage__fx (JS chờ ảnh mới load xong mới khởi động). dir='prev'
           -> slide vào từ mép trái, dir='next' -> slide vào từ mép phải. */
        if (fxBack && fxFront && oldSrc && oldSrc !== stage.src) {
            fxPlay(oldSrc, stage.src, dir);
        } else {
            fxReset();
        }
        btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        /* Cuốn thumb vào tầm nhìn có thể đổi layout -> đo lại trạng thái nút */
        updateThumbsNav();
    };

    thumbs.forEach((btn, i) => {
        btn.dataset.index = String(i);
        /* Bấm thumb: suy ra hướng slide từ vị trí thumb so với ảnh đang xem */
        btn.addEventListener('click', () => goTo(i, i < current ? 'prev' : 'next'));
    });

    /* Nút prev/next (overlay trên ảnh to + 2 nút nhỏ cạnh hàng thumbs).
       Dùng click + stopPropagation: nếu là hệ quả của vuốt thì đã bị
       gallerySuppressClick chặn. Guard btn.disabled — khi đang ở biên
       (prev: ảnh đầu, next: ảnh cuối) trình duyệt đã không phát click;
       guard này chặn thêm cả trường hợp sự kiện tổng hợp (keyboard/JS)
       lọt tới -> hết wrap-around qua NÚT. */
    qa('[data-pd-nav]').forEach(btn => btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (btn.disabled) return; // đã ở biên -> không đổi ảnh, không wrap
        if (gallerySuppressClick) return; // vừa vuốt xong -> bỏ qua click dư
        const delta = parseInt(btn.dataset.pdNav, 10) || 0;
        /* Truyền hướng tương ứng ('prev' nếu lùi, 'next' nếu tới) để hiệu
           ứng slide vào đúng chiều mũi tên bấm */
        goTo(current + delta, delta < 0 ? 'prev' : 'next');
    }));

    /* ===== VUỐT TRÁI/PHẢI ĐỔI ẢNH — mọi màn hình (Pointer Events) =====
       Listener đặt trên khung .pd-stage (gộp touch/mouse/pen). */
    if (stageBox) {
        const SWIPE_MIN_X = 12;   // FIX: hạ ngưỡng 28 -> 12px để chuột vuốt ngắn cũng ăn
        const AXIS_RATIO = 1.4;   // trục ngang phải thắng trục dọc mức này
        let sx = 0, sy = 0, swiping = false, pid = null, captured = false;

        stageBox.addEventListener('pointerdown', e => {
            /* Desktop: chỉ theo dõi vuốt khi nhấn CHUỘT TRÁI (button===0) hoặc
               pointer cảm ứng — rê chuột với phím khác không kích hoạt vuốt/zoom */
            if (e.pointerType === 'mouse' && e.button !== 0) { swiping = false; return; }
            swiping = true;
            sx = e.clientX;
            sy = e.clientY;
            pid = e.pointerId;
            captured = false;
            /* KHÔNG capture khi bấm vào nút prev/next overlay — capture sẽ cướp
               click của button (nguyên nhân "bấm nút không đổi ảnh"). Với pointer
               chuột trên ảnh: capture giúp theo dõi cả khi con trỏ rời khỏi khung. */
            const onNavBtn = !!e.target.closest('[data-pd-nav]');
            if (!onNavBtn) {
                try { stageBox.setPointerCapture(pid); captured = true; } catch (_) { /* browser cũ: bỏ qua */ }
            }
        });

        stageBox.addEventListener('pointermove', e => {
            if (!swiping || e.pointerId !== pid) return;
            const dx = e.clientX - sx;
            const dy = e.clientY - sy;
            if (Math.abs(dx) < SWIPE_MIN_X) return;
            if (Math.abs(dx) < Math.abs(dy) * AXIS_RATIO) return; // vuốt dọc -> để trang cuộn
            swiping = false;
            setGallerySuppress(true);
            /* Vuốt trái = ảnh sau (slide vào từ mép phải), vuốt phải = ảnh trước
               (slide vào từ mép trái) — cùng chiều với tay người dùng */
            goTo(current + (dx < 0 ? 1 : -1), dx < 0 ? 'next' : 'prev');
            if (captured && pid !== null) { try { stageBox.releasePointerCapture(pid); } catch (_) { } captured = false; }
            /* Tự nhả flag sau 1 tick — click "dư" (nếu có) luôn fire ngay sau pointerup */
            setTimeout(() => { setGallerySuppress(false); }, 350);
        });

        const endSwipe = e => {
            if (!swiping) return;
            swiping = false;
            if (captured && pid !== null) { try { stageBox.releasePointerCapture(pid); } catch (_) { } captured = false; }
        };
        stageBox.addEventListener('pointerup', endSwipe);
        stageBox.addEventListener('pointercancel', endSwipe);

        /* Chặn HTML5 native image drag trong khung ảnh — native drag cắt stream
           pointermove giữa chừng khiến vuốt bằng chuột không đạt ngưỡng. */
        stageBox.addEventListener('dragstart', e => e.preventDefault());
    }

    stage.dataset.index = String(current);
    /* Đồng bộ disabled prev/next ngay lần render đầu (ảnh 1 -> prev disabled,
       ảnh cuối -> next disabled) — phòng hờ JS chạy sau khi đã có data-index */
    updateNavState();
}

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
        (mặc định của lib là ẨN toolbar trên mobile = "thiếu công cụ" trên
        mobile, phải ghi đè).
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

    /* Đọc danh sách ảnh từ Blade (nguồn duy nhất, trùng data-full hàng thumbs)
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

/* =====================================================================
   PD SHARE: hàng nút chia sẻ dưới .pd-thumbs — chỉ render khi SP đang
   có flash sale (xem components/product/gallery.blade.php).
   - [data-share-native]: gọi navigator.share (mobile); trình duyệt không
     hỗ trợ / lỗi => fallback copy link + toast (#toast contract cũ).
   - [data-copy-url]: copy link trực tiếp; clipboard API lỗi (HTTP, trình
     duyệt cũ) => fallback textarea + document.execCommand.
   - Event delegation trên document: bind 1 lần, không lo timing DOM.
   ===================================================================== */
async function copyLink(url) {
    try {
        await navigator.clipboard.writeText(url);
        toast('Đã sao chép liên kết sản phẩm');
    } catch (e) {
        // Fallback cho môi trường không có clipboard API (HTTP, trình duyệt cũ)
        const ta = document.createElement('textarea');
        ta.value = url;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        let ok = false;
        try { ok = document.execCommand('copy'); } catch (_) { ok = false; }
        document.body.removeChild(ta);
        toast(ok ? 'Đã sao chép liên kết sản phẩm' : 'Không sao chép được, hãy copy thủ công');
    }
}

document.addEventListener('click', e => {
    const nativeBtn = e.target.closest('[data-share-native]');
    if (nativeBtn) {
        e.preventDefault();
        const url = nativeBtn.dataset.shareUrl || location.href;
        const title = nativeBtn.dataset.shareTitle || document.title;
        if (navigator.share) {
            navigator.share({ title, text: '⚡ Flash Sale: ' + title, url })
                .catch(err => {
                    // AbortError = user bấm hủy -> im lặng; lỗi khác -> copy link
                    if (err && err.name !== 'AbortError') copyLink(url);
                });
        } else {
            copyLink(url);
        }
        return;
    }

    const copyBtn = e.target.closest('[data-copy-url]');
    if (copyBtn) {
        e.preventDefault();
        copyLink(copyBtn.dataset.copyUrl);
    }
});

/* Tabs ARIA */
const tabs = qa('[role="tab"]');
tabs.forEach(tab => tab.addEventListener('click', () => {
    tabs.forEach(t => {
        const on = t === tab;
        t.setAttribute('aria-selected', String(on));
        t.tabIndex = on ? 0 : -1;
        const panel = q('#' + t.getAttribute('aria-controls'));
        if (panel) panel.hidden = !on;
    });
}));

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