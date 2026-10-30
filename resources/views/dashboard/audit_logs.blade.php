@extends('layouts.app')

@section('title', 'Jejak Audit Aktivitas')
@section('page_title', 'Audit Log Sistem')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-800">Riwayat Jejak Audit Sistem</h2>
                <p class="text-xs text-slate-500">Merekam login, pesanan, pembayaran, penyesuaian timbangan, dan mutasi stok</p>
            </div>
        </div>
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3">Waktu</th>
                    <th class="px-5 py-3">Aksi</th>
                    <th class="px-5 py-3">Pengguna</th>
                    <th class="px-5 py-3">Deskripsi Aktivitas</th>
                    <th class="px-5 py-3">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3 text-xs text-slate-500">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold 
                                {{ $log->action === 'login' ? 'bg-cyan-100 text-cyan-800' : ($log->action === 'payment' ? 'bg-emerald-100 text-emerald-800' : ($log->action === 'price_change' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700')) }}">
                                {{ strtoupper($log->action) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 font-medium text-slate-800">
                            {{ $log->user?->name ?? 'Sistem' }}
                        </td>
                        <td class="px-5 py-3 text-xs text-slate-700">
                            {{ $log->description }}
                        </td>
                        <td class="px-5 py-3 text-xs font-mono text-slate-400">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada catatan aktivitas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

