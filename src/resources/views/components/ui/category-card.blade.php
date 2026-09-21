@props(['url' => '#', 'image' => '', 'name' => '', 'count' => 0])
<a class="col-card" href="{{ $url }}">
    <img class="col-card__img" src="{{ asset($image) }}" alt="Danh mục {{ $name }}" width="300" height="300"
        loading="lazy">
    <b>{{ $name }}</b><small>{{ $count }} sản phẩm</small>
</a>