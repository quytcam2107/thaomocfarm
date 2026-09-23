<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Requests\CategoryShowRequest;
use App\Services\CatalogService;
use Illuminate\View\View;

/**
 * Controller trang danh mục sản phẩm phía khách.
 */
class CategoryController extends Controller
{
    /**
     * Hiển thị trang danh mục theo slug: breadcrumb, bộ lọc, lưới sản phẩm, phân trang, SEO text.
     * Trả null từ service => 404 (slug không tồn tại hoặc status != active).
     */
    public function show(CategoryShowRequest $request, CatalogService $service, string $slug): View
    {
        $data = $service->getCategoryShow($slug, $request->validated());
        
        abort_if($data === null, 404);

        return view('web.category', $data);
    }
}