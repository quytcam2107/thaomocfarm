@props([
    'title' => 'Mộc Xanh',
    'seoDescription' => 'Thảo mộc & đặc sản Tây Bắc',
    'ogType' => 'website',
    'ogImage' => null,
    'bodyClass' => '',
    'hideCatnav' => false,
    'hideFloatnav' => false,
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

    {{ $extra ?? '' }}

    <div class="toast" id="toast" role="status" aria-live="polite"></div>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/cart.js') }}"></script>
    @stack('scripts')
</body>

</html>