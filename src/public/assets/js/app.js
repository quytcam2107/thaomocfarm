(function () {
    'use strict';
    const q = (s, c = document) => c.querySelector(s);
    const qa = (s, c = document) => [...c.querySelectorAll(s)];
    const money = n => n.toLocaleString('vi-VN') + '₫';
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* Toast */
    let toastTimer;
    function toast(msg) {
        const t = q('#toast'); if (!t) return;
        t.textContent = msg; t.classList.add('show');
        clearTimeout(toastTimer); toastTimer = setTimeout(() => t.classList.remove('show'), 1800);
    }

    /* Chặn link demo href="#" */
    document.addEventListener('click', e => {
        const a = e.target.closest('a[href="#"]');
        if (a) e.preventDefault();
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

    /* ===== Drawer ===== */
    const burger = q('#burgerBtn'), drawer = q('#drawer');
    const lock = on => document.body.classList.toggle('is-locked', on);
    if (burger && drawer) {
        burger.addEventListener('click', () => { drawer.classList.add('is-open'); lock(true); burger.setAttribute('aria-expanded', 'true'); });
    }
    qa('[data-drawer-open]').forEach(btn => btn.addEventListener('click', () => {
        const d = q(btn.getAttribute('data-drawer-open')); if (!d) return;
        d.classList.add('is-open'); lock(true); btn.setAttribute('aria-expanded', 'true');
    }));
    qa('[data-drawer-close]').forEach(btn => btn.addEventListener('click', () => {
        const d = btn.closest('.drawer'); if (!d) return;
        d.classList.remove('is-open'); lock(false);
        const opener = q('[data-drawer-open="#' + d.id + '"]') || (d.id === 'drawer' ? q('#burgerBtn') : null);
        if (opener) opener.setAttribute('aria-expanded', 'false');
    }));
    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        qa('.drawer.is-open').forEach(d => d.classList.remove('is-open'));
        lock(false);
    });

    /* Clone bộ lọc sang drawer mobile */
    const fg = q('#filterGroups'), fd = q('#filterDrawerBody');
    if (fg && fd) fd.innerHTML = fg.innerHTML;

    /* Countdown tới cuối ngày */
    const cdH = q('#cdH'), cdM = q('#cdM'), cdS = q('#cdS');
    if (cdH && cdM && cdS) {
        const end = new Date(); end.setHours(23, 59, 59, 999);
        const pad = n => String(n).padStart(2, '0');
        const tick = () => {
            const s = Math.max(0, Math.floor((end - Date.now()) / 1000));
            cdH.textContent = pad(Math.floor(s / 3600));
            cdM.textContent = pad(Math.floor((s % 3600) / 60));
            cdS.textContent = pad(s % 60);
        };
        tick(); setInterval(tick, 1000);
    }

    /* Copy mã giảm giá */
    qa('.copy-btn').forEach(btn => btn.addEventListener('click', async () => {
        const code = btn.dataset.code;
        try { await navigator.clipboard.writeText(code); }
        catch {
            const t = document.createElement('textarea');
            t.value = code; document.body.appendChild(t); t.select();
            document.execCommand('copy'); t.remove();
        }
        const old = btn.textContent; btn.textContent = 'Đã chép!';
        setTimeout(() => (btn.textContent = old), 1500);
        toast('Đã sao chép mã ' + code);
    }));

    /* Thêm vào giỏ + badge */
    let cartCount = 0;
    const badge = q('#cartBadge');
    qa('.add-cart').forEach(btn => btn.addEventListener('click', () => {
        cartCount++;
        if (badge) {
            badge.textContent = cartCount;
            badge.classList.remove('bump'); void badge.offsetWidth; badge.classList.add('bump');
        }
        toast('Đã thêm "' + btn.dataset.name + '" vào giỏ 🛒');
    }));

    /* Yêu thích */
    qa('.pcard__fav, .pcard__fav--lg').forEach(b => b.addEventListener('click', () => {
        const on = b.getAttribute('aria-pressed') === 'true';
        b.setAttribute('aria-pressed', String(!on));
        b.classList.toggle('is-on', !on);
        if (b.classList.contains('pcard__fav')) b.textContent = !on ? '♥' : '♡';
        toast(!on ? 'Đã thêm vào yêu thích ♥' : 'Đã bỏ khỏi yêu thích');
    }));

    /* Stepper + tính tiền giỏ */
    const FREE_SHIP = 200000, SHIP_FEE = 20000;
    let discount = 0;
    function recalcCart() {
        const list = q('#cartList'); if (!list) return;
        const items = qa('.cart-item', list);
        const empty = q('#cartEmpty');
        if (empty) empty.hidden = items.length > 0;
        list.style.display = items.length ? '' : 'none';
        let sub = 0;
        items.forEach(it => {
            const p = +it.dataset.price, qty = +it.querySelector('.qty input').value;
            it.querySelector('.cart-item__total').textContent = money(p * qty);
            sub += p * qty;
        });
        const ship = (sub === 0 || sub >= FREE_SHIP) ? 0 : SHIP_FEE;
        const note = q('#freeShipNote');
        if (note) note.textContent = sub === 0 ? '' : (sub >= FREE_SHIP ? '🎉 Đơn của bạn được FREESHIP!' : '🚚 Mua thêm ' + money(FREE_SHIP - sub) + ' để được FREESHIP');
        const set = (id, v) => { const el = q(id); if (el) el.textContent = v; };
        set('#subTotal', money(sub));
        const dr = q('#discountRow'); if (dr) dr.hidden = discount === 0;
        set('#discountTotal', '-' + money(discount));
        set('#shipTotal', ship === 0 ? 'Miễn phí' : money(ship));
        set('#grandTotal', money(Math.max(0, sub - discount + ship)));
    }
   
    const applyBtn = q('#applyCoupon');
    if (applyBtn) applyBtn.addEventListener('click', () => {
        const code = (q('#couponCode').value || '').trim().toUpperCase();
        if (code === 'SALE9K') discount = 9000;
        else if (code === 'SALE22K') discount = 22000;
        else if (code === 'TAYBAC20') discount = 20000;
        else { discount = 0; recalcCart(); toast('Mã không hợp lệ hoặc hết hạn'); return; }
        recalcCart(); toast('Đã áp dụng mã ' + code);
    });

    /* Gallery: đổi ảnh chính theo thumb (chỉ thay src — chuẩn Blade sau này) */
    qa('.pd-thumbs button').forEach(btn => btn.addEventListener('click', () => {
        const img = q('#pdStageImg');
        if (img && btn.dataset.full) img.src = btn.dataset.full;
        qa('.pd-thumbs button').forEach(b => b.setAttribute('aria-current', String(b === btn)));
    }));

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
    const buyNow = q('#buyNow');
    if (buyNow) buyNow.addEventListener('click', () => toast('Demo: chuyển tới thanh toán'));

    /* Danh mục */
    const sort = q('#sort');
    if (sort) sort.addEventListener('change', () => toast('Demo sắp xếp: ' + sort.value));
    qa('.chip button').forEach(b => b.addEventListener('click', () => b.closest('.chip').remove()));
    qa('.pagination a').forEach(a => a.addEventListener('click', e => { e.preventDefault(); toast('Demo phân trang'); }));
    qa('.filter-actions .btn').forEach(b => b.addEventListener('click', () => toast('Demo: áp dụng bộ lọc')));

    /* Form */
    const search = q('.search');
    if (search) search.addEventListener('submit', e => {
        e.preventDefault();
        const v = search.querySelector('input').value.trim();
        toast(v ? 'Demo tìm kiếm: "' + v + '"' : 'Nhập từ khoá cần tìm nhé!');
    });
    const news = q('#newsForm');
    if (news) news.addEventListener('submit', e => { e.preventDefault(); toast('Cảm ơn bạn đã đăng ký! 🌿'); news.reset(); });
    const co = q('#checkoutForm');
    if (co) co.addEventListener('submit', e => {
        e.preventDefault();
        if (!co.checkValidity()) { co.reportValidity(); return; }
        toast('🎉 Đặt hàng thành công! Mã đơn: TMX' + String(Date.now()).slice(-6));
        co.reset();
    });

    /* Năm hiện tại */
    const y = q('#year'); if (y) y.textContent = new Date().getFullYear();
})();