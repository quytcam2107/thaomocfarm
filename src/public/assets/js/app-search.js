/**
 * APP SEARCH — gợi ý AJAX + spotlight + typing placeholder.
 * Loader chỉ nạp khi header có đủ #searchForm + #searchInput + #searchSuggest.
 */
import { q, qa } from '@tm/core';

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