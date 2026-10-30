@extends('layouts.app')

@section('title', 'Laporan Finansial & Operasional')
@section('page_title', 'Laporan & Analitik')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="{{ route('dashboard.reports') }}" class="flex flex-wrap items-center gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-1.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-1.5 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-cyan-500">
            </div>
            <div class="self-end">
                <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition">
                    Terapkan Filter
                </button>
            </div>
        </form>

        <div class="text-xs text-slate-500">
            Periode: <strong class="text-slate-800">{{ $startDate }}</strong> s/d <strong class="text-slate-800">{{ $endDate }}</strong>
        </div>
    </div>

    <div class="border-b border-slate-200">
        <nav class="flex space-x-6">
            <a href="{{ route('dashboard.reports', ['tab' => 'sales', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                class="py-3 px-1 border-b-2 font-medium text-sm transition {{ $tab === 'sales' ? 'border-cyan-600 text-cyan-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Ringkasan Penjualan
            </a>
            <a href="{{ route('dashboard.reports', ['tab' => 'profit', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                class="py-3 px-1 border-b-2 font-medium text-sm transition {{ $tab === 'profit' ? 'border-cyan-600 text-cyan-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Profit & HPP (FIFO)
            </a>
            <a href="{{ route('dashboard.reports', ['tab' => 'menu', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                class="py-3 px-1 border-b-2 font-medium text-sm transition {{ $tab === 'menu' ? 'border-cyan-600 text-cyan-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Peringkat Menu (Best Seller)
            </a>
            <a href="{{ route('dashboard.reports', ['tab' => 'payments', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                class="py-3 px-1 border-b-2 font-medium text-sm transition {{ $tab === 'payments' ? 'border-cyan-600 text-cyan-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Rekapitulasi Pembayaran
            </a>
            <a href="{{ route('dashboard.reports', ['tab' => 'movements', 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                class="py-3 px-1 border-b-2 font-medium text-sm transition {{ $tab === 'movements' ? 'border-cyan-600 text-cyan-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                Mutasi Stok
            </a>
        </nav>
    </div>

    @if($tab === 'sales')
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-xl border border-slate-200">
                <div class="text-xs text-slate-500 font-semibold uppercase">Omzet Bersih (Net Sales)</div>
                <div class="text-2xl font-bold text-slate-900 mt-2">Rp {{ number_format($salesReport['summary']['net_sales'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200">
                <div class="text-xs text-slate-500 font-semibold uppercase">Total Pesanan</div>
                <div class="text-2xl font-bold text-slate-900 mt-2">{{ $salesReport['summary']['total_orders'] }} Transaksi</div>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200">
                <div class="text-xs text-slate-500 font-semibold uppercase">Rata-rata Transaksi (AOV)</div>
                <div class="text-2xl font-bold text-slate-900 mt-2">Rp {{ number_format($salesReport['summary']['average_order_value'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200">
                <div class="text-xs text-slate-500 font-semibold uppercase">Belanja per Tamu</div>
                <div class="text-2xl font-bold text-slate-900 mt-2">Rp {{ number_format($salesReport['summary']['average_spend_per_guest'], 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-4 border-b border-slate-100 font-bold text-sm text-slate-800">Rincian Tren Penjualan Harian</div>
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3 text-center">Jumlah Order</th>
                        <th class="px-5 py-3 text-right">Penjualan Kotor</th>
                        <th class="px-5 py-3 text-right">Total Diskon</th>
                        <th class="px-5 py-3 text-right">Omzet Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($salesReport['daily_trends'] as $trend)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $trend['date'] }}</td>
                            <td class="px-5 py-3 text-center">{{ $trend['total_orders'] }}</td>
                            <td class="px-5 py-3 text-right">Rp {{ number_format($trend['gross_sales'], 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right text-rose-600">Rp {{ number_format($trend['discounts'], 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-900">Rp {{ number_format($trend['net_sales'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400">Tidak ada transaksi pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($tab === 'profit')
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-xl border border-slate-200">
                <div class="text-xs text-slate-500 font-semibold uppercase">Total Pendapatan</div>
                <div class="text-2xl font-bold text-slate-900 mt-2">Rp {{ number_format($profitReport['total_revenue'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200">
                <div class="text-xs text-slate-500 font-semibold uppercase">Total HPP (Modal Beli FIFO)</div>
                <div class="text-2xl font-bold text-rose-600 mt-2">Rp {{ number_format($profitReport['total_cogs'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200">
                <div class="text-xs text-slate-500 font-semibold uppercase">Laba Kotor Bersih</div>
                <div class="text-2xl font-bold text-emerald-600 mt-2">Rp {{ number_format($profitReport['total_gross_profit'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-200">
                <div class="text-xs text-slate-500 font-semibold uppercase">Margin Keuntungan Riil</div>
                <div class="text-2xl font-bold text-cyan-600 mt-2">{{ $profitReport['margin_percentage'] ?? 0 }}%</div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-4 border-b border-slate-100 font-bold text-sm text-slate-800">Breakdown Profit per Menu Seafood</div>
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Menu</th>
                        <th class="px-5 py-3">Varian</th>
                        <th class="px-5 py-3 text-center">Terjual</th>
                        <th class="px-5 py-3 text-right">Omzet</th>
                        <th class="px-5 py-3 text-right">Total HPP</th>
                        <th class="px-5 py-3 text-right">Laba Kotor</th>
                        <th class="px-5 py-3 text-right">Margin (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($profitReport['menu_breakdown'] ?? $profitReport['items_breakdown'] ?? [] as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-semibold text-slate-800">{{ $item['menu_name'] }}</td>
                            <td class="px-5 py-3">{{ $item['variant_name'] ?? '-' }}</td>
                            <td class="px-5 py-3 text-center font-medium">{{ $item['quantity_sold'] }} {{ $item['sales_unit'] ?? '' }}</td>
                            <td class="px-5 py-3 text-right">Rp {{ number_format($item['total_revenue'] ?? $item['revenue'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right text-rose-600">Rp {{ number_format($item['total_cogs'] ?? $item['cogs'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-emerald-600">Rp {{ number_format($item['gross_profit'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-900">{{ $item['margin_percentage'] ?? 0 }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">Belum ada item pesanan selesai pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($tab === 'menu')
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-4 border-b border-slate-100 font-bold text-sm text-slate-800">Peringkat Menu Terlaris (Best Seller)</div>
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Peringkat</th>
                        <th class="px-5 py-3">Menu</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3 text-center">Qty Terjual</th>
                        <th class="px-5 py-3 text-right">Total Omzet</th>
                        <th class="px-5 py-3 text-right">Kontribusi Omzet</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($menuReport['menu_rankings'] as $index => $ranking)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-bold text-slate-700">#{{ $index + 1 }}</td>
                            <td class="px-5 py-3 font-semibold text-slate-900">{{ $ranking['menu_name'] }}</td>
                            <td class="px-5 py-3"><span class="px-2 py-0.5 rounded text-xs bg-slate-100 uppercase">{{ $ranking['menu_type'] }}</span></td>
                            <td class="px-5 py-3 text-center font-medium">{{ $ranking['quantity_sold'] }} {{ $ranking['sales_unit'] }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-900">Rp {{ number_format($ranking['total_revenue'], 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-20 bg-slate-200 rounded-full h-2 overflow-hidden">
                                        <div class="bg-cyan-600 h-2" style="width: {{ min(100, $ranking['revenue_percentage']) }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold">{{ $ranking['revenue_percentage'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">Belum ada data penjualan menu.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($tab === 'payments')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($paymentReport['by_method'] as $method)
                <div class="bg-white p-5 rounded-xl border border-slate-200">
                    <div class="text-xs font-semibold text-slate-500 uppercase">{{ $method['method'] }}</div>
                    <div class="text-2xl font-bold text-slate-900 mt-2">Rp {{ number_format($method['total_amount'], 0, ',', '.') }}</div>
                    <div class="text-xs text-slate-500 mt-1">{{ $method['transaction_count'] }} Transaksi ({{ $method['percentage'] }}%)</div>
                </div>
            @endforeach
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-4 border-b border-slate-100 font-bold text-sm text-slate-800">Daftar Transaksi Kasir</div>
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">No. Order</th>
                        <th class="px-5 py-3">Meja</th>
                        <th class="px-5 py-3">Metode</th>
                        <th class="px-5 py-3">Kasir</th>
                        <th class="px-5 py-3 text-right">Nominal</th>
                        <th class="px-5 py-3">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($paymentReport['transactions'] as $tx)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-semibold text-slate-900">{{ $tx['order_number'] }}</td>
                            <td class="px-5 py-3">{{ $tx['table_number'] ?? '-' }}</td>
                            <td class="px-5 py-3 uppercase text-xs font-medium">{{ $tx['method'] }}</td>
                            <td class="px-5 py-3 text-slate-700">{{ $tx['cashier_name'] }}</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-900">Rp {{ number_format($tx['amount'], 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($tx['created_at'])->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400">Belum ada transaksi pembayaran pada rentang tanggal ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($tab === 'movements')
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-4 border-b border-slate-100 font-bold text-sm text-slate-800">Riwayat Mutasi Stok Bahan Baku (FIFO)</div>
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3">Waktu</th>
                        <th class="px-5 py-3">Bahan Baku</th>
                        <th class="px-5 py-3">Tipe Mutasi</th>
                        <th class="px-5 py-3 text-center">Kuantitas</th>
                        <th class="px-5 py-3 text-right">Biaya Satuan</th>
                        <th class="px-5 py-3 text-right">Total Biaya</th>
                        <th class="px-5 py-3">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stockMovements['data'] as $sm)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($sm['created_at'])->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3 font-semibold text-slate-900">{{ $sm['ingredient_name'] }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold 
                                    {{ $sm['type'] === 'in' ? 'bg-emerald-100 text-emerald-800' : ($sm['type'] === 'sale' ? 'bg-cyan-100 text-cyan-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ strtoupper($sm['type']) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center font-medium">{{ $sm['quantity'] }} {{ $sm['unit'] }}</td>
                            <td class="px-5 py-3 text-right">Rp {{ number_format($sm['cost_per_unit'], 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-right font-bold text-slate-900">Rp {{ number_format($sm['total_cost'], 0, ',', '.') }}</td>
                            <td class="px-5 py-3 text-xs text-slate-600">{{ $sm['created_by_name'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-400">Belum ada mutasi stok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

