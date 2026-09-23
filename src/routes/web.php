<?php

declare(strict_types=1);

use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CategoryController;
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

// 2. Sản phẩm chi tiết (prefix cố định)
Route::get('/san-pham/{slug}', [ProductController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('web.product.show');

// 3. Thêm vào giỏ (POST)
Route::post('/gio-hang/them', [CartController::class, 'add'])->name('web.cart.add');

// 4. Danh mục — URL chuẩn /danh-muc/{slug}
Route::get('/danh-muc/{slug}', [CategoryController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('web.category.show');

// 5. Alias pretty-url /{slug} — BẮT BUỘC ở cuối cùng, KHÔNG được đặt trước
Route::get('/{slug}', [CategoryController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('web.category.pretty');