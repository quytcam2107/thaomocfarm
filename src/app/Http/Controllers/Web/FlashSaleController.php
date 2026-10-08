<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\FlashSalePayloadCache;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller phục vụ cơ chế RENDER Flash Sale: Blade chỉ in khung rỗng,
 * JS (assets/js/flash-live.js) gọi 2 endpoint này MỖI 3 PHÚT để cập nhật
 * section #flash (home) và block .pd-flash (PDP).
 *
 * TỐI ƯU SPEED (yêu cầu mới): payload do FlashSalePayloadCache lưu CACHE
 * FILE 3 PHÚT (đúng chu kỳ poll của JS):
 *   - Cache còn hạn -> trả thẳng chuỗi JSON từ file, không đụng DB (<100ms).
 *   - Cache hết hạn -> client "check mới refresh": chính request này build
 *     lại từ DB rồi trả dữ liệu MỚI NHẤT + ghi cache cho 3 phút tiếp theo.
 *   - Header X-Flash-Cache: HIT | REFRESH | STALE để debug.
 */
class FlashSaleController extends Controller
{
    public function __construct(
        private readonly FlashSalePayloadCache $cache
    ) {
    }

    /** GET /flash-sale — dữ liệu rail deal + đếm ngược + social proof trang chủ. */
    public function home(): Response
    {
        $raw = $this->cache->homeJson();

        return $this->jsonFromCache($raw, $this->cache->lastState());
    }

    /**
     * GET /flash-sale/san-pham/{id} — dữ liệu block flash + giá deal cho PDP.
     * {id} là số (where constraint ở routes) nên không xung đột catch-all /{slug}.
     */
    public function product(Request $request, int $id): Response
    {
        $raw = $this->cache->pdpJson($id);

        return $this->jsonFromCache($raw, $this->cache->lastState());
    }

    /**
     * Gửi raw JSON đã cache thẳng ra browser — không deserialize/encode lại.
     * JsonResponse nhận string => body là JSON gốc trong file cache.
     */
    private function jsonFromCache(string $rawJson, string $state): Response
    {
        return response($rawJson, 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'X-Flash-Cache' => $state,      // HIT = đọc file (<100ms), REFRESH = vừa rebuild
            'Cache-Control' => 'no-store',  // JS tự quản lý chu kỳ 3 phút
        ]);
    }
     /** POST /flash-sale/xoa-cache — bump nhóm 'flashlive' để JS poll kế lấy dữ liệu mới. */
    public function clearCache(Request $request): Response
    {
        $this->cache->clear();

        return response()->json(['ok' => true]);
    }
}