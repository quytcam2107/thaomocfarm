<x-layouts.app :title="$product->name . ' - ' . number_format($product->price) . '₫ | Thảo Mộc Xanh'"
    :seoDescription="$product->meta_description" ogType="product" :ogImage="asset($product->image)"
    bodyClass="has-buybar">

    {{-- Inject Schema JSON-LD vào slot $schema của layout --}}
    <x-slot name="schema">
        <x-product.schema :product="$product" />
    </x-slot>

    <div class="container">
        <x-ui.breadcrumb :items="$breadcrumbs" />

        <div class="pd-layout">
            <x-product.gallery :images="$product->images" :alt="$product->name" />
            <x-product.info :product="$product" :variants="$variants" />
        </div>

        <x-product.tabs :description="$product->description" :reviews="$reviews" :ratingStats="$ratingStats" />

        <x-product.related :products="$relatedProducts" />
    </div>

    {{-- Inject Buybar vào slot $extra (nằm sau footer) --}}
    <x-slot name="extra">
        <x-product.buybar :product="$product" />
    </x-slot>

</x-layouts.app>