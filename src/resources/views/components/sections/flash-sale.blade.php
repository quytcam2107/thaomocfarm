@props(['products' => null])
<section class="flash reveal" id="flash" aria-labelledby="flashTitle">
    <div class="container">
        <div class="flash__head">
            <h2 class="flash__title" id="flashTitle">⚡ Giá siêu hời</h2>
            <p class="countdown" role="timer" aria-live="off">Kết thúc sau
                <b class="cd" id="cdH">00</b>:<b class="cd" id="cdM">00</b>:<b class="cd" id="cdS">00</b>
            </p>
            <a class="flash__all" href="{{ url('/category') }}">Xem tất cả →</a>
        </div>
        <div class="flash__rail no-scrollbar" role="region" aria-label="Deal chớp nhoáng, cuộn ngang" tabindex="0">
            @if($products && count($products))
                @foreach($products as $p)
                    <x-ui.product-card :url="$p->url" :image="$p->image" :name="$p->name" :price="$p->price"
                        :oldPrice="$p->old_price" :discount="$p->discount" :rating="$p->rating" :sold="$p->sold" />
                @endforeach
            @else
                <x-ui.product-card url="#" image="images/cu_tam_that_1.jpg"
                    name="Củ Tam Thất Bắc Khô Loại 1 Nguyên Củ Chuẩn" price="800.000₫" oldPrice="1.100.000₫" :discount="20"
                    rating="4.9" sold="3.1k" />
                <x-ui.product-card url="#" image="images/hoa_tam_that_1.png"
                    name="Nụ Tam Thất Bắc Loại 1 Túi 500g Nguyên Chất" price="150.000₫" oldPrice="950.000₫" :discount="35"
                    rating="4.9" sold="2.7k" />
                <x-ui.product-card url="#" image="images/thit_trau_gac_bep.png"
                    name="Thịt Trâu Gác Bếp Chuẩn Tây Bắc - Mắc Khén Hạt Dổi" price="115.000₫" oldPrice="155.000₫"
                    :discount="25" rating="4.8" sold="1.8k" />
                <x-ui.product-card url="#" image="images/tao_do1.png" name="Táo Đỏ Sấy Khô Quả To Loại 1" price="185.000₫"
                    oldPrice="225.000₫" :discount="18" rating="4.9" sold="2.2k" />
            @endif
        </div>
    </div>
</section>