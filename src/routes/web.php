<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;

Route::get('/', HomeController::class)->name('home');
Route::get('/san-pham/{slug}', [ProductController::class, 'show'])->name('products.show');