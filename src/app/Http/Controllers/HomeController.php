<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $pageTitle = __('Trang chủ');

        // --- FAKE DỮ LIỆU KHỚP 100% VỚI PHẦN @else ---
        $fakeProducts = [
            (object) [
                'url' => '#',
                'image' => 'assets/images/cu_tam_that_1.jpg',
                'name' => 'Củ Tam Thất Bắc Khô Loại 1 Nguyên Củ Chuẩn',
                'price' => '800.000₫',
                'old_price' => '1.100.000₫', // Lưu ý: dùng old_price (có gạch dưới)
                'discount' => 20,           // Số nguyên
                'rating' => '4.9',
                'sold' => '3.1k'
            ],
            (object) [
                'url' => '#',
                'image' => 'assets/images/hoa_tam_that_1.png',
                'name' => 'Nụ Tam Thất Bắc Loại 1 Túi 500g Nguyên Chất',
                'price' => '150.000₫',
                'old_price' => '950.000₫',
                'discount' => 35,
                'rating' => '4.9',
                'sold' => '2.7k'
            ],
            (object) [
                'url' => '#',
                'image' => 'assets/images/thit_trau_gac_bep.png',
                'name' => 'Thịt Trâu Gác Bếp Chuẩn Tây Bắc - Mắc Khén Hạt Dổi',
                'price' => '115.000₫',
                'old_price' => '155.000₫',
                'discount' => 25,
                'rating' => '4.8',
                'sold' => '1.8k'
            ],
            (object) [
                'url' => '#',
                'image' => 'assets/images/tao_do1.png',
                'name' => 'Táo Đỏ Sấy Khô Quả To Loại 1',
                'price' => '185.000₫',
                'old_price' => '225.000₫',
                'discount' => 18,
                'rating' => '4.9',
                'sold' => '2.2k'
            ]
        ];
        // ------------------------------------------------
        
        return view('home', [
            'pageTitle' => $pageTitle,
            'products' => $fakeProducts, // Truyền xuống View
        ]);
    }
}