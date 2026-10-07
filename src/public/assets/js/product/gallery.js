/**
 * APP PRODUCT — GALLERY PDP: thumb click + nút prev/next + VUỐT ĐỔI ẢNH
 * + hiệu ứng slide mượt theo hướng trên 2 lớp phủ .pd-stage__fx.
 * Tách từ app-product.js cũ (dòng 54–333) — logic GIỮ NGUYÊN BẢN, chỉ thêm dòng import.
 * Ghi window.__pdGallerySuppressClick để lightgallery.js chặn "click dư sau vuốt".
 */
import { q, qa } from '@tm/core';

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
       TRÀN NGANG (còn ảnh ngoài tầm nhìn -> cần vuốt).
       - Blade render sẵn 2 span [data-thumbs-nav] với attribute `hidden`
         => mặc định ẩn trên MỌI màn hình, kể cả khi JS chưa/chạy lỗi.
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