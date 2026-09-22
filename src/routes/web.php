<?php

use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('web.home');
// Danh mục
Route::get('{slug}', [CategoryController::class, 'show'])->name('web.category.show'); 
// Sản phẩm
Route::get('san-pham/{slug}', [ProductController::class, 'show'])->name('web.product.show');
// Giỏ hàng
Route::post('/gio-hang/them', [CartController::class, 'add'])->name('web.cart.add');