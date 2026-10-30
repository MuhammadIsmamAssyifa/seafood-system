@extends('layouts.app')

@section('title', 'Katalog Menu & Resep')
@section('page_title', 'Katalog Menu & Varian Harga')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200">
            <div class="text-xs font-semibold text-slate-500 uppercase">Total Menu Master</div>
            <div class="text-2xl font-bold text-slate-900 mt-2">{{ count($menus) }} Menu</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200">
            <div class="text-xs font-semibold text-slate-500 uppercase">Kitchen Stations</div>
            <div class="text-2xl font-bold text-cyan-600 mt-2">{{ count($stations) }} Stasiun Dapur</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200">
            <div class="text-xs font-semibold text-slate-500 uppercase">Metode Masak</div>
            <div class="text-2xl font-bold text-emerald-600 mt-2">{{ count($cookingMethods) }} Metode</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-slate-200">
            <div class="text-xs font-semibold text-slate-500 uppercase">Bahan Baku Utama</div>
            <div class="text-2xl font-bold text-slate-900 mt-2">{{ count($ingredients) }} Bahan</div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-800">Daftar Menu & Varian Harga</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($menus as $menu)
                <div class="p-5 hover:bg-slate-50 transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $menu->type === 'seafood' ? 'bg-cyan-100 text-cyan-700' : ($menu->type === 'drink' ? 'bg-amber-100 text-amber-700' : 'bg-indigo-100 text-indigo-700') }}">
                                @if($menu->type === 'seafood')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                    </svg>
                                @elseif($menu->type === 'drink')
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.523a9.75 9.75 0 01-6.46 0L10.2 15.3m9.6 0a2.25 2.25 0 011.65 2.167V19.5a1.5 1.5 0 01-1.5 1.5H4.05a1.5 1.5 0 01-1.5-1.5v-2.033a2.25 2.25 0 011.65-2.167M10.2 15.3l-5.2-1.733" />
                                    </svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">{{ $menu->name }}</h3>
                                <span class="text-xs px-2 py-0.5 rounded uppercase font-semibold 
                                    {{ $menu->type === 'seafood' ? 'bg-cyan-100 text-cyan-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $menu->type }}
                                </span>
                            </div>
                        </div>
                        <span class="text-xs text-slate-500 font-medium">{{ count($menu->variants) }} Varian Pilihan</span>
                    </div>

                    @if($menu->variants->isNotEmpty())
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full text-left text-xs text-slate-600 border border-slate-200 rounded-lg overflow-hidden">
                                <thead class="bg-slate-100 text-slate-500 font-semibold uppercase">
                                    <tr>
                                        <th class="px-4 py-2">Nama Varian</th>
                                        <th class="px-4 py-2">Stasiun Dapur</th>
                                        <th class="px-4 py-2">Metode Masak</th>
                                        <th class="px-4 py-2">Bahan Baku Pokok</th>
                                        <th class="px-4 py-2 text-center">Satuan Jual</th>
                                        <th class="px-4 py-2 text-right">Harga Jual</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    @foreach($menu->variants as $variant)
                                        <tr>
                                            <td class="px-4 py-2.5 font-semibold text-slate-800">{{ $variant->name }}</td>
                                            <td class="px-4 py-2.5"><span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700">{{ $variant->kitchenStation?->name ?? '-' }}</span></td>
                                            <td class="px-4 py-2.5">{{ $variant->cookingMethod?->name ?? '-' }}</td>
                                            <td class="px-4 py-2.5">{{ $variant->mainIngredient?->name ?? '-' }}</td>
                                            <td class="px-4 py-2.5 text-center uppercase">{{ $variant->sales_unit instanceof \BackedEnum ? $variant->sales_unit->value : $variant->sales_unit }}</td>
                                            <td class="px-4 py-2.5 text-right font-bold text-slate-900">
                                                Rp {{ number_format($variant->price, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-sm text-slate-400">Belum ada menu di katalog.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

