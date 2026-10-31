<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Seafood POS') }} - Admin & Owner Portal</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <script>
        window.__APP_CONFIG__ = {
            appName: "{{ config('app.name', 'Seafood POS') }}",
            apiBaseUrl: "{{ url('/api/v1') }}",
            adminBaseUrl: "{{ url('/admin') }}"
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/admin/app.js'])
</head>
<body class="h-full font-sans antialiased bg-slate-50 text-slate-800 selection:bg-blue-500 selection:text-white">
    <div id="admin-app" class="h-full w-full"></div>
</body>
</html>

