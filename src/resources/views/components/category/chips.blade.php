@props([
    'chips' => [],
])

@if (count($chips) > 0)
    <div class="chips" aria-label="Bộ lọc đang áp dụng">
        @foreach ($chips as $chip)
            <span class="chip">{{ $chip['label'] }}
                <button type="button" data-href="{{ $chip['url'] }}" aria-label="Bỏ lọc {{ $chip['label'] }}">✕</button>
            </span>
        @endforeach
    </div>
@endif