@props(['coupons' => null])
<section class="container coupons reveal" aria-labelledby="cpnTitle">
    <h2 class="sec-title" id="cpnTitle">Mã giảm giá</h2>
    <div class="cpn-rail no-scrollbar" role="region" aria-label="Mã giảm giá, cuộn ngang" tabindex="0">
        @if($coupons && count($coupons))
            @foreach($coupons as $c)
                <x-ui.coupon-card :code="$c->code" :desc="$c->desc" :minOrder="$c->min_order" :exp="$c->exp" />
            @endforeach
        @else
            <x-ui.coupon-card code="SALE9K" minOrder="Giảm 9K" desc="Đơn tối thiểu 99K · tối đa 1 mã/đơn"
                exp="HSD: 30/09" />
            <x-ui.coupon-card code="TAYBAC20" minOrder="-20K" desc="Áp dụng riêng nhóm đặc sản Tây Bắc, đơn từ 250K"
                exp="HSD: 15/10" />
            <x-ui.coupon-card code="FREESSHIP" minOrder="0₫ ship" desc="Đơn tối thiểu 150K · toàn quốc" exp="HSD: 15/10" />
            <x-ui.coupon-card code="MOI10" minOrder="-10%" desc="Khách hàng mới · giảm tối đa 50K" exp="HSD: 31/12" />
        @endif
    </div>
</section>