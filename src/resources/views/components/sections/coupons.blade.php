@props(['coupons' => null])

@if(is_array($coupons) && count($coupons) > 0)
    <section class="container coupons reveal" aria-labelledby="cpnTitle">
        <h2 class="sec-title" id="cpnTitle">Mã giảm giá</h2>
        <div class="cpn-rail no-scrollbar" role="region" aria-label="Mã giảm giá, cuộn ngang" tabindex="0">
            @foreach($coupons as $c)
                <x-ui.coupon-card :code="$c['code']" :desc="$c['desc']" :min-order="$c['minOrder']" :exp="$c['exp']" />
            @endforeach
        </div>
    </section>

    @push('scripts')
        <script>
            (function () {
                // Guard: chỉ bind 1 lần dù component render lại
                if (window.__tmHomeCouponCopyBound) return;
                window.__tmHomeCouponCopyBound = true;

                document.addEventListener('click', function (e) {
                    // Chỉ bắt nút copy TRONG block coupon trang chủ (không đụng trang giỏ)
                    const btn = e.target.closest('.coupons .copy-btn');
                    if (!btn || btn.disabled) return;

                    const code = (btn.dataset.code || '').trim().toUpperCase();
                    if (!code) return;

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(code).catch(function () { fallbackCopy(code); });
                    } else {
                        fallbackCopy(code);
                    }

                    const toast = document.getElementById('toast');
                    if (toast) {
                        toast.textContent = 'Đã sao chép mã ' + code + ' — dùng ở trang giỏ hàng';
                        toast.classList.add('show');
                        clearTimeout(window.__tmHomeCouponCopyTimer);
                        window.__tmHomeCouponCopyTimer = setTimeout(function () {
                            toast.classList.remove('show');
                        }, 2200);
                    }
                });

                function fallbackCopy(text) {
                    const ta = document.createElement('textarea');
                    ta.value = text;
                    ta.style.position = 'fixed';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.select();
                    try { document.execCommand('copy'); } catch (err) { /* ignore */ }
                    document.body.removeChild(ta);
                }
            })();
        </script>
    @endpush
@endif