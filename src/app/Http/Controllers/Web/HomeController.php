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
     * Trang chủ: render các block featured, flash sale, best sellers, trà hoa.
     * Controller mỏng – toàn bộ logic nằm trong HomeService.
     */
    public function index(): View
    {
        return view('web.home', [
            'featuredCategories' => $this->homeService->featuredCategories(),
            'flashProducts' => $this->homeService->flashSale(),
            'bestSellers' => $this->homeService->bestSellers(),
            'herbalTeaProducts' => $this->homeService->herbalTea(),
        ]);
    }
}