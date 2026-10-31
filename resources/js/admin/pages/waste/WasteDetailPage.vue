<script setup>
import { onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useWasteStore } from '../../stores/waste.store';
import { formatDate, formatDateTime, formatCurrency, formatNumber } from '../../utils/formatter';
import WasteReasonBadge from './components/WasteReasonBadge.vue';
import {
    ArrowLeft,
    Trash2,
    Calendar,
    User,
    FileText,
    Layers,
    DollarSign,
    Scale,
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const wasteStore = useWasteStore();

onMounted(() => {
    const id = route.params.id;
    if (id) {
        wasteStore.fetchDetail(id);
    }
});
</script>

<template>
    <div class="space-y-6">
        <!-- Back Navigation -->
        <div>
            <button
                @click="router.push('/waste')"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors"
            >
                <ArrowLeft class="w-4 h-4" />
                <span>Kembali ke Waste Management</span>
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="wasteStore.detailLoading" class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
            <div class="w-8 h-8 border-2 border-red-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
            <p class="text-xs text-slate-500">Memuat detail catatan waste...</p>
        </div>

        <!-- Detail Content -->
        <div v-else-if="wasteStore.selectedRecord" class="space-y-6">
            <!-- Header Banner -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                        <Trash2 class="w-6 h-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                                {{ wasteStore.selectedRecord.ingredient_name }}
                            </h1>
                            <WasteReasonBadge
                                :reason="wasteStore.selectedRecord.reason"
                                :label="wasteStore.selectedRecord.reason_label"
                            />
                        </div>
                        <p class="text-xs text-slate-500 mt-1 flex items-center gap-2">
                            <span>Record #{{ wasteStore.selectedRecord.id }}</span>
                            <span>•</span>
                            <span>{{ formatDate(wasteStore.selectedRecord.created_at, 'DD MMMM YYYY, HH:mm') }} WIB</span>
                        </p>
                    </div>
                </div>

                <div class="text-left md:text-right bg-red-50/50 md:bg-transparent p-4 md:p-0 rounded-xl">
                    <div class="text-xs font-semibold text-red-600 uppercase tracking-wider">Total Nilai Kerugian</div>
                    <div class="text-2xl font-black text-red-600 tracking-tight mt-0.5">
                        {{ formatCurrency(wasteStore.selectedRecord.total_cost) }}
                    </div>
                </div>
            </div>

            <!-- Metric Breakdown Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
                    <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold uppercase">
                        <Scale class="w-4 h-4 text-amber-500" />
                        <span>Kuantitas Terbuang</span>
                    </div>
                    <div class="mt-2 text-xl font-bold text-slate-900">
                        {{ formatNumber(wasteStore.selectedRecord.quantity, 3) }}
                        <span class="text-sm font-normal text-slate-500">{{ wasteStore.selectedRecord.unit }}</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
                    <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold uppercase">
                        <DollarSign class="w-4 h-4 text-emerald-500" />
                        <span>Harga Pokok Rata-Rata</span>
                    </div>
                    <div class="mt-2 text-xl font-bold text-slate-900">
                        {{ formatCurrency(wasteStore.selectedRecord.unit_cost) }}
                        <span class="text-xs font-normal text-slate-500">/{{ wasteStore.selectedRecord.unit }}</span>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
                    <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold uppercase">
                        <User class="w-4 h-4 text-indigo-500" />
                        <span>Dicatat Oleh</span>
                    </div>
                    <div class="mt-2 text-xl font-bold text-slate-900 truncate">
                        {{ wasteStore.selectedRecord.creator_name || 'Admin' }}
                    </div>
                </div>
            </div>

            <!-- Notes & Context -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs space-y-2">
                <div class="flex items-center gap-2 text-slate-700 text-xs font-bold uppercase tracking-wider">
                    <FileText class="w-4 h-4 text-slate-500" />
                    <span>Catatan & Keterangan Kejadian</span>
                </div>
                <p class="text-xs text-slate-600 bg-slate-50 p-3 rounded-lg leading-relaxed">
                    {{ wasteStore.selectedRecord.note || 'Tidak ada catatan keterangan khusus yang dilampirkan.' }}
                </p>
            </div>

            <!-- FIFO Stock Movements Deducted -->
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Layers class="w-4 h-4 text-slate-600" />
                        <h3 class="text-sm font-semibold text-slate-900">Alokasi Pengurangan Batch FIFO</h3>
                    </div>
                    <span class="text-xs text-slate-400">
                        {{ wasteStore.selectedRecord.stock_movements?.length || 0 }} batch terpotong
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-semibold uppercase">
                                <th class="py-3 px-4">Batch ID</th>
                                <th class="py-3 px-4 text-right">Kuantitas Dipotong</th>
                                <th class="py-3 px-4 text-right">Modal/Unit</th>
                                <th class="py-3 px-4 text-right">Subtotal Kerugian</th>
                                <th class="py-3 px-4 text-right">Waktu Eksekusi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr
                                v-for="m in wasteStore.selectedRecord.stock_movements"
                                :key="m.id"
                                class="hover:bg-slate-50/50"
                            >
                                <td class="py-3 px-4 font-mono font-medium text-slate-900">
                                    Batch #{{ m.batch_id || '-' }}
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-red-600">
                                    {{ formatNumber(Math.abs(m.quantity), 3) }} {{ wasteStore.selectedRecord.unit }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    {{ formatCurrency(m.cost_per_unit) }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">
                                    {{ formatCurrency(m.total_cost) }}
                                </td>
                                <td class="py-3 px-4 text-right text-slate-400">
                                    {{ formatDate(m.created_at, 'DD/MM/YYYY HH:mm') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

