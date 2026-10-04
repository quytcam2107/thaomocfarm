/**
 * BACK-TO-TOP — loader chỉ nạp khi trang có #backToTop (hiện chỉ home bật :show-back-to-top).
 * Quy tắc: ĐẦU TRANG -> ẩn, VUỐT XUỐNG -> hiện.
 * - CSS đã mặc định ẨN (.back-to-top { visibility:hidden; opacity:0 } khai báo trong
 *   partials/12-layout-extras.css) -> kể cả khi JS chưa chạy nút cũng không hiện.
 * - 2 ngưỡng chống nhấp nháy (hysteresis): hiện khi scrollY > SHOW_AT (160px),
 *   ẩn chỉ khi scrollY < HIDE_AT (60px). Vùng 60-160px giữ nguyên trạng thái cũ,
 *   nên nút không "lúc hiện lúc không" khi trang dao động quanh một ngưỡng.
 * - Event scroll throttle bằng requestAnimationFrame; đồng bộ lại khi load/pageshow
 *   (mở tab giữa trang, restore vị trí cuộn) và khi resize (layout đổi -> hết "treo" trạng thái).
 * - Bấm nút: TỰ ĐIỀU KHIỂN cuộn bằng requestAnimationFrame (hàm easeInOutCubic,
 *   ~1 giây) -> mượt và đều hơn native smooth. Nút tự ẩn khi scrollY chạm ngưỡng
 *   HIDE_AT trong quá trình cuộn lên — không set ẩn sớm để tránh nút biến mất giữa chừng rồi hiện lại.
 * - Người dùng can thiệp (lăn chuột / cảm ứng / phím điều hướng) => hủy animation
 *   ngay, trả quyền cuộn cho người dùng. Chỉ toggle class .is-visible (không inline style).
 */
import { q } from '@tm/core';

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

    /* ===== Cuộn mượt tự điều khiển bằng rAF (easeInOutCubic, ~1 giây) =====
       - Native smooth: tốc độ do trình duyệt quyết, trang dài cuộn rất nhanh, cảm giác "vút";
         bản này duration cố định + easing -> đều và mượt hơn trên mọi thiết bị.
       - start < 0 => đang chạy animation, chặn bấm lặp (click liên tiếp không giật).
       - Hủy khi người dùng lăn chuột/cảm ứng/phím: trả quyền cuộn ngay cho user. */
    let bttRaf = -1;
    const bttStop = () => {
        if (bttRaf >= 0) { cancelAnimationFrame(bttRaf); bttRaf = -1; }
        backToTop.classList.remove('is-scrolling');   // gỡ trạng thái "đang tự cuộn" của CSS
    };
    ['wheel', 'touchstart', 'keydown'].forEach(evt =>
        window.addEventListener(evt, bttStop, { passive: true })
    );

    backToTop.addEventListener('click', () => {
        if (bttRaf >= 0) return;                       // đang cuộn thì bỏ qua click lặp
        const from = window.scrollY || document.documentElement.scrollTop || 0;
        if (from <= 1) return;                         // đã ở đầu trang
        backToTop.classList.add('is-scrolling');       // CSS đổi nền/icon báo "đang cuộn"
        const dur = Math.min(1200, Math.max(650, from * 0.8)); // ngắn->650ms, dài->max 1.2s
        const t0 = performance.now();
        const easeInOutCubic = p => p < .5 ? 4 * p * p * p : 1 - Math.pow(-2 * p + 2, 3) / 2;
        const step = now => {
            const p = Math.min(1, (now - t0) / dur);
            window.scrollTo(0, from * (1 - easeInOutCubic(p)));
            if (p < 1) bttRaf = requestAnimationFrame(step);
            else bttStop();                            // kết thúc -> giải phóng khóa
        };
        bttRaf = requestAnimationFrame(step);
    });
}