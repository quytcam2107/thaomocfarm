@props([
    'action' => '',
    'clearUrl' => '',
    'children' => [],
    'filters' => [],
    'priceRanges' => [],
    'keyword' => null,
])

<aside class="filters" aria-label="Bộ lọc sản phẩm">
    <form id="filterForm" method="GET" action="{{ $action }}">
        @if ($keyword !== null && $keyword !== '')
            <input type="hidden" name="q" value="{{ $keyword }}">
        @endif
        <div id="filterGroups">
            <x-category.filter-groups :children="$children" :filters="$filters" :priceRanges="$priceRanges" />
        </div>
        <div class="filter-actions">
            <button class="btn btn--leaf" type="submit">Áp dụng</button>
            <a class="btn btn--ghost" href="{{ $clearUrl }}">Xoá lọc</a>
        </div>
    </form>
</aside>