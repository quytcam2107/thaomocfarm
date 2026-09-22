<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#2e7d4f">

    {{-- CSRF token: bắt buộc để JS fetch POST (nút "Thêm vào giỏ") hoạt động --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Thảo Mộc Farm' }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/logo_thao_moc_farm.png') }}">

    {{-- SEO Meta --}}
    <meta name="description" content="{{ $seoDescription ?? 'Thảo mộc & đặc sản Tây Bắc' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:locale" content="vi_VN">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $seoDescription ?? '' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('assets/images/og-cover.jpg') }}">

    {{-- ===== Google Fonts: Be Vietnam Pro + Lora (có tiếng Việt) ===== --}}
    {{-- 1. Preconnect: mở sớm kết nối TCP/TLS tới Google CDN, tiết kiệm ~200ms --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- 2. Preload file CSS font (LCP-friendly): tải song song với HTML --}}
    <link rel="preload" as="style"
        href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Be+Vietnam+Pro:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap">

    {{-- 3. Load cả 2 font trong 1 request duy nhất (giảm RTT so với 2 link riêng) --}}
    {{-- subset=vietnamese được tự động include nhờ unicode-range trong file CSS của Google --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Be+Vietnam+Pro:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet">
    {{-- ===== End Google Fonts ===== --}}

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