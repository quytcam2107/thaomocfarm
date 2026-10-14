<x-layouts.app title="Mộc Xanh — Thảo mộc nguyên chất & Đặc sản Tây Bắc" :show-back-to-top="true">
    {{-- NEW TOOLTIP: wrapper scope để app.js chỉ nạp @tm/tooltip trên trang home
    (marker .home-tip-scope). display:contents -> không phá layout grid/flex sẵn có. --}}
    <div class="home-tip-scope" style="display:contents">
        <x-sections.hero />
        <x-sections.usp />
        <x-sections.collections :categories="$featuredCategories ?? null" />
        <x-sections.flash-sale :products="$flashProducts ?? null" />
        <x-sections.coupons :coupons="$coupons ?? null" />
        <x-sections.best-sellers :products="$bestSellers ?? null" />
        <x-sections.herbal-tea :products="$herbalTeaProducts ?? null" />
        <x-sections.region />
        <x-sections.stats />
        <x-sections.testimonials :reviews="$testimonials ?? null" />
        <x-sections.trust />
        <x-sections.tips :tips="$tips ?? null" />
        <x-sections.newsletter />
    </div>
</x-layouts.app>