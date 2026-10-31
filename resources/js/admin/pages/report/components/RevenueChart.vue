<script setup>
import { computed, ref } from 'vue';
import { BarChart3, TrendingUp, Calendar } from 'lucide-vue-next';
import { formatCurrency, formatNumber, formatDate } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';
import BaseEmpty from '../../../components/common/BaseEmpty.vue';

const props = defineProps({
    trends: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const hoveredPoint = ref(null);

const maxSales = computed(() => {
    if (!props.trends.length) return 1;
    return Math.max(...props.trends.map((t) => Number(t.net_sales || 0)), 1);
});

const totalPeriodRevenue = computed(() => {
    return props.trends.reduce((sum, t) => sum + Number(t.net_sales || 0), 0);
});
</script>

<template>
    <BaseCard title="Grafik Trend Penjualan Harian" subtitle="Visualisasi fluktuasi omset dan volume pesanan per hari">
        <template #action>
            <div class="text-right">
                <span class="text-xs text-slate-500 block">Total Pada Grafik</span>
                <span class="text-sm font-bold text-slate-800 font-mono">{{ formatCurrency(totalPeriodRevenue) }}</span>
            </div>
        </template>

        <div v-if="loading" class="animate-pulse space-y-4 py-4">
            <div class="h-44 bg-slate-100 rounded-xl"></div>
        </div>

        <div v-else-if="!trends || trends.length === 0" class="py-6">
            <BaseEmpty
                title="Tidak Ada Riwayat Penjualan"
                message="Tidak ditemukan transaksi selesai pada rentang tanggal yang dipilih."
            />
        </div>

        <div v-else class="space-y-4">
            <!-- Active Hovered Inspector Tooltip -->
            <div class="h-10 flex items-center justify-between px-3 rounded-lg bg-slate-50 border border-slate-200/80 text-xs">
                <div v-if="hoveredPoint" class="flex items-center gap-4 text-slate-700">
                    <span class="font-bold flex items-center gap-1 text-slate-900">
                        <Calendar class="w-3.5 h-3.5 text-blue-600" />
                        {{ formatDate(hoveredPoint.date, 'DD MMMM YYYY') }}
                    </span>
                    <span>Omset: <strong class="text-blue-600 font-mono">{{ formatCurrency(hoveredPoint.net_sales) }}</strong></span>
                    <span>Order: <strong class="text-indigo-600 font-mono">{{ formatNumber(hoveredPoint.total_orders) }} trx</strong></span>
                    <span v-if="hoveredPoint.gross_profit">Profit: <strong class="text-emerald-600 font-mono">{{ formatCurrency(hoveredPoint.gross_profit) }}</strong></span>
                </div>
                <div v-else class="text-slate-400 italic">
                    Arahkan kursor pada batang grafik di bawah untuk melihat rincian tanggal tertentu
                </div>
            </div>

            <!-- Bar Chart Visualization -->
            <div class="relative pt-6 pb-2">
                <div class="h-48 flex items-end gap-2 sm:gap-3 overflow-x-auto pb-4 pt-2">
                    <div
                        v-for="item in trends"
                        :key="item.date"
                        class="flex-1 min-w-[28px] max-w-[56px] flex flex-col items-center gap-2 group cursor-pointer"
                        @mouseenter="hoveredPoint = item"
                        @mouseleave="hoveredPoint = null"
                    >
                        <!-- Bar Container -->
                        <div class="w-full h-36 bg-slate-100 rounded-t-lg flex items-end overflow-hidden p-0.5">
                            <div
                                class="w-full rounded-t-md transition-all duration-300 group-hover:brightness-110"
                                :class="[
                                    hoveredPoint?.date === item.date
                                        ? 'bg-blue-600 shadow-sm shadow-blue-500/40'
                                        : 'bg-blue-500/85',
                                ]"
                                :style="{ height: `${Math.max(8, (Number(item.net_sales || 0) / maxSales) * 100)}%` }"
                            ></div>
                        </div>

                        <!-- Date Label -->
                        <span class="text-[10px] font-mono text-slate-500 group-hover:text-blue-600 group-hover:font-bold whitespace-nowrap">
                            {{ formatDate(item.date, 'DD/MM') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

