@props(['categories' => null])
<section class="container collections reveal" aria-labelledby="colTitle">
    <h2 class="sec-title" id="colTitle">Danh mục nổi bật</h2>
    <div class="col-grid">
        @if($categories && count($categories))
            @foreach($categories as $cat)
                <x-ui.category-card :url="$cat['slug']" :image="$cat['image']" :name="$cat['name']"
                    :count="$cat['products_count'] ?? 0" />
            @endforeach
        @endif
    </div>
</section>