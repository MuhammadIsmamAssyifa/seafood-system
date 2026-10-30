@extends('layouts.app')

@section('title', 'Dashboard Ringkasan')
@section('page_title', 'Ringkasan Restoran')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Omzet Hari Ini</div>
            <div class="mt-2 flex items-baseline justify-between">
                <div class="text-2xl font-bold text-slate-900">Rp {{ number_format($summary['sales']['today_revenue'], 0, ',', '.') }}</div>
                <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Bruto</span>
            </div>
            <div class="mt-2 text-xs text-slate-500">Dari {{ $summary['orders']['completed_today'] }} pesanan lunas</div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">HPP (COGS FIFO)</div>
            <div class="mt-2 flex items-baseline justify-between">
                <div class="text-2xl font-bold text-rose-600">Rp {{ number_format($summary['sales']['today_cogs'], 0, ',', '.') }}</div>
                <span class="text-xs font-medium text-rose-600 bg-rose-50 px-2 py-0.5 rounded">Modal</span>
            </div>
            <div class="mt-2 text-xs text-slate-500">Nilai modal beli bahan baku riil</div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Laba Kotor Hari Ini</div>
            <div class="mt-2 flex items-baseline justify-between">
                <div class="text-2xl font-bold text-emerald-600">Rp {{ number_format($summary['sales']['today_gross_profit'], 0, ',', '.') }}</div>
                <span class="text-xs font-semibold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">{{ $summary['sales']['today_profit_margin'] }}%</span>
            </div>
            <div class="mt-2 text-xs text-slate-500">Margin laba kotor hari ini</div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Okupansi Meja</div>
            <div class="mt-2 flex items-baseline justify-between">
                <div class="text-2xl font-bold text-slate-900">{{ $summary['tables']['occupancy_rate'] }}%</div>
                <span class="text-xs font-semibold text-cyan-700 bg-cyan-100 px-2 py-0.5 rounded">{{ $summary['tables']['occupied_tables'] }}/{{ $summary['tables']['total_tables'] }} Meja</span>
            </div>
            <div class="mt-2 text-xs text-slate-500">{{ $summary['tables']['available_tables'] }} meja tersedia &bull; {{ $summary['orders']['active_orders'] }} order aktif</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-slate-800">Tren Penjualan 7 Hari Terakhir</h2>
                <span class="text-xs text-slate-500">Omzet Bersih per Hari</span>
            </div>
            <div class="h-64">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs flex flex-col">
            <h2 class="text-base font-bold text-slate-800 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <span>Peringatan Bahan Menipis</span>
            </h2>
            <div class="flex-1 overflow-y-auto space-y-2.5">
                @forelse($summary['low_stock_alert'] ?? $summary['low_stock_alerts'] ?? [] as $alert)
                    <div class="p-3 rounded-lg bg-amber-50 border border-amber-200 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">{{ $alert['name'] }}</div>
                            <div class="text-xs text-slate-500">Satuan: {{ $alert['unit'] }}</div>
                        </div>
                        <span class="text-xs font-bold px-2 py-1 rounded bg-amber-200 text-amber-900">
                            {{ $alert['current_stock'] }} {{ $alert['unit'] }}
                        </span>
                    </div>
                @empty
                    <div class="py-12 text-center text-sm text-slate-400">
                        Seluruh stok bahan baku dalam batas aman.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-800">5 Pesanan Terbaru</h2>
            <a href="{{ route('dashboard.reports', ['tab' => 'sales']) }}" class="text-xs font-medium text-cyan-600 hover:text-cyan-700">Lihat Semua &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">No. Pesanan</th>
                        <th class="px-5 py-3">Meja</th>
                        <th class="px-5 py-3">Pelanggan</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Total Tagihan</th>
                        <th class="px-5 py-3">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($summary['recent_orders'] as $order)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $order['order_number'] }}</td>
                            <td class="px-5 py-3.5">{{ $order['table_number'] ?? '-' }}</td>
                            <td class="px-5 py-3.5">{{ $order['customer_name'] }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                    {{ $order['status'] === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-cyan-100 text-cyan-800' }}">
                                    {{ ucfirst($order['status']) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-slate-900">
                                Rp {{ number_format($order['grand_total'], 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500">
                                {{ \Illuminate\Support\Carbon::parse($order['created_at'])->format('H:i') }} WIB
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">Belum ada transaksi hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const trends = @json($salesReport['daily_trends']);
        const ctx = document.getElementById('salesTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trends.map(t => t.date),
                datasets: [{
                    label: 'Omzet Bersih (Rp)',
                    data: trends.map(t => t.net_sales),
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000).toLocaleString('id-ID') + 'k';
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection

