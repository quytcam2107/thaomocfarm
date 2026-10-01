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

    /* ===== Back-to-top (chỉ render ở trang bật :show-back-to-top — hiện là home) =====
       Quy tắc: ĐẦU TRANG -> ẩn, VUỐT XUỐNG -> hiện.
       - CSS đã mặc định ẨN (.back-to-top { visibility:hidden; opacity:0 } khai báo trong
         partials/12-layout-extras.css) -> kể cả khi JS chưa chạy nút cũng không hiện.
       - 2 ngưỡng chống nhấp nháy (hysteresis): hiện khi scrollY > SHOW_AT (160px),
         ẩn chỉ khi scrollY < HIDE_AT (60px). Vùng 60-160px giữ nguyên trạng thái cũ,
         nên nút không "lúc hiện lúc không" khi trang dao động quanh một ngưỡng.
       - Event scroll throttle bằng requestAnimationFrame; đồng bộ lại khi load/pageshow
         (mở tab giữa trang, restore vị trí cuộn) và khi resize (layout đổi -> hết "treo" trạng thái).
       - Bấm nút: cuộn mượt về đầu; nút tự ẩn khi scrollY chạm ngưỡng HIDE_AT trong quá trình
         cuộn lên — không set ẩn sớm để tránh nút biến mất giữa chừng rồi hiện lại.
       - Chỉ toggle class .is-visible (không inline style); tôn trọng prefers-reduced-motion. */
    const backToTop = q('#backToTop');
    if (backToTop) {
        const SHOW_AT = 160;   // cuộn qua mốc này -> hiện nút
        const HIDE_AT = 60;    // cuộn về dưới mốc này -> ẩn nút
        let bttVisible = false;
        let bttTicking = false;

        const bttUpdate = () => {
            bttTicking = false;
            const y = window.scrollY || document.documentElement.scrollTop || 0;
            // 2 ngưỡng: qua SHOW_AT thì hiện, về dưới HIDE_AT thì ẩn, ở giữa giữ nguyên
            if (y > SHOW_AT) bttVisible = true;
            else if (y < HIDE_AT) bttVisible = false;
            backToTop.classList.toggle('is-visible', bttVisible);
            backToTop.setAttribute('aria-hidden', String(!bttVisible));
        };

        window.addEventListener('scroll', () => {
            if (bttTicking) return;          // throttle bằng rAF — rẻ khi cuộn nhanh
            bttTicking = true;
            requestAnimationFrame(bttUpdate);
        }, { passive: true });

        // Đồng bộ trạng thái ngay khi tải trang + khi mở lại tab (restore scroll) + khi đổi màn hình
        bttUpdate();
        window.addEventListener('pageshow', bttUpdate);
        window.addEventListener('resize', () => requestAnimationFrame(bttUpdate));

        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
        });
    }

    /* Clone bộ lọc sang drawer mobile */
    const fg = q('#filterGroups'), fd = q('#filterDrawerBody');
    if (fg && fd) fd.innerHTML = fg.innerHTML;

    /* =====================================================================
       COUNTDOWN FLASH SALE — HOME + PDP dùng chung 1 engine GIỜ:PHÚT:GIÂY
       - data-ends (unix giây) do SERVER tính tại thời điểm render và đã clamp
         <= 24h: phiên còn chạy quá 24h -> đếm đúng 24h; phiên đã hết hạn ->
         block VẪN hiển thị và đếm vòng 24h từ lúc render.
       - Vì mốc là unix giây server sinh, JS chỉ việc đếm — KHÔNG cần meta
         server-time (đã loại bỏ để không ảnh hưởng SEO).
       - KHÔNG hiển thị ô "ngày": tổng giây còn lại quy hết ra giờ (max 24).
       ===================================================================== */
    const pad = n => String(n).padStart(2, '0');

    /* Engine đếm H:M:S tới 1 unix timestamp; timer trả về để clear khi cần */
    function runCountdown(hEl, mEl, sEl, endsUnix) {
        const tick = () => {
            const s = Math.max(0, Math.floor((endsUnix * 1000 - Date.now()) / 1000));
            hEl.textContent = pad(Math.floor(s / 3600));
            mEl.textContent = pad(Math.floor((s % 3600) / 60));
            sEl.textContent = pad(s % 60);
            if (s <= 0) clearInterval(timer); // dừng ở 00:00:00, vẫn giữ block hiển thị
        };
        tick();
        const timer = setInterval(tick, 1000);
        return timer;
    }

    /* Countdown trang home: đếm tới mốc data-ends server đã clamp <= 24h */
    const cdHome = q('.countdown[data-ends]');
    const cdH = q('#cdH'), cdM = q('#cdM'), cdS = q('#cdS');
    if (cdH && cdM && cdS && cdHome) {
        const endsUnix = parseInt(cdHome.dataset.ends, 10);
        if (!isNaN(endsUnix) && endsUnix > 0) {
            runCountdown(cdH, cdM, cdS, endsUnix);
        }
    }

    /* Countdown flash sale trên PDP — CÙNG engine, CÙNG công thức mốc với home */
    const pdBox = q('[data-pd-flash]');
    if (pdBox) {
        const pdH = q('#pdCdH'), pdM = q('#pdCdM'), pdS = q('#pdCdS');
        const endsUnix = parseInt(pdBox.dataset.ends, 10);
        if (pdH && pdM && pdS && !isNaN(endsUnix) && endsUnix > 0) {
            runCountdown(pdH, pdM, pdS, endsUnix);
        }
    }

    /* Gallery: đổi ảnh chính theo thumb */
    qa('.pd-thumbs button').forEach(btn => btn.addEventListener('click', () => {
        const img = q('#pdStageImg');
        if (img && btn.dataset.full) img.src = btn.dataset.full;
        qa('.pd-thumbs button').forEach(b => b.setAttribute('aria-current', String(b === btn)));
    }));

    /* =====================================================================
       PD SHARE: hàng nút chia sẻ dưới .pd-thumbs — chỉ render khi SP đang
       có flash sale (xem components/product/gallery.blade.php).
       - [data-share-native]: gọi navigator.share (mobile); trình duyệt không
         hỗ trợ / lỗi => fallback copy link + toast (#toast contract cũ).
       - [data-copy-url]: copy link trực tiếp; clipboard API lỗi (HTTP, trình
         duyệt cũ) => fallback textarea + document.execCommand.
       - Event delegation trên document: bind 1 lần, không lo timing DOM.
       ===================================================================== */
    async function copyLink(url) {
        try {
            await navigator.clipboard.writeText(url);
            toast('Đã sao chép liên kết sản phẩm');
        } catch (e) {
            // Fallback cho môi trường không có clipboard API (HTTP, trình duyệt cũ)
            const ta = document.createElement('textarea');
            ta.value = url;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            let ok = false;
            try { ok = document.execCommand('copy'); } catch (_) { ok = false; }
            document.body.removeChild(ta);
            toast(ok ? 'Đã sao chép liên kết sản phẩm' : 'Không sao chép được, hãy copy thủ công');
        }
    }

    document.addEventListener('click', e => {
        const nativeBtn = e.target.closest('[data-share-native]');
        if (nativeBtn) {
            e.preventDefault();
            const url = nativeBtn.dataset.shareUrl || location.href;
            const title = nativeBtn.dataset.shareTitle || document.title;
            if (navigator.share) {
                navigator.share({ title, text: '⚡ Flash Sale: ' + title, url })
                    .catch(err => {
                        // AbortError = user bấm hủy -> im lặng; lỗi khác -> copy link
                        if (err && err.name !== 'AbortError') copyLink(url);
                    });
            } else {
                copyLink(url);
            }
            return;
        }

        const copyBtn = e.target.closest('[data-copy-url]');
        if (copyBtn) {
            e.preventDefault();
            copyLink(copyBtn.dataset.copyUrl);
        }
    });

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

    /* =====================================================================
       BUY NOW (#buyNow desktop + #buyNowMobile buybar):
       - Thu thập: product_id (data-product-id), variant_id (radio checked
         name="variant_id" — fallback name="variant"), qty (input[name="qty"])
       - POST /gio-hang/mua-ngay (kèm X-CSRF-TOKEN từ meta[name="csrf-token"])
       - Thành công: cập nhật badge qua window.updateCartCount (contract cart.js)
         rồi redirect tới resp.redirect (/thanh-toan)
       - Dùng event delegation trên document → bind được cả 2 nút, không lo
         timing DOM. Biến isBuying chặn spam click khi đang gửi request.
       ===================================================================== */
    const csrfToken = q('meta[name="csrf-token"]')?.content;
    let isBuying = false;

    async function buyNow(button) {
        if (isBuying) return;

        const productId = parseInt(button.dataset.productId, 10);
        if (isNaN(productId)) { toast('Không tìm thấy mã sản phẩm'); return; }

        // Variant đang chọn trên PDP (khớp convention của cart.js)
        const selectedVariant = q('input[name="variant_id"]:checked') || q('input[name="variant"]:checked');
        const variantId = selectedVariant ? parseInt(selectedVariant.value, 10) : NaN;
        if (isNaN(variantId)) {
            toast('Vui lòng chọn quy cách sản phẩm');
            const pills = q('.pills');
            if (pills) pills.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // Số lượng từ stepper PDP, clamp >= 1
        const qtyInput = q('input[name="qty"]');
        let qty = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;
        if (qty < 1) qty = 1;

        if (!csrfToken) { toast('Lỗi hệ thống: Không tìm thấy CSRF token'); return; }

        isBuying = true;
        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.textContent = 'Đang xử lý…';

        try {
            const res = await fetch('/gio-hang/mua-ngay', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ product_id: productId, variant_id: variantId, qty }),
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();

            if (data.success) {
                // Đồng bộ badge giỏ (header + floatnav) trước khi rời trang
                if (typeof window.updateCartCount === 'function') {
                    window.updateCartCount(data.cartCount);
                }
                if (data.redirect) {
                    window.location.assign(data.redirect);
                    return; // giữ nguyên trạng thái disabled tới khi chuyển trang
                }
            } else {
                toast(data.message || 'Không thể mua ngay, vui lòng thử lại');
            }
        } catch (err) {
            console.error('[BuyNow]', err);
            toast('Có lỗi xảy ra, vui lòng thử lại');
        } finally {
            // Chỉ reset khi KHÔNG redirect (lỗi / thiếu URL)
            isBuying = false;
            button.disabled = false;
            button.innerHTML = originalHtml;
        }
    }

    document.addEventListener('click', e => {
        const btn = e.target.closest('#buyNow, #buyNowMobile');
        if (!btn) return;
        e.preventDefault();
        buyNow(btn);
    });

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