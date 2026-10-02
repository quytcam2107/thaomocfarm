{{-- =====================================================================
TRANG CẨM NANG (blog): danh sách bài viết từ bảng posts, phân trang + lọc chuyên mục.
Dữ liệu: BlogService (paginate/categories/indexSeoMeta).
===================================================================== --}}
@php
    use Illuminate\Support\Str;

    // JSON-LD ItemList cho SEO danh sách
    $itemListSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'numberOfItems' => $posts->total(),
        'itemListElement' => collect($posts->items())->values()->map(fn($p, $i) => [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'url' => route('web.blog.show', $p->slug),
            'name' => $p->title,
        ])->all(),
    ];

    $clearUrl = $currentCat ? route('web.blog.category', $currentCat) : route('web.blog.index');
@endphp

<x-layouts.app :title="$seo['title']" :seoDescription="$seo['description']" :hide-catnav="true">

    <x-slot name="schema">
        <script
            type="application/ld+json">{!! json_encode($itemListSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
        <script type="application/ld+json">
            {!! json_encode(\App\Services\Catalog\BreadcrumbBuilder::schema([
    ['label' => 'Trang chủ', 'url' => route('web.home')],
    ['label' => 'Cẩm nang', 'url' => $currentCat ? route('web.blog.index') : null],
    ...($currentCat ? [['label' => Str::title($seo['heading']), 'url' => null]] : []),
]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
    </x-slot>

    <div class="container">
        {{-- Breadcrumb đồng bộ markup trang tĩnh --}}
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('web.home') }}">Trang chủ</a></li>
                <li><a href="{{ route('web.blog.index') }}">Cẩm nang</a></li>
                @if($currentCat)
                    <li><span aria-current="page">{{ $seo['heading'] }}</span></li>
                @endif
            </ol>
        </nav>

        <header class="sp-hero reveal">
            <p class="sp-hero__eyebrow">🌿 Cẩm nang</p>
            <h1>{{ $seo['heading'] }}</h1>
            <p class="sp-hero__lead">
                Hướng dẫn chọn mua, sơ chế, hãm trà và bảo quản đặc sản Tây Bắc —
                viết bởi đội ngũ Mộc Xanh từ kinh nghiệm thực tế với bà con vùng trồng.
            </p>
        </header>

        {{-- Chips lọc chuyên mục (ẩn khi chỉ có 1 hoặc 0 chuyên mục) --}}
        @if (count($categories) > 1)
            <nav class="bl-chips reveal" aria-label="Chuyên mục cẩm nang">
                <a class="bl-chip {{ $currentCat === null ? 'is-active' : '' }}" href="{{ route('web.blog.index') }}">
                    Tất cả
                </a>
                @foreach ($categories as $cat)
                    <a class="bl-chip {{ $currentCat === $cat['slug'] ? 'is-active' : '' }}" href="{{ $cat['url'] }}">
                        {{ $cat['name'] }} <small>({{ $cat['posts_count'] }})</small>
                    </a>
                @endforeach
            </nav>
        @endif

        @if ($posts->total() === 0)
            {{-- Chưa có bài nào: hiển thị lời thân thiện + CTA mua sắm --}}
            <section class="sp-section sp-section--last reveal">
                <p class="bl-empty">Chưa có bài viết nào trong chuyên mục này. Mời bạn quay lại sau — hoặc khám phá
                    <a href="{{ route('web.products.index') }}">tất cả sản phẩm</a> của Mộc Xanh.
                </p>
            </section>
        @else
            <div class="bl-grid reveal">
                @foreach ($posts as $post)
                    <article class="bl-card">
                        <a class="bl-card__media" href="{{ route('web.blog.show', $post->slug) }}" tabindex="-1"
                            aria-hidden="true">
                            <img src="{{ asset($post->cover) }}" alt="Ảnh bìa: {{ $post->title }}" width="640" height="400" loading="lazy">
                        </a>
                        <div class="bl-card__body">
                            @if ($post->category)
                                <a class="bl-card__cat" href="{{ route('web.blog.category', $post->category->slug) }}">
                                    {{ $post->category->name }}
                                </a>
                            @endif
                            <h2 class="bl-card__title">
                                <a href="{{ route('web.blog.show', $post->slug) }}">{{ $post->title }}</a>
                            </h2>
                            <p class="bl-card__excerpt">{{ Str::limit(strip_tags((string) $post->excerpt), 130) }}</p>
                            <p class="bl-card__meta">
                                <time datetime="{{ $post->published_at?->toAtomString() }}">
                                    {{ $post->published_at?->format('d/m/Y') }}
                                </time>
                                <span aria-hidden="true">·</span>
                                {{ $post->reading_minutes }} phút đọc
                            </p>
                            <a class="bl-card__link" href="{{ route('web.blog.show', $post->slug) }}">Đọc tiếp →</a>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- Phân trang dùng component ui.pagination (contract meta: current_page/last_page) --}}
            <x-ui.pagination :meta="[
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
            ]" />

            {{-- SEO text động cuối trang danh sách --}}
            <section class="sp-section sp-section--last bl-seo-text reveal">
                <h2 class="sec-title">Về chuyên mục Cẩm nang</h2>
                <p>
                    Hiện có <b>{{ $posts->total() }}</b> bài
                    viết{{ $currentCat ? ' thuộc chuyên mục ' . $seo['heading'] : '' }}
                    được Mộc Xanh cập nhật thường xuyên: công thức nấu món đặc sản Tây Bắc, nhiệt độ nước hãm từng loại
                    trà hoa, mẹo bảo quản thịt gác bếp mùa nồm ẩm và cách chọn dược liệu đạt chuẩn OCOP, VietGAP.
                </p>
            </section>
        @endif
    </div>
</x-layouts.app>