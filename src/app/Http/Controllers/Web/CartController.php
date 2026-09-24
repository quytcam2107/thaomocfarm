<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Requests\AddToCartRequest;
use App\Requests\UpdateCartRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
    ) {
    }

    /**
     * Hiển thị trang giỏ hàng.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $cart = $this->cartService->getOrCreateCart();
        $cartDetails = $this->cartService->getCartDetails($cart->id);

        $subtotal = $cartDetails['subtotal'];
        $shippingFee = $this->cartService->calculateShippingFee($subtotal);
        $total = $subtotal + $shippingFee;

        return view('web.cart', [
            'cartItems' => $cartDetails['items'],
            'totalQty' => $cartDetails['total_qty'],
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'total' => $total,
            'freeShippingThreshold' => config('thaomoc.shipping.free_threshold', 500000), // Khớp với config
        ]);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng qua AJAX.
     *
     * @param AddToCartRequest $request
     * @return JsonResponse
     */
    public function add(AddToCartRequest $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart();

        try {
            // SỬA LỖI: Ép kiểu rõ ràng sang int để khớp với strict type của CartService
            $this->cartService->addToCart(
                $cart->id,
                $request->integer('product_id'),
                $request->integer('variant_id'),
                $request->integer('qty', 1) // Mặc định là 1 nếu client gửi thiếu
            );

            $response = response()->json([
                'success' => true,
                'message' => 'Đã thêm vào giỏ hàng',
                'cartCount' => $this->cartService->getCartItemCount($cart->id),
            ]);

            // Lưu cookie cho guest user
            if (!Auth::check() && $cart->cart_token) {
                $response->withCookie(cookie('cart_token', $cart->cart_token, 43200)); // 30 ngày
            }

            return $response;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng.
     *
     * @param UpdateCartRequest $request
     * @param int $itemId
     * @return JsonResponse
     */
    public function update(UpdateCartRequest $request, int $itemId): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart();

        try {
            $updated = $this->cartService->updateCartItem($cart->id, $itemId, $request->integer('qty', 1));
            if (!$updated) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm trong giỏ'], 400);
            }

            return $this->getCartSummaryJson($cart->id);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng.
     *
     * @param int $itemId
     * @return JsonResponse
     */
    public function remove(int $itemId): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart();
        $this->cartService->removeItem($cart->id, $itemId);

        return $this->getCartSummaryJson($cart->id);
    }

    /**
     * Lấy tổng số lượng sản phẩm trong giỏ hàng (dùng cho JS cập nhật badge).
     *
     * @return JsonResponse
     */
    public function count(): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart();

        return response()->json([
            'count' => $this->cartService->getCartItemCount($cart->id),
        ]);
    }

    /**
     * Trả về tóm tắt giỏ hàng dưới dạng JSON (subtotal, shipping, total, itemCount).
     *
     * @param int $cartId
     * @return JsonResponse
     */
    private function getCartSummaryJson(int $cartId): JsonResponse
    {
        $cartDetails = $this->cartService->getCartDetails($cartId);
        $subtotal = $cartDetails['subtotal'];
        $shippingFee = $this->cartService->calculateShippingFee($subtotal);
        $total = $subtotal + $shippingFee;

        return response()->json([
            'success' => true,
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'total' => $total,
            'itemCount' => $cartDetails['total_items'] ?? 0,
        ]);
    }
}