<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Stub trang danh mục – chỉ để route('web.category.show') hoạt động.
     * Batch sau sẽ thay bằng CatalogService đầy đủ (lọc giá, sort, phân trang).
     */
    public function show(string $slug): string
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        return "Danh mục: {$category->name} – trang đầy đủ sẽ triển khai ở batch sau.";
    }
}