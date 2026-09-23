@props([
    'heading' => '',
    'paragraph' => null,
    'subheading' => null,
    'tips' => [],
])

@if (!empty($heading) || !empty($paragraph) || count($tips) > 0)
    <section class="seo-text">
        @if (!empty($heading))
            <h2>{{ $heading }}</h2>
        @endif

        @if (!empty($paragraph))
            <p>{!! nl2br(e((string) $paragraph)) !!}</p>
        @endif

        @if (!empty($subheading))
            <h3>{{ $subheading }}</h3>
        @endif

        @if (count($tips) > 0)
            <ul>
                @foreach ($tips as $tip)
                    <li>{{ $tip }}</li>
                @endforeach
            </ul>
        @endif
    </section>
@endif