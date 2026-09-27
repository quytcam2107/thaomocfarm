{{-- Trang tất cả sản phẩm --}}
@php
    $seoDescription = $pageDescription;

    $clearUrl = route('web.products.index');

    // URL bỏ bớt 1 điều kiện lọc: $value = null => xoá cả key
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

    // Dựng danh sách chip từ bộ lọc đang áp dụng
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

<x-layouts.app :title="$pageTitle . ' | Thảo Mộc Farm'" :seoDescription="$seoDescription" ogType="website"
    :ogImage="$ogImage" bodyClass="page-category">

    <x-slot name="schema">
        <script type="application/ld+json">
            {!! json_encode($breadcrumb_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    </x-slot>

    <div class="container">
        <x-ui.breadcrumb :items="$breadcrumbs" />

        <div class="page-head">
            <h1>{{ $pageTitle }}</h1>
            <p>
                {{ $meta['total'] }} sản phẩm
                — Tinh hoa đặc sản vùng cao, cam kết chuẩn gốc Tây Bắc
            </p>
        </div>

        <x-category.toolbar :total="$meta['total']" :from="$meta['from']" :to="$meta['to']" :sortOptions="$sortOptions"
            :currentSort="$filters['sort']" />

        <x-category.chips :chips="$chips" />

        <div class="cat-layout">
            <x-category.filters :action="$clearUrl" :clearUrl="$clearUrl" :children="$children" :filters="$filters"
                :priceRanges="$priceRanges" />

            <div class="cat-main">
                <div class="product-grid">
                    @forelse ($products as $item)
                        <x-ui.product-card :url="$item['url']" :image="$item['image']" :name="$item['name']"
                            :price="$item['price']" :oldPrice="$item['oldPrice']" :discount="$item['discount']"
                            :rating="$item['rating']" :sold="$item['sold']" />
                    @empty
                        <div class="seo-text">
                            <p>Chưa có sản phẩm nào phù hợp với bộ lọc hiện tại.</p>
                            <p><a class="btn btn--leaf" href="{{ $clearUrl }}">Xoá bộ lọc</a></p>
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
            :children="$children" :filters="$filters" :priceRanges="$priceRanges" />

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
                                const badge = document.getElementById('cartBadge');
                                if (badge) {
                                    badge.textContent = (parseInt(badge.textContent) || 0) + 1;
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