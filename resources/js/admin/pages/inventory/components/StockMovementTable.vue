<script setup>
import { ArrowDownLeft, ArrowUpRight, ShoppingCart, RefreshCw, Clock, User } from 'lucide-vue-next';
import { formatNumber, formatCurrency, formatTime, formatDate } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';
import BaseEmpty from '../../../components/common/BaseEmpty.vue';

defineProps({
    movements: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

function getMovementTypeBadge(type) {
    switch (type?.toLowerCase()) {
        case 'in':
            return {
                label: 'Masuk (In)',
                class: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                sign: '+',
            };
        case 'sale':
            return {
                label: 'Penjualan Dapur',
                class: 'bg-blue-50 text-blue-700 border-blue-200',
                sign: '-',
            };
        case 'waste':
            return {
                label: 'Terbuang (Waste)',
                class: 'bg-rose-50 text-rose-700 border-rose-200',
                sign: '-',
            };
        case 'adjustment':
        default:
            return {
                label: 'Koreksi Stok',
                class: 'bg-amber-50 text-amber-700 border-amber-200',
                sign: '-',
            };
    }
}
</script>

<template>
    <BaseCard title="Riwayat Pergerakan Stok (Stock Movements)" subtitle="Log histori mutasi masuk, pemakaian masak pesanan, dan koreksi stok">
        <div v-if="loading" class="p-6 space-y-3">
            <div v-for="i in 5" :key="i" class="h-10 bg-slate-100 rounded-lg animate-pulse"></div>
        </div>

        <div v-else-if="!movements || movements.length === 0" class="py-6">
            <BaseEmpty
                title="Belum Ada Riwayat Mutasi"
                message="Riwayat pergerakan stok bahan baku ini akan muncul otomatis saat ada transaksi dapur atau restock."
            />
        </div>

        <div v-else class="overflow-x-auto -mx-6">
            <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/70 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-3">Tipe Mutasi</th>
                        <th class="px-4 py-3 text-right">Kuantitas</th>
                        <th class="px-4 py-3 text-right">Biaya Satuan</th>
                        <th class="px-4 py-3">Waktu & Dicatat Oleh</th>
                        <th class="px-6 py-3">Keterangan / Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <tr v-for="m in movements" :key="m.id" class="hover:bg-slate-50/60">
                        <!-- Type -->
                        <td class="px-6 py-3 whitespace-nowrap">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold border"
                                :class="getMovementTypeBadge(m.type).class"
                            >
                                {{ getMovementTypeBadge(m.type).label }}
                            </span>
                        </td>

                        <!-- Quantity -->
                        <td class="px-4 py-3 whitespace-nowrap text-right font-mono font-bold text-sm" :class="[m.type === 'in' ? 'text-emerald-600' : 'text-slate-800']">
                            {{ getMovementTypeBadge(m.type).sign }}{{ formatNumber(m.quantity, 2) }}
                        </td>

                        <!-- Cost -->
                        <td class="px-4 py-3 whitespace-nowrap text-right font-mono text-slate-500">
                            {{ formatCurrency(m.cost_per_unit) }}
                        </td>

                        <!-- Time & User -->
                        <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                            <div class="flex items-center gap-1">
                                <Clock class="w-3 h-3 text-slate-400" />
                                <span>{{ formatDate(m.created_at, 'DD/MM/YYYY') }} {{ formatTime(m.created_at) }}</span>
                            </div>
                            <div v-if="m.created_by" class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
                                <User class="w-3 h-3" />
                                <span>{{ m.created_by }}</span>
                            </div>
                        </td>

                        <!-- Note -->
                        <td class="px-6 py-3 text-slate-600">
                            {{ m.note || '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </BaseCard>
</template>

