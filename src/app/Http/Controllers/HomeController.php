<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $pageTitle = __('Trang chủ');

        return view('home', [
            'pageTitle' => $pageTitle,
        ]);
    }
}