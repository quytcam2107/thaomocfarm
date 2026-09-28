<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SearchTerm;
use App\Requests\CategoryShowRequest;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(private readonly CatalogService $catalog)
    {
    }

    /**
     * Trang kết quả tìm kiếm: /tim-kiem?q=tu-khoa
     */
    public function index(CategoryShowRequest $request): View
    {
        $keyword = trim((string) $request->query('q', ''));

        // Log từ khoá tìm kiếm
        if ($keyword !== '') {
            SearchTerm::track($keyword);
        }

        $data = $this->catalog->searchProducts($keyword, $request->validated());

        return view('web.search', $data);
    }

    /**
     * API gợi ý nhanh cho header search
     * GET /tim-kiem/goi-y?q=tu-khoa
     */
    public function suggest(Request $request): JsonResponse
    {
        $limit = (int) config('thaomoc.rate_limits.search', 30);
        $key = 'search_suggest|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return response()->json([
                'items' => [],
                'total' => 0,
                'more_url' => route('web.search.index', ['q' => trim((string) $request->query('q', ''))]),
            ], 429);
        }
        RateLimiter::hit($key, 60);

        $keyword = trim((string) $request->query('q', ''));

        if (mb_strlen($keyword) < 2) {
            return response()->json(['items' => [], 'total' => 0, 'more_url' => null]);
        }

        $result = $this->catalog->searchProducts($keyword, [], 6);

        return response()->json([
            'items' => $result['products'],
            'total' => $result['meta']['total'],
            'more_url' => route('web.search.index', ['q' => $keyword]),
        ]);
    }
}