@props([
    'action' => '',
    'clearUrl' => '',
    'currentSort' => 'bestsell',
    'children' => [],
    'filters' => [],
    'priceRanges' => [],
    'keyword' => null,
])

<div class="drawer" id="filterDrawer" role="dialog" aria-modal="true" aria-label="Bộ lọc sản phẩm">
    <div class="drawer__overlay" data-drawer-close></div>
    <div class="drawer__panel drawer__panel--right">
        <p class="drawer__head">⚙ Bộ lọc
            <button class="drawer__close" type="button" data-drawer-close aria-label="Đóng bộ lọc">✕</button>
        </p>
        <div class="drawer__filter">
            <form id="filterFormMobile" method="GET" action="{{ $action }}">
                @if ($keyword !== null && $keyword !== '')
                    <input type="hidden" name="q" value="{{ $keyword }}">
                @endif
                <input type="hidden" name="sort" value="{{ $currentSort }}">
                <div id="filterDrawerBody">
                    <x-category.filter-groups :children="$children" :filters="$filters" :priceRanges="$priceRanges" />
                </div>
                <div class="filter-actions">
                    <button class="btn btn--leaf btn--block" type="submit">Xem kết quả</button>
                    <a class="btn btn--ghost btn--block" href="{{ $clearUrl }}">Xoá lọc</a>
                </div>
            </form>
        </div>
    </div>
</div>