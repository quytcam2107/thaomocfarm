/**
 * FLASH LIVE — cơ chế RENDER Flash Sale bằng JS, cập nhật MỖI 3 PHÚT (180.000 ms).
 * Nạp qua @push('scripts') ở home.blade.php + product.blade.php (KHÔNG đụng importmap/layout).
 *
 * - Home:  GET route web.flash-sale.home  -> cập nhật #flash (head/stats/rail/card)
 * - PDP:   GET route web.flash-sale.product -> cập nhật .pd-flash + giá .pd-price
 * - Giữ NGUYÊN countdown.js (đếm tới nửa đêm) — chỉ ghi đè data-ends seed.
 * - Giữ contract .add-cart: khi render card mới, dispatch event 'tm:dom-update'
 *   để cart-add.js (delegate sẵn trên document) tiếp tục bắt được nút.
 * - Tab bị background-throttling: mỗi visibilitychange sẽ refresh ngay 1 lần.
 */
(() => {
    const INTERVAL_MS = 180000; // 3 phút theo yêu cầu
    const fmt = n => Number(n || 0).toLocaleString('vi-VN');

    const section = document.querySelector('[data-flash-home]');
    const pdpBox = document.querySelector('[data-flash-pdp]');

    async function getJson(url) {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
    }

    /* ============================ HOME ============================ */
    const TONE_CLASS = { hot: 'is-hot', deep: 'is-deep', sell: 'is-sell', new: 'is-new' };

    function hookHtml(item) {
        const tone = TONE_CLASS[item.urgent_tone] || 'is-new';
        return `<span class="flash__urgent ${tone}" data-fs-hook>${item.urgent_text ?? ''}</span>`;
    }

    function cardHtml(item) {
        const slots = item.slots_left > 0 ? `Còn ${fmt(item.slots_left)} slot` : '';
        return `
<article class="pcard pcard--flash" data-flash-card data-product-id="${item.product_id}">
  <div class="pcard__media">
    ${hookHtml(item)}
    <a href="${item.url}" aria-label="Xem ${item.name}">
      <img class="pcard__img" src="${item.image}" alt="${item.name}" width="600" height="600" loading="lazy">
    </a>
    <span class="pcard__flag pcard__flag--hot" data-fs-flag>-${item.discount_percent}%</span>
  </div>
  <div class="pcard__body">
    <h3 class="pcard__name"><a href="${item.url}">${item.name}</a></h3>
    <p class="pcard__rate"><span class="stars">★★★★★</span>
      <span data-fs-rate>${item.rating_avg} · ${item.sold_text} đã bán</span></p>
    <div class="flash__prog" role="progressbar" aria-valuemin="0" aria-valuemax="100"
         aria-valuenow="${item.sold_percent}" aria-label="Tiến độ bán deal ${item.name}" data-fs-prog-wrap>
      <span class="flash__prog-bar ${item.urgent_tone === 'hot' ? 'is-hot' : ''}" data-fs-prog-bar
            style="width:${Math.min(100, Math.max(5, item.sold_percent))}%;"></span>
    </div>
    <p class="flash__prog-meta" data-fs-prog-meta>
      <b data-fs-progress-text>${item.progress_text}</b><small data-fs-slots>${slots}</small>
    </p>
    <p class="flash__sold" data-fs-sold-today>${item.sold_text_today}</p>
    ${item.saved_amount > 0 ? `<p class="flash__save" data-fs-save><i class="flash__save-label">Tiết kiệm</i>
      <b class="flash__save-value" data-fs-save-value>${fmt(item.saved_amount)}₫</b></p>`
                : `<p class="flash__save" data-fs-save hidden><i class="flash__save-label">Tiết kiệm</i>
      <b class="flash__save-value" data-fs-save-value></b></p>`}
    <div class="pcard__buy">
      <p class="pcard__price"><b data-fs-price>${fmt(item.flash_price)}₫</b>
        <s data-fs-old>${fmt(item.original_price)}₫</s></p>
      <button class="pcard__add add-cart" type="button" data-product-id="${item.product_id}"
        data-variant-id="${item.variant_id}" data-name="${item.name}" aria-label="Thêm ${item.name} vào giỏ">
        <img class="pcard__add-icon" src="/assets/images/svg/cart-add.svg" alt="Thêm giỏ hàng" width="20" height="20">
      </button>
    </div>
  </div>
</article>`;
    }

    function renderHome(data) {
        if (!section) return;

        // Không có phiên/deal -> ẩn sạch section (giống hành vi Blade @if cũ)
        if (!data.active || !Array.isArray(data.items) || data.items.length === 0) {
            section.hidden = true;
            return;
        }
        section.hidden = false;

        // Seed mốc ends_at cho countdown.js (module chỉ đọc data-ends làm marker)
        const cd = section.querySelector('.countdown[data-ends]');
        if (cd && data.ends_at_unix) cd.dataset.ends = String(data.ends_at_unix);

        // STATS: 2 chip social proof
        const stats = section.querySelector('[data-flash-stats]');
        if (stats) {
            let html = '';
            if (data.sold_today > 0) {
                html += `<span class="flash__chip">🔥 Đã bán ${fmt(data.sold_today)} sản phẩm hôm nay</span>`;
            }
            if (data.urgent_count > 0) {
                html += `<span class="flash__chip flash__chip--hot">${fmt(data.urgent_count)} deal sắp cháy hàng</span>`;
            }
            stats.innerHTML = html;
            stats.hidden = html === '';
        }

        // RAIL: cập nhật tại chỗ card trùng product_id, xóa card hết deal, chèn deal mới
        const rail = section.querySelector('[data-flash-rail]');
        if (!rail) return;

        const seen = new Set();
        data.items.forEach(item => {
            seen.add(String(item.product_id));
            const old = rail.querySelector(`[data-flash-card][data-product-id="${item.product_id}"]`);
            if (old) { updateCard(old, item); return; }
            rail.insertAdjacentHTML('beforeend', cardHtml(item));
        });

        // Deal đã kết thúc / hết hạn mức -> gỡ card khỏi rail
        [...rail.querySelectorAll('[data-flash-card]')].forEach(el => {
            if (!seen.has(el.dataset.productId)) el.remove();
        });

        // Báo cho các module delegate (cart-add, reveal) rằng DOM vừa đổi
        document.dispatchEvent(new CustomEvent('tm:dom-update', { detail: { scope: 'flash-home' } }));
    }

    function updateCard(el, item) {
        const set = (sel, text, html = false) => {
            const n = el.querySelector(sel);
            if (!n) return;
            if (html) n.innerHTML = text; else n.textContent = text;
        };
        set('[data-fs-price]', `${fmt(item.flash_price)}₫`);
        set('[data-fs-old]', `${fmt(item.original_price)}₫`);
        set('[data-fs-flag]', `-${item.discount_percent}%`);
        set('[data-fs-rate]', `${item.rating_avg} · ${item.sold_text} đã bán`);
        set('[data-fs-progress-text]', item.progress_text);
        set('[data-fs-slots]', item.slots_left > 0 ? `Còn ${fmt(item.slots_left)} slot` : '');
        set('[data-fs-sold-today]', item.sold_text_today);
        set('[data-fs-hook]', item.urgent_text ?? '');

        const hook = el.querySelector('[data-fs-hook]');
        if (hook) {
            hook.className = `flash__urgent ${TONE_CLASS[item.urgent_tone] || 'is-new'}`;
            hook.hidden = !item.urgent_text;
        }
        const bar = el.querySelector('[data-fs-prog-bar]');
        if (bar) {
            bar.style.width = `${Math.min(100, Math.max(5, item.sold_percent))}%`;
            bar.classList.toggle('is-hot', item.urgent_tone === 'hot');
        }
        const progWrap = el.querySelector('[data-fs-prog-wrap]');
        if (progWrap) {
            progWrap.hidden = item.sold_percent <= 0;
            progWrap.setAttribute('aria-valuenow', item.sold_percent);
        }
        const meta = el.querySelector('[data-fs-prog-meta]');
        if (meta) meta.hidden = item.sold_percent <= 0;

        const save = el.querySelector('[data-fs-save]');
        if (save) {
            save.hidden = !(item.saved_amount > 0);
            set('[data-fs-save-value]', `${fmt(item.saved_amount)}₫`);
        }
    }

    /* ============================= PDP ============================= */
    function renderPdp(data) {
        if (!pdpBox) return;

        if (!data.active) {
            pdpBox.classList.add('is-ended');   // CSS: display:none (07d-pdp-tabs.css)
            return;
        }
        pdpBox.classList.remove('is-ended');

        if (data.ends_at_unix) {
            pdpBox.dataset.ends = String(data.ends_at_unix);
        }

        const name = pdpBox.querySelector('[data-fsp-name]');
        if (name && data.name) name.textContent = data.name;

        const wrap = pdpBox.querySelector('[data-fsp-prog-wrap]');
        const bar = pdpBox.querySelector('[data-fsp-prog-bar]');
        const meta = pdpBox.querySelector('[data-fsp-prog-meta]');
        if (wrap && bar && meta) {
            const pct = Number(data.sold_percent || 0);
            wrap.hidden = pct <= 0;
            meta.hidden = pct <= 0;
            wrap.setAttribute('aria-valuenow', pct);
            bar.style.width = `${Math.min(100, Math.max(5, pct))}%`;
            bar.classList.toggle('is-hot', pct >= 70);
            const p = meta.querySelector('[data-fsp-percent]');
            if (p) p.textContent = `${pct}% đã bán`;
            const s = meta.querySelector('[data-fsp-slots]');
            if (s) s.textContent = data.slots_left > 0 ? `Còn ${fmt(data.slots_left)} slot` : 'Hết slot';
        }

        const li = pdpBox.querySelector('[data-fsp-discount]');
        if (li) {
            li.hidden = !(data.discount_percent > 0);
            const b = li.querySelector('b');
            if (b) b.textContent = `-${data.discount_percent}%`;
            const sv = li.querySelector('[data-fsp-saved]');
            if (sv) sv.textContent = data.saved_amount > 0
                ? `— tiết kiệm ${fmt(data.saved_amount)}₫` : '';
        }

        // Đồng bộ GIÁ PDP theo deal (nguồn: FlashSalePriceService)
        const priceEl = document.querySelector('.pd-price .price');
        const oldEl = document.querySelector('.pd-price s');
        const offEl = document.querySelector('.pd-price .off');
        if (priceEl && data.price > 0) {
            priceEl.textContent = `${fmt(data.price)}₫`;
            if (oldEl && data.original_price > data.price) {
                oldEl.textContent = `${fmt(data.original_price)}₫`;
                oldEl.style.display = 'inline';
                if (offEl) {
                    offEl.textContent = `-${data.discount_percent}%`;
                    offEl.style.display = 'inline';
                }
            } else if (oldEl) {
                oldEl.style.display = 'none';
                if (offEl) offEl.style.display = 'none';
            }
            const buybar = document.querySelector('.buybar__price');
            if (buybar) buybar.textContent = `${fmt(data.price)}₫`;
        }
    }

    /* =========================== POLLER =========================== */
    async function refresh() {
        const jobs = [];
        if (section) {
            jobs.push(getJson(section.dataset.flashUrl).then(renderHome).catch(e => console.warn('[FlashLive home]', e)));
        }
        if (pdpBox) {
            jobs.push(getJson(pdpBox.dataset.flashUrl).then(renderPdp).catch(e => console.warn('[FlashLive pdp]', e)));
        }
        await Promise.allSettled(jobs);
    }

    if (!section && !pdpBox) return; // trang không có Flash Sale -> không tốn request

    refresh();                       // fetch ngay lần đầu (thay dữ liệu seed của Blade)
    setInterval(refresh, INTERVAL_MS); // rồi lặp lại mỗi 3 phút

    // Quay lại tab: cập nhật ngay cho khớp, không chờ tới chu kỳ kế tiếp
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) refresh();
    });
})();