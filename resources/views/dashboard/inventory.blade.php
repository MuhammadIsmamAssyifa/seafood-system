@extends('layouts.app')

@section('title', 'Inventori Bahan Baku & FIFO')
@section('page_title', 'Inventori & Stok Bahan Baku')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200">
            <div class="text-xs font-semibold text-slate-500 uppercase">Total Valuasi Aset Inventori</div>
            <div class="text-2xl font-bold text-slate-900 mt-2">Rp {{ number_format($inventoryReport['summary']['total_valuation'] ?? $inventoryReport['total_inventory_valuation'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-xs text-slate-500 mt-1">Nilai rupiah modal beli stok aktif saat ini</div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200">
            <div class="text-xs font-semibold text-slate-500 uppercase">Total Jenis Bahan Baku</div>
            <div class="text-2xl font-bold text-cyan-600 mt-2">{{ count($inventoryReport['ingredients']) }} Jenis</div>
            <div class="text-xs text-slate-500 mt-1">Bahan baku terdaftar pada sistem</div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase">Restock Gudang / Dapur</div>
                <div class="text-sm text-slate-600 mt-1">Input barang masuk dengan harga beli FIFO</div>
            </div>
            @can('inventory.manage')
                <button onclick="document.getElementById('restockModal').classList.remove('hidden')" class="px-4 py-2 bg-cyan-600 text-white text-sm font-semibold rounded-lg hover:bg-cyan-700 transition">
                    + Tambah Stok
                </button>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-slate-800">Daftar Stok Bahan Baku Aktif</h2>
        </div>
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3">Nama Bahan Baku</th>
                    <th class="px-5 py-3">Satuan</th>
                    <th class="px-5 py-3 text-center">Stok Tersedia</th>
                    <th class="px-5 py-3 text-right">Rata-rata Harga Modal</th>
                    <th class="px-5 py-3 text-right">Total Valuasi Stok</th>
                    <th class="px-5 py-3 text-center">Batch Aktif</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($inventoryReport['ingredients'] as $ing)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $ing['name'] }}</td>
                        <td class="px-5 py-3.5"><span class="px-2 py-0.5 rounded text-xs bg-slate-100">{{ $ing['unit'] }}</span></td>
                        <td class="px-5 py-3.5 text-center font-bold {{ $ing['current_stock'] <= 5 ? 'text-amber-600' : 'text-slate-800' }}">
                            {{ $ing['current_stock'] }} {{ $ing['unit'] }}
                        </td>
                        <td class="px-5 py-3.5 text-right">Rp {{ number_format($ing['current_stock'] > 0 ? ($ing['valuation'] / $ing['current_stock']) : 0, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-right font-bold text-slate-900">Rp {{ number_format($ing['valuation'] ?? $ing['total_valuation'] ?? 0, 0, ',', '.') }}</td>
                        <td class="px-5 py-3.5 text-center text-xs font-medium text-slate-500">{{ $ing['active_batches_count'] }} Batch FIFO</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-slate-400">Belum ada bahan baku terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @can('inventory.manage')
    <div id="restockModal" class="hidden fixed inset-0 bg-slate-900/50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Input Stok Bahan Baku Baru</h3>
                <button onclick="document.getElementById('restockModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            <form method="POST" action="{{ route('dashboard.inventory.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Pilih Bahan Baku</label>
                    <select name="ingredient_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        @foreach($ingredients as $ing)
                            <option value="{{ $ing->id }}">{{ $ing->name }} ({{ $ing->unit instanceof \BackedEnum ? $ing->unit->value : $ing->unit }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kuantitas Masuk</label>
                    <input type="number" step="0.001" name="quantity" required placeholder="Contoh: 10.5" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Harga Modal Beli per Satuan (Rp)</label>
                    <input type="number" step="1" name="cost_per_unit" required placeholder="Contoh: 45000" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan / Sumber</label>
                    <input type="text" name="note" placeholder="Contoh: Pembelian Pasar Muara Angke" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('restockModal').classList.add('hidden')" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-cyan-600 text-white text-sm font-semibold rounded-lg hover:bg-cyan-700">Simpan Batch FIFO</button>
                </div>
            </form>
        </div>
    </div>
    @endcan
</div>
@endsection

