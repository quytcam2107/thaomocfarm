<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;

/**
 * MenuService — dữ liệu cho drawer menu mobile (x-drawer).
 * - Lấy danh mục ACTIVE thật từ bảng categories (status='active'), sắp theo sort_order, id
 * - Kèm số sản phẩm đang bán (withCount qua scopeActive của Product — chống N+1)
 * - Cache array thuần theo nhóm "content" (remember_group), TTL config('home.ttl.featured_categories')
 * - Trả [] khi DB lỗi/không có dữ liệu => component Blade tự ẩn phần danh mục
 */
class MenuService
{
    /**
     * @return array<int, array{name: string, slug: string, image: string, products_count: int, url: string}>
     */
    public function drawerCategories(): array
    {
        try {
            return remember_group(
                'content',
                'drawer_categories',
                (int) config('home.ttl.featured_categories', 600),
                function (): array {
                    return Category::query()
                        ->where('status', 'active')
                        ->withCount([
                            // Đếm riêng sản phẩm đang active (scopeActive của Product)
                            'products as products_count' => fn(Builder $q): Builder => $q->active(),
                        ])
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->limit((int) config('home.limits.featured_categories', 8))
                        ->get(['id', 'name', 'slug', 'icon'])
                        ->map(fn(Category $c): array => [
                            'name' => $c->name,
                            'slug' => $c->slug,
                            'image' => $c->icon
                                ? asset('assets/images/categories/' . $c->icon)
                                : asset('assets/images/category-default.svg'),
                            'products_count' => (int) $c->products_count,
                            // URL chuẩn thật: /danh-muc/{slug} (route web.category.show)
                            'url' => route('web.category.show', ['slug' => $c->slug]),
                        ])
                        ->all();
                }
            );
        } catch (\Throwable) {
            // Drawer không được làm chết trang khi cache/DB có vấn đề
            return [];
        }
    }
}