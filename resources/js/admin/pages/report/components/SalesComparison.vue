<script setup>
import { ArrowUpRight, ArrowDownRight, GitCompare, Calendar } from 'lucide-vue-next';
import { formatCurrency, formatNumber, formatDate } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';

defineProps({
    comparison: {
        type: Object,
        required: true,
    },
    currentSales: {
        type: Number,
        default: 0,
    },
    currentOrders: {
        type: Number,
        default: 0,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <BaseCard title="Perbandingan dengan Periode Sebelumnya" subtitle="Analisis komparatif performa bisnis periode sekarang vs periode lalu">
        <div v-if="loading" class="animate-pulse space-y-3 py-2">
            <div class="h-16 bg-slate-100 rounded-xl"></div>
            <div class="h-16 bg-slate-100 rounded-xl"></div>
        </div>

        <div v-else class="space-y-4">
            <!-- Period Dates Indicator -->
            <div v-if="comparison.previous_start_date" class="text-xs text-slate-500 flex items-center gap-1.5 p-2.5 rounded-lg bg-slate-50 border border-slate-100">
                <Calendar class="w-3.5 h-3.5 text-slate-400" />
                <span>Membandingkan dengan: <strong>{{ formatDate(comparison.previous_start_date) }}</strong> s/d <strong>{{ formatDate(comparison.previous_end_date) }}</strong></span>
            </div>

            <!-- Two comparison cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Sales Comparison -->
                <div class="p-4 rounded-xl border" :class="[comparison.sales_growth >= 0 ? 'bg-emerald-50/40 border-emerald-200' : 'bg-rose-50/40 border-rose-200']">
                    <span class="text-xs font-semibold text-slate-600 block mb-1">Pertumbuhan Omzet</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-xl font-bold font-mono" :class="[comparison.sales_growth >= 0 ? 'text-emerald-700' : 'text-rose-700']">
                            {{ comparison.sales_growth >= 0 ? '+' : '' }}{{ comparison.sales_growth }}%
                        </span>
                        <span class="text-xs text-slate-500">
                            ({{ formatCurrency(currentSales - (comparison.previous_net_sales || 0)) }})
                        </span>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-500">
                        Periode lalu: {{ formatCurrency(comparison.previous_net_sales) }}
                    </div>
                </div>

                <!-- Order Count Comparison -->
                <div class="p-4 rounded-xl border" :class="[comparison.orders_growth >= 0 ? 'bg-indigo-50/40 border-indigo-200' : 'bg-rose-50/40 border-rose-200']">
                    <span class="text-xs font-semibold text-slate-600 block mb-1">Pertumbuhan Volume Transaksi</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-xl font-bold font-mono" :class="[comparison.orders_growth >= 0 ? 'text-indigo-700' : 'text-rose-700']">
                            {{ comparison.orders_growth >= 0 ? '+' : '' }}{{ comparison.orders_growth }}%
                        </span>
                        <span class="text-xs text-slate-500">
                            ({{ currentOrders - (comparison.previous_orders_count || 0) }} trx)
                        </span>
                    </div>
                    <div class="mt-2 text-[11px] text-slate-500">
                        Periode lalu: {{ formatNumber(comparison.previous_orders_count) }} transaksi
                    </div>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

