@props(['products' => null])

@if($products && !empty($products['items']))
    <section class="flash reveal" id="flash" aria-labelledby="flashTitle">
        <div class="container">
            {{-- HEAD: [tiêu đề] ..... [đếm ngược] — 2 thành phần LUÔN ngang hàng trên mọi
            responsive (kể cả điện thoại):
            - CSS .flash__head bỏ flex-wrap:wrap -> nowrap; title co lại bằng min-width:0,
            countdown nowrap + flex-shrink:0 nên không bao giờ rơi xuống dòng chồng title
            (xem partials/14-flash-sale.css)
            - ĐÃ XÓA link .flash__all "Xem tất cả ưu đãi →" (href trỏ về đúng trang chủ, vô nghĩa)
            - data-ends = unix giây MỐC ĐẾM server đã clamp <= 24h — app.js đếm về mốc này qua contract
                .countdown[data-ends] + #cdH/#cdM/#cdS (KHÔNG đổi id/class/data-ends) - Label đếm ngược tách 2 bản:
                .cd-label--full ("Kết thúc sau") hiện trên màn thường, .cd-label--short ("Còn") chỉ hiện ở màn rất hẹp
                <360px (CSS display:none) — JS không đụng vào phần label này nên an toàn contract --}} <div
                class="flash__head">
                <h2 class="flash__title" id="flashTitle">⚡ Flash Sale</h2>

                <p class="countdown" data-ends="{{ $products['ends_at_unix'] }}" role="timer" aria-live="polite">
                    <span
                        class="cd-label cd-label--full">{{ !empty($products['is_ended']) ? 'Ưu đãi kết thúc sau' : 'Kết thúc sau' }}</span>
                    <b class="cd" id="cdH">00</b>:<b class="cd" id="cdM">00</b>:<b class="cd" id="cdS">00</b>
                </p>
        </div>

        {{-- STATS: dải social proof tổng của phiên flash sale (data từ HomeService)
        - Đặt DƯỚI head, sát rail deal: mắt đọc theo thứ tự tiêu đề → đếm giờ → bằng chứng bán chạy → deal
        - Nếu cả 2 số liệu đều bằng 0 thì ẩn cả dải, tránh dòng rỗng --}}
        @php
            $flashSoldToday = (int) ($products['sold_today'] ?? 0);
            $flashUrgentCount = (int) ($products['urgent_count'] ?? 0);
        @endphp
        @if($flashSoldToday > 0 || $flashUrgentCount > 0)
            <p class="flash__stats">
                @if($flashSoldToday > 0)
                    <span class="flash__chip">🔥 Đã bán {{ number_format($flashSoldToday, 0, ',', '.') }} sản phẩm
                        hôm nay</span>
                @endif
                @if($flashUrgentCount > 0)
                    <span class="flash__chip flash__chip--hot">{{ $flashUrgentCount }} deal sắp cháy hàng</span>
                @endif
            </p>
        @endif

        <div class="flash__rail no-scrollbar" role="region" aria-label="Deal chớp nhoáng, cuộn ngang" tabindex="0">
            {{-- flash-card: thanh tiến độ % đã bán + text hook đầu card --}}
            @foreach($products['items'] as $product)
                <x-ui.flash-card :url="$product['url']" :image="$product['image']" :name="$product['name']"
                    :price="$product['flash_price_formatted']" :oldPrice="$product['original_price_formatted']"
                    :discount="$product['discount_percent']" :rating="$product['rating_avg']" :sold="$product['sold_text']"
                    :productId="$product['product_id']" :variantId="$product['variant_id']"
                    :soldPercent="$product['sold_percent']" :soldTextToday="$product['sold_text_today']"
                    :urgentText="$product['urgent_text']" :urgentTone="$product['urgent_tone']"
                    :slotsLeft="$product['slots_left']" :progressText="$product['progress_text']"
                    :savedAmount="$product['saved_amount'] ?? null" />
            @endforeach
        </div>
        </div>
    </section>
@endif