<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Hiển thị trang chi tiết sản phẩm
     * Route: /san-pham/{slug}
     */
    public function show($slug)
    {
        // 🎭 FAKE PRODUCT - Dùng stdClass để giả lập Eloquent Model
        $product = (object) [
            'id' => 1,
            'name' => 'Thịt Trâu Gác Bếp Sơn La Hun Khói 48h Thượng Hạng 500g',
            'slug' => 'thit-trau-gac-bep-son-la-500g',
            'description' => '
            <p>Thịt trâu thả rông vùng núi cao Sơn La, chọn phần bắp và thăn sau, tẩm ướp mắc khén – hạt dổi – ớt rừng – sả rồi treo gác bếp, hun khói củi suốt 48 giờ.</p>
            <ul>
                <li>Thớ thịt đen óng, xé sợi dai ngọt, cay thơm đậm đà.</li>
                <li>Không chất bảo quản, không phẩm màu, không hun công nghiệp.</li>
                <li>Cách dùng: nướng lại 3–5 phút hoặc quay nồi chiên không dầu 160°C trong 6 phút, đập dập xé sợi chấm chẩm chéo.</li>
                <li>Bảo quản: ngăn mát 7 ngày, ngăn đông 6 tháng.</li>
            </ul>
        ',
            'meta_description' => 'Thịt trâu gác bếp Sơn La hun khói 48 giờ, tẩm mắc khén hạt dổi chuẩn bản địa. Gói 500g hút chân không.',
            'sku' => 'TMX-TTG-500',
            'price' => 265000,
            'old_price' => 330000,
            'discount_percent' => 20,
            'stock' => 100,
            'sold_count' => 3140,
            'avg_rating' => 4.9,
            'review_count' => 216,
            'image' => 'images/thit_trau_gac_bep.png',
            'images' => [
                'images/thit_trau_gac_bep.png',
                'images/cu_tam_that_1.jpg',
                'images/hoa_tam_that_1.png',
                'images/tao_do1.png',
            ],
            'url' => '/san-pham/thit-trau-gac-bep-son-la-500g',
            'category_id' => 1,
            'category' => (object) [
                'id' => 1,
                'name' => 'Đặc sản thịt gác bếp',
                'slug' => 'thit-gac-bep',
                'url' => '/danh-muc/thit-gac-bep',
            ],
        ];

        // 🎭 FAKE BREADCRUMBS
        $breadcrumbs = [
            ['label' => 'Trang chủ', 'url' => '/'],
            ['label' => $product->category->name, 'url' => $product->category->url],
            ['label' => $product->name, 'url' => null],
        ];

        // 🎭 FAKE VARIANTS
        $variants = [
            ['name' => 'weight', 'value' => '250', 'label' => '250g', 'label_group' => 'Khối lượng', 'selected' => false],
            ['name' => 'weight', 'value' => '500', 'label' => '500g', 'label_group' => 'Khối lượng', 'selected' => true],
            ['name' => 'weight', 'value' => '1000', 'label' => '1kg (+255K)', 'label_group' => 'Khối lượng', 'selected' => false],
        ];

        // 🎭 FAKE REVIEWS (3 đánh giá mẫu)
        $reviews = collect([
            (object) [
                'user_name' => 'Anh Dũng (Bình Thạnh)',
                'avatar' => 'images/placeholder.svg',
                'rating' => 5,
                'content' => 'Đúng vị hồi đi Sơn La mình ăn, khói thơm không khét, xé sợi chấm tương ớt cũng ngon. Sẽ ủng hộ shop dài dài.',
            ],
            (object) [
                'user_name' => 'Chị Hằng (Cầu Giấy, Hà Nội)',
                'avatar' => 'images/placeholder.svg',
                'rating' => 5,
                'content' => 'Đóng gói hút chân không kỹ, ship Hà Nội 2 ngày vẫn ngon. Thịt mềm, thơm mùi mắc khén đặc trưng.',
            ],
            (object) [
                'user_name' => 'Anh Tuấn (Thủ Đức)',
                'avatar' => 'images/placeholder.svg',
                'rating' => 5,
                'content' => 'Mua biếu bố vợ, ông khen hết lời. Gói 500g ăn vừa đủ, hút chân không sạch sẽ. 10 điểm!',
            ],
        ]);

        // 🎭 FAKE RATING STATS
        $ratingStats = [
            'avg' => 4.9,
            'total' => 216,
        ];

        // 🎭 FAKE RELATED PRODUCTS (4 sản phẩm liên quan)
        $relatedProducts = collect([
            (object) [
                'id' => 2,
                'name' => 'Chẩm Chéo Chấm Thịt Gác Bếp Chuẩn Bản 200g',
                'slug' => 'cham-cheo-200g',
                'url' => '/san-pham/cham-cheo-200g',
                'image' => 'images/gia_vi_tay_bac.png',
                'price' => 65000,
                'old_price' => 80000,
                'discount_percent' => 19,
                'avg_rating' => 4.8,
                'sold_count' => 1600,
            ],
            (object) [
                'id' => 3,
                'name' => 'Mắc Khén Khô Tây Bắc Nguyên Hạt 100g',
                'slug' => 'mac-khen-100g',
                'url' => '/san-pham/mac-khen-100g',
                'image' => 'images/gia_vi_tay_bac.png',
                'price' => 75000,
                'old_price' => 95000,
                'discount_percent' => 21,
                'avg_rating' => 4.9,
                'sold_count' => 2300,
            ],
            (object) [
                'id' => 4,
                'name' => 'Hạt Dổi Rừng Sơn La Loại 1 Túi 50g',
                'slug' => 'hat-doi-50g',
                'url' => '/san-pham/hat-doi-50g',
                'image' => 'images/gia_vi_tay_bac.png',
                'price' => 135000,
                'old_price' => 170000,
                'discount_percent' => 21,
                'avg_rating' => 4.9,
                'sold_count' => 1100,
            ],
            (object) [
                'id' => 5,
                'name' => 'Rượu Táo Mèo Yên Bái Ủ Truyền Thống 750ml',
                'slug' => 'ruou-tao-meo-750ml',
                'url' => '/san-pham/ruou-tao-meo-750ml',
                'image' => 'images/mat_ong.png',
                'price' => 155000,
                'old_price' => 190000,
                'discount_percent' => 18,
                'avg_rating' => 4.7,
                'sold_count' => 760,
            ],
        ]);

        // 🎯 Trả về view với toàn bộ dữ liệu fake
        return view('product', [
            'product' => $product,
            'breadcrumbs' => $breadcrumbs,
            'variants' => $variants,
            'reviews' => $reviews,
            'ratingStats' => $ratingStats,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}