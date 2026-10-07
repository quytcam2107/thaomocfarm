<x-layouts.app :title="$product->name . ' - ' . number_format($product->price) . '₫ | Mộc Xanh'"
    :seoDescription="$product->meta_description" ogType="product" :ogImage="asset($product->image)"
    bodyClass="has-buybar">

    <x-slot name="schema">
        <x-product.schema :product="$product" />
    </x-slot>

    {{-- NEW LIGHTGALLERY: riêng trang chi tiết sản phẩm nạp vendor CSS + JS
    (self-host public/assets/vendor/lightgallery — xem lệnh tải Bước 0).
    THỨ TỰ JS PHẢI ĐÚNG: core -> plugin -> (app.js do layout render sau stack).
    Plugin caption KHÔNG tồn tại trong lightgallery@2.8.x — subHtml do core
    render nên không cần nạp. --}}
    @push('vendorStyles')
        <link rel="stylesheet" href="{{ asset('assets/vendor/lightgallery/lightgallery-bundle.min.css') }}">
        {{-- NEW REVIEW: style widget sao rateyo (self-host, cùng convention lightgallery) --}}
        <link rel="stylesheet" href="{{ asset('assets/vendor/rateyo/jquery.rateyo.min.css') }}">
    @endpush

    {{-- NEW REVIEW: vendorScripts chạy TRƯỚC app.js (layout @stack). jQuery full
    (bản slim thiếu hiệu ứng needed bởi rateyo) + jquery.rateyo.min.js. Chỉ PDP nạp.
    FIX 404: rateyo dùng bản 2.3.4 path /min/ (npm không có 1.4.2/src); jQuery đặt
    thẳng tại assets/vendor/jquery.min.js theo cấu trúc folder đã chốt. --}}
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
            {{-- Truyền thêm shareUrl/shareTitle/hasFlashSale: hàng nút chia sẻ chỉ hiện
            khi sản phẩm thuộc flash sale (lấp khoảng trống dưới pd-thumbs).
            Route web.product.show dùng đúng $product->slug (cột slug bảng products). --}}
            <x-product.gallery :images="$images" :thumbs="$imageThumbs ?? null" :alt="$product->name"
                :share-url="route('web.product.show', $product->slug)" :share-title="$product->name"
                :has-flash-sale="$product->flashSale !== null" />
            <x-product.info :product="$product" :variants="$variants" />
        </div>

        {{-- NEW REVIEW: truyền slug + tên SP để khối đánh giá gọi đúng route
        web.product.reviews.store / web.review.helpful --}}
        <x-product.tabs :description="$product->description" :reviews="$reviews" :ratingStats="$ratingStats"
            :product-slug="$product->slug" :product-name="$product->name" :product="$product" :variants="$variants"
            :category="$category ?? null" />

        <x-product.related :products="$relatedProducts" />
    </div>

    <x-slot name="extra">
        <x-product.buybar :product="$product" />

        {{-- FIX ĐỒNG BỘ: countdown PDP do app.js engine dùng chung với home điều khiển
        (data-ends = unix end_at của phiên) — KHÔNG nạp pdp-flash.js riêng nữa,
        vì 2 script đếm 2 kiểu khác nhau khiến Home và PDP lệch giờ. --}}

        {{-- FIX TRAN NGANG PDP: XOA script inline trung lap truoc day (gallery thumbs,
        variant, qty, tabs) vi no trung voi module ES public/assets/js/app-product.js
        (duoc app.js loader nap khi DOM co .pd-thumbs / [role="tab"] / #buyNow).
        Script cu chay bang DOMContentLoaded khong co scrollIntoView -> tranh chap
        click handler voi module, khi nhieu anh thumb bi cuon vang kho tam nhin.
        Module app-product.js giu nguyen toan bo logic cu + them cuon thumb vao tam
        nhin, nen PDP van chay du thieu script inline nay. --}}
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
                const qtyContainer = document.querySelector('[data-qty]');
                if (qtyContainer) {
                    qtyContainer.addEventListener('click', (e) => {
                        const step = e.target.dataset.step;
                        if (!step) return;
                        let val = parseInt(qtyInput.value) || 1;
                        const max = parseInt(qtyInput.max) || 99;

                        if (step === '1' && val < max) qtyInput.value = val + 1;
                        if (step === '-1' && val > 1) qtyInput.value = val - 1;
                    });
                }
            });
        </script>
    </x-slot>

</x-layouts.app>