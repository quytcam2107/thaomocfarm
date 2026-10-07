<?php

declare(strict_types=1);

use App\Http\Controllers\Web\BlogController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\SearchController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. Trang chủ
Route::get('/', [HomeController::class, 'index'])->name('web.home');

// 2. Tất cả sản phẩm
Route::get('/tat-ca-san-pham', [ProductController::class, 'index'])->name('web.products.index');

// 2b. Tìm kiếm — phải đặt TRƯỚC route {slug} bên dưới
Route::get('/tim-kiem', [SearchController::class, 'index'])->name('web.search.index');
Route::get('/tim-kiem/goi-y', [SearchController::class, 'suggest'])->name('web.search.suggest');

// 3. Danh mục — URL chuẩn /danh-muc/{slug}
Route::get('/danh-muc/{slug}', [CategoryController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('web.category.show');

// 4. Gio hang
Route::get('/gio-hang', [CartController::class, 'index'])->name('web.cart.index');
Route::post('/gio-hang/them', [CartController::class, 'add'])->name('web.cart.add');
// 4a. Mua nhanh (Buy Now): them vao gio roi chuyen thang den thanh toan — dat trong nhom /gio-hang/*, TRƯỚC catch-all {slug}
Route::post('/gio-hang/mua-ngay', [CartController::class, 'buyNow'])->name('web.cart.buy-now');
Route::post('/gio-hang/cap-nhat/{itemId}', [CartController::class, 'update'])->name('web.cart.update');
Route::delete('/gio-hang/xoa/{itemId}', [CartController::class, 'remove'])->name('web.cart.remove');
Route::get('/gio-hang/count', [CartController::class, 'count'])->name('web.cart.count');

// 4b. Ma giam gia cho gio hang
Route::post('/gio-hang/ma-giam-gia/ap-dung', [CartController::class, 'applyCoupon'])->name('web.cart.coupon.apply');
Route::delete('/gio-hang/ma-giam-gia', [CartController::class, 'removeCoupon'])->name('web.cart.coupon.remove');

// 5. Checkout Routes
Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('web.checkout.index');
Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('web.checkout.store');
Route::get('/dat-hang-thanh-cong/{order_number}', [CheckoutController::class, 'success'])->name('web.checkout.success');

// 5a. Địa chính 2 cấp (tỉnh/thành → xã/phường) cho cascading select checkout — TRƯỚC catch-all /{slug}
Route::get('/dia-chi/xa-phuong', [LocationController::class, 'wards'])->name('web.locations.wards');

// 6. Chi tiet san pham
Route::get('/san-pham/{slug}', [ProductController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('web.product.show');

// 6a. Danh gia san pham (PDP) — NEW: dat TRONG nhom /san-pham/*, TRƯỚC catch-all /{slug}
Route::post('/san-pham/{slug}/danh-gia', [ReviewController::class, 'store'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('web.product.reviews.store');
Route::post('/danh-gia/{review}/huu-ich', [ReviewController::class, 'helpful'])
    ->where('review', '[0-9]+')
    ->name('web.review.helpful');
// NEW: admin/staff phản hồi từng đánh giá — cùng nhóm /danh-gia/*, TRƯỚC catch-all /{slug}
Route::post('/danh-gia/{review}/phan-hoi', [ReviewController::class, 'reply'])
    ->where('review', '[0-9]+')
    ->name('web.review.reply');

// 6b. Cac trang tinh "Ho tro" (footer) — BẮT BUỘC đặt TRƯỚC catch-all /{slug} bên dưới
Route::get('/huong-dan-dat-hang', [PageController::class, 'orderGuide'])->name('web.page.order-guide');
Route::get('/chinh-sach-doi-tra', [PageController::class, 'returnPolicy'])->name('web.page.return-policy');
Route::get('/chinh-sach-bao-mat', [PageController::class, 'privacyPolicy'])->name('web.page.privacy');
Route::get('/dieu-khoan-su-dung', [PageController::class, 'terms'])->name('web.page.terms');

// 6c. Cac trang tinh "Ve chung toi" (footer moi): Gioi thieu + Lien he — cung phai dat TRƯỚC catch-all /{slug}
Route::get('/gioi-thieu', [PageController::class, 'about'])->name('web.page.about');
Route::get('/lien-he', [PageController::class, 'contact'])->name('web.page.contact');

// 6d. Cam nang (blog) — dat TRƯỚC catch-all /{slug}; prefix web. theo quy ước
Route::get('/cam-nang', [BlogController::class, 'index'])->name('web.blog.index');
Route::get('/cam-nang/category/{slug}', [BlogController::class, 'category'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('web.blog.category');
Route::get('/cam-nang/{slug}', [BlogController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('web.blog.show');

// 7. Alias pretty-url /{slug} — BẮT BUỘC ở cuối cùng
Route::get('/{slug}', [CategoryController::class, 'show'])
    ->where('slug', '[a-z0-9\-]+')
    ->name('web.category.alias');