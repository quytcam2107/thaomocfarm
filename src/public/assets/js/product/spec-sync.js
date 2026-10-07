/**
 * APP PRODUCT — ĐỒNG BỘ BẢNG "QUY CÁCH & GIÁ BÁN" ([data-spec-table]) theo khối lượng đang chọn.
 * Tách từ app-product.js cũ (dòng 552–651) — logic GIỮ NGUYÊN BẢN, chỉ thêm dòng import.
 */
import { q, qa, money } from '@tm/core';

/* =====================================================================
   NEW FIX PDP — ĐỒNG BỘ BẢNG "QUY CÁCH & GIÁ BÁN" (tab Chi tiết sản phẩm)
   VỚI KHỐI LƯỢNG ĐANG CHỌN:
   - Bug: nhãn .spec-tag "Đang chọn" chỉ được Blade render MỘT LẦN theo
     $v['selected'] = is_default (ProductDetailFetcher set cứng từ DB).
     Khi khách bấm pill khối lượng khác, chỉ giá/qty đổi (inline script),
     bảng trong #panel-spec vẫn giữ tag ở dòng default -> "không chọn đúng".
   - Fix: lắng nghe change trên các radio quy cách (name="variant_id" —
     fallback name="variant" theo convention info.blade.php), tìm <tr> có
     data-variant-id trùng value radio rồi:
       1) Di chuyển .spec-tag sang cell quy cách của dòng đó (xóa dòng cũ);
       2) Bật class .is-selected cho dòng đang chọn (CSS 07d highlight);
       3) Vẽ lại Ô "Giá bán" của dòng đang chọn: <b>giá bán</b> + (nếu có)
          <s>giá niêm yết gạch ngang</s>.
   - YÊU CẦU MỚI (PDP): bảng ĐÃ BỎ CỘT "Giá niêm yết"; giá niêm yết (lấy từ
     data-old-price = compare_price / flash_price sau FlashSalePriceService
     applyToDetailArray — tức đã gồm giá deal, KHÔNG tự tính lại %) giờ nằm
     GẠCH NGANG NGAY TRONG CỘT "Giá bán" của dòng đang chọn, giống khối
     .pd-price của x-product.info. Dòng không được chọn chỉ hiện giá bán.
   - Chạy cả lần đầu (syncSpecTable(null)) để DOM khớp radio checked thực tế
     (phòng cache cũ / variant default bị ẩn khỏi pills).
   ===================================================================== */
const specTable = q('[data-spec-table]');
if (specTable) {
    const TAG_TEXT = 'Đang chọn';
    const fmtVND = n => money(Math.round(n)); // money() của @tm/core: 1.234.567₫

    /** Vẽ ô "Giá bán" của 1 dòng: <b>giá bán</b> + <s>niêm yết gạch ngang</s> (cột cũ gộp vào đây) */
    function renderRowPrice(row, isSelected) {
        const priceCell = row.cells[1];
        if (!priceCell) return;
        const price = parseInt(row.dataset.price, 10) || 0;
        const oldPrice = parseInt(row.dataset.oldPrice, 10) || 0;

        const strong = document.createElement('b');
        strong.textContent = fmtVND(price);
        priceCell.replaceChildren(strong);

        /* Chỉ gạch niêm yết khi dòng ĐANG CHỌN và niêm yết CAO HƠN giá bán
           (đúng điều kiện cột "Giá niêm yết" cũ) */
        if (isSelected && oldPrice > price) {
            const strike = document.createElement('s');
            strike.textContent = fmtVND(oldPrice);
            priceCell.append(' ', strike);
        }
    }

    /** Đồng bộ 1 dòng đang chọn; idVar = value radio checked (null = dòng đang .is-selected) */
    function syncSpecTable(idVar) {
        const rows = qa('tr[data-variant-id]', specTable);
        if (!rows.length) return;

        let target = null;
        if (idVar !== null && idVar !== undefined && idVar !== '') {
            target = rows.find(r => r.dataset.variantId === String(idVar));
        }
        if (!target) {
            // Không tìm thấy theo id -> giữ dòng đang được Blade đánh dấu selected
            target = rows.find(r => r.classList.contains('is-selected')) || rows[0];
        }
        if (!target) return;

        rows.forEach(row => {
            const on = row === target;
            row.classList.toggle('is-selected', on);

            /* 1) Nhãn "Đang chọn": chỉ dòng đang chọn có .spec-tag */
            const labelCell = row.querySelector('td');
            if (!labelCell) return;
            const existingTag = labelCell.querySelector('.spec-tag');
            if (on) {
                if (!existingTag) {
                    labelCell.append(document.createTextNode(' '));
                    const tag = document.createElement('span');
                    tag.className = 'spec-tag';
                    tag.textContent = TAG_TEXT;
                    labelCell.append(tag);
                }
            } else if (existingTag) {
                existingTag.remove();
            }

            /* 2) Ô Giá bán: dòng đang chọn kèm giá niêm yết gạch ngang
                  (thay cho cột "Giá niêm yết" đã bỏ) — đồng bộ đúng .pd-price
                  của x-product.info sau khi đổi khối lượng. */
            renderRowPrice(row, on);
        });
    }

    /* Bind theo CẢ 2 tên radio để an toàn với fallback name="variant" */
    qa('input[name="variant_id"], input[name="variant"]').forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.checked) syncSpecTable(radio.value);
        });
    });

    /* Lần đầu: bám theo radio đang checked thực tế trên DOM */
    const initialChecked = q('input[name="variant_id"]:checked') || q('input[name="variant"]:checked');
    syncSpecTable(initialChecked ? initialChecked.value : null);
}