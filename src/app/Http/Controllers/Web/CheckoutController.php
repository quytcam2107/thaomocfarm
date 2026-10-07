<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\VietnamLocationService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService,
        private readonly CartService $cartService,
        private readonly VietnamLocationService $locations
    ) {
    }

    /**
     * Hiển thị trang thanh toán.
     * Giữ lại phương thức vận chuyển đã chọn khi form bị validate fail (old input).
     */
    public function index(Request $request)
    {
        $cart = $this->cartService->getOrCreateCart();

        // Ưu tiên old('shipping_method') để radio vẫn chọn đúng sau khi back()->withInput()
        $data = $this->checkoutService->getCheckoutData(
            $cart->id,
            (string) $request->old('shipping_method', 'standard')
        );

        if (empty($data)) {
            return redirect()->route('web.cart.index')->with('error', 'Giỏ hàng đang trống, vui lòng thêm sản phẩm.');
        }

        // Địa chính 2 cấp: đổ tỉnh/thành vào select; nếu quay lại do validate fail
        // thì pre-render luôn xã/phường của tỉnh đã chọn (để selected without JS).
        $data['provinces'] = $this->locations->provincesForSelect();

        $oldProvince = $request->old('province_code');
        $data['wards'] = $oldProvince !== null
            ? $this->locations->wardsForProvince((int) $oldProvince)
            : [];

        return view('web.checkout', $data);
    }

    /**
     * Xử lý lưu đơn hàng.
     */
    public function store(CheckoutRequest $request)
    {
        $cart = $this->cartService->getOrCreateCart();

        try {
            $order = $this->checkoutService->processCheckout($cart->id, $request->validated());

            $response = redirect()->route('web.checkout.success', ['order_number' => $order->order_number]);

            // Xóa cookie cart_token nếu là guest
            if (!auth()->check()) {
                $response->withCookie(cookie()->forget('cart_token'));
            }

            return $response;
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Trang đặt hàng thành công.
     */
    public function success(Request $request, string $order_number)
    {
        $order = Order::where('order_number', $order_number)->firstOrFail();

        return view('web.checkout-success', compact('order'));
    }
}