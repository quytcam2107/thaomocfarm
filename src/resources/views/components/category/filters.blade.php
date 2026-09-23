@props([
    'action' => '',
    'clearUrl' => '',
    'children' => [],
    'filters' => [],
    'priceRanges' => [],
])

<aside class="filters" aria-label="Bộ lọc sản phẩm">
    <form id="filterForm" method="GET" action="{{ $action }}">
        <div id="filterGroups">
            <x-category.filter-groups :children="$children" :filters="$filters" :priceRanges="$priceRanges" />
        </div>
        <div class="filter-actions">
            <button class="btn btn--leaf" type="submit">Áp dụng</button>
            <a class="btn btn--ghost" href="{{ $clearUrl }}">Xoá lọc</a>
        </div>
    </form>
</aside>