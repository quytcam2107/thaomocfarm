/**
 * APP PRODUCT — countdown flash sale (home + PDP), gallery thumbs + vuốt ảnh,
 * nút chia sẻ, tabs ARIA, Buy Now. Loader chỉ nạp khi có một trong các marker:
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
      trong block này — đổi tên từ suppressClick để không nhầm với scope khác)
      + capture-phase click listener (chỉ chặn khi quãng di chuyển >= ngưỡng
      thật sự là vuốt). NEW: giá trị flag còn được đồng bộ sang
      window.__pdGallerySuppressClick để lightbox đọc được -> vuốt/lướt chuột
      trên ảnh to KHÔNG bao giờ mở lightbox phóng to.
   - FIX YÊU CẦU MỚI NHẤT (desktop): pointerdown chỉ theo dõi vuốt khi nhấn
     CHUỘT TRÁI (button===0); hover thuần không sinh action nào, con trỏ là
     HÌNH BÀN TAY (không icon kính lúp +, không cursor zoom-in). Chỉ CLICK
     chuột trái thật sự vào ảnh to mới mở lightbox.
   - Wrap-around: ẢNH CUỐI vuốt trái -> về ảnh ĐẦU, ẢNH ĐẦU vuốt phải -> ra
     ảnh cuối (chỉ áp dụng cho VUỐT; NÚT prev/next thì stop ở biên — xem
     updateNavState bên dưới).
   - NEW DISABLED Ở BIÊN: nút prev disabled khi đang xem ảnh đầu, nút next
     disabled khi đang xem ảnh cuối. Blade render sẵn prev disabled (trang
     luôn mở đầu ở ảnh 1) để đúng cả trước khi JS chạy; updateNavState()
     đồng bộ lại sau MỌI lần đổi ảnh (nút / thumb / vuốt).
   - Hiệu ứng chuyển ảnh MƯỢT THEO HƯỚNG (FIX GIẬT HÌNH): không chạy animation
     trên <img> thật nữa mà trên 2 lớp phủ .pd-stage__fx (Blade render khi >1
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
       FIX YÊU CẦU MỚI NHẤT: đồng bộ sang window.__pdGallerySuppressClick để
       lightbox (initPdZoom bên dưới) đọc được qua isSuppressed() — nhờ đó
       CLICK DƯ sau vuốt chuột trên ảnh to KHÔNG bao giờ mở lightbox
       ("lướt chuột là tự zoom" như bản cũ). */
    let gallerySuppressClick = false;
    window.__pdGallerySuppressClick = false;
    const setGallerySuppress = (v) => {
        gallerySuppressClick = v;
        window.__pdGallerySuppressClick = v; // chia sẻ trạng thái cho lightbox
    };

    /* =================================================================
       FIX NEW: 2 nút prev/next của HÀNG THUMBS chỉ hiện khi hàng thumbs
       THỰC SỰ tràn ngang (còn ảnh ngoài tầm nhìn -> cần vuốt).
       - Blade render sẵn 2 span [data-thumbs-nav] với attribute `hidden`
         => mặc định ẩn trên MỌI màn hình, kể cả khi JS chưa kịp chạy.
       - updateThumbsNav() đo scrollWidth > clientWidth (+2px dung sai):
         * không tràn -> ẩn 2 nút + data-cols="auto": hàng thumbs co về
           đúng khổ nội dung (không giữ 76px slot nút thừa -> khỏi lệch
           tâm so với khung ảnh to).
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
       DISABLED NÚT prev/next Ở 2 BIÊN (mới):
       - prev (data-pd-edge="first") disabled khi current === 0 (đang xem ảnh đầu)
       - next (data-pd-edge="last")  disabled khi current === n-1 (đang xem ảnh cuối)
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
       NEW (CHỐNG GIẬT + SLIDE THEO HƯỚNG): hiệu ứng chuyển ảnh KHÔNG chạy
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
        /* NEW: bật/tắt 2 nút prev/next theo vị trí ảnh hiện tại */
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
       gallerySuppressClick chặn ở capture phase bên dưới.
       NEW: guard btn.disabled — khi đang ở biên (prev: ảnh đầu, next: ảnh cuối)
       trình duyệt đã không phát click; guard này chặn thêm cả trường hợp
       sự kiện tổng hợp (keyboard/JS) lọt tới -> hết wrap-around qua NÚT. */
    qa('[data-pd-nav]').forEach(btn => btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (btn.disabled) return; // đã ở biên -> không đổi ảnh, không wrap
        if (gallerySuppressClick) return; // vừa vuốt xong -> bỏ qua click dư
        const delta = parseInt(btn.dataset.pdNav, 10) || 0;
        /* NEW: truyền hướng tương ứng ('prev' nếu lùi, 'next' nếu tới) để hiệu
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
            /* FIX YÊU CẦU MỚI NHẤT (desktop): chỉ theo dõi vuốt khi nhấn CHUỘT
               TRÁI (button===0) hoặc pointer cảm ứng — rê chuột với phím khác
               không được kích hoạt logic vuốt/zoom */
            if (e.pointerType === 'mouse' && e.button !== 0) { swiping = false; return; }
            swiping = true;
            sx = e.clientX;
            sy = e.clientY;
            pid = e.pointerId;
            captured = false;
            /* FIX #1: KHÔNG capture khi bấm vào nút prev/next overlay —
               capture sẽ cướp click của button (nguyên nhân "bấm nút không đổi ảnh").
               Với pointer chuột trên ảnh: capture giúp theo dõi cả khi con trỏ
               rời khỏi khung giữa chừng. */
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

        /* FIX #2: chặn HTML5 native image drag trong khung ảnh — native drag
           cắt stream pointermove giữa chừng khiến vuốt bằng chuột không đạt ngưỡng. */
        stageBox.addEventListener('dragstart', e => e.preventDefault());
    }

    stage.dataset.index = String(current);
    /* NEW: đồng bộ disabled prev/next ngay lần render đầu (ảnh 1 -> prev disabled,
       ảnh cuối -> next disabled) — phòng hờ JS chạy sau khi đã có data-index */
    updateNavState();
}

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

