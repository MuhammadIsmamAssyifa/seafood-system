<script setup>
import { computed } from 'vue';
import { DollarSign, TrendingUp, Percent, ShieldCheck } from 'lucide-vue-next';
import FoodCostBadge from './FoodCostBadge.vue';
import { formatCurrency } from '../../../utils/formatter';

const props = defineProps({
    costData: {
        type: Object,
        default: () => ({
            total_cost: 0,
            selling_price: 0,
            gross_margin: 0,
            food_cost_percentage: 0,
            margin_percentage: 0,
        }),
    },
});
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Selling Price -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                <DollarSign class="w-6 h-6" />
            </div>
            <div>
                <span class="text-xs text-slate-400 block font-medium">Harga Jual Menu</span>
                <span class="text-lg font-bold text-slate-800">
                    {{ formatCurrency(costData.selling_price) }}
                </span>
            </div>
        </div>

        <!-- Total Modal / Recipe Cost -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                <ShieldCheck class="w-6 h-6" />
            </div>
            <div>
                <span class="text-xs text-slate-400 block font-medium">Modal Bahan (Cost)</span>
                <span class="text-lg font-bold text-amber-600">
                    {{ formatCurrency(costData.total_cost) }}
                </span>
            </div>
        </div>

        <!-- Gross Margin -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                <TrendingUp class="w-6 h-6" />
            </div>
            <div>
                <span class="text-xs text-slate-400 block font-medium">Margin Keuntungan</span>
                <span class="text-lg font-bold text-emerald-600">
                    {{ formatCurrency(costData.gross_margin) }}
                </span>
                <span class="text-[11px] text-slate-400 block font-medium">({{ costData.margin_percentage }}% margin)</span>
            </div>
        </div>

        <!-- Food Cost Percentage -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                <Percent class="w-6 h-6" />
            </div>
            <div>
                <span class="text-xs text-slate-400 block font-medium">Food Cost %</span>
                <div class="mt-1">
                    <FoodCostBadge :percentage="costData.food_cost_percentage" />
                </div>
            </div>
        </div>
    </div>
</template>

