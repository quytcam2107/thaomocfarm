/* =====================================================================
   PDP FLASH SALE — countdown theo end_at của phiên flash sale
   - Chỉ chạy khi trang có [data-pd-flash] (component product/flash-block)
   - Đọc data-ends (unix giây, giờ server) -> bù trừ lệch đồng hồ máy khách
   - Dùng id riêng #pdCdD/H/M/S => KHÔNG đụng #cdH/#cdM/#cdS của app.js (home)
   - Hết giờ: ẩn block + toast báo kết thúc (reuse #toast nếu có)
   ===================================================================== */
(function () {
    'use strict';

    var box = document.querySelector('[data-pd-flash]');
    if (!box) return;

    var endsUnix = parseInt(box.dataset.ends, 10);
    if (!endsUnix || isNaN(endsUnix)) return;

    var elD = document.getElementById('pdCdD'),
        elH = document.getElementById('pdCdH'),
        elM = document.getElementById('pdCdM'),
        elS = document.getElementById('pdCdS');
    if (!elH || !elM || !elS) return;

    // Bù lệch: server_time (nếu page có render meta) — fallback Date.now()
    var serverTimeEl = document.querySelector('meta[name="server-time"]');
    var offsetMs = serverTimeEl
        ? (parseInt(serverTimeEl.content, 10) * 1000) - Date.now()
        : 0;

    var pad = function (n) { return String(n).padStart(2, '0'); };

    function tick() {
        var now = Date.now() + offsetMs;
        var s = Math.floor((endsUnix * 1000 - now) / 1000);

        if (s <= 0) {
            elD && (elD.textContent = '00');
            elH.textContent = '00';
            elM.textContent = '00';
            elS.textContent = '00';
            clearInterval(timer);
            // Kết thúc phiên: ẩn gọn block, không để 00:00:00 treo
            box.classList.add('is-ended');
            var t = document.getElementById('toast');
            if (t) {
                t.textContent = 'Flash sale đã kết thúc — giá đã về mức thường';
                t.classList.add('show');
                setTimeout(function () { t.classList.remove('show'); }, 2500);
            }
            return;
        }

        var d = Math.floor(s / 86400);
        elH.textContent = pad(Math.floor((s % 86400) / 3600));
        elM.textContent = pad(Math.floor((s % 3600) / 60));
        elS.textContent = pad(s % 60);

        if (elD) {
            elD.parentElement && (elD.parentElement.style.display = d > 0 ? '' : 'none');
            elD.textContent = pad(d);
        }
    }

    tick();
    var timer = setInterval(tick, 1000);
})();