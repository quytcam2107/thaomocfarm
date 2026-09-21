<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#2e7d4f">
    <title>{{ $title ?? 'Thảo Mộc Farm' }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/logo_thao_moc_farm.png') }}">

    {{-- SEO Meta (dùng $seoDescription để tránh conflict) --}}
    <meta name="description" content="{{ $seoDescription ?? 'Thảo mộc & đặc sản Tây Bắc' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:locale" content="vi_VN">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $seoDescription ?? '' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('assets/images/og-cover.jpg') }}">

    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    {{-- Slot riêng cho Schema JSON-LD (Product, BreadcrumbList...) --}}
    {{ $schema ?? '' }}
    @stack('styles')
</head>
{{-- THÊM: $bodyClass để trang product truyền 'has-buybar' --}}

<body class="{{ $bodyClass ?? '' }}">
    <a class="skip-link" href="#main">Bỏ qua menu</a>

    <x-header />

    <main id="main">
        {{ $slot }}
    </main>

    <x-footer />
    <x-floatnav />
    <x-drawer />

    {{-- Slot cho các thành phần đặc biệt (như buybar của product) --}}
    {{ $extra ?? '' }}

    <div class="toast" id="toast" role="status" aria-live="polite"></div>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    @stack('scripts')
</body>

</html>