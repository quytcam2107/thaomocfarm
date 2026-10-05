@props([
    'title' => 'Mộc Xanh',
    'seoDescription' => 'Thảo mộc & đặc sản Tây Bắc',
    'ogType' => 'website',
    'ogImage' => null,
    'bodyClass' => '',
    'hideCatnav' => false,
    'hideFloatnav' => false,
    'showBackToTop' => false,
])
<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#2e7d4f">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/logo_thao_moc_farm.png') }}">

    {{-- SEO Meta --}}
    <meta name="description" content="{{ $seoDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:locale" content="vi_VN">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('assets/images/og-cover.jpg') }}">

    <link rel="preload" href="{{ asset('fonts/lora-vietnamese-700-normal.woff2') }}" as="font" type="font/woff2"
        crossorigin>
    <link rel="preload" href="{{ asset('fonts/be-vietnam-pro-vietnamese-400-normal.woff2') }}" as="font"
        type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    {{-- NEW LIGHTGALLERY: CSS vendor nạp bằng
    <link> RIÊNG qua stack (KHÔNG @import
    vào style.css — style.css đã có @import fonts ở partial 01-base.css, mọi @import
    đứng sau sẽ bị browser bỏ theo đặc tả CSS). View nào push mới tải. --}}
    @stack('vendorStyles')

    {{-- Import map: cho phép module con import bằng tên định danh @tm/... thay vì đường dẫn tương đối --}}
    <script type="importmap">
    {
        "imports": {
            "@tm/core": "{{ asset('assets/js/app-core.js') }}",
            "@tm/ui": "{{ asset('assets/js/app-ui.js') }}",
            "@tm/backtotop": "{{ asset('assets/js/app-backtotop.js') }}",
            "@tm/product": "{{ asset('assets/js/app-product.js') }}",
            "@tm/search": "{{ asset('assets/js/app-search.js') }}",
            "@tm/cart-badge": "{{ asset('assets/js/cart-badge.js') }}",
            "@tm/cart-add": "{{ asset('assets/js/cart-add.js') }}"
        }
    }
    </script>

    {{ $schema ?? '' }}
    @stack('styles')
</head>

<body class="{{ $bodyClass }}">
    <a class="skip-link" href="#main">Bỏ qua menu</a>

    <x-header :hide-catnav="$hideCatnav" />

    <main id="main">
        {{ $slot }}
    </main>

    <x-footer />
    <x-floatnav :hide-floatnav="$hideFloatnav" />
    <x-drawer />

    {{-- Nút "lên đầu trang": chỉ render ở các màn được bật flag (trang home) --}}
    @if($showBackToTop)
        <x-back-to-top />
    @endif

    {{ $extra ?? '' }}

    <div class="toast" id="toast" role="status" aria-live="polite"></div>

    {{-- NEW LIGHTGALLERY: vendor scripts (core + plugin UMD) — CHỈ khi view PDP
    push vào stack. Đặt TRƯỚC app.js để window.lightGallery/lgZoom... kịp tồn tại
    trước khi module @tm/product chạy (module luôn defer -> script thường phía
    trên chắc chắn execute trước). --}}
    @stack('vendorScripts')

    {{-- JS tách module: app.js là loader dispatch theo DOM; cart.js là shim tương thích ngược --}}
    <script type="module" src="{{ asset('assets/js/app.js') }}"></script>
    <script type="module" src="{{ asset('assets/js/cart.js') }}"></script>
    @stack('scripts')
</body>

</html>