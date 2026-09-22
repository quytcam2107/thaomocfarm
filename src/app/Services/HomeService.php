<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class HomeService
{
    /** Số danh mục nổi bật tối đa hiển thị ở block trang chủ */
    private const FEATURED_LIMIT = 8;

    /** TTL cache của block danh mục nổi bật (10 phút) */
    private const FEATURED_TTL = 600;

    /**
     * Danh sách danh mục nổi bật kèm số sản phẩm đang bán.
     * - 1 query duy nhất nhờ withCount (subquery), tuyệt đối không N+1
     * - Cache theo nhóm "home" qua remember_group()
     *
     * @return array<int, array{id: int, name: string, slug: string, image: string, products_count: int, url: string}>
     */
    public function featuredCategories(): array
    {
        return remember_group('home', 'featured_categories', self::FEATURED_TTL, function (): array {
            return Category::query()
                ->where('status', 'active')
                ->where('is_featured', true)
                ->withCount([
                    // Đếm riêng sản phẩm đang active, alias thẳng vào attribute
                    'products as products_count' => fn(Builder $q): Builder => $q->active(),
                ])
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(self::FEATURED_LIMIT)
                ->get(['id', 'name', 'slug', 'icon', 'sort_order'])
                ->map(fn(Category $category): array => [
                    'id' => (int) $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'image' => $category->icon
                        ? asset('assets/images/categories/' . $category->icon)
                        : asset('assets/images/category-default.svg'),
                    'products_count' => (int) $category->products_count,
                    'url' => route('web.category.show', ['slug' => $category->slug]),
                ])
                ->all();
        });
    }
}