<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('web.home');
Route::get('{slug}', [CategoryController::class, 'show'])->name('web.category.show');