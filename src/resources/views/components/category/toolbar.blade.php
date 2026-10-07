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
        {{-- Sửa lỗi text lệch trên mobile: icon ⚙ là span trang trí (aria-hidden),
        nhãn "Bộ lọc" nằm trong span riêng để flex căn giữa ổn định, không bị
        baseline emoji kéo chữ lệch lên/xuống --}}
        <button class="filter-btn" type="button" data-drawer-open="#filterDrawer" aria-expanded="false"
            aria-controls="filterDrawer"><span class="filter-btn__icon" aria-hidden="true">⚙</span><span
                class="filter-btn__label">Bộ lọc</span></button>
    </div>
</div>