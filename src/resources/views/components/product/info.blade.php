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
                    <span>{{ $v['label'] }}</span>
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
            🛒 Thêm vào giỏ
        </button>
        <button class="btn btn--clay" id="buyNow" data-id="{{ $product->id }}">⚡ Mua ngay</button>
        <button class="btn btn--ghost pcard__fav--lg" aria-label="Thêm vào yêu thích" aria-pressed="false"
            data-product-id="{{ $product->id }}">♡ Yêu thích</button>
    </div>

    <ul class="pd-policy">
        <li>🚚 Freeship đơn từ 200K — giao 2h nội thành Hà Nội</li>
        <li>🧊 Đóng gói hút chân không, kèm túi giữ lạnh khi giao xa</li>
        <li>🔄 Đổi trả trong 7 ngày nếu lỗi nhà sản xuất</li>
    </ul>
</div>