{{-- =====================================================================
TRANG CHI TIẾT BÀI VIẾT CẨM NANG (/cam-nang/{slug}).
SEO: Article JSON-LD + BreadcrumbList + canonical (layout) + og article.
Nội dung: $post->content là HTML an toàn do biên tập viên nhập (import SQL/seeder), render {} !!}.\nSidebar: 3 widget
theo thứ tự — Bài viết mới / Có thể bạn sẽ thích (sản phẩm) / Chuyên mục.
===================================================================== --}}
@php
    use Illuminate\Support\Str;

    $shareUrl = route('web.blog.show', $post->slug);

    $coverUrl = $post->cover
        ? (Str::startsWith($post->cover, ['http://', 'https://']) ? $post->cover : asset($post->cover))
        : asset('assets/images/placeholder.svg');
    // JSON-LD Article theo schema.org
    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => Str::limit($post->title, 110),
        'description' => Str::limit(strip_tags((string) $post->excerpt), 160),
        'image' => [$coverUrl],
        'datePublished' => $post->published_at?->toAtomString(),
        'dateModified' => $post->updated_at?->toAtomString(),
        'wordCount' => str_word_count(strip_tags($post->content)),
        'author' => [
            '@type' => 'Organization',
            'name' => 'Mộc Xanh',
            'url' => url('/'),
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'Mộc Xanh',
            'logo' => ['@type' => 'ImageObject', 'url' => asset('assets/images/logo_thao_moc_farm.png')],
        ],
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $shareUrl],
    ];

    // Ảnh đại diện bài viết cho sidebar (dùng chung $coverUrl đã resolve ở trên)
    $thumbUrl = static fn(array $lp): string => !empty($lp['cover'])
        ? (Str::startsWith($lp['cover'], ['http://', 'https://']) ? $lp['cover'] : asset($lp['cover']))
        : asset('assets/images/placeholder.svg');
@endphp

