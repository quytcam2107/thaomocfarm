<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Thêm sản phẩm vào giỏ hàng.
     * Nếu guest: tạo cart bằng token trong cookie.
     * Nếu đã đăng nhập: dùng user_id.
     */
    public function addToCart(int $productId, ?int $variantId, int $qty): array
    {
        // Kiểm tra sản phẩm tồn tại và active
        $product = Product::where('id', $productId)->where('status', 'active')->first();
        if (!$product) {
            return ['success' => false, 'message' => 'Sản phẩm không tồn tại hoặc đã bị ẩn.'];
        }

        // Kiểm tra variant
        $variant = null;
        if ($variantId) {
            $variant = ProductVariant::where('id', $variantId)->where('product_id', $productId)->first();
            if (!$variant) {
                return ['success' => false, 'message' => 'Phiên bản sản phẩm không hợp lệ.'];
            }
        } else {
            // Nếu không truyền variant, lấy variant mặc định
            $variant = ProductVariant::where('product_id', $productId)->where('is_default', true)->first()
                ?? ProductVariant::where('product_id', $productId)->first();

            if (!$variant) {
                return ['success' => false, 'message' => 'Sản phẩm chưa có phiên bản để mua.'];
            }
            $variantId = $variant->id;
        }

        // Kiểm tra tồn kho
        if ($variant->stock < $qty) {
            return ['success' => false, 'message' => 'Sản phẩm không đủ số lượng trong kho.'];
        }

        // Lấy hoặc tạo giỏ hàng
        $cart = $this->getOrCreateCart();

        // Kiểm tra đã có sản phẩm này trong giỏ chưa
        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($existingItem) {
            // Đã có: cộng thêm số lượng
            $newQty = $existingItem->qty + $qty;
            if ($newQty > $variant->stock) {
                return ['success' => false, 'message' => 'Tổng số lượng vượt quá tồn kho.'];
            }
            $existingItem->update(['qty' => $newQty]);
        } else {
            // Chưa có: thêm mới
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'qty' => $qty,
            ]);
        }

        // Đếm tổng số sản phẩm trong giỏ
        $cartCount = CartItem::where('cart_id', $cart->id)->sum('qty');
        $cartTotal = $this->calculateCartTotal($cart->id);

        return [
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng',
            'cart_count' => $cartCount,
            'cart_total' => $cartTotal,
        ];
    }

    /**
     * Lấy giỏ hàng hiện tại hoặc tạo mới cho guest.
     */
    public function getOrCreateCart(): Cart
    {
        $userId = auth()->id();

        // Nếu đã đăng nhập, tìm cart theo user_id
        if ($userId) {
            $cart = Cart::where('user_id', $userId)->first();
            if ($cart) {
                return $cart;
            }
        }

        // Guest hoặc chưa có cart: dùng token từ cookie
        $cartToken = Cookie::get('cart_token');

        if ($cartToken) {
            $cart = Cart::where('cart_token', $cartToken)->first();
            if ($cart) {
                // Nếu đã đăng nhập mà cart chưa có user_id thì gán
                if ($userId && !$cart->user_id) {
                    $cart->update(['user_id' => $userId]);
                }
                return $cart;
            }
        }

        // Tạo cart mới
        $newToken = Str::uuid()->toString();
        $cart = Cart::create([
            'user_id' => $userId,
            'cart_token' => $newToken,
        ]);

        // Set cookie 30 ngày
        Cookie::queue('cart_token', $newToken, 60 * 24 * 30);

        return $cart;
    }

    /**
     * Tính tổng tiền giỏ hàng.
     */
    public function calculateCartTotal(int $cartId): int
    {
        $items = CartItem::where('cart_id', $cartId)
            ->with(['variant' => fn($q) => $q->select('id', 'price')])
            ->get();

        $total = 0;
        foreach ($items as $item) {
            if ($item->variant) {
                $total += $item->variant->price * $item->qty;
            }
        }

        return $total;
    }
}