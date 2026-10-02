<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;
use App\Models\PostCategory;
use App\Services\Catalog\BreadcrumbBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * BlogService — dữ liệu cho trang Cẩm nang (blog) + block tips trang chủ.
 * - Cache array thuần theo nhóm "content" (remember_group), TTL 1800s theo 02-CODE.md.
 *   Bump khi có bài mới: bump_group_version('content') qua tinker.
 * - Trang chi tiết KHÔNG cache vì view_count tăng từng lượt xem.
 */
class BlogService
{
    private const TTL = 1800;

    /**
     * Block tips trang chủ: 4 bài mới nhất (id giảm dần khi cùng published_at).
     *
     * @return array<int, array{id: int, title: string, url: string, reading_minutes: int}>
     */
    public function homeTips(): array
    {
        try {
            return remember_group('home', 'tips', $this->ttl(), function (): array {
                return Post::query()
                    ->published()
                    ->orderByDesc('published_at')
                    ->orderByDesc('id')
                    ->limit(4)
                    ->get(['id', 'title', 'slug', 'reading_minutes'])
                    ->map(fn(Post $p): array => [
                        'id' => (int) $p->id,
                        'title' => $p->title,
                        'url' => route('web.blog.show', $p->slug),
                        'reading_minutes' => (int) $p->reading_minutes,
                    ])
                    ->all();
            });
        } catch (\Throwable) {
            // Component Blade tự ẩn khi rỗng — không làm chết trang chủ
            return [];
        }
    }