<x-layouts.app :title="$post->title . ' | Cẩm nang Mộc Xanh'" :seoDescription="Str::limit(strip_tags((string) $post->excerpt ?: $post->title), 155)" ogType="article" :ogImage="$coverUrl" bodyClass="page-blog-post"
    :hide-catnav="true">
    @push('styles')
        {{--
        <link rel="stylesheet" href="{{ asset('assets/css/blog-post.css') }}"> --}}
    @endpush

    <x-slot name="schema">
        <script
            type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
        <script
            type="application/ld+json">{!! json_encode($breadcrumb_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    </x-slot>

    {{-- Wrapper 2 cột desktop: breadcrumb full-width, article + aside là 2 item của grid --}}
    <div class="container bs-wrap">
        <div class="bs-wrap__top">
            <x-ui.breadcrumb :items="$breadcrumbs" />
        </div>

        <article class="bs-article reveal">
            <header class="bs-head">
                @if ($post->category)
                    <a class="bs-head__cat" href="{{ route('web.blog.category', $post->category->slug) }}">
                        🌿 {{ $post->category->name }}
                    </a>
                @endif
                <h1>{{ $post->title }}</h1>
                <p class="bs-head__meta">
                    <time datetime="{{ $post->published_at?->toAtomString() }}">
                        {{ $post->published_at?->format('d/m/Y') }}
                    </time>
                    <span aria-hidden="true">·</span> {{ $post->reading_minutes }} phút đọc
                    <span aria-hidden="true">·</span> {{ number_format($post->view_count) }} lượt xem
                </p>
            </header>

            @if ($post->cover)
                <figure class="bs-cover">
                    <img src="{{ $coverUrl }}" alt="{{ $post->title }}" width="960" height="540" fetchpriority="high">
                </figure>
            @endif

            @if ($post->excerpt)
                <p class="bs-lead">{{ $post->excerpt }}</p>
            @endif

            {{-- Nội dung HTML từ CMS/import SQL. FIX: content có thể chứa literal
            "{{ asset('...') }}" -> render_cms_html() compile Blade expression
            trong data + sanitize whitelist (app/Support/helpers.php) --}}
            <div class="bs-content">
                {!! render_cms_html((string) $post->content) !!}
            </div>

            {{-- Nút chia sẻ (không cần JS mới — dùng link native) --}}
            <footer class="bs-share">
                <span>Chia sẻ:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank"
                    rel="noopener nofollow" aria-label="Chia sẻ lên Facebook">Facebook</a>
                <a href="https://zalo.me/share?url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener nofollow"
                    aria-label="Chia sẻ qua Zalo">Zalo</a>
                <a href="mailto:?subject={{ urlencode($post->title) }}&body={{ urlencode($shareUrl) }}"
                    aria-label="Gửi qua email">Email</a>
            </footer>
        </article>

        {{-- Sidebar: thứ tự mới — Bài viết mới / Có thể bạn sẽ thích / Chuyên mục (mỗi khối tự ẩn khi rỗng) --}}
        <aside class="bs-side reveal" aria-label="Liên quan">
            @if (count($latestPosts))
                <section class="bs-widget">
                    <h2 class="bs-widget__title"><span aria-hidden="true">🕐</span> Bài viết mới</h2>
                    <ul class="bs-latest">
                        @foreach ($latestPosts as $lp)
                            <li>
                                <a href="{{ $lp['url'] }}">
                                    <span class="bs-latest__thumb">
                                        <img src="{{ asset($thumbUrl($lp)) }}" alt="" width="64" height="64" loading="lazy">
                                    </span>
                                    <span class="bs-latest__body">
                                        <b>{{ $lp['title'] }}</b>
                                        <small>{{ $lp['published_at'] }} · {{ $lp['reading_minutes'] }} phút đọc</small>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if (!empty($suggestedProducts))
                <section class="bs-widget bs-widget--products">
                    <h2 class="bs-widget__title"><span aria-hidden="true">🌿</span> Có thể bạn sẽ thích</h2>
                    <div class="bsp-list">
                        @foreach ($suggestedProducts as $sp)
                            <x-blog.suggest-product :product="$sp" />
                        @endforeach
                    </div>
                    <a class="bs-widget__more" href="{{ route('web.products.index') }}">Xem tất cả sản phẩm →</a>
                </section>
            @endif

            @if (count($categories))
                <section class="bs-widget">
                    <h2 class="bs-widget__title"><span aria-hidden="true">📚</span> Chuyên mục</h2>
                    <ul class="bs-cats">
                        @foreach ($categories as $cat)
                            <li>
                                <a href="{{ $cat['url'] }}">
                                    <span class="bs-cats__name">{{ $cat['name'] }}</span>
                                    <span class="bs-cats__count">{{ $cat['posts_count'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <a class="btn btn--leaf bs-widget__cta" href="{{ route('web.products.index') }}">🛍️ Xem sản phẩm Mộc
                Xanh</a>
        </aside>
    </div>

    {{-- Bài liên quan: ra ngoài .bs-wrap để luôn full-width (không bị grid 2 cột bó hẹp) --}}
    @if (count($relatedPosts))
        <div class="container">
            <section class="sp-section reveal" aria-labelledby="relTitle">
                <h2 class="sec-title" id="relTitle">Bài viết liên quan</h2>
                <div class="bl-grid bl-grid--4">
                    @foreach ($relatedPosts as $rp)
                        <article class="bl-card">
                            <a class="bl-card__media" href="{{ $rp['url'] }}" tabindex="-1" aria-hidden="true">
                                <img src="{{ $thumbUrl($rp) }}" alt="Ảnh bìa: {{ $rp['title'] }}" width="640" height="400"
                                    loading="lazy">
                            </a>
                            <div class="bl-card__body">
                                <h3 class="bl-card__title"><a href="{{ $rp['url'] }}">{{ $rp['title'] }}</a></h3>
                                <p class="bl-card__meta">{{ $rp['published_at'] }} · {{ $rp['reading_minutes'] }} phút đọc</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    @endif
</x-layouts.app>