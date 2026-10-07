/**
 * APP PRODUCT — XEM THÊM / THU GỌN MÔ TẢ ([data-desc-collapse], tab #panel-desc).
 * Tách từ app-product.js cũ (dòng 737–831) — logic GIỮ NGUYÊN BẢN.
 * FIX TIỀM ẨN: bản cũ dùng biến reduceMotion nhưng KHÔNG khai báo ở đâu trong file ->
 * bấm "Thu gọn" mô tả sẽ nổ ReferenceError; module này bổ sung import reduceMotion từ @tm/core.
 */
import { q, reduceMotion } from '@tm/core';

/* =====================================================================
   NEW XEM THÊM MÔ TẢ (tab #panel-desc): description longtext rất dài ->
   gập lại + nút "Xem thêm". Nguyên tắc:
   - Blade render sẵn .is-collapsed (CSS clamp max-height) => không FOUC,
     hoạt động cả khi JS chưa/chạy lỗi.
   - initDescCollapse(): đo scrollHeight của body so với chiều cao clamp
     (mask-height CSS). Nếu KHÔNG tràn -> gỡ .is-foldable + ẩn foot (nút
     biến mất, mô tả hiện đầy đủ). Nếu tràn -> giữ nút.
   - Panel đang [hidden] (display:none) không đo được -> ResizeObserver +
     guard khi đổi tab: chỉ đo khi offsetParent !== null.
   - Mở: gán max-height = scrollHeight px (transition mượt), sau khi xong
     đặt 'none' để responsive (ảnh/table lazy load không bị cắt).
   - Thu: gán lại px hiện tại -> reflow -> về clamp => có animation.
   - Thu gọn xong cuộn về đầu khối (behavior smooth trừ reduced motion).
   ===================================================================== */
const descBox = q('[data-desc-collapse]');
if (descBox) {
    const descBody = q('[data-desc-body]', descBox);
    const descFoot = q('[data-desc-foot]', descBox);
    const descBtn = q('[data-desc-toggle]', descBox);

    if (descBody && descFoot && descBtn) {
        const labelEl = q('.desc-more__label', descBtn);
        const LABEL_MORE = 'Xem thêm mô tả sản phẩm';
        const LABEL_LESS = 'Thu gọn mô tả';
        let measured = false;   // đã xác định nội dung đủ dài để cần nút chưa
        let animTimer = null;

        const clampPx = () => {
            const cs = getComputedStyle(descBody);
            return parseFloat(cs.maxHeight) || 320;
        };

        /* Nội dung có tràn khổ gập hay không (chênh 4px chống false-positive) */
        const isOverflowing = () => descBody.scrollHeight > clampPx() + 4;

        function measure() {
            if (!descBox.classList.contains('is-collapsed')) return; // đang mở -> khỏi đo
            if (!descBody.offsetParent) return;                      // tab ẩn -> chưa đo được
            if (!descBody.classList.contains('is-foldable')) descBody.classList.add('is-foldable');
            measured = true;
            const needToggle = isOverflowing();
            descFoot.hidden = !needToggle;
            if (!needToggle) descBody.classList.remove('is-foldable'); // hiện trọn vẹn, không mask
        }

        const openDesc = () => {
            descBox.classList.remove('is-collapsed');
            descBody.classList.add('is-foldable');
            descBody.style.maxHeight = descBody.scrollHeight + 'px';
            descBtn.setAttribute('aria-expanded', 'true');
            if (labelEl) labelEl.textContent = LABEL_LESS;
            clearTimeout(animTimer);
            /* Hết transition -> bỏ trần max-height để nội dung tự co giãn */
            animTimer = setTimeout(() => { descBody.style.maxHeight = 'none'; }, 480);
        };

        const closeDesc = () => {
            clearTimeout(animTimer);
            /* Chốt px hiện tại rồi mới gập -> trình duyệt có điểm xuất phát để animate */
            descBody.style.maxHeight = descBody.scrollHeight + 'px';
            void descBody.offsetHeight; // reflow
            descBox.classList.add('is-collapsed');
            descBtn.setAttribute('aria-expanded', 'false');
            if (labelEl) labelEl.textContent = LABEL_MORE;
            requestAnimationFrame(() => { descBody.style.maxHeight = ''; });
            /* Đưa người đọc về đầu khối mô tả, không lạc vị trí giữa trang dài */
            const top = descBox.getBoundingClientRect().top + window.scrollY - 90;
            window.scrollTo({ top: Math.max(0, top), behavior: reduceMotion ? 'auto' : 'smooth' });
        };

        descBtn.addEventListener('click', () => {
            if (descBox.classList.contains('is-collapsed')) openDesc();
            else closeDesc();
        });

        /* Đo lần đầu + đo lại khi layout thay đổi (tab hiện/ẩn, đổi khổ màn hình,
           nội dung lazy-load làm chiều cao đổi) */
        measure();
        if ('ResizeObserver' in window) {
            new ResizeObserver(() => { if (!measured) measure(); }).observe(descBody);
        } else {
            window.addEventListener('resize', () => { if (!measured) measure(); });
        }
        /* Tab Mô tả vừa được bật sau khi module chạy -> đo ngay lúc visible */
        document.addEventListener('click', (e) => {
            const tab = e.target.closest('[role="tab"]');
            if (!tab) return;
            if ((tab.getAttribute('aria-controls') || '') === 'panel-desc') {
                requestAnimationFrame(measure);
            }
        });
        window.addEventListener('load', measure);
    }
}