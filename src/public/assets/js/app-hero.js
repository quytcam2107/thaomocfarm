/**
 * APP HERO — slideshow khối .hero__art trang chủ (crossfade + Ken Burns nhẹ).
 * - Blade render sẵn slide ĐẦU trong HTML (LCP) + div.hero__stage mang
 *   data-hero-slides = JSON các ảnh còn lại (config thaomoc.hero_slides).
 * - JS nhân bản img LCP làm lớp 0, chèn các lớp còn lại vào .hero__stage,
 *   tạo dấu chấm chọn slide; tự chuyển mỗi 5s.
 * - Chỉ đổi class .is-active -> CSS lo animation; tôn trọng prefers-reduced-motion
 *   (không Ken Burns, chuyển chậm hơn).
 */
import { q, reduceMotion } from '@tm/core';

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
        const INTERVAL = reduceMotion ? 9000 : 5000; // giây/slide (lâu hơn khi giảm chuyển động)
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

        // Dấu chấm chọn slide (góc phải dưới, không đè badge)
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

        start();
    }
}