/* =====================================================================
   PHÓNG TO ẢNH (LIGHTBOX) — FIX LỖI "suppressClick is not defined":
   suppressClick là biến KHAI BÁO CỤC BỘ trong block gallery bên trên
   (`let suppressClick = false;` trong if (stage && thumbs.length)) ->
   không nhìn thấy được từ scope toàn cục. Bản vá cũ chèn code zoom ở
   NGOÀI block nên tham chiếu biến này bị ReferenceError.
   => Lightbox dùng cờ RIÊNG `zoomSuppressClick` (khai báo ngay trong
   scope initPdZoom), không phụ thuộc block gallery.

   FIX YÊU CẦU MỚI (hành vi bấm/chạm):
   - Trigger mở DUY NHẤT: [data-pd-zoom="stage"] — CLICK (desktop) hoặc
     CHẠM (mobile) vào ẢNH TO .pd-stage. Không có trigger nào khác.
   - Hàng thumbs (.pd-thumbs-wrap): bấm thumb luôn CHỈ đổi ảnh lớn như
     cũ, KHÔNG bao giờ mở lightbox (đã xóa hẳn branch data-pd-zoom="thumbs").
   - FIX YÊU CẦU MỚI NHẤT (hành vi chuột trên desktop):
     - CHỈ mở lightbox khi CLICK THẬT vào ảnh to: không đổi ảnh trong lúc rê
       chuột (không có "lướt là tự zoom"), hover chỉ đổi con trỏ thành hình
       BÀN TAY (cursor:pointer — đã xóa cursor:zoom-in và icon kính lúp +).
     - Chặn click "dư" bằng gallerySuppressClick (khai báo cục bộ trong block
       gallery, scope module — ánh xạ sang cờ zoomSuppressClick RIÊNG của
       lightbox qua window.__pdGallerySuppressClick getter, xem bên dưới).
   - Điều khiển: X / chạm nền / Esc đóng; prev-next + phím mũi tên +
     vuốt đổi ảnh; pinch 2 ngón hoặc double-tap zoom 1x→2x→4x→1x kèm kéo
     panoram khi đang zoom; scroll chuột phóng to/thu nhỏ (web).
   ===================================================================== */
const zoomOverlay = q('#pdZoomViewer');
if (zoomOverlay) initPdZoom(zoomOverlay);

