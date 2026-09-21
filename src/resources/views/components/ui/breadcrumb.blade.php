@props(['items' => []])

<nav class="breadcrumb" aria-label="Breadcrumb">
    <ol>
        @foreach($items as $index => $item)
            <li>
                {{-- Nếu có URL và không phải item cuối cùng thì hiển thị thẻ <a> --}}
                    @if(!empty($item['url']) && $index < count($items) - 1)
                        <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                    @else
                        {{-- Item cuối cùng hoặc không có URL thì hiển thị thẻ <span> --}}
                            <span aria-current="page">{{ $item['label'] }}</span>
                    @endif
            </li>
        @endforeach
    </ol>
</nav>