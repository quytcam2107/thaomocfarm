<x-layouts.app title="Mộc Xanh — Thảo mộc nguyên chất & Đặc sản Tây Bắc">
    <x-sections.hero />
    <x-sections.usp />
    <x-sections.collections :categories="$homeCategories ?? null" />
    <x-sections.flash-sale :products="$flashProducts ?? null" />
    <x-sections.coupons :coupons="$coupons ?? null" />
    <x-sections.best-sellers :products="$bestSellers ?? null" />
    <x-sections.herbal-tea :products="$herbalTeaProducts ?? null" />
    <x-sections.region />
    <x-sections.stats />
    <x-sections.testimonials :reviews="$testimonials ?? null" />
    <x-sections.trust />
    <x-sections.tips />
    <x-sections.newsletter />
</x-layouts.app>