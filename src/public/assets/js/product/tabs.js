/**
 * APP PRODUCT — TABS ARIA: chuyển panel theo [role="tab"] + aria-controls/aria-selected.
 * Tách từ app-product.js cũ (dòng 540–550) — logic GIỮ NGUYÊN BẢN, chỉ thêm dòng import.
 */
import { q, qa } from '@tm/core';

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