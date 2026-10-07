<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\VietnamLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Trả dữ liệu hành chính 2 cấp (tỉnh/thành → xã/phường) cho cascading select
 * ở trang thanh toán. KHÔNG dùng cấp quận/huyện (đã bỏ từ 01/07/2025).
 */
class LocationController extends Controller
{
    public function __construct(private readonly VietnamLocationService $locations)
    {
    }

    /**
     * GET /dia-chi/xa-phuong?province_code={code}
     * Danh sách xã/phường của một tỉnh — { wards: [{code, name}] }.
     * province_code không hợp lệ → trả rỗng (JS tự ẩn, không 500).
     */
    public function wards(Request $request): JsonResponse
    {
        $provinceCode = $request->query('province_code');

        if (!is_numeric($provinceCode)) {
            return response()->json(['wards' => []]);
        }

        return response()->json([
            'wards' => $this->locations->wardsForProvince((int) $provinceCode),
        ]);
    }
}
