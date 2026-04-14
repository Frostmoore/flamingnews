<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title', 'FlamingNews') — Notizie comparate</title>
    <meta name="description" content="@yield('meta_description', 'FlamingNews: leggi le notizie e confronta come diverse testate italiane raccontano lo stesso evento.')">

    <!-- Google Fonts: Playfair Display + Source Sans 3 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(config('ads.adsense_publisher_id'))
    <!-- Google AdSense -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('ads.adsense_publisher_id') }}"
            crossorigin="anonymous"></script>
    @endif
</head>
<body class="bg-[#F8F6F1] text-[#1A1A1A] antialiased" style="font-family: 'Source Sans 3', sans-serif;">

    <div id="app" @hasSection('requires_auth') x-data x-init="authGuard()" @endif>
        @yield('content')
    </div>

    @hasSection('requires_auth')
    <script>
    function authGuard() {
        const token = localStorage.getItem('fn_token');
        if (!token) {
            window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
        }
    }
    </script>
    @endif

</body>
</html>
