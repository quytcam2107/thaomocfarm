/**
 * APP CORE — phần dùng trên MỌI trang: toast, chặn link demo, reveal, count-up.
 * Được các module khác import lại (browser cache duplicate-free — cùng URL = 1 bản thể).
 */
export const q = (s, c = document) => c.querySelector(s);
export const qa = (s, c = document) => [...c.querySelectorAll(s)];
export const money = n => n.toLocaleString('vi-VN') + '₫';
export const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* Toast — dùng chung #toast contract với coupons.blade.php và cart */
let toastTimer;
export function toast(msg) {
    const t = q('#toast'); if (!t) return;
    t.textContent = msg; t.classList.add('show');
    clearTimeout(toastTimer); toastTimer = setTimeout(() => t.classList.remove('show'), 1800);
}

/* Chặn link demo href="#" */
document.addEventListener('click', e => {
    const a = e.target.closest('a[href="#"]');
    if (a) e.preventDefault();
});

/* ===== Floatnav "Flash sale": cuộn mượt tới section #flash trên trang chủ =====
   - Ở home (#flash tồn tại): chặn nhảy neo mặc định, scrollIntoView mượt
     (neo dưới .flash đã có scroll-margin-top trong 14-flash-sale.css).
   - Trang khác: để mặc định — href="/#flash" đưa về home rồi trình duyệt tự cuộn. */
document.addEventListener('click', e => {
    const a = e.target.closest('a[data-fn="flash"]');
    if (!a) return;
    const target = document.getElementById('flash');
    if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
    }
});

/* ===== Reveal khi cuộn (IntersectionObserver, tự unobserve → rẻ) ===== */
const revealEls = qa('.reveal');
if ('IntersectionObserver' in window && revealEls.length && !reduceMotion) {
    const io = new IntersectionObserver(entries => entries.forEach(en => {
        if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
    }), { rootMargin: '0px 0px -40px' });
    revealEls.forEach(el => io.observe(el));
} else {
    revealEls.forEach(el => el.classList.add('is-in'));
}

/* ===== Count-up số liệu uy tín ===== */
qa('[data-count]').forEach(el => {
    const target = parseFloat(el.dataset.count);
    const suffix = el.dataset.suffix || '';
    const fmt = n => n.toLocaleString('vi-VN') + suffix;
    if (!('IntersectionObserver' in window) || reduceMotion) { el.textContent = fmt(target); return; }
    const io2 = new IntersectionObserver(entries => {
        if (!entries[0].isIntersecting) return;
        io2.disconnect();
        const t0 = performance.now(), dur = 1200;
        const step = t => {
            const p = Math.min(1, (t - t0) / dur);
            el.textContent = fmt(Math.round(target * (1 - Math.pow(1 - p, 3))));
            if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    }, { threshold: .4 });
    io2.observe(el);
});