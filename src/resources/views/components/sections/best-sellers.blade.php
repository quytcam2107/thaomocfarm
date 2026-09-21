@props(['products' => null])
<section class="container best reveal" aria-labelledby="bestTitle">
    <h2 class="sec-title" id="bestTitle">Bán chạy tuần này</h2>
    <div class="product-grid">
        @if($products && count($products))
            @foreach($products as $p)
                <x-ui.product-card :url="$p->url" :image="$p->image" :name="$p->name" :price="$p->price"
                    :oldPrice="$p->old_price" :discount="$p->discount" :rating="$p->rating" :sold="$p->sold" />
            @endforeach
        @else
            <x-ui.product-card url="#" image="images/thit_trau_gac_bep.png"
                name="Thịt Trâu Gác Bếp Sơn La Hun Khói 48h Gói 500g" price="265.000₫" oldPrice="330.000₫" rating="4.9"
                sold="3.1k" />
            <x-ui.product-card url="#" image="images/tao_do1.png" name="Trà Hoa Cúc Sấy Khô Nguyên Bông Thư Giãn An Thần"
                price="52.000₫" oldPrice="85.000₫" rating="4.8" sold="1.9k" />
            <x-ui.product-card url="#" image="images/thit_trau_gac_bep.png"
                name="Hạt Mắc Ca Rang giòn Vỏ Mỏng 500g Kèm Dụng Cụ Tách" price="125.000₫" oldPrice="165.000₫" rating="4.8"
                sold="1.4k" />
            <x-ui.product-card url="#" image="images/thit_trau_gac_bep.png"
                name="Chè Shan Tuyết Hà Giang Cổ Thụ Thượng Hạng 200g" price="195.000₫" oldPrice="260.000₫" rating="4.9"
                sold="980" />
            <x-ui.product-card url="#" image="images/thit_trau_gac_bep.png" name="Lạp Xưởng Lợn Bản Tây Bắc Hun Khói 500g"
                price="145.000₫" oldPrice="180.000₫" rating="4.7" sold="1.1k" />
        @endif
    </div>
</section>