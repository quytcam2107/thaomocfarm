@props([
    'total' => 0,
    'from' => 0,
    'to' => 0,
    'sortOptions' => [],
    'currentSort' => 'bestsell',
])

<div class="toolbar">
    <p class="toolbar__count">Hiển thị <b>{{ $from }}–{{ $to }}</b> / {{ $total }} sản phẩm</p>
    <div class="toolbar__right">
        <label class="sr-only" for="sort">Sắp xếp sản phẩm</label>
        <select id="sort" name="sort" form="filterForm" class="sort-select">
            @foreach ($sortOptions as $key => $optionLabel)
                <option value="{{ $key }}" @selected($currentSort === $key)>{{ $optionLabel }}</option>
            @endforeach
        </select>
        <button class="filter-btn" type="button" data-drawer-open="#filterDrawer" aria-expanded="false"
            aria-controls="filterDrawer">⚙ Bộ lọc</button>
    </div>
</div>