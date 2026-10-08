/**
 * APP PRODUCT — COUNTDOWN FLASH SALE (home + PDP dùng chung 1 engine GIỜ:PHÚT:GIÂY).
 * FIX LẦN NÀY: mốc đếm KHÔNG lấy từ database/cache nữa — JS tự tính theo GIỜ HIỆN TẠI
 * CỦA MÁY NGƯỜI XEM: đếm ngược về 00:00:00 NỬA ĐÊM (0h ngày kế tiếp), tức bắt đầu
 * phiên luôn là 23:59:59 và chạy hết vòng 24h thì tự quay lại 24:00:00.
 * Marker: .countdown[data-ends] + #cdH/#cdM/#cdS (home), [data-pd-flash] + #pdCdH/#pdCdM/#pdCdS (PDP).
 */
import { q } from '@tm/core';

/* =====================================================================
   COUNTDOWN FLASH SALE — HOME + PDP DÙNG CHUNG 1 ENGINE H:i:s
   - LOGIC MỚI: không tin data-ends (giá trị cũ do server/DB sinh, bị cache
     đóng băng => số hiển thị sai so với giờ thực tế). Mốc đếm = NỬA ĐÊM TIẾP
     THEO tính theo đồng hồ máy người xem:
         nextMidnight = today 00:00:00 + 24h
         remaining    = nextMidnight - now   (luôn trong khoảng 0..86400s)
   - Vì thế khi mở trang lúc 10:00:00 sẽ thấy 14:00:00; reload lúc nào cũng
     khớp giờ hiện tại; chạm 00:00:00 thì tick sau đó tự nhảy về 23:59:59.
   - data-ends vẫn được Blade render (contract app.js: marker .countdown[data-ends]
     / [data-pd-flash] dùng để dynamic import module) nhưng JS KHÔNG dùng để tính.
   - KHÔNG hiển thị ô "ngày": tổng giây còn lại quy hết ra giờ (max 24).
   ===================================================================== */
const pad = n => String(n).padStart(2, '0');

/* Số giây còn lại tới 00:00:00 (nửa đêm) của NGÀY MAI — theo giờ máy người xem */
function secondsUntilMidnight() {
    const now = new Date();
    const nextMidnight = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1, 0, 0, 0, 0);
    return Math.max(0, Math.floor((nextMidnight.getTime() - now.getTime()) / 1000));
}

/* Engine đếm H:i:s — mỗi tick tự tính lại từ đồng hồ hiện tại (không cộng dồn,
   nên không lệch giờ khi tab bị treo/background throttling) */
function runCountdown(hEl, mEl, sEl) {
    let timer = null;
    const tick = () => {
        const s = secondsUntilMidnight();          // 0..86399 giây còn lại tới nửa đêm
        hEl.textContent = pad(Math.floor(s / 3600));
        mEl.textContent = pad(Math.floor((s % 3600) / 60));
        sEl.textContent = pad(s % 60);
        if (s <= 0 && timer) clearInterval(timer); // vừa về 00:00:00 -> dừng 1 nhịp
        else if (s > 0 && !timer) timer = setInterval(tick, 1000); // qua nửa đêm -> chạy lại
    };
    tick();                    // vẽ ngay bằng số đúng theo giờ hiện tại (không chờ 1s)
    timer = setInterval(tick, 1000);
    return timer;
}

/* Countdown trang chủ: chỉ cần đủ bộ 3 ô #cdH/#cdM/#cdS (data-ends = marker nạp module) */
const cdHome = q('.countdown[data-ends]');
const cdH = q('#cdH'), cdM = q('#cdM'), cdS = q('#cdS');
if (cdH && cdM && cdS && cdHome) {
    runCountdown(cdH, cdM, cdS);
}

/* Countdown flash sale trên PDP — CÙNG engine, CÙNG công thức nửa đêm với home */
const pdBox = q('[data-pd-flash]');
if (pdBox) {
    const pdH = q('#pdCdH'), pdM = q('#pdCdM'), pdS = q('#pdCdS');
    if (pdH && pdM && pdS) {
        runCountdown(pdH, pdM, pdS);
    }
}