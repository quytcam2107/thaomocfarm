@props(['categories' => null])
<section class="container collections reveal" aria-labelledby="colTitle">
    <h2 class="sec-title" id="colTitle">Danh mục nổi bật</h2>
    <div class="col-grid">
        @if($categories && count($categories))
            @foreach($categories as $cat)
                <x-ui.category-card :url="$cat->url" :image="$cat->image" :name="$cat->name" :count="$cat->products_count" />
            @endforeach
        @else
            <x-ui.category-card url="{{ url('/category/thao-moc') }}" image="images/thaomoc.png" name="Thảo mộc"
                :count="24" />
            <x-ui.category-card url="{{ url('/category/thit-gac-bep') }}" image="images/thị_gac_bep.png" name="Thịt gác bếp"
                :count="32" />
            <x-ui.category-card url="{{ url('/category/gia-vi-tay-bac') }}" image="images/gia_vi_tay_bac.png"
                name="Gia vị Tây Bắc" :count="21" />
            <x-ui.category-card url="{{ url('/category/mat-ong') }}" image="images/mat_ong.png" name="Mật ong"
                :count="11" />
        @endif
    </div>
</section>