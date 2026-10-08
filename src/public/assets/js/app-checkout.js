/**
 * CHECKOUT — 2 luồng:
 * 1) Cascading select Tỉnh/Thành → Xã/Phường (địa chính 2 cấp, bỏ quận/huyện).
 *    Loader app.js CHỈ nạp module này khi trang có cả #province và #ward.
 *    Nguồn dữ liệu: GET data-source (route web.locations.wards) ?province_code={code}
 *      → { wards: [{code, name}] } (đã cache nhóm `content` phía server).
 *    Quy ước:
 *     - #ward luôn disabled khi chưa chọn tỉnh (không submit value rỗng).
 *     - Khi back()->withInput() do validate fail, server đã pre-render sẵn option
 *       phường của tỉnh cũ + selected → options.length > 1 → KHÔNG fetch lại lần đầu.
 *     - Đổi tỉnh → fetch lại và reset selected.
 * 2) Co-bar sticky đáy trang (mobile < 1024px): nút "✅ Đặt hàng" luôn thấy khi cuộn.
 *    - Button #coBarSubmit nằm NGOÀI <form id="checkoutForm"> → gán thuộc tính form
 *      trong HTML; với trình duyệt không hỗ trợ, bind click fallback requestSubmit().
 *    - Thêm class body.has-co-bar để CSS chừa padding-bottom khỏi che nội dung.
 *    - VALIDATE 2 TẦNG khi bấm CTA: client check trước bằng reportValidity() — fail
 *      thì chặn hẳn, KHÔNG gửi request sang BE; pass thì submit thường và BE vẫn
 *      chạy CheckoutRequest validate lại lần 2 (nguồn chuẩn cuối).
 */
import { q, toast } from '@tm/core';

/* ================= CO-BAR STICKY (Đặt hàng đáy trang) ================= */
const coBar = q('#coBar');
if (coBar) {
    // Đệm thân trang để thanh sticky không che khối ghi chú cuối form
    document.body.classList.add('has-co-bar');

    const coForm = q('#checkoutForm');
    const coSubmit = q('#coBarSubmit', coBar);

    if (coSubmit && coForm) {
        /* ===== VALIDATE 2 TẦNG khi bấm CTA #coBarSubmit:
           1) CLIENT: form mang novalidate nên trình duyệt KHÔNG tự chặn submit →
              gọi coForm.reportValidity() thủ công. Trả false ⇒ browser bật tooltip
              ngay tại field lỗi đầu tiên + preventDefault DỪNG hẳn, không gửi
              request sang BE + toast hướng dẫn.
           2) SERVER: client pass thì submit thường, CheckoutRequest vẫn validate
              lại lần 2 (nguồn chuẩn cuối: exists provinces/wards, cross-check
              phường thuộc tỉnh, whitelist shipping/payment…).

           Handler click bind BUBBLE và đặt TRƯỚC handler fallback bên dưới ⇒ khi
           client fail, stopImmediatePropagation() khiến fallback không kịp
           requestSubmit(); khi client pass, fallback vẫn chạy đúng vai trò của nó.
           Chặn cả Enter-to-submit bằng listener 'keydown' trên form (Enter không
           đi qua click của CTA). ===== */
        const coClientGate = () => {
            const ok = coForm.reportValidity();
            if (!ok) toast('Vui lòng kiểm tra lại các trường được đánh dấu.');
            return ok;
        };

        // Chặn Enter-to-submit (submit native do phím) — client fail thì cancel
        coForm.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !eShiftKeyGuard(e)) {
                if (!coClientGate()) {
                    e.preventDefault();
                }
            }
        });

        coSubmit.addEventListener('click', (e) => {
            if (!coClientGate()) {
                e.preventDefault(); // client fail → không gửi request sang BE
                e.stopImmediatePropagation();
            }
        });

        // Fallback trình duyệt cũ không hỗ trợ attribute form="..." trên button ngoài form
        coSubmit.addEventListener('click', (e) => {
            if (coSubmit.form !== coForm) {
                e.preventDefault();
                if (typeof coForm.requestSubmit === 'function') {
                    coForm.requestSubmit(coSubmit);
                } else {
                    coForm.submit();
                }
            }
        });

        // Chặn double-submit sau khi bấm đặt hàng (tránh tạo 2 đơn)
        coForm.addEventListener('submit', () => {
            coSubmit.disabled = true;
        });

        /* Server validate fail quay lại (back()->withInput(), <small class="error">
           render trong field) → mở khóa sẵn CTA; đồng thời tái kích hoạt ngay khi
           người dùng bắt đầu sửa bất kỳ trường nào (không cần reload trang). */
        if (q('small.error', coForm)) {
            coSubmit.disabled = false;
        }
        ['input', 'change'].forEach((evt) => {
            coForm.addEventListener(evt, () => {
                if (coSubmit.disabled) coSubmit.disabled = false;
            }, true);
        });
    }
}

/* ================= CASCADING TỈNH → XÃ/PHƯỜNG ================= */
const province = q('#province');
const ward = q('#ward');

if (province && ward) {
    const source = ward.dataset.source || '';
    const placeholderEl = ward.querySelector('option[value=""]');
    const defaultLabel = placeholderEl ? placeholderEl.textContent.trim() : '— Chọn xã / phường —';

    // Dựng lại select phường với duy nhất 1 option placeholder (nhãn tùy chọn).
    const resetWard = (label = defaultLabel) => {
        ward.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = label;
        ward.appendChild(opt);
    };

    // Đổ danh sách phường; selectedCode dùng để chọn lại (old input / lần đầu).
    const fillWards = (wards, selectedCode = '') => {
        resetWard();
        wards.forEach(w => {
            const opt = document.createElement('option');
            opt.value = w.code;
            opt.textContent = w.name;
            if (String(selectedCode) !== '' && String(selectedCode) === String(w.code)) {
                opt.selected = true;
            }
            ward.appendChild(opt);
        });
        ward.disabled = false;
    };

    const loadWards = async (provinceCode, selectedCode = '') => {
        if (!provinceCode) {
            resetWard();
            ward.disabled = true;
            return;
        }

        ward.disabled = true;
        resetWard('Đang tải…');

        try {
            const res = await fetch(`${source}?province_code=${encodeURIComponent(provinceCode)}`, {
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const data = await res.json();
            const list = Array.isArray(data.wards) ? data.wards : [];

            if (list.length === 0) {
                resetWard('— Không có xã/phường —');
                ward.disabled = true;
                return;
            }

            fillWards(list, selectedCode);
        } catch (err) {
            resetWard();
            ward.disabled = false;
            toast('Không tải được danh sách xã/phường. Vui lòng thử lại.');
        }
    };

    // Đổi tỉnh → nạp lại phường (bỏ selected cũ).
    province.addEventListener('change', () => loadWards(province.value));

    // Khởi tạo theo trạng thái hiện tại của tỉnh:
    //  - Tỉnh đã chọn + select phường rỗng (chỉ placeholder) → fetch (kèm old ward_code).
    //  - Tỉnh đã chọn + phường đã pre-render (options > 1) → giữ nguyên, chỉ bỏ disabled.
    //  - Chưa chọn tỉnh → khóa select phường.
    if (province.value) {
        if (ward.options.length <= 1) {
            loadWards(province.value, ward.dataset.selected || '');
        } else {
            ward.disabled = false;
        }
    } else {
        ward.disabled = true;
    }
}