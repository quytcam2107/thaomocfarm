<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    /**
     * Hiển thị trang chi tiết sản phẩm
     * Route: /san-pham/{slug}
     */
    public function show($slug)
    {
        return view('web.product', [
            'product' => '',
        ]);
    }
}