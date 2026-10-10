@props(['product', 'variants' => []])

<div class="pd-info">
    <h1>{{ $product->name }}</h1>

    <p class="pd-meta">
        <span class="stars" aria-label="{{ $product->avg_rating ?? 5 }} trên 5 sao">★★★★★</span>
        {{ $product->avg_rating ?? 5.0 }}
        <span>· {{ $product->review_count ?? 0 }} đánh giá</span>
        <span>· {{ number_format($product->sold_count ?? 0) }} đã bán</span>
        <span>· SKU: {{ $product->sku }}</span>
    </p>

    {{-- NEW: block Flash Sale + countdown (tự ẩn khi SP không thuộc deal => UI cũ không đổi) --}}
    <x-product.flash-block :product="$product" />

    <p class="pd-price">
        <b class="price">{{ number_format($product->price) }}₫</b>
        @if($product->old_price)
            <s>{{ number_format($product->old_price) }}₫</s>
            <span class="off">-{{ $product->discount_percent }}%</span>
        @endif
    </p>

    @if(count($variants))
        <p class="opt-title" id="variantLabel">{{ $variants[0]['label_group'] ?? 'Phân loại' }}</p>
        <div class="pills" role="radiogroup" aria-labelledby="variantLabel">
            @foreach($variants as $v)
                <label class="pill">
                    <input type="radio" name="{{ $v['name'] ?? 'variant' }}" value="{{ $v['value'] }}" {{ ($v['selected'] ?? false) ? 'checked' : '' }}>
                    <span>{{ $v->label ?? $v['label'] }}</span>
                </label>
            @endforeach
        </div>
    @endif

    <p class="opt-title">Số lượng</p>
    <div class="qty" data-qty>
        <button type="button" data-step="-1" aria-label="Giảm số lượng">−</button>
        <input type="number" name="qty" inputmode="numeric" min="1" max="{{ $product->stock ?? 99 }}" value="1"
            aria-label="Số lượng">
        <button type="button" data-step="1" aria-label="Tăng số lượng">+</button>
    </div>

    <div class="pd-actions">
        <button class="btn btn--leaf add-cart" data-name="{{ $product->name }}" data-product-id="{{ $product->id }}">
            Thêm vào giỏ
        </button>
        {{-- Buy Now: JS (app.js) đọc data-product-id + radio variant_id:checked + input[name=qty] rồi POST
        /gio-hang/mua-ngay --}}
        {{-- NEW: thêm dòng phụ "Gọi điện và giao tận nơi" dưới text "Mua ngay".
        app.js lưu/khôi phục button.innerHTML nên span con không phá logic buyNow(). --}}
        <button class="btn btn--clay" id="buyNow" type="button" data-product-id="{{ $product->id }}"
            data-name="{{ $product->name }}">
            <span class="btn__main">Mua ngay</span>
            <a class="btn__sub" href="#" onclick="event.stopPropagation()"
                aria-label="Gọi điện và giao tận nơi qua hotline 0362 795 897">Gọi điện xác nhận và giao tận nơi</a>
        </button>
        {{-- <button class="btn btn--ghost pcard__fav--lg" aria-label="Thêm vào yêu thích" aria-pressed="false"
            data-product-id="{{ $product->id }}">♡ Yêu thích</button> --}}
    </div>

    {{-- NEW: dòng hotline "Gọi đặt mua" ngay dưới 2 nút Thêm vào giỏ / Mua ngay.
    Nằm NGOÀI .pd-actions (grid 2 cột) để không phá layout cặp nút; tel: mở trực tiếp
    trên mobile, giữ nguyên contract JS (.add-cart / #buyNow không đổi). --}}
    <p class="product-hotline mb-0 text-center">Gọi đặt mua <a href="tel:0362795897">0362.795.897</a> (7:30 - 22:00)</p>

    <ul class="pd-policy-pdp">
        <li>
            <img src="{{ asset('assets/images/svg/tick-check.svg') }}" alt="Vận chuyển">
            Miễn phí vận chuyển cho đơn từ 300K | Giao 1-3 ngày toàn quốc
        </li>
        <li>
            <img src="{{ asset('assets/images/svg/tick-check.svg') }}" alt="Kiểm tra">
            Được kiểm tra sản phẩm trước khi nhận.
        </li>
        <li>
            <img src="{{ asset('assets/images/svg/tick-check.svg') }}" alt="Hút chân không">
            Đóng gói hút chân không
        </li>
        <li>
            <img src="{{ asset('assets/images/svg/tick-check.svg') }}" alt="Đổi trả">
            Miễn phí đổi trả nếu có lỗi từ sản phẩm.
        </li>
    </ul>
</div>