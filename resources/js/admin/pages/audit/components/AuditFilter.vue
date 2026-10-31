<script setup>
import { Search, RotateCcw, Calendar } from 'lucide-vue-next';

const props = defineProps({
    filters: {
        type: Object,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['filter', 'reset', 'preset']);

function onSearch() {
    emit('filter');
}

function onReset() {
    emit('reset');
}

function onPreset(preset) {
    emit('preset', preset);
}
</script>

<template>
    <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-sm space-y-4">
        <!-- Date Presets & Search Bar -->
        <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-medium">
                <span class="text-slate-500 mr-1 flex items-center gap-1">
                    <Calendar class="w-3.5 h-3.5" /> Rentang:
                </span>
                <button
                    type="button"
                    @click="onPreset('today')"
                    class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 transition"
                >
                    Hari Ini
                </button>
                <button
                    type="button"
                    @click="onPreset('week')"
                    class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 transition"
                >
                    7 Hari Terakhir
                </button>
                <button
                    type="button"
                    @click="onPreset('month')"
                    class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 transition"
                >
                    30 Hari Terakhir
                </button>
                <button
                    type="button"
                    @click="onPreset('all')"
                    class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 transition"
                >
                    Semua
                </button>
            </div>

            <!-- Search input -->
            <div class="relative flex-1 max-w-md">
                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    v-model="filters.search"
                    @keyup.enter="onSearch"
                    type="text"
                    placeholder="Cari deskripsi, aksi, atau user..."
                    class="w-full pl-9 pr-3 py-1.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 focus:bg-white transition"
                />
            </div>
        </div>

        <!-- Filter Selects -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 pt-2 border-t border-slate-100">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Modul Sistem</label>
                <select
                    v-model="filters.module"
                    @change="onSearch"
                    class="w-full text-xs py-1.5 px-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 focus:outline-none"
                >
                    <option value="">Semua Modul</option>
                    <option value="Order">Order & Kasir</option>
                    <option value="Payment">Pembayaran</option>
                    <option value="Inventory">Inventory & Waste</option>
                    <option value="Menu">Menu & Resep</option>
                    <option value="Employee & Auth">Karyawan & Akses</option>
                    <option value="Settings">Pengaturan Sistem</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Aksi</label>
                <select
                    v-model="filters.action"
                    @change="onSearch"
                    class="w-full text-xs py-1.5 px-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 focus:outline-none"
                >
                    <option value="">Semua Aksi</option>
                    <option value="login">Login</option>
                    <option value="logout">Logout</option>
                    <option value="create_order">Buat Pesanan</option>
                    <option value="cancel_order">Batalkan Pesanan</option>
                    <option value="stock_adjustment">Penyesuaian Stok</option>
                    <option value="waste_created">Pencatatan Waste</option>
                    <option value="menu_update">Ubah Menu</option>
                    <option value="user_management">Kelola Karyawan</option>
                    <option value="settings_updated">Ubah Pengaturan</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input
                    v-model="filters.start_date"
                    @change="onSearch"
                    type="date"
                    class="w-full text-xs py-1.5 px-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 focus:outline-none"
                />
            </div>

            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                    <input
                        v-model="filters.end_date"
                        @change="onSearch"
                        type="date"
                        class="w-full text-xs py-1.5 px-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 focus:outline-none"
                    />
                </div>
                <button
                    type="button"
                    @click="onReset"
                    title="Reset Filter"
                    class="p-2 border border-slate-200 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition"
                >
                    <RotateCcw class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>

