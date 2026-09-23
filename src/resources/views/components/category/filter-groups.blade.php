@props([
    'children' => [],
    'filters' => [],
    'priceRanges' => [],
])

@php
    $prices = $filters['prices'] ?? [];
    $cats = $filters['cats'] ?? [];
    $rating = $filters['rating'] ?? null;

    $rangeLabel = function (array $range): string {
        if (($range['max'] ?? null) === null) {
            return 'Trên ' . format_vnd((int) $range['min']);
        }
        if ((int) $range['min'] === 0) {
            return 'Dưới ' . format_vnd((int) $range['max']);
        }
        return format_vnd((int) $range['min']) . ' – ' . format_vnd((int) $range['max']);
    };
@endphp

<div class="filter-group">
    <h3>Giá bán</h3>
    @foreach ($priceRanges as $range)
        <label class="check">
            <input type="checkbox" name="price[]" value="{{ $range['key'] }}" @checked(in_array($range['key'], $prices, true))>
            {{ $rangeLabel($range) }}
        </label>
    @endforeach
</div>

@if (count($children) > 0)
    <div class="filter-group">
        <h3>Danh mục con</h3>
        @foreach ($children as $child)
            <label class="check">
                <input type="checkbox" name="cat[]" value="{{ $child['id'] }}" @checked(in_array($child['id'], $cats, true))>
                {{ $child['name'] }} ({{ $child['count'] }})
            </label>
        @endforeach
    </div>
@endif

<div class="filter-group">
    <h3>Đánh giá</h3>
    <label class="check">
        <input type="radio" name="rating" value="4" @checked((int) $rating === 4)> ★★★★ trở lên
    </label>
    <label class="check">
        <input type="radio" name="rating" value="3" @checked((int) $rating === 3)> ★★★ trở lên
    </label>
    <label class="check">
        <input type="radio" name="rating" value="" @checked($rating === null)> Tất cả
    </label>
</div>