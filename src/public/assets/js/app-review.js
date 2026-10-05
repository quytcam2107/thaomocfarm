/**
 * APP REVIEW — khối đánh giá PDP: widget sao rateyo, gửi review AJAX,
 * lọc sao client-side, nút "Hữu ích".
 * Loader app.js chỉ nạp khi DOM có marker .rv-zone (đặt cạnh [role="tab"]).
 * KHÔNG đụng importmap cũ: khai báo @tm/reviews trong layouts/app.blade.php.
 * Giữ contract: toast() từ @tm/core, meta[name="csrf-token"].
 */
import { q, qa, toast } from '@tm/core';

const zone = q('.rv-zone');
if (zone) {
    const slug = zone.dataset.productSlug || '';

    /* ============ 1) RATEYO — widget sao chuẩn (vendor self-host) ============
       rateyo là jQuery plugin: chờ window.jQuery + $.fn.rateyo xuất hiện
       (vendor <script defer> có thể迟到 hơn module ES — y pattern lightgallery). */
    const ratingInput = q('input.rv-rating');
    const rateyoBox = q('.rv-rateyo');
    const scoreText = q('#rvScoreText');

    function initRateyo() {
        if (!window.jQuery || typeof window.jQuery.fn.rateyo !== 'function' || !rateyoBox || !ratingInput) {
            return false;
        }
        window.jQuery(rateyoBox).rateyo({
            init: 0,
            max: 5,
            starWidth: '24px',
            normalFill: '#d8d2c4',
            ratedFill: '#e7b23a',       // khớp --gold của design system
            readOnly: false,
            numberDecimals: 0,
            captionTexts: [ '', 'Tệ', 'Tạm', 'Ổn', 'Tốt', 'Xuất sắc!' ],
            onInit: function (_, val) { scoreText && (scoreText.textContent = val > 0 ? `${val} sao` : 'Chưa chọn sao'); },
            /* Contract JS dự án: set input.value bằng JS phải dispatchEvent('change') */
            onChange: function (_, val) {
                ratingInput.value = String(val);
                ratingInput.dispatchEvent(new Event('change'));
                scoreText && (scoreText.textContent = val > 0 ? `${val} sao` : 'Chưa chọn sao');
            },
        });
        return true;
    }

    /* Vendor defer có thể chạy sau module -> retry ngắn (tối đa ~3s) */
    if (!initRateyo()) {
        let tries = 0;
        const iv = setInterval(() => {
            if (initRateyo() || ++tries > 30) clearInterval(iv);
        }, 100);
    }

    /* ============ 2) MỞ/ĐÓNG FORM ============ */
    const form = q('#rvForm');
    const openBtn = q('#rvOpenForm');
    const cancelBtn = q('#rvCancel');
    const errorEl = q('#rvError');

    const setFormError = (msg) => {
        if (!errorEl) return;
        errorEl.textContent = msg || '';
        errorEl.hidden = !msg;
    };

    openBtn?.addEventListener('click', () => {
        if (!form) return;
        form.hidden = !form.hidden;
        openBtn.setAttribute('aria-expanded', String(!form.hidden));
        if (!form.hidden) form.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
    cancelBtn?.addEventListener('click', () => {
        if (!form) return;
        form.reset();
        setFormError('');
        if (ratingInput) { ratingInput.value = '0'; ratingInput.dispatchEvent(new Event('change')); }
        if (window.jQuery && rateyoBox && rateyoBox.dataset.rateyoInited === 'undefined') { /* noop */ }
        if (window.jQuery && typeof window.jQuery(rateyoBox).rateyo === 'function') {
            try { window.jQuery(rateyoBox).rateyo('setValue', 0); } catch (_) { }
        }
        form.hidden = true;
        openBtn?.setAttribute('aria-expanded', 'false');
    });

    /* ============ 3) GỬI REVIEW (AJAX + CSRF + Accept JSON) ============ */
    const csrf = q('meta[name="csrf-token"]')?.content;
    const submitBtn = q('#rvSubmit');
    let sending = false;

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (sending) return;

        const rating = parseInt(ratingInput?.value || '0', 10);
        const content = (q('#rvContent')?.value || '').trim();
        const name = (q('#rvName')?.value || '').trim();
        const email = (q('#rvEmail')?.value || '').trim();

        // Validate phía client — mirrors rules của StoreReviewRequest
        if (rating < 1) { setFormError('Vui lòng chọn số sao đánh giá'); return; }
        if (content.length < 10) { setFormError('Nhận xét cần tối thiểu 10 ký tự'); return; }
        if (!name) { setFormError('Vui lòng nhập tên hiển thị'); return; }
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setFormError('Email chưa hợp lệ (dùng để đối chiếu đơn hàng)'); return; }
        if (!csrf) { setFormError('Lỗi hệ thống: thiếu CSRF token'); return; }

        sending = true;
        submitBtn.disabled = true;
        setFormError('');

        try {
            const res = await fetch(`/san-pham/${encodeURIComponent(slug)}/danh-gia`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ rating, content, name, email }),
            });
            const data = await res.json().catch(() => ({}));

            if (res.ok && data.success) {
                toast(data.message || 'Đã gửi đánh giá');
                form.reset();
                try { window.jQuery && window.jQuery(rateyoBox).rateyo('setValue', 0); } catch (_) { }
                if (ratingInput) { ratingInput.value = '0'; ratingInput.dispatchEvent(new Event('change')); }
                form.hidden = true;
                openBtn?.setAttribute('aria-expanded', 'false');
            } else {
                setFormError(data.message || 'Không gửi được đánh giá, vui lòng thử lại');
            }
        } catch (err) {
            console.error('[Review]', err);
            setFormError('Có lỗi mạng, vui lòng thử lại');
        } finally {
            sending = false;
            submitBtn.disabled = false;
        }
    });

    /* ============ 4) LỌC SAO CLIENT-SIDE (bấm thanh phân bố) ============ */
    const list = q('#reviewList');
    const bar = q('#rvFilterBar');
    const label = q('#rvFilterLabel');

    function applyStarFilter(star) {
        if (!list) return;
        const items = qa('.review', list);
        let shown = 0;
        items.forEach(el => {
            const on = !star || el.dataset.star === String(star);
            el.style.display = on ? '' : 'none';
            if (on) shown++;
        });
        if (bar) bar.hidden = !star;
        if (label && star) label.textContent = `${star} sao (${shown})`;
        // Đổi highlight giữa các thanh
        qa('.js-rv-filter').forEach(b => b.classList.toggle('is-active', b.dataset.star === String(star)));
    }

    qa('.js-rv-filter').forEach(btn => {
        btn.addEventListener('click', () => applyStarFilter(btn.dataset.star));
    });
    q('#rvFilterClear')?.addEventListener('click', () => applyStarFilter(null));

    /* ============ 5) HỮU ÍCH — POST /danh-gia/{id}/huu-ich ============
       Delegation trên list: chống double-click bằng disabled + class is-voted. */
    list?.addEventListener('click', async (e) => {
        const btn = e.target.closest('.js-rv-helpful');
        if (!btn || btn.disabled || !csrf) return;
        const id = btn.dataset.reviewId;
        if (!id || id === '0') return;

        btn.disabled = true;
        try {
            const res = await fetch(`/danh-gia/${id}/huu-ich`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.success) {
                const c = q('.rv-helpful__count', btn);
                if (c) c.textContent = String(data.helpful_count ?? 0);
                btn.classList.add('is-voted');
                toast('Cảm ơn bạn đã bình chọn');
            } else {
                btn.disabled = false;
            }
        } catch (_) {
            btn.disabled = false;
        }
    });
}