/**
 * CHECKOUT — cascading select Tỉnh/Thành → Xã/Phường (địa chính 2 cấp, bỏ quận/huyện).
 * Loader app.js CHỈ nạp module này khi trang có cả #province và #ward.
 *
 * Nguồn dữ liệu: GET data-source (route web.locations.wards) ?province_code={code}
 *   → { wards: [{code, name}] } (đã cache nhóm `content` phía server).
 *
 * Quy ước:
 *  - #ward luôn disabled khi chưa chọn tỉnh (không submit value rỗng).
 *  - Khi back()->withInput() do validate fail, server đã pre-render sẵn option
 *    phường của tỉnh cũ + selected → options.length > 1 → KHÔNG fetch lại lần đầu.
 *  - Đổi tỉnh → fetch lại và reset selected.
 */
import { q, toast } from '@tm/core';

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
