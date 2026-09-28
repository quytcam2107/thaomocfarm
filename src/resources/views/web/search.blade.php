{{-- Trang kết quả tìm kiếm --}}
@php
    $seoDescription = $pageDescription;
    $clearUrl = route('web.search.index', ['q' => $keyword]);

    $removeUrl = function (string $key, ?string $value = null) use ($clearUrl): string {
        $query = request()->query();
        if ($value === null) {
            unset($query[$key]);
        } elseif (isset($query[$key]) && is_array($query[$key])) {
            $query[$key] = array_values(array_diff($query[$key], [$value]));
            if ($query[$key] === []) {
                unset($query[$key]);
            }
        }
        unset($query['page']);
        $qs = http_build_query($query);
        return $clearUrl . ($qs !== '' ? '?' . $qs : '');
    };

    $chips = [];
    foreach ($filters['prices'] as $key) {
        $range = collect($priceRanges)->firstWhere('key', $key);
        if ($range !== null) {
            $label = ($range['max'] === null)
                ? 'Trên ' . format_vnd($range['min'])
                : (($range['min'] === 0) ? 'Dưới ' . format_vnd($range['max']) : format_vnd($range['min']) . ' – ' . format_vnd($range['max']));
            $chips[] = ['label' => 'Giá: ' . $label, 'url' => $removeUrl('price', $key)];
        }
    }
    if ($filters['rating'] !== null) {
        $chips[] = ['label' => $filters['rating'] . '★ trở lên', 'url' => $removeUrl('rating')];
    }
    if ($filters['sort'] !== 'bestsell') {
        $chips[] = ['label' => 'Sắp xếp: ' . $sortOptions[$filters['sort']], 'url' => $removeUrl('sort')];
    }

    $ogImage = count($products) > 0 ? $products[0]['image'] : 'images/product-default.svg';
@endphp

<x-layouts.app :title="$pageTitle . ' | Mộc Xanh'" :seoDescription="$seoDescription" ogType="website"
    :ogImage="$ogImage" bodyClass="page-category">

    <x-slot name="schema">
        <script type="application/ld+json">
            {!! json_encode($breadcrumb_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    </x-slot>

    <div class="container">
        <x-ui.breadcrumb :items="$breadcrumbs" />

        <div class="page-head">
            <h1>Kết quả tìm kiếm: “{{ $keyword }}”</h1>
            <p>
                {{ $meta['total'] }} sản phẩm
                @if ($meta['total'] === 0)
                    — thử từ khoá khác như: tam thất, trà hoa, táo đỏ, mắc khén...
                @else
                    — khớp với từ khoá bạn tìm, cam kết chuẩn gốc Tây Bắc
                @endif
            </p>
        </div>

        <x-category.toolbar :total="$meta['total']" :from="$meta['from']" :to="$meta['to']" :sortOptions="$sortOptions"
            :currentSort="$filters['sort']" />

        <x-category.chips :chips="$chips" />

        <div class="cat-layout">
            <x-category.filters :action="$clearUrl" :clearUrl="$clearUrl" :children="$children" :filters="$filters"
                :priceRanges="$priceRanges" :keyword="$keyword" />

            <div class="cat-main">
                <div class="product-grid">
                    @forelse ($products as $item)
                        <x-ui.product-card :url="$item['url']" :image="$item['image']" :name="$item['name']"
                            :price="$item['price']" :oldPrice="$item['oldPrice']" :discount="$item['discount']"
                            :rating="$item['rating']" :sold="$item['sold']" />
                    @empty
                        <div class="seo-text">
                            <p>Không tìm thấy sản phẩm nào khớp với từ khoá “{{ $keyword }}”.</p>
                            <p><a class="btn btn--leaf" href="{{ route('web.products.index') }}">Xem tất cả sản phẩm</a></p>
                        </div>
                    @endforelse
                </div>

                <x-ui.pagination :meta="$meta" />

                <x-category.seo-text :heading="$seoText['heading']" :paragraph="$seoText['paragraph']"
                    :subheading="$seoText['subheading']" :tips="$seoText['tips']" />
            </div>
        </div>
    </div>

    <x-slot name="extra">
        <x-category.filter-drawer :action="$clearUrl" :clearUrl="$clearUrl" :currentSort="$filters['sort']"
            :children="$children" :filters="$filters" :priceRanges="$priceRanges" :keyword="$keyword" />

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const sortSelect = document.getElementById('sort');
                if (sortSelect) {
                    sortSelect.addEventListener('change', () => {
                        const form = document.getElementById('filterForm');
                        if (form) form.submit();
                    });
                }

                document.querySelectorAll('.chip button[data-href]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        window.location.assign(btn.dataset.href);
                    });
                });

                const products = @json($products);
                const idByUrl = {};
                products.forEach(p => { idByUrl[p.url] = p.id; });

                document.querySelectorAll('.product-grid .add-cart').forEach(btn => {
                    btn.addEventListener('click', async () => {
                        const card = btn.closest('.pcard');
                        const link = card ? card.querySelector('a[href]') : null;
                        if (!link) return;

                        const productId = idByUrl[link.href];
                        if (!productId) return;

                        const original = btn.innerHTML;
                        btn.disabled = true;
                        btn.innerHTML = '…';

                        try {
                            const res = await fetch('{{ route('web.cart.add') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({
                                    product_id: productId,
                                    variant_id: null,
                                    qty: 1,
                                }),
                            });

                            if (res.ok) {
                                btn.innerHTML = '✓';

                                // ✅ Đồng bộ badge floatnav (mobile) + header__acts (desktop)
                                let newCount = null;
                                try {
                                    const data = await res.clone().json();
                                    if (typeof data.cartCount === 'number') newCount = data.cartCount;
                                } catch (e) { /* ignore parse error */ }

                                if (newCount === null) {
                                    const first = document.querySelector('.js-cart-count');
                                    newCount = (parseInt(first?.textContent, 10) || 0) + 1;
                                }

                                if (typeof window.updateCartCount === 'function') {
                                    window.updateCartCount(newCount);
                                } else {
                                    document.querySelectorAll('.js-cart-count').forEach(badge => {
                                        badge.textContent = newCount;
                                        badge.hidden = newCount === 0;
                                    });
                                }

                                setTimeout(() => {
                                    btn.innerHTML = original;
                                    btn.disabled = false;
                                }, 1500);
                            } else {
                                throw new Error('add-cart failed');
                            }
                        } catch (e) {
                            alert('Có lỗi xảy ra, vui lòng thử lại!');
                            btn.innerHTML = original;
                            btn.disabled = false;
                        }
                    });
                });
            });
        </script>
    </x-slot>
</x-layouts.app>