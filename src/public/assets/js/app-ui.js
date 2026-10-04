/**
 * APP UI — drawer header (burger) + Escape đóng drawer + clone bộ lọc sang drawer mobile.
 * Loader chỉ nạp module này khi trang có #burgerBtn hoặc cặp #filterGroups/#filterDrawerBody.
 */
import { q, qa } from '@tm/core';

const burger = q('#burgerBtn'), drawer = q('#drawer');
const lock = on => document.body.classList.toggle('is-locked', on);
if (burger && drawer) {
    burger.addEventListener('click', () => { drawer.classList.add('is-open'); lock(true); burger.setAttribute('aria-expanded', 'true'); });
}
qa('[data-drawer-open]').forEach(btn => btn.addEventListener('click', () => {
    const d = q(btn.getAttribute('data-drawer-open')); if (!d) return;
    d.classList.add('is-open'); lock(true); btn.setAttribute('aria-expanded', 'true');
}));
qa('[data-drawer-close]').forEach(btn => btn.addEventListener('click', () => {
    const d = btn.closest('.drawer'); if (!d) return;
    d.classList.remove('is-open'); lock(false);
    const opener = q('[data-drawer-open="#' + d.id + '"]') || (d.id === 'drawer' ? q('#burgerBtn') : null);
    if (opener) opener.setAttribute('aria-expanded', 'false');
}));
document.addEventListener('keydown', e => {
    if (e.key !== 'Escape') return;
    qa('.drawer.is-open').forEach(d => d.classList.remove('is-open'));
    lock(false);
});

/* Clone bộ lọc sang drawer mobile */
const fg = q('#filterGroups'), fd = q('#filterDrawerBody');
if (fg && fd) fd.innerHTML = fg.innerHTML;