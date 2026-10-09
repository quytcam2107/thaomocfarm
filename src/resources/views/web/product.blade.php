<x-layouts.app :title="$product->name . ' - ' . number_format($product->price) . '₫ | Mộc Xanh'"
    :seoDescription="$product->meta_description" ogType="product" :ogImage="asset($product->image)"
    bodyClass="has-buybar">

    <x-slot name="schema">
        <x-product.schema :product="$product" />
    </x-slot>

    {{-- NEW LIGHTGALLERY: riêng trang chi tiết sản phẩm nạp vendor CSS + JS
    (self-host public/assets/vendor/lightgallery — xem lệnh tải Bước 0).
    THỨ TỰ JS PHẢI ĐÚNG: core -> plugin -> (app.js do layout render sau stack). --}}
    @push('vendorStyles')
        <link rel="stylesheet" href="{{ asset('assets/vendor/lightgallery/lightgallery-bundle.min.css') }}">
        {{-- NEW REVIEW: style widget sao rateyo (self-host, cùng convention lightgallery) --}}
        <link rel="stylesheet" href="{{ asset('assets/vendor/rateyo/jquery.rateyo.min.css') }}">
    @endpush

    {{-- NEW REVIEW: vendorScripts chạy TRƯỚC app.js (layout @stack). jQuery full
    (bản slim thiếu hiệu ứng needed bởi rateyo) + jquery.rateyo.min.js. Chỉ PDP nạp. --}}
    @push('vendorScripts')
        <script src="{{ asset('assets/vendor/lightgallery/lightgallery.umd.min.js') }}" defer></script>
        <script src="{{ asset('assets/vendor/lightgallery/lg-zoom.umd.min.js') }}" defer></script>
        <script src="{{ asset('assets/vendor/lightgallery/lg-thumbnail.umd.min.js') }}" defer></script>
        <script src="{{ asset('assets/vendor/lightgallery/lg-autoplay.umd.min.js') }}" defer></script>
        <script src="{{ asset('assets/vendor/lightgallery/lg-share.umd.min.js') }}" defer></script>
        <script src="{{ asset('assets/vendor/lightgallery/lg-fullscreen.umd.min.js') }}" defer></script>
        <script src="{{ asset('assets/vendor/lightgallery/lg-pager.umd.min.js') }}" defer></script>
        <script src="{{ asset('assets/vendor/jquery.min.js') }}" defer></script>
        <script src="{{ asset('assets/vendor/rateyo/jquery.rateyo.min.js') }}" defer></script>
    @endpush

    <div class="container">
        <x-ui.breadcrumb :items="$breadcrumbs" />

        <div class="pd-layout">
            {{-- hasFlashSale: seed ban đầu; sau 3 phút JS có thể bật block nếu SP vào deal mới,
            nhưng vị trí nút chia sẻ giữ nguyên theo dữ liệu render lần đầu. --}}
            <x-product.gallery :images="$images" :thumbs="$imageThumbs ?? null" :alt="$product->name"
                :share-url="route('web.product.show', $product->slug)" :share-title="$product->name"
                :has-flash-sale="$product->flashSale !== null" />
            <x-product.info :product="$product" :variants="$variants" />
        </div>

        <x-product.tabs :description="$product->description" :reviews="$reviews" :ratingStats="$ratingStats"
            :product-slug="$product->slug" :product-name="$product->name" :product="$product" :variants="$variants"
            :category="$category ?? null" :product-specs="$product_specs ?? []" />

        <x-product.related :products="$relatedProducts" />
    </div>

    <x-slot name="extra">
        <x-product.buybar :product="$product" />

        {{-- FLASH LIVE: cập nhật block .pd-flash + giá deal mỗi 3 phút (assets/js/flash-live.js) --}}
        <script type="module" src="{{ asset('assets/js/flash-live.js') }}"></script>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const variants = @json($variants);

                // 2. Variant Selection (logic doc lap, giu lai tai day)
                const radios = document.querySelectorAll('input[name="variant_id"]');
                const priceEl = document.querySelector('.pd-price .price');
                const oldPriceEl = document.querySelector('.pd-price s');
                const offEl = document.querySelector('.pd-price .off');
                const qtyInput = document.querySelector('.qty input');
                const buybarPrice = document.querySelector('.buybar__price');

                const formatVND = (num) => new Intl.NumberFormat('vi-VN').format(num) + '₫';

                radios.forEach(radio => {
                    radio.addEventListener('change', (e) => {
                        const selected = variants.find(v => v.value === e.target.value);
                        if (!selected) return;

                        priceEl.textContent = formatVND(selected.price);
                        if (buybarPrice) buybarPrice.textContent = formatVND(selected.price);

                        if (selected.old_price && selected.old_price > selected.price) {
                            oldPriceEl.textContent = formatVND(selected.old_price);
                            oldPriceEl.style.display = 'inline';
                            const percent = Math.round((1 - selected.price / selected.old_price) * 100);
                            offEl.textContent = `-${percent}%`;
                            offEl.style.display = 'inline';
                        } else {
                            oldPriceEl.style.display = 'none';
                            offEl.style.display = 'none';
                        }

                        qtyInput.max = selected.stock;
                        if (parseInt(qtyInput.value) > selected.stock) {
                            qtyInput.value = selected.stock;
                        }
                    });
                });

                // 3. Quantity Stepper
                // FIX BUG: nút +/- trong <div class="qty" data-qty> không hoạt động.
                // Nguyên nhân: Blade (components/product/info.blade.php) render nút với
                // data-step="-1" và data-step="1" (đúng convention trang Giỏ hàng),
                // còn code cũ so sánh step === 'inc' / 'dec' -> không khớp bao giờ,
                // giá trị bị gán lại y nguyên => bấm không thấy phản ứng.
                // Fix: đọc data-step bằng parseInt (hỗ trợ "-1"/"1", đồng thời vẫn
                // tương thích ngược nếu nơi nào đó còn dùng 'inc'/'dec'), clamp
                // trong khoảng [min, max] lấy từ chính input (min=1, max=stock variant).
                const qtyContainer = document.querySelector('[data-qty]');
                if (qtyContainer && qtyInput) {
                    qtyContainer.addEventListener('click', (e) => {
                        const btn = e.target.closest('[data-step]');
                        if (!btn || btn.disabled) return;

                        const raw = btn.dataset.step;
                        let delta = parseInt(raw, 10);
                        // Tương thích ngược: bản markup cũ dùng 'inc'/'dec'
                        if (isNaN(delta)) delta = (raw === 'dec') ? -1 : (raw === 'inc') ? 1 : 0;
                        if (delta === 0) return;

                        const min = parseInt(qtyInput.min, 10) || 1;
                        const max = parseInt(qtyInput.max, 10) || 99;
                        let val = parseInt(qtyInput.value, 10);
                        if (isNaN(val)) val = min;

                        qtyInput.value = Math.min(max, Math.max(min, val + delta));
                    });
                }
            });
        </script>
    </x-slot>
</x-layouts.app>