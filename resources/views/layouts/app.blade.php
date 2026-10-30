<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Seafood RMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased">
    <div class="flex h-screen overflow-hidden">
        <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col flex-shrink-0">
            <div class="h-16 flex items-center px-6 bg-slate-950 font-bold text-lg text-white tracking-wide border-b border-slate-800 gap-3">
                <div class="w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.651V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009 9.35c.66 0 1.282-.213 1.79-.575.508.362 1.13.575 1.79.575s1.282-.213 1.79-.575c.508.362 1.13.575 1.79.575s1.282-.213 1.79-.575a3.001 3.001 0 003.75.615v11.65" />
                    </svg>
                </div>
                <span>Seafood RMS</span>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                @can('dashboard.view')
                    <a href="{{ route('dashboard.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard.index') || request()->routeIs('dashboard') ? 'bg-cyan-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>
                @endcan

                @can('report.view')
                    <a href="{{ route('dashboard.reports') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard.reports') ? 'bg-cyan-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Laporan Finansial
                    </a>
                @endcan

                @can('inventory.view')
                    <a href="{{ route('dashboard.inventory') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard.inventory') ? 'bg-cyan-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Inventori & Stok FIFO
                    </a>
                @endcan

                @can('menu.view')
                    <a href="{{ route('dashboard.menus') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard.menus') ? 'bg-cyan-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Katalog Menu
                    </a>
                @endcan

                @can('user.manage')
                    <a href="{{ route('dashboard.users') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard.users') ? 'bg-cyan-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Pengguna & Staf
                    </a>
                @endcan

                @if(auth()->user()?->can('report.view') || auth()->user()?->can('user.manage'))
                    <a href="{{ route('dashboard.audit_logs') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard.audit_logs') ? 'bg-cyan-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Jejak Audit
                    </a>
                @endif

                <div class="pt-4 mt-4 border-t border-slate-800">
                    <div class="px-3 mb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Layar Operasional</div>
                    <a href="{{ url('/kitchen') }}" target="_blank" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition text-amber-300 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/20 group">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Kitchen Display (KDS)
                        </span>
                        <svg class="w-4 h-4 text-amber-400/60 group-hover:text-amber-300 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>
            </nav>
            <div class="p-4 border-t border-slate-800 text-xs text-slate-500">
                Seafood RMS v1.0 &bull; Production Ready
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 z-10">
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-bold text-slate-800">@yield('page_title', 'Overview')</h1>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Sistem Online
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5 text-xs font-medium text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        <span>{{ $outlet?->name ?? 'Main Outlet' }}</span>
                    </div>

                    <div class="h-6 w-px bg-slate-200"></div>

                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <div class="text-xs font-bold text-slate-800">{{ auth()->user()->name ?? 'Owner Seafood' }}</div>
                            <div class="text-[11px] text-slate-500 capitalize">{{ auth()->user()->roles->first()?->name ?? 'Administrator' }}</div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" title="Keluar dari sistem" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6 bg-slate-50">
                @if(session('success'))
                    <div class="mb-5 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 p-4 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
