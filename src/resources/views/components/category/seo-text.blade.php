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
            {{-- FIX CMS IMG: paragraph lấy từ DB/config có thể chứa HTML (<p>, <img>
                kèm literal "{{ asset(...) }}"). Trước đây e() escape toàn bộ -> tag
                hiện thành text. Nay: nội dung có HTML -> render_cms_html() (compile
                Blade expression + sanitize whitelist); text thường -> giữ escape an toàn. --}}
                @php
                    $para = (string) $paragraph;
                    /* Regex delimiter '~' — KHÔNG dùng '#' vì pattern chứa '#' (anchor href="#...")
                       sẽ bị PHP coi là kết thúc delimiter sớm => "Unknown modifier" */
                    $looksHtml = preg_match('~<[a-z][\s\S]*>~i', $para) === 1;
                @endphp
                @if ($looksHtml)
                    {!! render_cms_html($para) !!}
                @else
                    <p>{!! nl2br(e($para)) !!}</p>
                @endif
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