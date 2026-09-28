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

    /* Gallery: đổi ảnh chính theo thumb */
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

    /* =====================================================================
       SEARCH (header):
       - Gõ >= 2 ký tự -> debounce 300ms -> AJAX /tim-kiem/goi-y -> dropdown gợi ý
       - Bấm nút tìm kiếm / Enter (không chọn item) -> submit thường sang /tim-kiem
       - Khi focus input: bật "spotlight" — overlay mờ toàn trang,
         chỉ ô search + dropdown gợi ý nổi sáng phía trên overlay
       - TẮT spotlight CHỈ khi: click ra ngoài form search / Esc / blur input
         (KHÔNG tắt trong closeSuggest để tránh nháy khi đang gõ)
       ===================================================================== */
    const searchForm = q('#searchForm');
    const searchInput = q('#searchInput');
    const suggestBox = q('#searchSuggest');
    const spotlightEl = q('#spotlightOverlay');

    /* =====================================================================
   SEARCH (header):
   - Typing placeholder (Luôn bật)
   - Spotlight, Gợi ý AJAX, Điều hướng phím
   ===================================================================== */
    if (searchForm && searchInput && suggestBox) {

        /* ====== TYPING PLACEHOLDER (GÕ & XÓA TỪNG CHỮ) ====== */
        const phrases = [
            'Tìm củ tam thất, trà hoa, táo đỏ...',
            'Bạn cần tìm thảo mộc hay quà biếu...',
        ];

        let phraseIdx = 0;
        let charIdx = 0;
        let typingTimer;
        let isTyping = false;
        let isDeleting = false; // Biến theo dõi trạng thái đang xóa chữ

        const type = () => {
            if (!isTyping) return;
            const currentPhrase = phrases[phraseIdx];

            if (!isDeleting) {
                // --- GIAI ĐOẠN GÕ (Type In) ---
                if (charIdx < currentPhrase.length) {
                    searchInput.setAttribute('placeholder', currentPhrase.substring(0, charIdx + 1));
                    charIdx++;
                    typingTimer = setTimeout(type, 40); // Tốc độ gõ
                } else {
                    // Gõ xong -> Nghỉ 2.5s rồi bắt đầu XÓA
                    typingTimer = setTimeout(() => {
                        isDeleting = true;
                        type();
                    }, 2000);
                }
            } else {
                // --- GIAI ĐOẠN XÓA (Type Out / Reverse) ---
                if (charIdx > 0) {
                    searchInput.setAttribute('placeholder', currentPhrase.substring(0, charIdx - 1));
                    charIdx--;
                    typingTimer = setTimeout(type, 20); // Tốc độ xóa (thường nhanh gấp đôi gõ cho mượt)
                } else {
                    // Xóa sạch -> Nghỉ 0.5s rồi sang câu tiếp theo
                    isDeleting = false;
                    phraseIdx = (phraseIdx + 1) % phrases.length;
                    typingTimer = setTimeout(type, 500);
                }
            }
        };

        const startTyping = () => {
            if (isTyping) return;
            isTyping = true;
            isDeleting = false;
            type();
        };

        const stopTyping = () => {
            isTyping = false;
            isDeleting = false;
            clearTimeout(typingTimer);
            charIdx = 0;
            // Khi focus vào ô search -> Dừng hiệu ứng, hiện ngay câu hoàn chỉnh
            searchInput.setAttribute('placeholder', phrases[phraseIdx]);
        };

        // Bắt sự kiện Focus / Blur cho Typing
        searchInput.addEventListener('focus', stopTyping);
        searchInput.addEventListener('blur', () => {
            // Nếu blur ra ngoài mà chưa gõ gì -> Đổi sang câu mới và gõ lại từ đầu
            if (!searchInput.value.trim()) {
                phraseIdx = (phraseIdx + 1) % phrases.length;
                charIdx = 0;
                isDeleting = false;
                startTyping();
            }
        });

        // Khởi chạy lúc tải trang
        startTyping();
        /* ====================================================== */


        const SUGGEST_URL = searchForm.action + '/goi-y';
        const esc = s => String(s).replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[c]);

        let debounceTimer = null;
        let lastReqId = 0;
        let activeIdx = -1;

        /* ---- Spotlight overlay ---- */
        const spotlightOn = () => document.body.classList.add('is-spotlight');
        const spotlightOff = () => document.body.classList.remove('is-spotlight');

        function closeSuggest() {
            suggestBox.hidden = true;
            suggestBox.innerHTML = '';
            searchInput.setAttribute('aria-expanded', 'false');
            activeIdx = -1;
        }

        function renderSuggest(data) {
            if (!data.items || data.items.length === 0) {
                suggestBox.innerHTML = '<div class="search__suggest-empty">Không tìm thấy sản phẩm phù hợp.</div>';
            } else {
                const rows = data.items.map(it => `
                    <a class="search__suggest-item" href="${esc(it.url)}">
                        <img src="${esc(it.image)}" alt="" width="40" height="40" loading="lazy">
                        <span class="search__suggest-info">
                            <b class="search__suggest-name">${esc(it.name)}</b>
                            <span class="search__suggest-price">${esc(it.price)}${it.oldPrice ? ' <s>' + esc(it.oldPrice) + '</s>' : ''}</span>
                        </span>
                        ${it.discount ? `<span class="search__suggest-sale">-${it.discount}%</span>` : ''}
                    </a>`).join('');
                const more = `<a class="search__suggest-more" href="${esc(data.more_url)}">Xem tất cả ${Number(data.total) || 0} kết quả →</a>`;
                suggestBox.innerHTML = rows + more;
            }
            suggestBox.hidden = false;
            searchInput.setAttribute('aria-expanded', 'true');
            activeIdx = -1;
        }

        async function fetchSuggest(term) {
            const reqId = ++lastReqId;
            try {
                const res = await fetch(`${SUGGEST_URL}?q=${encodeURIComponent(term)}`, {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok || reqId !== lastReqId) return;
                const data = await res.json();
                if (reqId !== lastReqId) return;
                renderSuggest(data);
            } catch (e) { /* im lặng */ }
        }

        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            const term = searchInput.value.trim();
            if (term.length < 2) { closeSuggest(); return; }
            debounceTimer = setTimeout(() => fetchSuggest(term), 300);
        });

        searchInput.addEventListener('focus', spotlightOn);
        searchInput.addEventListener('blur', spotlightOff);

        searchInput.addEventListener('keydown', e => {
            if (suggestBox.hidden) return;
            const items = qa('.search__suggest-item', suggestBox);
            if (!items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIdx = (activeIdx + 1) % items.length;
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIdx = activeIdx <= 0 ? items.length - 1 : activeIdx - 1;
            } else if (e.key === 'Enter' && activeIdx >= 0) {
                e.preventDefault();
                window.location.assign(items[activeIdx].href);
                return;
            } else if (e.key === 'Escape') {
                closeSuggest();
                spotlightOff();
                searchInput.blur();
                return;
            } else {
                return;
            }
            items.forEach((el, i) => el.classList.toggle('is-active', i === activeIdx));
            items[activeIdx].scrollIntoView({ block: 'nearest' });
        });

        document.addEventListener('click', e => {
            if (!searchForm.contains(e.target)) {
                closeSuggest();
                spotlightOff();
            }
        });

        searchForm.addEventListener('submit', () => {
            closeSuggest();
            spotlightOff();
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && document.body.classList.contains('is-spotlight')) {
                closeSuggest();
                spotlightOff();
                searchInput.blur();
            }
        });
    }
})();