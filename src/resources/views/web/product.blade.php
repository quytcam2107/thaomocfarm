<x-layouts.app :title="$product->name . ' - ' . number_format($product->price) . '₫ | Thảo Mộc Xanh'"
    :seoDescription="$product->meta_description" ogType="product" :ogImage="asset($product->image)"
    bodyClass="has-buybar">

    <x-slot name="schema">
        <x-product.schema :product="$product" />
    </x-slot>

    <div class="container">
        <x-ui.breadcrumb :items="$breadcrumbs" />

        <div class="pd-layout">
            <x-product.gallery :images="$images" :alt="$product->name" />
            <x-product.info :product="$product" :variants="$variants" />
        </div>

        <x-product.tabs :description="$product->description" :reviews="$reviews" :ratingStats="$ratingStats" />

        <x-product.related :products="$relatedProducts" />
    </div>

    <x-slot name="extra">
        <x-product.buybar :product="$product" />

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const variants = @json($variants);

                // 1. Gallery Thumbnail Switching
                const thumbs = document.querySelectorAll('.pd-thumbs button');
                const stageImg = document.getElementById('pdStageImg');
                thumbs.forEach(btn => {
                    btn.addEventListener('click', () => {
                        stageImg.src = btn.dataset.full;
                        thumbs.forEach(b => b.setAttribute('aria-current', 'false'));
                        btn.setAttribute('aria-current', 'true');
                    });
                });

                // 2. Variant Selection
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

                // 4. Tabs
                const tabs = document.querySelectorAll('[role="tab"]');
                const panels = document.querySelectorAll('[role="tabpanel"]');
                tabs.forEach(tab => {
                    tab.addEventListener('click', () => {
                        tabs.forEach(t => {
                            t.setAttribute('aria-selected', 'false');
                            t.tabIndex = -1;
                        });
                        panels.forEach(p => p.hidden = true);

                        tab.setAttribute('aria-selected', 'true');
                        tab.tabIndex = 0;
                        const panel = document.getElementById(tab.getAttribute('aria-controls'));
                        if (panel) panel.hidden = false;
                    });
                });

            });
        </script>
    </x-slot>

</x-layouts.app>