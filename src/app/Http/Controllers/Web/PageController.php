<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\HomeService;
use Illuminate\Contracts\View\View;

/**
 * Controller MỎNG cho 4 trang tĩnh "Hỗ trợ" (link ở footer):
 * Hướng dẫn đặt hàng / Chính sách đổi trả / Chính sách bảo mật / Điều khoản.
 *
 * TOÀN BỘ nội dung trang đã fix cứng trong Blade — controller KHÔNG chứa text,
 * chỉ lấy thêm vài sản phẩm thật (dùng cache có sẵn của HomeService) để gắn
 * block "gợi ý mua nhanh" vào 2 trang hướng dẫn & đổi trả.
 */
class PageController extends Controller
{
    public function __construct(
        private readonly HomeService $homeService,
    ) {
    }

    /** Hướng dẫn đặt hàng — gợi ý 4 sản phẩm bán chạy. */
    public function orderGuide(): View
    {
        return view('web.pages.order-guide', [
            'suggestedProducts' => array_slice($this->homeService->bestSellers(), 0, 4),
        ]);
    }

    /** Chính sách đổi trả — gợi ý 4 sản phẩm trà hoa. */
    public function returnPolicy(): View
    {
        return view('web.pages.return-policy', [
            'suggestedProducts' => array_slice($this->homeService->herbalTea(), 0, 4),
        ]);
    }

    /** Chính sách bảo mật — render thuần, nội dung fix cứng trong Blade. */
    public function privacyPolicy(): View
    {
        return view('web.pages.privacy-policy');
    }

    /** Điều khoản sử dụng — render thuần, nội dung fix cứng trong Blade. */
    public function terms(): View
    {
        return view('web.pages.terms');
    }
}