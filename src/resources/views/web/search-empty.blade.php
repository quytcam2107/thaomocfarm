{{-- Trang tìm kiếm khi để trống từ khoá /tim-kiem --}}
<x-layouts.app title="Tìm kiếm | Mộc Xanh"
    seoDescription="Tìm thảo mộc và đặc sản Tây Bắc theo tên, danh mục hoặc mô tả." ogType="website"
    bodyClass="page-category">

    <div class="container">
        <x-ui.breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('web.home')],
            ['label' => 'Tìm kiếm', 'url' => null],
        ]" />

        <div class="page-head">
            <h1>Bạn muốn tìm gì hôm nay?</h1>
            <p>Nhập từ khoá như “tam thất”, “trà hoa”, “táo đỏ”… vào ô tìm kiếm ở đầu trang.</p>
        </div>

        <div class="seo-text">
            <p><a class="btn btn--leaf" href="{{ route('web.products.index') }}">Xem tất cả sản phẩm</a></p>
        </div>
    </div>
</x-layouts.app>
