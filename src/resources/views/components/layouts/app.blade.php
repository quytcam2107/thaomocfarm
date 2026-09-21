<!doctype html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#2e7d4f">
    <title>{{ $title ?? 'Thảo Mộc Farm — Thảo mộc nguyên chất & Đặc sản Tây Bắc' }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo_thao_moc_farm.png') }}">
    <meta name="description"
        content="{{ $description ?? 'Thịt trâu gác bếp, mắc khén, hạt dổi, mật ong rừng cùng trà hoa thảo mộc sấy lạnh.' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:locale" content="vi_VN">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $ogTitle ?? 'Thảo Mộc Farm — Thảo mộc & Đặc sản tây bắc' }}">
    <meta property="og:description" content="Nguồn gốc vùng trồng rõ ràng, OCOP-VietGAP, freeship đơn từ 200K.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/og-cover.jpg') }}">
    <meta name="twitter:card" content="summary_large_image">
    <script type="application/ld+json">
    {"@context":"https://schema.org","@graph":[
     {"@type":"Organization","name":"Thảo Mộc Xanh","url":"{{ url('/') }}","telephone":"0362795897"},
     {"@type":"WebSite","name":"Thảo Mộc Xanh","url":"{{ url('/') }}",
      "potentialAction":{"@type":"SearchAction","target":"{{ url('/tim-kiem') }}?q={search_term_string}","query-input":"required name=search_term_string"}}]}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Lora:wght@500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    @stack('styles')
</head>

<body>
    <a class="skip-link" href="#main">Bỏ qua menu</a>

    <x-header />

    <main id="main">
        {{ $slot }}
    </main>

    <x-footer />
    <x-floatnav />
    <x-drawer />

    <div class="toast" id="toast" role="status" aria-live="polite"></div>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>

</html>