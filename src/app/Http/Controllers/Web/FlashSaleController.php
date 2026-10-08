<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\FlashSaleRenderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller phụ vụ cơ chế RENDER Flash Sale MỚI: Blade chỉ in khung rỗng,
 * JS (assets/js/flash-live.js) gọi 2 endpoint này MỖI 3 PHÚT để cập nhật
 * section #flash (home) và block .pd-flash (PDP).
 *
 * Không cache HTTP ở controller — để mỗi lần poll lấy dữ liệu mới nhất từ
 * service (FlashSalePriceService tự cache 'catalog' 300s).
 */
class FlashSaleController extends Controller
{
    public function __construct(
        private readonly FlashSaleRenderService $renderer
    ) {
    }

    /** GET /flash-sale — dữ liệu rail deal + đếm ngược + social proof trang chủ. */
    public function home(): JsonResponse
    {
        return response()->json($this->renderer->homePayload());
    }

    /**
     * GET /flash-sale/san-pham/{id} — dữ liệu block flash + giá deal cho PDP.
     * {id} là số (where constraint ở routes) nên không xung đột catch-all /{slug}.
     */
    public function product(Request $request, int $id): JsonResponse
    {
        return response()->json($this->renderer->pdpPayload($id));
    }
}