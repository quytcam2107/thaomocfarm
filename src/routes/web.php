<?php

declare(strict_types=1);

use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ProductController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Luật quan trọng: route CÓ prefix cụ thể PHẢI đặt TRƯỚC route {slug} bắt-tất.
| Route danh mục {slug} luôn ở DƯỚI CÙNG để không nuốt /san-pham/*, /gio-hang/*...
*/

// 1. Trang chủ
Route::get('/', [HomeController::class, 'index'])->name('web.home');

// 2. Tất cả sản phẩm (MỚI)
Route::get('/tat-ca-san-pham', [ProductController::class, 'index'])->name('web.products.index');

// 3. Danh mục — URL chuẩn /danh-muc/{slug}
Route::get('/danh-muc/{slug}', [CategoryController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('web.category.show');

// 4. Gio hang
Route::get('/gio-hang', [CartController::class, 'index'])->name('web.cart.index');
Route::post('/gio-hang/them', [CartController::class, 'add'])->name('web.cart.add');
Route::post('/gio-hang/cap-nhat/{itemId}', [CartController::class, 'update'])->name('web.cart.update');
Route::delete('/gio-hang/xoa/{itemId}', [CartController::class, 'remove'])->name('web.cart.remove');
Route::get('/gio-hang/count', [CartController::class, 'count'])->name('web.cart.count');

// 4b. Ma giam gia cho gio hang (session-based, checkout doc o buoc sau)
Route::post('/gio-hang/ma-giam-gia/ap-dung', [CartController::class, 'applyCoupon'])->name('web.cart.coupon.apply');
Route::delete('/gio-hang/ma-giam-gia', [CartController::class, 'removeCoupon'])->name('web.cart.coupon.remove');

// 5. Checkout Routes
Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('web.checkout.index');
Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('web.checkout.store');
Route::get('/dat-hang-thanh-cong/{order_number}', [CheckoutController::class, 'success'])->name('web.checkout.success');

// 6. Chi tiet san pham
Route::get('/san-pham/{slug}', [ProductController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('web.product.show');

// 7. Alias pretty-url /{slug} — BẮT BUỘC ở cuối cùng, KHÔNG được đặt trước
Route::get('/{slug}', [CategoryController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('web.category.pretty');