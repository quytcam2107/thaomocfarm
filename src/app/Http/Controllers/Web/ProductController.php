<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CatalogService;

class ProductController extends Controller
{
    /**
     * Hiển thị trang chi tiết sản phẩm
     * Route: /san-pham/{slug}
     */
    public function show(CatalogService $catalog, string $slug)
    {
        $data = $catalog->getProductDetail($slug);
        // Atomic increment view_count, không ảnh hưởng đến cache
        Product::where('id', $data['product']->id)->increment('view_count');
        return view('web.product', $data);
    }
}