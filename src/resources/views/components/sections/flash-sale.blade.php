@props(['products' => null])

{{-- CƠ CHẾ RENDER MỚI (theo yêu cầu): section Flash Sale KHÔNG còn do PHP đổ
dữ liệu chi tiết nữa. Blade chỉ in KHUNG (head + đếm ngược + rail rỗng) kèm:
- data-flash-home : marker để flash-live.js nhận diện & fetch
- data-flash-url : URL API web.flash-sale.home (JS gọi mỗi 3 phút)
- data-ends : VẪN giữ contract — marker app.js dynamic import @tm/product
(countdown.js đếm tới nửa đêm theo giờ máy người xem)
$products (nếu controller còn truyền) chỉ dùng để biết "có phiên hay không"
nhằm quyết định render khung; nội dung card/stats do JS cập nhật realtime. --}}
@if($products !== null || true)
    <section class="flash reveal" id="flash" aria-labelledby="flashTitle" data-flash-home
        data-flash-url="{{ route('web.flash-sale.home') }}">
        <div class="container">
            {{-- HEAD: [tiêu đề] ..... [đếm ngược] — 2 thành phần LUÔN ngang hàng trên mọi
            responsive (kể cả điện thoại): CSS .flash__head nowrap (partials/14-flash-sale.css).
            Label đếm ngược tách 2 bản: .cd-label--full / .cd-label--short (<360px). --}} <div class="flash__head">
                <h2 class="flash__title" id="flashTitle">⚡ Flash Sale</h2>

                <p class="countdown" data-ends="{{ $products['ends_at_unix'] ?? now()->addDay()->startOfDay()->timestamp }}"
                    role="timer" aria-live="polite">
                    <span class="cd-label cd-label--full">Kết thúc sau</span>
                    <b class="cd" id="cdH">--</b>:<b class="cd" id="cdM">--</b>:<b class="cd" id="cdS">--</b>
                </p>
        </div>

        {{-- STATS: dải social proof — JS điền nội dung mỗi 3 phút; rỗng thì CSS ẩn --}}
        <p class="flash__stats" data-flash-stats hidden></p>

        <div class="flash__rail no-scrollbar" role="region" aria-label="Deal chớp nhoáng, cuộn ngang" tabindex="0"
            data-flash-rail>
            {{-- Server-render dự phòng (SEO / JS tắt): vẫn đổ 1 lần nếu controller có data,
            sau đó JS ghi đè khi fetch về. --}}
            @if($products && !empty($products['items']))
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
            @endif
        </div>
        </div>
    </section>
@endif