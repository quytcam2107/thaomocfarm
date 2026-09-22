<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\HomeService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomeService $homeService,
    ) {
    }

    /**
     * Trang chủ: render block "Danh mục nổi bật".
     * Controller mỏng – toàn bộ logic nằm trong HomeService.
     */
    public function index(): View
    {
        // dd($this->homeService->featuredCategories());
        return view('web.home', [
            'featuredCategories' => $this->homeService->featuredCategories(),
        ]);
    }
}