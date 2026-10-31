<script setup>
import { TrendingUp, Minus, Equal, ArrowRight } from 'lucide-vue-next';
import { formatCurrency } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';

defineProps({
    summary: {
        type: Object,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <BaseCard title="Rangkuman Laba Kotor (Gross Profit)" subtitle="Kalkulasi pendapatan dikurangi Harga Pokok Penjualan (HPP / COGS)">
        <div v-if="loading" class="animate-pulse space-y-3 py-2">
            <div class="h-20 bg-slate-100 rounded-xl"></div>
        </div>

        <div v-else class="space-y-4">
            <!-- Math calculation row -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 grid grid-cols-1 md:grid-cols-5 items-center gap-3 text-center">
                <!-- Revenue -->
                <div class="p-3 bg-white rounded-lg border border-slate-200/80">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Total Omzet Bersih</span>
                    <span class="text-base font-extrabold font-mono text-blue-700">{{ formatCurrency(summary.net_sales) }}</span>
                </div>

                <div class="hidden md:flex justify-center text-slate-400">
                    <Minus class="w-5 h-5" />
                </div>

                <!-- COGS -->
                <div class="p-3 bg-white rounded-lg border border-slate-200/80">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Estimasi HPP (COGS)</span>
                    <span class="text-base font-extrabold font-mono text-amber-700">{{ formatCurrency(summary.total_cogs) }}</span>
                </div>

                <div class="hidden md:flex justify-center text-slate-400">
                    <Equal class="w-5 h-5" />
                </div>

                <!-- Gross Profit -->
                <div class="p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                    <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider block mb-1">Gross Profit (Laba)</span>
                    <span class="text-base font-extrabold font-mono text-emerald-700">{{ formatCurrency(summary.gross_profit) }}</span>
                </div>
            </div>

            <!-- Margin Progress Bar -->
            <div>
                <div class="flex items-center justify-between text-xs mb-1.5 font-medium">
                    <span class="text-slate-600">Gross Profit Margin</span>
                    <span class="text-emerald-700 font-bold font-mono">{{ summary.profit_margin }}% dari Penjualan</span>
                </div>
                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden flex">
                    <div
                        class="bg-emerald-500 transition-all duration-500"
                        :style="{ width: `${Math.min(100, Math.max(0, summary.profit_margin))}%` }"
                    ></div>
                    <div
                        class="bg-amber-400 transition-all duration-500"
                        :style="{ width: `${Math.min(100, Math.max(0, 100 - summary.profit_margin))}%` }"
                    ></div>
                </div>
                <div class="flex justify-between items-center text-[10px] text-slate-400 mt-1">
                    <span>Laba Kotor ({{ summary.profit_margin }}%)</span>
                    <span>Beban HPP Bahan Baku ({{ (100 - summary.profit_margin).toFixed(1) }}%)</span>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