    /**
     * Danh mục chuyên mục ACTIVE (kèm số bài đã publish) cho sidebar/filter.
     *
     * @return array<int, array{id: int, name: string, slug: string, posts_count: int, url: string}>
     */
    public function categories(): array
    {
        try {
            return remember_group('content', 'post_categories', $this->ttl(), function (): array {
                return PostCategory::query()
                    ->active()
                    ->withCount([
                        // Đếm riêng bài đã publish (scopePublished của Post)
                        'posts as posts_count' => fn(Builder $q): Builder => $q->published(),
                    ])
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get(['id', 'name', 'slug'])
                    ->map(fn(PostCategory $c): array => [
                        'id' => (int) $c->id,
                        'name' => $c->name,
                        'slug' => $c->slug,
                        'posts_count' => (int) $c->posts_count,
                        'url' => route('web.blog.category', $c->slug),
                    ])
                    ->all();
            });
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Phân trang danh sách bài (toàn bộ hoặc theo chuyên mục).
     * Trả về Eloquent paginator để giữ thuộc tính thật (category relation, cover...).
     */
    public function paginate(?string $categorySlug, int $perPage = 9): LengthAwarePaginator
    {
        $query = Post::query()
            ->published()
            ->with('category:id,name,slug')
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        if ($categorySlug !== null && $categorySlug !== '') {
            $query->whereHas(
                'category',
                fn(Builder $q): Builder => $q->where('slug', $categorySlug)->active()
            );
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Bài viết chi tiết (chỉ bài published) — kèm bài cùng chuyên mục + bài mới nhất.
     *
     * @return array{
     *     post: Post,
     *     relatedPosts: array<int, array<string, mixed>>,
     *     latestPosts: array<int, array<string, mixed>>,
     *     categories: array<int, array<string, mixed>>,
     *     breadcrumbs: list<array{label: string, url: string|null}>,
     *     breadcrumb_schema: array<string, mixed>
     * }|null
     */
    public function show(string $slug): ?array
    {
        $post = Post::query()
            ->published()
            ->with('category:id,name,slug')
            ->where('slug', $slug)
            ->first();

        if ($post === null) {
            return null;
        }

        // Bài cùng chuyên mục (loại trừ bài hiện tại), fallback: bài mới nhất khác bài hiện tại
        $related = Post::query()
            ->published()
            ->where('id', '!=', $post->id)
            ->when(
                $post->post_category_id !== null,
                fn(Builder $q): Builder => $q->where('post_category_id', $post->post_category_id),
                fn(Builder $q): Builder => $q->whereRaw('1 = 0')
            )
            ->orderByDesc('published_at')
            ->limit(4)
            ->get(['id', 'title', 'slug', 'excerpt', 'cover', 'published_at', 'reading_minutes']);

        if ($related->count() < 4) {
            $excludeIds = array_merge([$post->id], $related->pluck('id')->all());
            $filler = Post::query()
                ->published()
                ->whereNotIn('id', $excludeIds)
                ->orderByDesc('published_at')
                ->limit(4 - $related->count())
                ->get(['id', 'title', 'slug', 'excerpt', 'cover', 'published_at', 'reading_minutes']);
            $related = $related->merge($filler);
        }

        // Sidebar: 5 bài mới nhất (khác bài đang đọc)
        $latest = Post::query()
            ->published()
            ->where('id', '!=', $post->id)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'title', 'slug', 'excerpt', 'cover', 'published_at', 'reading_minutes']);

        // Breadcrumb: Trang chủ / Cẩm nang / [Chuyên mục] / Tên bài
        $crumbs = [
            ['label' => 'Trang chủ', 'url' => route('web.home')],
            ['label' => 'Cẩm nang', 'url' => route('web.blog.index')],
        ];
        if ($post->category !== null) {
            $crumbs[] = [
                'label' => $post->category->name,
                'url' => route('web.blog.category', $post->category->slug),
            ];
        }
        $crumbs[] = ['label' => $post->title, 'url' => null];
        return [
            'post' => $post,
            'relatedPosts' => $related->map(fn(Post $p): array => $this->cardData($p))->all(),
            'latestPosts' => $latest->map(fn(Post $p): array => $this->cardData($p))->all(),
            'categories' => $this->categories(),
            'breadcrumbs' => BreadcrumbBuilder::withCurrent($crumbs),
            'breadcrumb_schema' => BreadcrumbBuilder::schema($crumbs),
        ];
    }

    /** Meta SEO động cho trang danh sách (tổng số bài + chuyên mục đang lọc). */
    public function indexSeoMeta(?string $categorySlug): array
    {
        $total = Post::query()->published()->count();

        $category = null;
        if ($categorySlug !== null && $categorySlug !== '') {
            $category = PostCategory::query()->active()->where('slug', $categorySlug)->first();
        }

        if ($category !== null) {
            return [
                'title' => $category->name . ' — Cẩm nang Mộc Xanh',
                'description' => $category->name . ': các bài viết hướng dẫn chọn mua, sơ chế và bảo quản thảo mộc, đặc sản Tây Bắc từ Mộc Xanh.',
                'heading' => $category->name,
            ];
        }

        return [
            'title' => 'Cẩm nang vào bếp & pha trà | Mộc Xanh',
            'description' => "Cẩm nang Mộc Xanh với {$total} bài viết: cách dùng thịt trâu gác bếp, mắc khén, nhiệt độ hãm trà hoa, bảo quản đặc sản mùa nồm ẩm... chuẩn vị Tây Bắc.",
            'heading' => 'Cẩm nang vào bếp & pha trà',
        ];
    }

    /**
     * Mảng dữ liệu thuần cho card/listing (array cache-safe theo quy ước dự án).
     *
     * @return array<string, mixed>
     */
    private function cardData(Post $p): array
    {
        return [
            'id' => (int) $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'excerpt' => $p->excerpt ? \Illuminate\Support\Str::limit(strip_tags($p->excerpt), 120) : '',
            'cover' => asset($p->cover) ? asset($p->cover) : asset('assets/images/placeholder.svg'),
            'published_at' => $p->published_at?->format('d/m/Y'),
            'reading_minutes' => (int) $p->reading_minutes,
            'url' => route('web.blog.show', $p->slug),
        ];
    }

    private function ttl(): int
    {
        return self::TTL;
    }
}