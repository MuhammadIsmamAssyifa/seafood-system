<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 antialiased text-slate-800">
    <div class="w-full max-w-md text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-500/10 text-rose-500 border border-rose-500/20 mb-6 shadow-lg shadow-rose-500/10">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.002A11.959 11.959 0 0112 2.714zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>

        <h1 class="text-3xl font-extrabold text-white tracking-tight">403 Forbidden</h1>
        <p class="text-sm font-semibold text-rose-400 mt-2">Akses Ditolak &bull; Hak Akses Terbatas</p>
        <p class="text-xs text-slate-400 mt-2 max-w-sm mx-auto leading-relaxed">
            Peran akun Anda tidak memiliki izin untuk mengakses area ini. Area ini hanya diperuntukkan bagi Owner dan Manajemen Restoran.
        </p>

        <div class="mt-8 flex items-center justify-center gap-3">
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-md transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        <span>Keluar / Ganti Akun</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold rounded-xl shadow-md transition">
                    Kembali ke Halaman Login
                </a>
            @endauth
        </div>
    </div>
</body>
</html>

