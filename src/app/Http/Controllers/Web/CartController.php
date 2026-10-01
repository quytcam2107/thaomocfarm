<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Requests\AddToCartRequest;
use App\Requests\ApplyCouponRequest;
use App\Requests\UpdateCartRequest;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly CouponService $couponService
    ) {
    }

    /**
     * Hiển thị trang giỏ hàng kèm block mã giảm giá + mã đang áp.
     * Truyền đúng 4 props (code, desc, minOrder, exp) cho component cpn.
     */
    public function index(Request $request)
    {
        $cart = $this->cartService->getOrCreateCart();
        $details = $this->cartService->getCartDetails($cart->id);
        $summary = $this->cartService->getCartSummary($cart->id);

        // Mỗi phần tử có đúng 4 key mà component cpn dùng + key 'applied' cho highlight.
        $availableCoupons = $this->couponService->getAvailableCoupons($cart->id, Auth::id());

        return view('web.cart', [
            'cartItems' => $details['items'],
            'totalQty' => $summary['total_qty'],
            'subtotal' => $summary['subtotal'],
            'discountedSubtotal' => $summary['discounted_subtotal'],
            'discount' => $summary['discount'],
            'appliedCoupon' => $summary['applied'],
            'shippingFee' => $summary['shipping_fee'],
            'total' => $summary['total'],
            'availableCoupons' => $availableCoupons,
            'freeShippingThreshold' => config('thaomoc.shipping.free_threshold', 300000),
        ]);
    }

    public function add(AddToCartRequest $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart();

        try {
            $this->cartService->addToCart(
                $cart->id,
                $request->integer('product_id'),
                $request->integer('variant_id'),
                $request->integer('qty', 1)
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
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * MUA NHANG (Buy Now): thêm sản phẩm + variant + qty đang chọn trên PDP
     * vào giỏ (tái sử dụng addToCart — đã kiểm tra tồn kho), sau đó trả URL
     * trang thanh toán để JS redirect. Giữ nguyên cookie cart_token cho guest.
     */
    public function buyNow(AddToCartRequest $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart();

        try {
            $this->cartService->addToCart(
                $cart->id,
                $request->integer('product_id'),
                $request->integer('variant_id'),
                $request->integer('qty', 1)
            );

            $response = response()->json([
                'success' => true,
                'message' => 'Đang chuyển tới trang thanh toán',
                'cartCount' => $this->cartService->getCartItemCount($cart->id),
                'redirect' => route('web.checkout.index'),
            ]);

            if (!Auth::check() && $cart->cart_token) {
                $response->withCookie(cookie('cart_token', $cart->cart_token, 43200));
            }

            return $response;
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

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

    public function remove(int $itemId): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart();
        $this->cartService->removeItem($cart->id, $itemId);
        return $this->getCartSummaryJson($cart->id);
    }

    public function count(): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart();
        return response()->json(['count' => $this->cartService->getCartItemCount($cart->id)]);
    }

    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        $limit = (int) config('thaomoc.rate_limits.apply_coupon', 10);
        $key = 'apply_coupon|' . $request->ip() . '|' . (Auth::id() ?? 'guest');

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn thao tác quá nhanh, vui lòng thử lại sau ít phút.',
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $cart = $this->cartService->getOrCreateCart();
        $result = $this->couponService->applyToCart($request->string('code')->toString(), $cart->id, Auth::id());

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        RateLimiter::clear($key);

        $summary = $this->cartService->getCartSummary($cart->id);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'code' => $result['code'],
            'subtotal' => $summary['subtotal'],
            'discount' => $summary['discount'],
            'shippingFee' => $summary['shipping_fee'],
            'total' => $summary['total'],
        ]);
    }

    public function removeCoupon(): JsonResponse
    {
        $this->couponService->removeAppliedCoupon();
        $cart = $this->cartService->getOrCreateCart();
        return $this->getCartSummaryJson($cart->id);
    }

    private function getCartSummaryJson(int $cartId): JsonResponse
    {
        $summary = $this->cartService->getCartSummary($cartId);
        return response()->json([
            'success' => true,
            'subtotal' => $summary['subtotal'],
            'discount' => $summary['discount'],
            'appliedCode' => $summary['applied']['code'] ?? null,
            'shippingFee' => $summary['shipping_fee'],
            'total' => $summary['total'],
            'itemCount' => $summary['item_count'],
        ]);
    }
}