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
            'freeShippingThreshold' => config('thaomoc.shipping.free_threshold', 300000),
        ]);
    }

    public function add(AddToCartRequest $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart();

        try {
            $this->cartService->addToCart(
                $cart->id,
                $request->product_id,
                $request->variant_id,
                $request->qty
            );

            $response = response()->json([
                'success' => true,
                'message' => 'Đã thêm vào giỏ hàng',
                'cartCount' => $this->cartService->getCartItemCount($cart->id),
            ]);

            if (!Auth::check() && $cart->cart_token) {
                $response->withCookie(cookie('cart_token', $cart->cart_token, 43200));
            }

            return $response;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function update(UpdateCartRequest $request, int $itemId): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart();

        try {
            $updated = $this->cartService->updateCartItem($cart->id, $itemId, $request->qty);
            if (!$updated) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm'], 400);
            }

            return $this->getCartSummaryJson($cart->id);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function remove(int $itemId): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart();
        $this->cartService->removeItem($cart->id, $itemId);

        return $this->getCartSummaryJson($cart->id);
    }

    public function count(): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart();
        return response()->json([
            'count' => $this->cartService->getCartItemCount($cart->id),
        ]);
    }

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
            'itemCount' => $cartDetails['total_items'],
        ]);
    }
}