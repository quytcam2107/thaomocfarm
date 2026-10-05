/**
 * APP REVIEW — khối đánh giá PDP: widget sao rateyo, gửi review AJAX (multipart
 * có ảnh), lọc sao client-side, nút "Hữu ích", phản hồi admin (staff).
 * Loader app.js chỉ nạp khi DOM có marker .rv-zone (đặt cạnh [role="tab"]).
 * KHÔNG đụng importmap cũ: khai báo @tm/reviews trong layouts/app.blade.php.
 * Giữ contract: toast() từ @tm/core, meta[name="csrf-token"].
 */
import { q, qa, toast } from '@tm/core';

const zone = q('.rv-zone');
if (zone) {
    const slug = zone.dataset.productSlug || '';

    /* ============ 1) RATEYO — widget sao chuẩn (vendor self-host 2.3.4) ============
       FIX LỖI "không chọn được sao / không hiện sao": code cũ gọi API không tồn tại
       của rateyo 2.3.4 (init/max/numberDecimals/captionTexts/setValue). Bản 2.3.4
       chỉ nhận options: rating/numStars/maxValue/starWidth/normalFill/ratedFill/
       readOnly/precision/fullStar/halfStar/spacing/rtl/multiColor/onInit/onChange/onSet
       và method "rating" để set value (đã đối chiếu jquery.rateyo.min.js trong vendor).
       rateyo là jQuery plugin: chờ window.jQuery + $.fn.rateYo xuất hiện
       (vendor <script defer> có thể迟到 hơn module ES — y pattern lightgallery). */
    const ratingInput = q('input.rv-rating');
    const rateyoBox = q('.rv-rateyo');
    const scoreText = q('#rvScoreText');
    const STAR_LABELS = ['', 'Tệ', 'Tạm', 'Ổn', 'Tốt', 'Xuất sắc!'];

    /* FIX BUG "The rating field must be an integer": rateyo có thể nhả giá trị
       dạng chuỗi ("3.0000") hoặc NaN -> luôn ép về số nguyên 0..5 trước khi ghi
       vào input hidden, đảm bảo payload rating luôn là số nguyên hợp lệ. */
    function toIntStar(val) {
        const n = Math.round(parseFloat(val));
        return Number.isFinite(n) && n >= 1 && n <= 5 ? n : 0;
    }

    /* Radio fallback (.rv-rating-radio, KHÔNG có name để tránh trùng key multipart)
       lưu số sao khách bấm; input hidden .rv-rating[name="rating"] mới là nguồn
       của FormData — JS luôn ghi giá trị nguyên vào đó. */
    function syncRadios(star) {
        qa('input.rv-rating-radio').forEach(r => { r.checked = Number(r.value) === star; });
    }

    function checkedRadioRating() {
        const r = qa('input.rv-rating-radio').find(x => x.checked);
        return toIntStar(r?.value);
    }

    function setRating(val) {
        const star = toIntStar(val);
        if (ratingInput) {
            ratingInput.value = String(star);
            ratingInput.dispatchEvent(new Event('change'));
        }
        // Đồng bộ bộ radio fallback (name="rating") — nguồn dữ liệu thật của FormData
        syncRadios(star);
        if (scoreText) scoreText.textContent = star > 0 ? `${star} sao — ${STAR_LABELS[star] || ''}`.trim() : 'Chưa chọn sao';
    }

    function initRateyo() {
        // 2.3.4 đăng ký $.fn.rateYo (hoa 'Y') — kiểm tra cả hai để an toàn
        const jq = window.jQuery;
        if (!jq || !(typeof jq.fn.rateYo === 'function' || typeof jq.fn.rateyo === 'function') || !rateyoBox || !ratingInput) {
            return false;
        }
        const fn = typeof jq.fn.rateYo === 'function' ? 'rateYo' : 'rateyo';
        jq(rateyoBox)[fn]({
            rating: 0,                 // option thật của 2.3.4 (thay cho "init")
            numStars: 5,
            maxValue: 5,
            precision: 0,              // chỉ số nguyên (thay cho "numberDecimals")
            fullStar: true,            // từng sao rời, không nửa sao
            starWidth: '24px',
            normalFill: '#d8d2c4',
            ratedFill: '#e7b23a',      // khớp --gold của design system
            readOnly: false,
            onInit: function (_, val) { if (toIntStar(val) > 0) setRating(val); },
            /* Contract JS dự án: set input.value bằng JS phải dispatchEvent('change') */
            onSet: function (_, val) { setRating(val); },
            onChange: function (_, val) { setRating(val); },
        });
        return true;
    }

    // Khách bấm trực tiếp radio fallback khi widget chưa kịp init -> vẫn hiển thị số sao
    qa('input.rv-rating-radio').forEach(r => {
        r.addEventListener('change', () => { if (r.checked) setRating(Number(r.value)); });
    });

    /* Vendor defer có thể chạy sau module -> retry ngắn (tối đa ~3s) */
    if (!initRateyo()) {
        let tries = 0;
        const iv = setInterval(() => {
            if (initRateyo() || ++tries > 30) clearInterval(iv);
        }, 100);
    }

    // Set 0 sao bằng API thật của 2.3.4: method "rating"
    function resetStars() {
        const jq = window.jQuery;
        if (jq && rateyoBox) {
            const fn = typeof jq.fn.rateYo === 'function' ? 'rateYo' : 'rateyo';
            try { jq(rateyoBox)[fn]('rating', 0); } catch (_) { /* chưa init thì bỏ qua */ }
        }
        setRating(0);
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
        resetStars();
        clearPreviews();          // NEW: hủy ảnh preview + objectURL
        form.hidden = true;
        openBtn?.setAttribute('aria-expanded', 'false');
    });

    /* ============ 2b) ẢNH REVIEW — chọn ≤5, preview, xóa từng ảnh ============
       Chiến lược: giữ 1 FileList ảo trong biến `picked`; khi submit dựng FormData
       từ picked (input file gốc chỉ là "nút chọn", được remove khỏi FormData rồi
       add lại từng file đã lọc — tránh gửi file trùng khi khách chọn nhiều đợt). */
    const MAX_IMG = 5;
    const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    const MAX_BYTES = 2 * 1024 * 1024;
    const imgInput = q('#rvImages');
    const previews = q('#rvImgPreviews');
    /** @type {File[]} */
    let picked = [];

    function renderPreviews() {
        if (!previews) return;
        previews.innerHTML = '';
        picked.forEach((file, idx) => {
            const fig = document.createElement('figure');
            fig.className = 'rv-img-preview';
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = file.name;
            img.loading = 'lazy';
            const rm = document.createElement('button');
            rm.type = 'button';
            rm.className = 'rv-img-preview__remove';
            rm.setAttribute('aria-label', 'Xóa ảnh ' + (idx + 1));
            rm.textContent = '✕';
            rm.addEventListener('click', () => {
                picked.splice(idx, 1);
                renderPreviews();
            });
            const cap = document.createElement('figcaption');
            const kb = Math.max(1, Math.round(file.size / 1024));
            cap.textContent = `${idx + 1}/${MAX_IMG} · ${kb}KB`;
            fig.append(img, cap, rm);
            previews.appendChild(fig);
        });
        if (imgInput) {
            imgInput.disabled = picked.length >= MAX_IMG;
            const hint = imgInput?.closest('.rv-form__row')?.querySelector('.rv-form__hint');
            if (hint) hint.textContent = picked.length >= MAX_IMG
                ? `Đã đạt giới hạn ${MAX_IMG} ảnh — bấm ✕ trên ảnh để đổi.`
                : 'JPG, PNG hoặc WEBP — mỗi ảnh tối đa 2MB.';
        }
    }

    function clearPreviews() {
        picked = [];
        if (previews) previews.innerHTML = '';
        if (imgInput) { imgInput.value = ''; imgInput.disabled = false; }
        renderPreviews();
    }

    imgInput?.addEventListener('change', () => {
        const files = Array.from(imgInput.files || []);
        for (const f of files) {
            if (picked.length >= MAX_IMG) { toast(`Chỉ chọn tối đa ${MAX_IMG} ảnh`); break; }
            if (!ALLOWED_TYPES.includes(f.type)) { toast(`Bỏ qua "${f.name}": chỉ nhận JPG/PNG/WEBP`); continue; }
            if (f.size > MAX_BYTES) { toast(`Bỏ qua "${f.name}": vượt quá 2MB`); continue; }
            // Chống trùng cùng tên+cùng kích thước (khách chọn lại cùng file)
            if (picked.some(p => p.name === f.name && p.size === f.size)) continue;
            picked.push(f);
        }
        imgInput.value = ''; // reset để khách chọn tiếp file khác được (mutate picked thay thế)
        renderPreviews();
    });

    /* Nguồn thật cuối cùng của số sao: ưu tiên input hidden; nếu JS cũ/cache
       khiến input vẫn là "0"/rỗng thì hỏi trực tiếp rateyo instance, rồi mới
       suy ra từ chiều rộng lớp tô .jq-ry-rated-group trong DOM widget. */
    function readCurrentRating() {
        // 0) Radio fallback đang checked (khách bấm thẳng hoặc JS rateyo đã đồng bộ)
        const fromRadio = checkedRadioRating();
        if (fromRadio > 0) return fromRadio;

        const fromInput = toIntStar(ratingInput?.value);
        if (fromInput > 0) return fromInput;

        const jq = window.jQuery;
        if (jq && rateyoBox) {
            const fn = typeof jq.fn.rateYo === 'function' ? 'rateYo' : 'rateyo';
            try {
                const val = jq(rateyoBox)[fn]('rating');   // method trả number hiện tại
                const star = toIntStar(val);
                if (star > 0) { setRating(star); return star; }
            } catch (_) { /* chưa init thì bỏ qua */ }
        }

        const rated = rateyoBox?.querySelector('.jq-ry-rated-group');
        const normal = rateyoBox?.querySelector('.jq-ry-normal-group');
        if (rated && normal) {
            const wR = parseFloat(getComputedStyle(rated).width) || 0;
            const wN = parseFloat(getComputedStyle(normal).width) || 0;
            const star = wN > 0 ? toIntStar(wR / wN * 5) : 0;
            if (star > 0) { setRating(star); return star; }
        }
        return 0;
    }

    /* ============ 3) GỬI REVIEW (AJAX multipart + CSRF) ============ */
    const csrf = q('meta[name="csrf-token"]')?.content;
    const submitBtn = q('#rvSubmit');
    let sending = false;

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (sending) return;

        // Đọc sao từ NHIỀU nguồn (radio checked -> input hidden -> rateyo instance
        // -> DOM widget) để không bao giờ gửi sang server giá trị rỗng/"NaN"
        const rating = readCurrentRating();
        const content = (q('#rvContent')?.value || '').trim();
        const name = (q('#rvName')?.value || '').trim();
        const phone = (q('#rvPhone')?.value || '').replace(/[\s.]/g, '');
        const email = (q('#rvEmail')?.value || '').trim();

        // Validate phía client — mirrors rules của StoreReviewRequest
        if (rating < 1) { setFormError('Vui lòng chọn số sao đánh giá'); return; }
        if (content.length < 10) { setFormError('Nhận xét cần tối thiểu 10 ký tự'); return; }
        if (!name) { setFormError('Vui lòng nhập tên hiển thị'); return; }
        // FIX: SĐT bắt buộc (đối chiếu đơn), email không bắt buộc
        if (!/^(\+?\d{9,11}|0\d{8,10})$/.test(phone)) { setFormError('SĐT không hợp lệ (9–11 chữ số, dùng để đối chiếu đơn hàng)'); return; }
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setFormError('Email chưa hợp lệ'); return; }
        if (!csrf) { setFormError('Lỗi hệ thống: thiếu CSRF token'); return; }

        sending = true;
        submitBtn.disabled = true;
        setFormError('');

        try {
            // FormData từ form gốc (có _token + các field text), sau đó thay
            // ảnh bằng danh sách picked đã lọc ≤5 (input file gốc đang rỗng)
            const fd = new FormData(form);
            // Ghi DUY NHẤT một giá trị nguyên cho rating (fd.set thay thế mọi
            // phần tử "rating" do radio/hidden để lại trong FormData)
            fd.set('rating', String(rating));
            fd.set('phone', phone);
            fd.delete('images[]');
            fd.delete('images');
            picked.forEach(f => fd.append('images[]', f, f.name));

            const res = await fetch(`/san-pham/${encodeURIComponent(slug)}/danh-gia`, {
                method: 'POST',
                headers: {
                    // KHÔNG tự set Content-Type — browser thêm multipart boundary
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: fd,
            });
            const data = await res.json().catch(() => ({}));

            if (res.ok && data.success) {
                toast(data.message || 'Đã gửi đánh giá');
                form.reset();
                resetStars();
                clearPreviews();
                form.hidden = true;
                openBtn?.setAttribute('aria-expanded', 'false');
            } else {
                // 422 từ FormRequest trả errors — lấy message đầu tiên
                const firstErr = data.errors ? Object.values(data.errors)[0]?.[0] : null;
                setFormError(firstErr || data.message || 'Không gửi được đánh giá, vui lòng thử lại');
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

    /* ============ 6) NEW: ADMIN REPLY — bật/đóng box + POST /danh-gia/{id}/phan-hoi ============
       Chỉ render cho staff (blade check auth()->user()->isStaff()). Save gửi nội dung,
       Clear gửi chuỗi rỗng = xóa phản hồi (service hiểu null). Thành công cập nhật
       khối .review__reply ngay tại chỗ — không cần reload (cache đã bump server-side). */
    list?.addEventListener('click', async (e) => {
        const toggle = e.target.closest('.js-rv-reply');
        if (toggle) {
            const box = q('#rvReplyBox-' + toggle.dataset.reviewId);
            if (!box) return;
            box.hidden = !box.hidden;
            toggle.setAttribute('aria-expanded', String(!box.hidden));
            if (!box.hidden) box.querySelector('.js-rv-reply-text')?.focus();
            return;
        }

        const saveBtn = e.target.closest('.js-rv-reply-save, .js-rv-reply-clear');
        if (!saveBtn || saveBtn.disabled || !csrf) return;
        const id = saveBtn.dataset.reviewId;
        const isClear = saveBtn.classList.contains('js-rv-reply-clear');
        const item = saveBtn.closest('.review');
        const textarea = item?.querySelector('.js-rv-reply-text');
        const text = isClear ? '' : (textarea?.value || '').trim();
        if (!isClear && text === '') { toast('Nội dung phản hồi không được trống'); return; }

        saveBtn.disabled = true;
        try {
            const res = await fetch(`/danh-gia/${id}/phan-hoi`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ admin_reply: text }),
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.success) {
                toast(data.message || 'Đã lưu phản hồi');
                // Cập nhật khối reply tại chỗ
                let replyBox = item?.querySelector('.review__reply');
                if (data.admin_reply) {
                    if (!replyBox) {
                        replyBox = document.createElement('div');
                        replyBox.className = 'review__reply';
                        replyBox.innerHTML = '<b>Phản hồi từ Mộc Xanh</b><p></p>';
                        item?.querySelector('.review__foot')?.before(replyBox);
                    }
                    replyBox.querySelector('p').textContent = data.admin_reply;
                    if (textarea) textarea.value = data.admin_reply;
                } else {
                    replyBox?.remove();
                    if (textarea) textarea.value = '';
                }
                // Đóng box + đổi nhãn nút toggle
                const box = q('#rvReplyBox-' + id);
                if (box) box.hidden = true;
                const tgl = item?.querySelector('.js-rv-reply');
                if (tgl) {
                    tgl.setAttribute('aria-expanded', 'false');
                    tgl.textContent = data.admin_reply ? '💬 Sửa phản hồi' : '💬 Phản hồi';
                }
            } else {
                toast(data.message || 'Không lưu được phản hồi');
            }
        } catch (err) {
            console.error('[ReviewReply]', err);
            toast('Có lỗi mạng, vui lòng thử lại');
        } finally {
            saveBtn.disabled = false;
        }
    });
}