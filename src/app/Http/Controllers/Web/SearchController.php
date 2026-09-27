<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Requests\CategoryShowRequest;
use App\Models\SearchTerm;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller trang kết quả tìm kiếm sản phẩm phía khách.
 */
class SearchController extends Controller
{
    /**
     * Hiển thị trang /tim-kiem?q=...: lưới sản phẩm khớp từ khoá,
     * bộ lọc/sắp xếp tái dùng như danh mục, gợi ý từ khoá phổ biến.
     */
    public function index(Request $request, CatalogService $service): View
    {
        $keyword = trim((string) $request->query('q', ''));

        if ($keyword === '') {
            return view('web.search-empty');
        }

        // Validate các tham số bộ lọc đi kèm (sort/price/rating/cat/page)
        /** @var array<string, mixed> $filters */
        $filters = $request->validate(
            CategoryShowRequest::rulesFor($request),
            (new CategoryShowRequest())->messages()
        );

        // Ghi nhận từ khoá vào search_terms cho gợi ý & thống kê sau này
        SearchTerm::track($keyword);

        $data = $service->search($keyword, $filters);

        return view('web.search-results', $data);
    }
}
