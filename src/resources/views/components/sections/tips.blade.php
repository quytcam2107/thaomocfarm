{{-- Block Cẩm nang trang chủ: 4 bài viết mới nhất từ bảng posts (BlogService::homeTips).
Tự ẩn toàn bộ section khi chưa có bài nào (rule component). --}}
@props(['tips' => null])

@if (!empty($tips))
    <section class="container tips reveal" aria-labelledby="tipTitle">
        <h2 class="sec-title" id="tipTitle">Cẩm nang thảo mộc & pha trà</h2>
        <div class="tips__grid">
            @foreach ($tips as $i => $tip)
                <a class="tip" href="{{ $tip['url'] }}">
                    <span class="tip__num" aria-hidden="true">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    <div>
                        <b>{{ $tip['title'] }}</b>
                        <small>{{ $tip['reading_minutes'] }} phút đọc</small>
                    </div>
                </a>
            @endforeach
        </div>
        {{-- Link tới trang tổng hợp cẩm nang (route web.blog.index) --}}
        <p class="tips__more">
            <a href="{{ route('web.blog.index') }}">Xem tất cả bài viết <span aria-hidden="true">→</span></a>
        </p>
    </section>
@endif