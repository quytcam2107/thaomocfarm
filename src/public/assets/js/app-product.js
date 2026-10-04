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
   3) Chặn click "dư" sau vuốt bằng flag suppressClick + capture-phase click
      listener (chỉ chặn khi quãng di chuyển >= ngưỡng thật sự là vuốt).
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
    /* Flag chặn click "dư" phát ra sau một cú vuốt thật (desktop + mobile) */
    let suppressClick = false;

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
       suppressClick chặn ở capture phase bên dưới.
       NEW: guard btn.disabled — khi đang ở biên (prev: ảnh đầu, next: ảnh cuối)
       trình duyệt đã không phát click; guard này chặn thêm cả trường hợp
       sự kiện tổng hợp (keyboard/JS) lọt tới -> hết wrap-around qua NÚT. */
    qa('[data-pd-nav]').forEach(btn => btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        if (btn.disabled) return; // đã ở biên -> không đổi ảnh, không wrap
        if (suppressClick) return; // vừa vuốt xong -> bỏ qua click dư
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
            suppressClick = true;
            /* Vuốt trái = ảnh sau (slide vào từ mép phải), vuốt phải = ảnh trước
               (slide vào từ mép trái) — cùng chiều với tay người dùng */
            goTo(current + (dx < 0 ? 1 : -1), dx < 0 ? 'next' : 'prev');
            if (captured && pid !== null) { try { stageBox.releasePointerCapture(pid); } catch (_) { } captured = false; }
            /* Tự nhả flag sau 1 tick — click "dư" (nếu có) luôn fire ngay sau pointerup */
            setTimeout(() => { suppressClick = false; }, 350);
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