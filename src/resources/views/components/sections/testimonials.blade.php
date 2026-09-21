@props(['reviews' => null])
<section class="container voices reveal" aria-labelledby="voiceTitle">
    <h2 class="sec-title" id="voiceTitle">Khách hàng nói gì</h2>
    <div class="voices__grid">
        @if($reviews && count($reviews))
            @foreach($reviews as $r)
                <article class="voice">
                    <p class="voice__head">
                        <span class="voice__avatar"><img src="{{ asset($r->avatar ?? 'images/placeholder.svg') }}"
                                alt="Ảnh khách hàng" width="88" height="88" loading="lazy"></span>
                        <span><b>{{ $r->name }}</b><small>{{ $r->location }} · đã mua {{ $r->orders }} đơn</small></span>
                    </p>
                    <p class="stars" aria-label="5 trên 5 sao">★★★★★</p>
                    <p>"{{ $r->content }}"</p>
                </article>
            @endforeach
        @else
            <article class="voice">
                <p class="voice__head">
                    <span class="voice__avatar"><img src="{{ asset('images/placeholder.svg') }}"
                            alt="Ảnh khách hàng chị Hương" width="88" height="88" loading="lazy"></span>
                    <span><b>Chị Thu Hương</b><small>Cầu Giấy, Hà Nội · đã mua 6 đơn</small></span>
                </p>
                <p class="stars" aria-label="5 trên 5 sao">★★★★★</p>
                <p>"Thịt trâu gác bếp đúng vị mình ăn hồi đi Sơn La, xé sợi chấm chẳng chéo là hết ý. Trà hoa hồng thì thơm
                    dịu, đóng gói kín đáo."</p>
            </article>
            <article class="voice">
                <p class="voice__head">
                    <span class="voice__avatar"><img src="{{ asset('images/placeholder.svg') }}"
                            alt="Ảnh khách hàng anh Minh" width="88" height="88" loading="lazy"></span>
                    <span><b>Anh Quốc Minh</b><small>Thủ Đức, TP.HCM · đã mua 4 đơn</small></span>
                </p>
                <p class="stars" aria-label="5 trên 5 sao">★★★★★</p>
                <p>"Mua combo quà Tết biếu đối tác, hộp đẹp và có giấy chứng nhận OCOP kèm theo nên rất yên tâm. Giao đúng
                    hẹn."</p>
            </article>
            <article class="voice">
                <p class="voice__head">
                    <span class="voice__avatar"><img src="{{ asset('images/placeholder.svg') }}" alt="Ảnh khách hàng cô Lan"
                            width="88" height="88" loading="lazy"></span>
                    <span><b>Cô Ngọc Lan</b><small>Hoàn Kiếm, Hà Nội · đã mua 9 đơn</small></span>
                </p>
                <p class="stars" aria-label="5 trên 5 sao">★★★★★</p>
                <p>"Mắc khén và hạt dổi thơm chuẩn, nấu món nào ra món đó. Shop tư vấn nhiệt tình, đổi trả cũng nhanh khi
                    mình đặt nhầm."</p>
            </article>
        @endif
    </div>
</section>