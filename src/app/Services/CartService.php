<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Lấy hoặc tạo giỏ hàng
     */
    public function getOrCreateCart(): Cart
    {
        if (auth()->check()) {
            $cart = Cart::where('user_id', auth()->id())->first();

            if ($cart) {
                $this->mergeGuestCart($cart);
                return $cart;
            }

            return Cart::create([
                'user_id' => auth()->id(),
                'cart_token' => null,
            ]);
        }

        $cartToken = request()->cookie('cart_token');

        if ($cartToken) {
            $cart = Cart::where('cart_token', $cartToken)->first();
            if ($cart) {
                return $cart;
            }
        }

        return Cart::create([
            'user_id' => null,
            'cart_token' => Str::uuid()->toString(),
        ]);
    }

    /**
     * Gộp giỏ hàng guest vào user cart khi login
     */
    public function mergeGuestCart(Cart $userCart): void
    {
        $cartToken = request()->cookie('cart_token');

        if (!$cartToken) {
            return;
        }

        $guestCart = Cart::where('cart_token', $cartToken)
            ->whereNull('user_id')
            ->first();

        if (!$guestCart || $guestCart->id === $userCart->id) {
            return;
        }

        $guestItems = CartItem::where('cart_id', $guestCart->id)->get();

        foreach ($guestItems as $guestItem) {
            $existingItem = CartItem::where('cart_id', $userCart->id)
                ->where('product_variant_id', $guestItem->product_variant_id)
                ->first();

            if ($existingItem) {
                $existingItem->increment('qty', $guestItem->qty);
            } else {
                CartItem::create([
                    'cart_id' => $userCart->id,
                    'product_id' => $guestItem->product_id,
                    'product_variant_id' => $guestItem->product_variant_id,
                    'qty' => $guestItem->qty,
                ]);
            }

            $guestItem->delete();
        }

        $guestCart->delete();
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function addToCart(int $cartId, int $productId, int $variantId, int $qty = 1): CartItem
    {
        $variant = ProductVariant::where('id', $variantId)
            ->where('product_id', $productId)
            ->firstOrFail();

        if ($variant->stock < $qty) {
            throw new \Exception("Sản phẩm chỉ còn {$variant->stock} sản phẩm");
        }

        $cartItem = CartItem::where('cart_id', $cartId)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($cartItem) {
            $newQty = $cartItem->qty + $qty;

            if ($variant->stock < $newQty) {
                throw new \Exception("Số lượng trong giỏ vượt quá tồn kho (còn {$variant->stock})");
            }

            $cartItem->update(['qty' => $newQty]);
            return $cartItem->fresh();
        }

        return CartItem::create([
            'cart_id' => $cartId,
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'qty' => $qty,
        ]);
    }

    /**
     * Cập nhật số lượng
     */
    public function updateCartItem(int $cartId, int $itemId, int $qty): bool
    {
        $cartItem = CartItem::where('cart_id', $cartId)
            ->where('id', $itemId)
            ->with('variant')
            ->first();

        if (!$cartItem) {
            return false;
        }

        if ($cartItem->variant->stock < $qty) {
            throw new \Exception("Sản phẩm chỉ còn {$cartItem->variant->stock} sản phẩm");
        }

        return $cartItem->update(['qty' => $qty]);
    }

    /**
     * Xóa sản phẩm khỏi giỏ
     */
    public function removeItem(int $cartId, int $itemId): bool
    {
        return CartItem::where('cart_id', $cartId)
            ->where('id', $itemId)
            ->delete() > 0;
    }

    /**
     * Lấy chi tiết giỏ hàng
     * FIX: Dùng closure với tên bảng đầy đủ cho mọi select() trong ofMany
     * để tránh lỗi MySQL 1052 Column ambiguous
     */
    public function getCartDetails(int $cartId): array
    {
        $items = CartItem::where('cart_items.cart_id', $cartId)
            ->with([
                // Product relation - định danh bảng products
                'product' => function (BelongsTo $query): void {
                    $query->select([
                        'products.id',
                        'products.name',
                        'products.slug',
                    ])->with([
                                // coverImage là ofMany -> BẮT BUỘC định danh product_images.*
                                'coverImage' => function (HasOne $q): void {
                                    $q->select([
                                        'product_images.id',
                                        'product_images.product_id',
                                        'product_images.path',
                                    ]);
                                },
                            ]);
                },
                // Variant relation - định danh bảng product_variants
                'variant' => function (BelongsTo $query): void {
                    $query->select([
                        'product_variants.id',
                        'product_variants.product_id',
                        'product_variants.label',
                        'product_variants.price',
                        'product_variants.compare_price',
                        'product_variants.stock',
                    ]);
                },
            ])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'product_slug' => $item->product->slug,
                    'variant_label' => $item->variant->label,
                    'price' => format_vnd($item->variant->price),
                    'compare_price' => format_vnd($item->variant->compare_price),
                    'qty' => $item->qty,
                    'subtotal' => $item->qty * $item->variant->price,
                    'image' => asset(
                        $item->product->coverImage?->path
                        ? 'assets/images/' . ltrim($item->product->coverImage->path, '/')
                        : 'assets/images/placeholder.svg'
                    ),
                    'stock' => $item->variant->stock,
                    'url' => route('web.product.show', $item->product->slug),
                ];
            });
        $subtotal = $items->sum('subtotal');
        $totalItems = $items->sum('qty');
        
        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'total_items' => $totalItems,
        ];
    }

    /**
     * Đếm số lượng sản phẩm trong giỏ
     */
    public function getCartItemCount(int $cartId): int
    {
        return (int) CartItem::where('cart_id', $cartId)->sum('qty');
    }

    /**
     * Tính phí vận chuyển
     */
    public function calculateShippingFee(int $subtotal): int
    {
        $freeThreshold = config('thaomoc.shipping.free_threshold', 300000);

        if ($subtotal >= $freeThreshold) {
            return 0;
        }

        return config('thaomoc.shipping.default_fee', 30000);
    }
}