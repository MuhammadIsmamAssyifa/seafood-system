<script setup>
import { computed } from 'vue';
import { formatCurrency, formatNumber } from '../../../utils/formatter';
import {
    Flame,
    Scale,
    TrendingDown,
    AlertOctagon,
    ArrowUpRight,
} from 'lucide-vue-next';

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({
            total_cost: 0,
            total_quantity: 0,
            total_records: 0,
            waste_percentage: 0,
            most_wasted_ingredient: null,
        }),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const mostWasted = computed(() => props.summary?.most_wasted_ingredient);
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Waste Cost -->
        <div class="bg-white rounded-xl border border-red-100 p-5 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-red-600 uppercase tracking-wider">Total Kerugian Waste</span>
                <div class="w-9 h-9 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                    <TrendingDown class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-3">
                <div v-if="loading" class="h-8 w-32 bg-slate-100 animate-pulse rounded"></div>
                <div v-else class="text-2xl font-bold text-slate-900 tracking-tight">
                    {{ formatCurrency(summary.total_cost || 0) }}
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                <span>{{ summary.total_records || 0 }} kali kejadian tercatat</span>
            </p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-red-500"></div>
        </div>

        <!-- Total Waste Quantity -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Kuantitas Terbuang</span>
                <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <Scale class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-3">
                <div v-if="loading" class="h-8 w-28 bg-slate-100 animate-pulse rounded"></div>
                <div v-else class="text-2xl font-bold text-slate-900 tracking-tight flex items-baseline gap-1.5">
                    <span>{{ formatNumber(summary.total_quantity || 0, 3) }}</span>
                    <span class="text-sm font-normal text-slate-500">kg/satuan</span>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Akumulasi fisik bahan yang hilang
            </p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-amber-500"></div>
        </div>

        <!-- Most Wasted Ingredient -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Bahan Paling Banyak Terbuang</span>
                <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <Flame class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-3">
                <div v-if="loading" class="h-8 w-36 bg-slate-100 animate-pulse rounded"></div>
                <div v-else-if="mostWasted" class="truncate">
                    <div class="text-lg font-bold text-slate-900 truncate">
                        {{ mostWasted.ingredient_name }}
                    </div>
                    <div class="text-xs text-red-600 font-semibold mt-0.5 flex items-center gap-1.5">
                        <span>{{ formatCurrency(mostWasted.total_cost) }}</span>
                        <span class="text-slate-400">•</span>
                        <span class="text-slate-600">{{ formatNumber(mostWasted.total_quantity, 3) }} {{ mostWasted.unit }}</span>
                    </div>
                </div>
                <div v-else class="text-sm font-medium text-slate-400 mt-1">
                    Belum ada data waste
                </div>
            </div>
            <p v-if="!mostWasted && !loading" class="text-xs text-slate-400 mt-1">
                Data bahan terbuang akan muncul di sini
            </p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-indigo-500"></div>
        </div>

        <!-- Waste Percentage / Loss Ratio -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Rasio Kehilangan (Loss)</span>
                <div class="w-9 h-9 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <AlertOctagon class="w-5 h-5" />
                </div>
            </div>
            <div class="mt-3">
                <div v-if="loading" class="h-8 w-24 bg-slate-100 animate-pulse rounded"></div>
                <div v-else class="text-2xl font-bold text-slate-900 tracking-tight flex items-baseline gap-1.5">
                    <span :class="summary.waste_percentage > 5 ? 'text-red-600' : 'text-slate-900'">
                        {{ summary.waste_percentage || 0 }}%
                    </span>
                    <span class="text-xs font-medium text-slate-500">dari modal stok</span>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                <span :class="summary.waste_percentage <= 3 ? 'text-emerald-600 font-medium' : 'text-amber-600 font-medium'">
                    {{ summary.waste_percentage <= 3 ? 'Target efisien terlampaui (≤3%)' : 'Perlu evaluasi penanganan dapur' }}
                </span>
            </p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-rose-500"></div>
        </div>
    </div>
</template>

