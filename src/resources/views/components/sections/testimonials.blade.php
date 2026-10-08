@props(['reviews' => null])

<section class="container voices reveal" aria-labelledby="voiceTitle">
    <h2 class="sec-title" id="voiceTitle">Khách hàng nói gì về Mộc Xanh?</h2>

    <div class="voices__grid">
        @if($reviews && count($reviews))
            @foreach($reviews as $r)
                <article class="voice">
                    <p class="voice__head">
                        <span class="voice__avatar">
                            <img src="{{ asset($r->avatar ?? 'assets/images/placeholder.svg') }}" alt="Ảnh khách hàng"
                                width="88" height="88" loading="lazy">
                        </span>

                        <span>
                            <b>{{ $r->name }}</b>
                            <small>{{ $r->location }} · đã mua {{ $r->orders }} đơn</small>
                        </span>
                    </p>

                    <p class="stars" aria-label="5 trên 5 sao">★★★★★</p>

                    <p>"{{ $r->content }}"</p>
                </article>
            @endforeach
        @else

            <article class="voice">
                <p class="voice__head">
                    <span class="voice__avatar">
                        <img src="{{ asset('assets/images/placeholder.svg') }}" alt="Ảnh khách hàng chị Hương" width="88"
                            height="88" loading="lazy">
                    </span>

                    <span>
                        <b>Chị Thu Hương</b>
                        <small>Cầu Giấy, Hà Nội · đã mua 6 đơn</small>
                    </span>
                </p>

                <p class="stars" aria-label="5 trên 5 sao">★★★★★</p>

                <p>
                    "Củ tam thất khô bên Mộc Xanh tự nhiên, củ chắc và
                    thơm đặc trưng. Mình mua về dùng và thấy khá hài lòng,
                    đóng gói cũng cẩn thận, sạch sẽ."
                </p>
            </article>

            <article class="voice">
                <p class="voice__head">
                    <span class="voice__avatar">
                        <img src="{{ asset('assets/images/placeholder.svg') }}" alt="Ảnh khách hàng anh Minh" width="88"
                            height="88" loading="lazy">
                    </span>

                    <span>
                        <b>Anh Quốc Minh</b>
                        <small>Thủ Đức, TP.HCM · đã mua 4 đơn</small>
                    </span>
                </p>

                <p class="stars" aria-label="5 trên 5 sao">★★★★★</p>

                <p>
                    "Mình mua nụ hoa tam thất để pha trà uống hằng ngày.
                    Hoa khô khá đẹp, búp nguyên, pha lên nước có mùi thơm
                    nhẹ và vị đặc trưng. Đóng gói kỹ nên nhận hàng rất yên tâm."
                </p>
            </article>

            <article class="voice">
                <p class="voice__head">
                    <span class="voice__avatar">
                        <img src="{{ asset('assets/images/placeholder.svg') }}" alt="Ảnh khách hàng cô Lan" width="88"
                            height="88" loading="lazy">
                    </span>

                    <span>
                        <b>Cô Ngọc Lan</b>
                        <small>Hoàn Kiếm, Hà Nội · đã mua 9 đơn</small>
                    </span>
                </p>

                <p class="stars" aria-label="5 trên 5 sao">★★★★★</p>

                <p>
                    "Mình đã mua cả củ tam thất và nụ hoa tam thất của Mộc Xanh.
                    Sản phẩm được đóng gói sạch sẽ, thông tin rõ ràng, tư vấn
                    nhiệt tình. Nụ hoa khô đều, dễ pha trà và hương vị khá dịu."
                </p>
            </article>

        @endif
    </div>
</section>
