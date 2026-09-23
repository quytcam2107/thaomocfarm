@props([
    'meta' => [],
])

@php
    $current = (int) ($meta['current_page'] ?? 1);
    $last = (int) ($meta['last_page'] ?? 1);

    $pageUrl = function (int $page): string {
        $query = request()->query();
        if ($page > 1) {
            $query['page'] = $page;
        } else {
            unset($query['page']);
        }
        $qs = http_build_query($query);
        return request()->url() . ($qs !== '' ? '?' . $qs : '');
    };

    $pages = [];
    if ($last <= 7) {
        $pages = range(1, $last);
    } else {
        $pages[] = 1;
        if ($current > 4) {
            $pages[] = 'dots';
        }
        for ($i = max(2, $current - 1); $i <= min($last - 1, $current + 1); $i++) {
            $pages[] = $i;
        }
        if ($current < $last - 3) {
            $pages[] = 'dots';
        }
        $pages[] = $last;
    }
@endphp

@if ($last > 1)
    <nav class="pagination" aria-label="Phân trang">
        @if ($current > 1)
            <a href="{{ $pageUrl($current - 1) }}" aria-label="Trang trước">‹</a>
        @endif

        @foreach ($pages as $page)
            @if ($page === 'dots')
                <span class="dots hide-sm" aria-hidden="true">…</span>
            @elseif ($page === $current)
                <span class="current" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $pageUrl((int) $page) }}">{{ $page }}</a>
            @endif
        @endforeach

        @if ($current < $last)
            <a href="{{ $pageUrl($current + 1) }}" aria-label="Trang sau">›</a>
        @endif
    </nav>
@endif