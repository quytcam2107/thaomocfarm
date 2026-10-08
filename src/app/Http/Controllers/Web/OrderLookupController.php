<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Order\OrderLookupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * OrderLookupController — trang "Tra cứu đơn hàng" (thay vai trò nút Đăng nhập cũ).
 * Luồng: form GET (?phone=...) -> normalize -> rate limit -> query orders -> render.
 * Không đăng nhập, không session đặc biệt; CSRF do middleware web group đảm nhận (GET).
 */
class OrderLookupController extends Controller
{
    public function __construct(private readonly OrderLookupService $lookup)
    {
    }

    public function index(Request $request): View
    {
        $raw = trim((string) $request->query('phone', ''));
        $normalized = $raw !== '' ? $this->lookup->normalizePhone($raw) : null;

        $error = null;
        $orders = [];

        if ($raw !== '') {
            if ($normalized === null) {
                // Sai định dạng: giữ lại số user đã gõ để họ sửa
                $error = 'Số điện thoại không hợp lệ. Vui lòng nhập 9–11 chữ số (ví dụ 0352806324).';
            } elseif (!$this->lookup->hitRateLimit((string) $request->ip())) {
                $error = 'Bạn tra cứu quá nhiều lần. Vui lòng thử lại sau ít phút.';
            } else {
                $found = $this->lookup->findOrdersByPhone($normalized);
                $orders = $found->map(fn($order) => $this->lookup->present($order))->all();

                if ($orders === []) {
                    $error = 'Không tìm thấy đơn hàng nào cho số ' . $this->formatInput($raw)
                        . '. Kiểm tra lại SĐT đã dùng khi đặt hàng, hoặc gọi hotline 0362 795 897 để được hỗ trợ.';
                }
            }
        }

        return view('web.order-lookup', [
            'inputPhone' => $this->formatInput($raw),
            'orders' => $orders,
            'error' => $error,
        ]);
    }

    /** Chỉ cho phép chữ số/khoảng trắng/gạch ngang/+ khi echo lại input (chống XSS ký tự lạ). */
    private function formatInput(string $raw): string
    {
        return preg_replace('/[^\d+\-\s]/u', '', $raw) ?? '';
    }
}