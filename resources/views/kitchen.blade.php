<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kitchen Display System (KDS) - {{ config('app.name', 'Seafood POS') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <script>
        window.__KDS_CONFIG__ = {
            appName: "{{ config('app.name', 'Seafood POS') }}",
            apiBaseUrl: "{{ url('/api/v1') }}",
            pusherKey: "{{ config('broadcasting.connections.pusher.key') ?? env('PUSHER_APP_KEY') }}",
            pusherCluster: "{{ config('broadcasting.connections.pusher.options.cluster') ?? env('PUSHER_APP_CLUSTER', 'ap1') }}",
            pusherHost: "{{ env('PUSHER_HOST') }}",
            pusherPort: "{{ env('PUSHER_PORT', 443) }}",
            pusherScheme: "{{ env('PUSHER_SCHEME', 'https') }}",
            kdsChannel: "{{ config('broadcasting.connections.pusher.kds_channel') ?? env('PUSHER_KDS_CHANNEL', 'pos-seafood-kds-development') }}"
        };
    </script>

    @vite(['resources/css/app.css', 'resources/js/kitchen/main.js'])
</head>
<body class="h-full font-sans antialiased bg-slate-950 text-slate-100 select-none overflow-hidden">
    <div id="kitchen-app" class="h-full w-full"></div>
</body>
</html>

