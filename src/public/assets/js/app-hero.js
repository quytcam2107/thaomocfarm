/**
 * APP HERO — slideshow khối .hero__art trang chủ (crossfade + Ken Burns nhẹ).
 * - Blade render sẵn slide ĐẦU trong HTML (LCP) + div.hero__stage mang
 *   data-hero-slides = JSON các ảnh còn lại (config thaomoc.hero_slides).
 * - JS nhân bản img LCP làm lớp 0, chèn các lớp còn lại vào .hero__stage,
 *   tạo dấu chấm chọn slide; tự chuyển mỗi 5s.
 * - Chỉ đổi class .is-active -> CSS lo animation.
 * - TẮT check prefers-reduced-motion trong JS: slideshow luôn chạy với nhịp
 *   cố định 5s/slide (CSS vẫn tự tắt Ken Burns khi người dùng giảm chuyển động).
 * - MỚI: VUỐT NGANG trên khối .hero__art để đổi slide (Pointer Events, vuốt dọc
 *   bỏ qua để trang cuộn — CSS đi kèm khai báo touch-action: pan-y).
 */
import { q } from '@tm/core';

const stage = q('.hero__stage[data-hero-slides]');
if (stage) {
    let extra = [];
    try {
        extra = JSON.parse(stage.dataset.heroSlides || '[]');
    } catch (e) {
        extra = [];
    }
    const first = q('.hero__slide', stage.closest('.hero__art'));
    // Cần ít nhất 2 slide tổng cộng mới chạy slideshow
    if (first && Array.isArray(extra) && extra.length) {
        const INTERVAL = 2500; // 5 giây/slide (không phụ thuộc prefers-reduced-motion)
        let current = 0;
        let timer = null;
        let paused = false;

        // Lớp 0: nhân bản img LCP để mọi slide dùng chung cơ chế .is-active
        const layer0 = first.cloneNode(false);
        first.replaceWith(layer0);
        const layers = [layer0, ...extra.map((src) => {
            const img = document.createElement('img');
            img.className = 'hero__slide';
            img.src = src;
            img.alt = 'Ảnh giới thiệu thảo mộc Mộc Xanh';
            img.loading = 'lazy';
            img.decoding = 'async';
            stage.appendChild(img);
            return img;
        })];

        // Dấu chấm chọn slide (giữa cạnh dưới của ảnh, tránh .hero__badge góc trái)
        const dots = document.createElement('div');
        dots.className = 'hero__dots';
        dots.setAttribute('aria-label', 'Chọn ảnh giới thiệu');
        const dotEls = layers.map((_, i) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'hero__dot' + (i === 0 ? ' is-active' : '');
            b.setAttribute('aria-label', 'Ảnh ' + (i + 1));
            b.addEventListener('click', () => { go(i); restart(); });
            dots.appendChild(b);
            return b;
        });
        stage.appendChild(dots);

        function go(i) {
            current = (i + layers.length) % layers.length;
            layers.forEach((el, k) => el.classList.toggle('is-active', k === current));
            dotEls.forEach((el, k) => el.classList.toggle('is-active', k === current));
        }

        function stop() {
            if (timer) { clearInterval(timer); timer = null; }
        }
        function start() {
            // Tab ẩn hoặc người dùng đang xem kỹ (hover/touch) -> tạm dừng
            if (paused || document.hidden) return;
            stop();
            timer = setInterval(() => go(current + 1), INTERVAL);
        }
        function restart() { stop(); start(); }

        const art = stage.closest('.hero__art');
        art.addEventListener('mouseenter', () => { paused = true; stop(); });
        art.addEventListener('mouseleave', () => { paused = false; start(); });
        art.addEventListener('touchstart', () => { paused = true; stop(); }, { passive: true });
        document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));

        /* ===== VUỐT NGANG TRÊN ẢNH ĐỂ ĐỔI SLIDE (mobile-first) =====
           Listener đặt trên khung .hero__art (bao trùm img LCP + .hero__stage)
           nên mọi điểm chạm/con trỏ trong vùng ảnh đều được theo dõi.
           - Ngưỡng ngang tối thiểu 12px + trục ngang phải thắng trục dọc 1.4 lần
             -> vuốt chéo nhẹ vẫn đổi slide; vuốt dọc KHÔNG đổi slide và trang vẫn
               cuộn bình thường (03-hero.css khai báo touch-action: pan-y).
           - setPointerCapture: theo dõi trọn cú vuốt dù ngón tay/con trỏ rời khung.
           - Bỏ qua khi điểm chạm rơi vào nút dấu chấm .hero__dot -> click chọn
             slide riêng không bị "vuốt" cướp thao tác.
           - Chặn dragstart: native image-drag của HTML5 cắt stream pointermove
             giữa chừng khiến vuốt bằng chuột không đạt ngưỡng.
           - Đầu/cuối cú vuốt gắn-xóa class .is-swiping -> CSS đổi con trỏ grab/grabbing.
           - Cuối cú vuốt gọi restart() để đồng hồ auto đếm lại từ đầu. */
        const SWIPE_MIN_X = 12;   // px — ngưỡng thấp để chuột vuốt ngắn cũng ăn
        const AXIS_RATIO = 1.4;   // trục ngang phải thắng trục dọc mức này
        let sx = 0, sy = 0, swiping = false, pid = null, captured = false;

        const endCapture = () => {
            if (captured && pid !== null) {
                try { art.releasePointerCapture(pid); } catch (_) { /* đã nhả sẵn */ }
            }
            captured = false;
            art.classList.remove('is-swiping');
        };

        art.addEventListener('pointerdown', (e) => {
            // Chuột: chỉ theo dõi khi nhấn phím trái (button 0); phím khác -> bỏ
            if (e.pointerType === 'mouse' && e.button !== 0) { swiping = false; return; }
            // Chạm vào nút dấu chấm -> trả quyền xử lý cho click của nút
            if (e.target.closest('.hero__dot')) { swiping = false; return; }
            swiping = true;
            sx = e.clientX;
            sy = e.clientY;
            pid = e.pointerId;
            captured = false;
            try { art.setPointerCapture(pid); captured = true; } catch (_) { /* browser cũ: bỏ qua */ }
            art.classList.add('is-swiping');
        });

        art.addEventListener('pointermove', (e) => {
            if (!swiping || e.pointerId !== pid) return;
            const dx = e.clientX - sx;
            const dy = e.clientY - sy;
            if (Math.abs(dx) < SWIPE_MIN_X) return;
            if (Math.abs(dx) < Math.abs(dy) * AXIS_RATIO) return; // vuốt dọc -> để trang cuộn
            swiping = false;
            // Vuốt trái (dx < 0) = slide kế tiếp; vuốt phải = slide trước (wrap-around)
            go(current + (dx < 0 ? 1 : -1));
            restart();
            endCapture();
        });

        const endSwipe = () => {
            swiping = false;
            endCapture();
        };
        art.addEventListener('pointerup', endSwipe);
        art.addEventListener('pointercancel', endSwipe);

        // Chặn kéo-thả ảnh mặc định của trình duyệt trong khung hero
        art.addEventListener('dragstart', (e) => e.preventDefault());

        start();
    }
}