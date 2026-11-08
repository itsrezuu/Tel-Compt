<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'TEL-COMPT')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:#f8f9fc; margin:0;">
    <main style="max-width:1200px; margin:0 auto; padding:32px;">
        @yield('content')
    </main>
</body>
</html>
