<script setup>
import { DollarSign, ShoppingCart, TrendingUp, ArrowUpRight, ArrowDownRight, Layers } from 'lucide-vue-next';
import { formatCurrency, formatNumber } from '../../../utils/formatter';

defineProps({
    summary: {
        type: Object,
        required: true,
    },
    comparison: {
        type: Object,
        default: () => ({}),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Net Sales -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Omzet Bersih</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <DollarSign class="w-4.5 h-4.5" />
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-800 tracking-tight">
                {{ formatCurrency(summary.net_sales) }}
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-slate-500">Gross: {{ formatCurrency(summary.gross_sales) }}</span>
                <span
                    v-if="comparison.sales_growth !== undefined"
                    class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full font-semibold font-mono text-[10px]"
                    :class="[
                        comparison.sales_growth >= 0
                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                            : 'bg-rose-50 text-rose-700 border border-rose-200',
                    ]"
                >
                    <ArrowUpRight v-if="comparison.sales_growth >= 0" class="w-3 h-3" />
                    <ArrowDownRight v-else class="w-3 h-3" />
                    {{ comparison.sales_growth >= 0 ? '+' : '' }}{{ comparison.sales_growth }}%
                </span>
            </div>
        </div>

        <!-- 2. Total Transaksi -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Transaksi</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <ShoppingCart class="w-4.5 h-4.5" />
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-800 tracking-tight">
                {{ formatNumber(summary.total_orders) }} Transaksi
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-slate-500">{{ formatNumber(summary.total_guests) }} total tamu</span>
                <span
                    v-if="comparison.orders_growth !== undefined"
                    class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full font-semibold font-mono text-[10px]"
                    :class="[
                        comparison.orders_growth >= 0
                            ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                            : 'bg-rose-50 text-rose-700 border border-rose-200',
                    ]"
                >
                    <ArrowUpRight v-if="comparison.orders_growth >= 0" class="w-3 h-3" />
                    <ArrowDownRight v-else class="w-3 h-3" />
                    {{ comparison.orders_growth >= 0 ? '+' : '' }}{{ comparison.orders_growth }}%
                </span>
            </div>
        </div>

        <!-- 3. Average Order Value -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rata-rata Order (AOV)</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <Layers class="w-4.5 h-4.5" />
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-800 tracking-tight">
                {{ formatCurrency(summary.average_order_value) }}
            </div>
            <div class="mt-2 text-xs text-slate-500">
                <span>Per tamu: {{ formatCurrency(summary.average_spend_per_guest) }}</span>
            </div>
        </div>

        <!-- 4. Gross Profit -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Estimasi Gross Profit</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <TrendingUp class="w-4.5 h-4.5" />
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-800 tracking-tight">
                {{ formatCurrency(summary.gross_profit) }}
            </div>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-slate-500">Margin: {{ summary.profit_margin }}%</span>
                <span class="text-slate-400 font-mono text-[11px]">HPP: {{ formatCurrency(summary.total_cogs) }}</span>
            </div>
        </div>
    </div>
</template>

