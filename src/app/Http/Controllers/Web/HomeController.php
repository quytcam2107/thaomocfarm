<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\BlogService;
use App\Services\HomeService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomeService $homeService,
        private readonly BlogService $blogService,
    ) {
    }

    /**
     * Trang chủ: render các block featured, flash sale, coupons, best sellers, trà hoa.
     * Controller mỏng – toàn bộ logic nằm trong HomeService/BlogService.
     */
    public function index(): View
    {
        return view('web.home', [
            'featuredCategories' => $this->homeService->featuredCategories(),
            'flashProducts' => $this->homeService->flashSale(),
            'coupons' => $this->homeService->homeCoupons(),
            'bestSellers' => $this->homeService->bestSellers(),
            'herbalTeaProducts' => $this->homeService->herbalTea(),
            // Tips trang chủ giờ lấy từ bảng posts (4 bài mới nhất, cache nhóm home)
            'tips' => $this->blogService->homeTips(),
        ]);
    }
}