function initPdZoom(overlay) {
    const stageBox = q('[data-zoom-stage]', overlay);
    const bigImg = q('[data-zoom-img]', overlay);
    const counterEl = q('[data-zoom-counter]', overlay);
    const closeBtn = q('[data-zoom-close]', overlay);
    const navPrev = q('[data-zoom-nav="-1"]', overlay);
    const navNext = q('[data-zoom-nav="1"]', overlay);

    /* Danh sách ảnh full lấy từ data-full của hàng thumbs (nguồn duy nhất) */
    const srcs = qa('.pd-thumbs button[data-full]').map(b => b.dataset.full);
    let zi = 0;               // ảnh đang xem trong lightbox
    let isOpen = false;
    /* CỜ RIÊNG của lightbox — thay cho suppressClick cục bộ của block gallery.
       FIX "lướt chuột là tự zoom": dùng GETTER đọc thẳng cờ vuốt của block
       gallery (window.__pdGallerySuppressClick do block gallery cập nhật) +
       cờ nội bộ zoomSuppressClick cho các vuốt BÊN TRONG lightbox. */
    let zoomSuppressClick = false; // true tạm thời khi vừa vuốt xong trong lightbox
    const isSuppressed = () => zoomSuppressClick || !!window.__pdGallerySuppressClick;

    /* ====== Zoom/Pan state ====== */
    let scale = 1, tx = 0, ty = 0;
    let imgW = 0, imgH = 0;   // kích thước hiển thị thực tế của bigImg
    let boxW = 0, boxH = 0;   // kích thước khung .pd-zoom__stage
    const MAX_SCALE = 4, MIN_SCALE = 1;

    function measure() {
        const rStage = stageBox.getBoundingClientRect();
        boxW = rStage.width; boxH = rStage.height;
        const rImg = bigImg.getBoundingClientRect();
        // rect đã bao gồm transform hiện tại -> chia scale để về kích thước gốc
        imgW = rImg.width / (scale || 1);
        imgH = rImg.height / (scale || 1);
    }

    function clampPan() {
        const maxX = Math.max(0, (imgW * scale - boxW) / 2);
        const maxY = Math.max(0, (imgH * scale - boxH) / 2);
        tx = Math.min(maxX, Math.max(-maxX, tx));
        ty = Math.min(maxY, Math.max(-maxY, ty));
    }

    function applyTransform() {
        clampPan();
        bigImg.style.transform = `translate(${tx}px, ${ty}px) scale(${scale})`;
        stageBox.classList.toggle('is-zoomed', scale > 1);
    }

    function resetView() {
        scale = 1; tx = 0; ty = 0;
        bigImg.style.transition = 'transform .2s ease';
        applyTransform();
        setTimeout(() => { bigImg.style.transition = ''; }, 220);
    }

    /* Phóng to quanh 1 điểm (x,y tính từ TÂM khung) — giữ đúng vùng dưới ngón/con trỏ */
    function zoomAt(newScale, x, y) {
        const ns = Math.min(MAX_SCALE, Math.max(MIN_SCALE, newScale));
        if (ns === scale) return;
        const k = ns / scale;
        tx = x - (x - tx) * k;
        ty = y - (y - ty) * k;
        scale = ns;
        applyTransform();
    }

    function show(i) {
        const n = srcs.length || 1;
        zi = ((i % n) + n) % n;
        if (srcs[zi]) bigImg.src = srcs[zi];
        if (counterEl) counterEl.textContent = `${zi + 1}/${n}`;
        resetView();
        updateZoomNav();
    }

    /* Nút prev/next lightbox stop ở 2 biên (đồng bộ hành vi với gallery ngoài) */
    function updateZoomNav() {
        const n = srcs.length;
        if (navPrev) navPrev.disabled = zi <= 0;
        if (navNext) navNext.disabled = zi >= n - 1;
    }

    /* ====== Mở / đóng ====== */
    function open(i) {
        isOpen = true;
        overlay.hidden = false;
        document.body.classList.add('has-zoom');
        show(i);
        requestAnimationFrame(measure);
        closeBtn && closeBtn.focus({ preventScroll: true });
    }

    function close() {
        isOpen = false;
        overlay.hidden = true;
        document.body.classList.remove('has-zoom');
        resetView();
        /* Xóa src để browser KHÔNG preload bản full từ trang list — mỗi lần mở
           sẽ request đúng 1 ảnh full đang chọn (tiết kiệm băng thông mobile) */
        bigImg.removeAttribute('src');
    }

    /* ====== Trigger mở (delegation — bind 1 lần, không lo timing DOM) ======
       FIX YÊU CẦU MỚI NHẤT (desktop): CHỈ CLICK TRÁI THẬT vào ảnh to mới mở
       lightbox. Các cơ chế chặn "tự zoom khi lướt chuột":
       1) e.button !== 0 -> bỏ qua (chuột phải/giữa).
       2) isSuppressed() đọc gallerySuppressClick của block gallery (qua
          window.__pdGallerySuppressClick) -> click dư phát ra sau một cú VUỐT
          ngang trên ảnh to bị bỏ qua, chỉ đổi ảnh chứ không phóng to.
       3) Ngưỡng vuốt 12px: rê chuột dưới 12px không tính là vuốt; trường hợp
          này trình duyệt sinh click nhưng người dùng thực tế đã nhấc/đặt chuột
          — hành vi chuẩn của mọi slider ảnh. Di chuột thuần (hover, không nhấn)
          KHÔNG sinh click -> không bao giờ tự zoom.
       Hàng thumbs .pd-thumbs-wrap: bấm thumb luôn CHỈ đổi ảnh lớn như cũ
       (branch data-pd-zoom="thumbs" đã xóa hẳn). */
    document.addEventListener('click', e => {
        if (isOpen || overlay.contains(e.target)) return;
        if (e.button !== undefined && e.button !== 0) return; // chỉ chuột trái
        /* Vừa vuốt xong (ngoài gallery HOẶC trong lightbox) thì click "dư"
           phát ra ngay sau pointerup -> bỏ qua, KHÔNG mở/đóng gì cả */
        if (isSuppressed()) return;
        /* KHÔNG mở lightbox khi pointer rơi vào nút prev/next hoặc counter
           overlay trên ảnh to — 2 element này pointer-events:auto và nằm
           TRONG [data-pd-zoom="stage"] nên closest() vẫn thấy stage; phải
           chặn tường minh ở đây. */
        if (e.target.closest('[data-pd-nav], .pd-counter')) return;
        const stageTrigger = e.target.closest('[data-pd-zoom="stage"]');
        if (stageTrigger) {
            const idx = parseInt((q('#pdStageImg') || {}).dataset?.index || '0', 10) || 0;
            open(idx);
        }
    });

    /* Chặn click dư sau khi VUỐT TRONG LIGHTBOX bằng capture-phase listener */
    overlay.addEventListener('click', e => {
        if (zoomSuppressClick) { e.stopPropagation(); e.preventDefault(); }
    }, true);

    closeBtn && closeBtn.addEventListener('click', close);

    /* Chấm nền (click đúng stage trống quanh ảnh) -> đóng */
    overlay.addEventListener('click', e => {
        if (e.target === overlay) close();
    });

    /* Keyboard: Esc đóng, mũi tên đổi ảnh, +/- zoom */
    document.addEventListener('keydown', e => {
        if (!isOpen) return;
        if (e.key === 'Escape') { close(); return; }
        if (e.key === 'ArrowLeft' && !navPrev?.disabled) show(zi - 1);
        if (e.key === 'ArrowRight' && !navNext?.disabled) show(zi + 1);
        if (e.key === '+' || e.key === '=') { measure(); zoomAt(scale * 1.4, 0, 0); }
        if (e.key === '-') { measure(); zoomAt(scale / 1.4, 0, 0); }
    });

    /* Scroll chuột = phóng to/thu nhỏ tại vị trí con trỏ (chỉ web).
       FIX YÊU CẦU MỚI NHẤT: thêm ngưỡng 6px — rê/di chuyển chuột thông thường
       trên ảnh to KHÔNG được tự zoom; chỉ khi THỰC SỰ lăn con lăn (deltaY>=6)
       mới đổi scale. */
    stageBox && stageBox.addEventListener('wheel', e => {
        if (!isOpen) return;
        if (Math.abs(e.deltaY) < 6 && Math.abs(e.deltaX) < 6) return; // không phải lăn chuột thật -> bỏ qua
        e.preventDefault();
        measure();
        const r = stageBox.getBoundingClientRect();
        const px = e.clientX - r.left - boxW / 2;
        const py = e.clientY - r.top - boxH / 2;
        zoomAt(scale * (e.deltaY < 0 ? 1.25 : 0.8), px, py);
    }, { passive: false });

    navPrev && navPrev.addEventListener('click', () => show(zi - 1));
    navNext && navNext.addEventListener('click', () => show(zi + 1));

    /* ====== Cảm ứng trong lightbox: pinch / double-tap / pan / swipe ====== */
    if (stageBox) {
        const pts = new Map();          // pointerId -> {x, y}
        let pinchStartDist = 0, pinchStartScale = 1;
        let panStart = null;            // {x, y, tx, ty}
        let swipe = null;               // {x, y, moved}
        let lastTap = 0;

        const dist2 = (a, b) => Math.hypot(a.x - b.x, a.y - b.y);
        const mid2 = (a, b) => ({ x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 });

        stageBox.addEventListener('pointerdown', e => {
            pts.set(e.pointerId, { x: e.clientX, y: e.clientY });
            try { stageBox.setPointerCapture(e.pointerId); } catch (_) { }
            measure();
            if (pts.size === 2) {
                const [p1, p2] = [...pts.values()];
                pinchStartDist = dist2(p1, p2);
                pinchStartScale = scale;
                swipe = null; panStart = null;
            } else if (pts.size === 1) {
                if (scale > 1) {
                    panStart = { x: e.clientX, y: e.clientY, tx, ty };
                } else {
                    swipe = { x: e.clientX, y: e.clientY, moved: false };
                }
            }
        });

        stageBox.addEventListener('pointermove', e => {
            if (!pts.has(e.pointerId)) return;
            pts.set(e.pointerId, { x: e.clientX, y: e.clientY });

            if (pts.size === 2 && pinchStartDist > 0) {
                /* PINCH ZOOM quanh điểm giữa 2 ngón */
                const [p1, p2] = [...pts.values()];
                const m = mid2(p1, p2);
                const r = stageBox.getBoundingClientRect();
                zoomAt(pinchStartScale * (dist2(p1, p2) / pinchStartDist),
                    m.x - r.left - boxW / 2, m.y - r.top - boxH / 2);
                return;
            }
            if (panStart && scale > 1) {
                /* Kéo panoram khi đang zoom */
                tx = panStart.tx + (e.clientX - panStart.x);
                ty = panStart.ty + (e.clientY - panStart.y);
                applyTransform();
                return;
            }
            if (swipe) {
                const dx = e.clientX - swipe.x, dy = e.clientY - swipe.y;
                if (Math.abs(dx) > 8 || Math.abs(dy) > 8) swipe.moved = true;
            }
        });

        const endPt = e => {
            const wasTwo = pts.size === 2;
            pts.delete(e.pointerId);
            if (wasTwo) { pinchStartDist = 0; }

            if (!isOpen) return;

            /* Double-tap (1 ngón) -> zoom bậc thang 1x→2x→4x→1x tại điểm chạm */
            if (e.pointerType === 'touch' && pts.size === 0 && !wasTwo && swipe && !swipe.moved) {
                const now = Date.now();
                if (now - lastTap < 320) {
                    const r = stageBox.getBoundingClientRect();
                    const px = e.clientX - r.left - boxW / 2;
                    const py = e.clientY - r.top - boxH / 2;
                    const next = scale === 1 ? 2 : (scale <= 2 ? 4 : 1);
                    if (next === 1) resetView();
                    else zoomAt(next, px, py);
                    lastTap = 0;
                } else {
                    lastTap = now;
                }
            }

            /* Vuốt ngang khi KHÔNG zoom -> đổi ảnh trong lightbox */
            if (swipe && swipe.moved && scale === 1) {
                const dx = e.clientX - swipe.x;
                if (Math.abs(dx) >= 40 && Math.abs(dx) > Math.abs(e.clientY - swipe.y) * 1.2) {
                    zoomSuppressClick = true;
                    setTimeout(() => { zoomSuppressClick = false; }, 350);
                    if (dx < 0 && zi < srcs.length - 1) show(zi + 1);
                    else if (dx > 0 && zi > 0) show(zi - 1);
                }
            }
            swipe = null; panStart = null;
        };
        stageBox.addEventListener('pointerup', endPt);
        stageBox.addEventListener('pointercancel', endPt);
        stageBox.addEventListener('dragstart', e => e.preventDefault());
    }

    /* Đổi orientation / resize -> đo lại để clamp panoram đúng */
    window.addEventListener('resize', () => { if (isOpen) { measure(); applyTransform(); } });
}