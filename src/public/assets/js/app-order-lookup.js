/**
 * ORDER LOOKUP — chỉ chạy trên trang /tra-cuu-don-hang (marker #orderLookupForm).
 * Vai trò MỎ: lọc ký tự rác khi gõ SĐT + toast nhắc khi nộp form trống.
 * Trang vẫn hoạt động đầy đủ khi tắt JS (form GET thuần server-render).
 */
import { q, toast } from '@tm/core';

const form = q('#orderLookupForm');
if (!form) {
    // Defensive: module chỉ được import khi form tồn tại (xem app.js)
} else {
    const input = q('#ol-phone', form);

    // Chỉ giữ chữ số, khoảng trắng, +, -, . — chống dán ký tự lạ
    input?.addEventListener('input', () => {
        const cleaned = input.value.replace(/[^\d\s+.\-]/g, '');
        if (cleaned !== input.value) {
            input.value = cleaned;
            // Quy ước project: set input.value bằng JS phải dispatch change
            input.dispatchEvent(new Event('change'));
        }
    });

    form.addEventListener('submit', (e) => {
        const digits = (input?.value || '').replace(/\D/g, '');
        if (digits.length < 9 || digits.length > 11) {
            e.preventDefault();
            toast('Nhập SĐT 9–11 chữ số để tra cứu nhé!');
            input?.focus();
        }
    });

    // Tự focus ô nhập trên desktop (mobile không focus để tránh popup bàn phím)
    if (window.matchMedia('(min-width: 768px)').matches) {
        input?.focus();
    }
}