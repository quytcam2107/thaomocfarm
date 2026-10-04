/**
 * APP PRODUCT — countdown flash sale (home + PDP), gallery thumbs, nút chia sẻ,
 * tabs ARIA, Buy Now. Loader chỉ nạp khi có một trong các marker:
 * .countdown[data-ends] / [data-pd-flash] / .pd-thumbs / [role="tab"] / #buyNow,#buyNowMobile.
 */
import { q, qa, toast } from '@tm/core';

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

/* =====================================================================
   GALLERY PDP: thumb click + nút prev/next (cả ảnh to .pd-stage lẫn
   hàng thumbs). Trạng thái index nằm trong dataset của #pdStageImg;
   wrap-around (ảnh cuối -> bấm next về ảnh đầu). Cuốn thumb đang chọn
   vào tầm nhìn khi hàng thumbs bị tràn ngang.
   ===================================================================== */
const stage = q('#pdStageImg');
const thumbs = qa('.pd-thumbs button');
if (stage && thumbs.length) {
    const counter = q('#pdCounter');
    let current = parseInt(stage.dataset.index || '0', 10) || 0;

    const goTo = (idx) => {
        const n = thumbs.length;
        const i = ((idx % n) + n) % n; // wrap-around, an toàn cả số âm
        const btn = thumbs[i];
        if (!btn) return;
        current = i;
        stage.src = btn.dataset.full;
        stage.dataset.index = String(i);
        thumbs.forEach(b => b.setAttribute('aria-current', String(b === btn)));
        if (counter) counter.textContent = `${i + 1}/${n}`;
        btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    };

    thumbs.forEach((btn, i) => {
        btn.dataset.index = String(i);
        btn.addEventListener('click', () => goTo(i));
    });

    qa('[data-pd-nav]').forEach(btn => btn.addEventListener('click', () => {
        goTo(current + (parseInt(btn.dataset.pdNav, 10) || 0));
    }));

    stage.dataset.index = String(current);
}

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