<script setup>
import { onMounted, ref } from 'vue';
import { useWasteStore } from '../../stores/waste.store';
import WasteSummaryCard from './components/WasteSummaryCard.vue';
import WasteCostCard from './components/WasteCostCard.vue';
import WasteTable from './components/WasteTable.vue';
import WasteFormModal from './components/WasteFormModal.vue';
import {
    Plus,
    Search,
    RotateCcw,
    Trash2,
    Calendar,
    Filter,
} from 'lucide-vue-next';

const wasteStore = useWasteStore();

const showModal = ref(false);
const activeDatePreset = ref('month'); // 'today', 'week', 'month', 'all'
const successToast = ref('');

function setDatePreset(preset) {
    activeDatePreset.value = preset;
    const now = new Date();

    if (preset === 'today') {
        const str = now.toISOString().split('T')[0];
        wasteStore.setFilter('start_date', str);
        wasteStore.setFilter('end_date', str);
    } else if (preset === 'week') {
        const d = new Date(now);
        const day = d.getDay();
        const diff = d.getDate() - day + (day === 0 ? -6 : 1);
        const monday = new Date(d.setDate(diff));
        wasteStore.setFilter('start_date', monday.toISOString().split('T')[0]);
        wasteStore.setFilter('end_date', now.toISOString().split('T')[0]);
    } else if (preset === 'month') {
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        wasteStore.setFilter('start_date', firstDay.toISOString().split('T')[0]);
        wasteStore.setFilter('end_date', now.toISOString().split('T')[0]);
    } else {
        wasteStore.setFilter('start_date', '');
        wasteStore.setFilter('end_date', '');
    }

    applyFilters();
}

function handleSearch(e) {
    wasteStore.setFilter('search', e.target.value);
    wasteStore.fetchRecords(1);
}

function handleReasonChange(e) {
    wasteStore.setFilter('reason', e.target.value);
    wasteStore.fetchRecords(1);
}

function applyFilters() {
    wasteStore.fetchSummary();
    wasteStore.fetchRecords(1);
}

function resetAllFilters() {
    activeDatePreset.value = 'all';
    wasteStore.resetFilters();
    applyFilters();
}

async function handleSaveWaste(payload) {
    try {
        await wasteStore.recordWaste(payload);
        showModal.value = false;
        successToast.value = 'Catatan waste berhasil disimpan dan stok otomatis disesuaikan.';
        setTimeout(() => {
            successToast.value = '';
        }, 4000);
    } catch (err) {
        console.error('Error saving waste:', err);
    }
}

onMounted(() => {
    // Default to month
    setDatePreset('month');
});
</script>

<template>
    <div class="space-y-6">
        <!-- Toast Feedback -->
        <div
            v-if="successToast"
            class="fixed top-5 right-5 z-50 bg-emerald-600 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 text-xs font-medium animate-bounce"
        >
            <span>{{ successToast }}</span>
        </div>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Waste Management</h1>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700">
                        Loss Control
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Pencatatan dan analisis kehilangan bahan baku untuk mencegah kebocoran modal dapur restoran.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <button
                    @click="showModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors"
                >
                    <Plus class="w-4 h-4" />
                    <span>Catat Bahan Terbuang</span>
                </button>
            </div>
        </div>

        <!-- Metric Summary Cards -->
        <WasteSummaryCard :summary="wasteStore.summary" :loading="wasteStore.summaryLoading" />

        <!-- Cost & Reason Analysis -->
        <WasteCostCard :summary="wasteStore.summary" :loading="wasteStore.summaryLoading" />

        <!-- Filter Bar -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <!-- Date presets -->
                <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl text-xs font-medium">
                    <button
                        @click="setDatePreset('today')"
                        :class="[
                            'px-3 py-1.5 rounded-lg transition-colors',
                            activeDatePreset === 'today' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900',
                        ]"
                    >
                        Hari Ini
                    </button>
                    <button
                        @click="setDatePreset('week')"
                        :class="[
                            'px-3 py-1.5 rounded-lg transition-colors',
                            activeDatePreset === 'week' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900',
                        ]"
                    >
                        Minggu Ini
                    </button>
                    <button
                        @click="setDatePreset('month')"
                        :class="[
                            'px-3 py-1.5 rounded-lg transition-colors',
                            activeDatePreset === 'month' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900',
                        ]"
                    >
                        Bulan Ini
                    </button>
                    <button
                        @click="setDatePreset('all')"
                        :class="[
                            'px-3 py-1.5 rounded-lg transition-colors',
                            activeDatePreset === 'all' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900',
                        ]"
                    >
                        Semua
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Search Input -->
                    <div class="relative w-56">
                        <input
                            :value="wasteStore.filters.search"
                            @input="handleSearch"
                            type="text"
                            placeholder="Cari nama bahan baku..."
                            class="w-full text-xs pl-8 pr-3 py-2 border border-slate-200 rounded-xl focus:border-red-500 focus:ring-red-500"
                        />
                        <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                    </div>

                    <!-- Filter Reason -->
                    <select
                        :value="wasteStore.filters.reason"
                        @change="handleReasonChange"
                        class="text-xs py-2 pl-3 pr-8 border border-slate-200 rounded-xl focus:border-red-500 focus:ring-red-500 bg-white"
                    >
                        <option value="">Semua Penyebab</option>
                        <option value="damaged">Rusak / Busuk</option>
                        <option value="expired">Kadaluarsa / Basi</option>
                        <option value="cooking_loss">Susut Masak / Handling</option>
                        <option value="wrong_preparation">Salah Olah / Masak</option>
                        <option value="other">Lainnya</option>
                    </select>

                    <!-- Reset Button -->
                    <button
                        @click="resetAllFilters"
                        class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors"
                        title="Reset Filter"
                    >
                        <RotateCcw class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Waste History Table -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Riwayat Kejadian Waste</h2>
                <span class="text-xs text-slate-400">Total {{ wasteStore.pagination.total }} kejadian tercatat</span>
            </div>

            <WasteTable
                :records="wasteStore.records"
                :pagination="wasteStore.pagination"
                :loading="wasteStore.loading"
                @page-change="(p) => wasteStore.fetchRecords(p)"
            />
        </div>

        <!-- Form Modal -->
        <WasteFormModal
            v-model="showModal"
            @saved="handleSaveWaste"
        />
    </div>
</template>

