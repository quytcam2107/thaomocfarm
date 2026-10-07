/**
 * APP PRODUCT — PD SHARE: nút chia sẻ native ([data-share-native]) + copy link ([data-copy-url]).
 * Tách từ app-product.js cũ (dòng 486–538) — logic GIỮ NGUYÊN BẢN, chỉ thêm dòng import.
 */
import { toast } from '@tm/core';

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