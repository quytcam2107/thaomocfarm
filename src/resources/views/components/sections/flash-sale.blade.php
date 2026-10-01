@props(['products' => null])

@if($products && !empty($products['items']))
    <section class="flash reveal" id="flash" aria-labelledby="flashTitle">
        <div class="container">
            <div class="flash__head">
                {{-- Giữ UI của B, nhưng dùng text của A --}}
                <h2 class="flash__title" id="flashTitle">⚡ Flash Sale hôm nay</h2>

                {{-- Thêm data-ends của A vào để JS đếm ngược hoạt động đúng --}}
                <p class="countdown" data-ends="{{ $products['ends_at'] }}" role="timer" aria-live="polite">
                    Kết thúc sau
                    <b class="cd" id="cdH">00</b>:<b class="cd" id="cdM">00</b>:<b class="cd" id="cdS">00</b>
                </p>

                {{-- Giữ UI của B, nhưng dùng route của A --}}
                <a class="flash__all" href="{{ route('web.home') }}">Xem tất cả ưu đãi →</a>
            </div>

            {{-- Dải social proof tổng của phiên flash sale (data mới từ HomeService) --}}
            <p class="flash__stats">
                @if(($products['sold_today'] ?? 0) > 0)
                    <span class="flash__chip">🔥 Đã bán {{ number_format((int) $products['sold_today'], 0, ',', '.') }} sản phẩm
                        hôm nay</span>
                @endif
                @if(($products['urgent_count'] ?? 0) > 0)
                    <span class="flash__chip flash__chip--hot">{{ (int) $products['urgent_count'] }} deal sắp cháy hàng</span>
                @endif
            </p>

            <div class="flash__rail no-scrollbar" role="region" aria-label="Deal chớp nhoáng, cuộn ngang" tabindex="0">
                {{-- Đổi sang flash-card: thêm thanh tiến độ % đã bán + text hook đầu card --}}
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