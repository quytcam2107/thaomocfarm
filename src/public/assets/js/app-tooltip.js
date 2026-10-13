
/**
 * TOOLTIP CON TRỎ TOÀN CỤC — kích hoạt bởi selector [data-tip].
 *
 * - Delegate sự kiện trên document: tương thích DOM được flash-live.js rebuild.
 * - Chờ 1 giây sau khi hover mới hiện tooltip.
 * - Rời phần tử trước 1 giây sẽ hủy hiển thị.
 * - Một node #tmTooltip duy nhất gắn trực tiếp vào <body>.
 * - Không bị giới hạn bởi overflow:hidden hoặc line-clamp của phần tử cha.
 * - Nội dung dài tự xuống dòng, không dùng innerHTML.
 * - Tooltip bám theo con trỏ và tự tránh mép viewport.
 * - Ẩn khi rời phần tử, cuộn trang hoặc nhấn Escape.
 */

const OFFSET = 14;       // Khoảng cách so với con trỏ
const EDGE = 8;          // Khoảng an toàn với mép viewport
const SHOW_DELAY = 1000; // Thời gian chờ trước khi hiện tooltip (ms)

/* ===== Tạo node tooltip đơn (singleton) ===== */

let tipEl = document.getElementById('tmTooltip');

if (!tipEl) {
    tipEl = document.createElement('div');
    tipEl.id = 'tmTooltip';
    tipEl.setAttribute('role', 'tooltip');
    tipEl.setAttribute('aria-hidden', 'true');
    document.body.appendChild(tipEl);
}

/* ===== Trạng thái ===== */

let currentTarget = null; // Phần tử đang hiển thị tooltip
let pendingTarget = null; // Phần tử đang chờ hết thời gian hover
let showTimer = null;     // Timer chờ hiển thị

let pendingX = 0;
let pendingY = 0;

/* ===== Tìm phần tử sở hữu data-tip ===== */

function resolveTip(el) {
    return el && typeof el.closest === 'function'
        ? el.closest('[data-tip]')
        : null;
}

/* ===== Định vị tooltip cạnh con trỏ ===== */

function place(x, y) {
    const r = tipEl.getBoundingClientRect();
    const vw = window.innerWidth;
    const vh = window.innerHeight;

    // Mặc định hiển thị phía dưới bên phải con trỏ
    let tx = x + OFFSET;
    let ty = y + OFFSET;

    // Nếu tràn mép phải, chuyển sang bên trái
    if (tx + r.width > vw - EDGE) {
        tx = x - OFFSET - r.width;
    }

    // Nếu tràn mép dưới, chuyển lên phía trên
    if (ty + r.height > vh - EDGE) {
        ty = y - OFFSET - r.height;
    }

    // Giữ tooltip trong phạm vi viewport
    tx = Math.max(EDGE, Math.min(tx, Math.max(EDGE, vw - r.width - EDGE)));
    ty = Math.max(EDGE, Math.min(ty, Math.max(EDGE, vh - r.height - EDGE)));

    tipEl.style.transform =
        `translate(${Math.round(tx)}px, ${Math.round(ty)}px)`;
}

/* ===== Hủy timer đang chờ ===== */

function cancelPending() {
    if (showTimer !== null) {
        clearTimeout(showTimer);
        showTimer = null;
    }

    pendingTarget = null;
}

/* ===== Hiển thị tooltip sau thời gian chờ ===== */

function show(target, x, y) {
    const text = (target.getAttribute('data-tip') || '').trim();

    if (!text) {
        hide();
        return;
    }

    // Không tạo lại timer nếu tooltip đang hiển thị trên cùng phần tử
    if (currentTarget === target) {
        place(x, y);
        return;
    }

    // Nếu đang chờ chính phần tử này thì giữ nguyên timer
    if (pendingTarget === target) {
        pendingX = x;
        pendingY = y;
        return;
    }

    // Hủy lần chờ trước đó nếu chuyển sang phần tử khác
    cancelPending();

    pendingTarget = target;
    pendingX = x;
    pendingY = y;

    showTimer = setTimeout(() => {
        showTimer = null;

        // Kiểm tra phần tử vẫn là mục tiêu đang chờ
        if (pendingTarget !== target) {
            return;
        }

        // Phần tử có thể đã bị xóa hoặc thay đổi nội dung trong lúc chờ
        if (!target.isConnected) {
            hide();
            return;
        }

        const latestText = (target.getAttribute('data-tip') || '').trim();

        if (!latestText) {
            hide();
            return;
        }

        tipEl.textContent = latestText;

        // Cập nhật trạng thái trước khi định vị
        currentTarget = target;
        pendingTarget = null;

        tipEl.classList.add('is-visible');
        tipEl.setAttribute('aria-hidden', 'false');

        place(pendingX, pendingY);
    }, SHOW_DELAY);
}

/* ===== Ẩn tooltip và hủy mọi timer ===== */

function hide() {
    cancelPending();

    currentTarget = null;

    tipEl.classList.remove('is-visible');
    tipEl.setAttribute('aria-hidden', 'true');
}

/* ===== Delegate sự kiện trên document ===== */

// Bắt đầu hover: chờ 1 giây trước khi hiện tooltip
document.addEventListener('mouseover', (e) => {
    const tip = resolveTip(e.target);

    if (!tip) {
        return;
    }

    if (tip === currentTarget) {
        return;
    }

    if (tip === pendingTarget) {
        return;
    }

    show(tip, e.clientX, e.clientY);
});

// Theo dõi con trỏ trong cả thời gian chờ và khi tooltip đang hiển thị
document.addEventListener('mousemove', (e) => {
    const hoveredTip = resolveTip(e.target);

    // Đang chờ hiển thị tooltip
    if (pendingTarget) {
        if (hoveredTip !== pendingTarget) {
            hide();
            return;
        }

        // Lưu tọa độ mới nhất để tooltip xuất hiện đúng vị trí
        pendingX = e.clientX;
        pendingY = e.clientY;
    }

    // Tooltip chưa hiển thị thì không cần định vị
    if (!currentTarget) {
        return;
    }

    // Con trỏ đã rời phần tử sở hữu tooltip
    if (hoveredTip !== currentTarget) {
        hide();
        return;
    }

    // Bám theo con trỏ
    place(e.clientX, e.clientY);
}, { passive: true });

// Rời phần tử: ẩn tooltip hoặc hủy thời gian chờ
document.addEventListener('mouseout', (e) => {
    const fromTip = resolveTip(e.target);

    if (!fromTip) {
        return;
    }

    const toTip = resolveTip(e.relatedTarget);

    // Di chuyển giữa các phần tử con của cùng một vùng data-tip
    // không được tính là rời khỏi phần tử
    if (toTip === fromTip) {
        return;
    }

    if (fromTip === pendingTarget || fromTip === currentTarget) {
        hide();
    }
});

// Ẩn tooltip khi cuộn trang
window.addEventListener('scroll', () => {
    if (currentTarget || pendingTarget) {
        hide();
    }
}, { passive: true });

// Ẩn khi con trỏ rời khỏi document
document.addEventListener('mouseleave', hide);

// Nhấn Escape để ẩn tooltip
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        hide();
    }
});

// Ẩn nếu phần tử mục tiêu bị xóa khỏi DOM trong lúc chờ
// hoặc trong khi tooltip đang hiển thị
const tooltipObserver = new MutationObserver(() => {
    if (
        (pendingTarget && !pendingTarget.isConnected) ||
        (currentTarget && !currentTarget.isConnected)
    ) {
        hide();
    }
});

tooltipObserver.observe(document.body, {
    childList: true,
    subtree: true
});
