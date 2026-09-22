<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    /**
     * Thêm sản phẩm vào giỏ hàng.
     * Route: POST /gio-hang/them
     */
    public function add(AddToCartRequest $request, CartService $cart): JsonResponse
    {
        $result = $cart->addToCart(
            productId: $request->validated('product_id'),
            variantId: $request->validated('variant_id'),
            qty: $request->validated('qty'),
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng',
            'cart_count' => $result['cart_count'],
            'cart_total' => $result['cart_total'],
        ]);
    }
}