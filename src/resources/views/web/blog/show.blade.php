{{-- =====================================================================
TRANG CHI TIẾT BÀI VIẾT CẨM NANG (/cam-nang/{slug}).
SEO: Article JSON-LD + BreadcrumbList + canonical (layout) + og article.
Nội dung: $post->content là HTML an toàn do biên tập viên nhập (import SQL/seeder), render {} !!}.
===================================================================== --}}
@php
    use Illuminate\Support\Str;

    $shareUrl = route('web.blog.show', $post->slug);

    $coverUrl = $post->cover
        ? (Str::startsWith($post->cover, ['http://', 'https://']) ? $post->cover : asset($post->cover))
        : asset('assets/images/default-blog-cover.jpg');
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
@endphp

<x-layouts.app :title="$post->title . ' | Cẩm nang Mộc Xanh'" :seoDescription="Str::limit(strip_tags((string) $post->excerpt ?: $post->title), 155)" ogType="article" :ogImage="$coverUrl" :hide-catnav="true">

    <x-slot name="schema">
        <script
            type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
        <script
            type="application/ld+json">{!! json_encode($breadcrumb_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    </x-slot>

    <div class="container">
        <x-ui.breadcrumb :items="$breadcrumbs" />

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

            {{-- Nội dung HTML từ CMS/import SQL (biên tập viên kiểm soát, an toàn) --}}
            <div class="bs-content">
                {!! $post->content !!}
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

        {{-- Sidebar: chuyên mục + bài mới nhất (tự ẩn từng khối khi rỗng) --}}
        <aside class="bs-side reveal" aria-label="Liên quan">
            @if (count($categories))
                <section class="bs-widget">
                    <h2>Chuyên mục</h2>
                    <ul>
                        @foreach ($categories as $cat)
                            <li>
                                <a href="{{ $cat['url'] }}">{{ $cat['name'] }}
                                    <small>({{ $cat['posts_count'] }})</small></a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if (count($latestPosts))
                <section class="bs-widget">
                    <h2>Bài viết mới</h2>
                    <ul class="bs-widget__list">
                        @foreach ($latestPosts as $lp)
                            <li>
                                <a href="{{ $lp['url'] }}">
                                    <b>{{ $lp['title'] }}</b>
                                    <small>{{ $lp['published_at'] }} · {{ $lp['reading_minutes'] }} phút đọc</small>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <a class="btn btn--leaf bs-widget__cta" href="{{ route('web.products.index') }}">🛍️ Xem sản phẩm Mộc
                Xanh</a>
        </aside>

        {{-- Bài liên quan --}}
        @if (count($relatedPosts))
        
            <section class="sp-section reveal" aria-labelledby="relTitle">
                <h2 class="sec-title" id="relTitle">Bài viết liên quan</h2>
                <div class="bl-grid bl-grid--4">
                    @foreach ($relatedPosts as $rp)
                        <article class="bl-card">
                            <a class="bl-card__media" href="{{ $rp['url'] }}" tabindex="-1" aria-hidden="true">
                                <img src="{{ asset($rp['cover']) }}" alt="Ảnh bìa: {{ $rp['title'] }}" width="640" height="400"
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
        @endif
    </div>
</x-layouts.app>