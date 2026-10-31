<script setup>
import { computed } from 'vue';
import { formatCurrency, formatNumber } from '../../../utils/formatter';
import WasteReasonBadge from './WasteReasonBadge.vue';
import { PieChart, AlertTriangle } from 'lucide-vue-next';

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({
            reason_breakdown: [],
            top_wasted_ingredients: [],
            total_cost: 0,
            waste_percentage: 0,
        }),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const reasons = computed(() => props.summary?.reason_breakdown || []);
const topIngredients = computed(() => props.summary?.top_wasted_ingredients || []);
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Breakdown by Reason -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                        <PieChart class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Analisis Penyebab Waste</h3>
                        <p class="text-xs text-slate-500">Distribusi kerugian berdasarkan kategori alasan</p>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="space-y-3 py-2">
                <div v-for="i in 4" :key="i" class="h-10 bg-slate-100 animate-pulse rounded-lg"></div>
            </div>

            <div v-else-if="reasons.length === 0" class="py-10 text-center">
                <p class="text-xs text-slate-400">Belum ada catatan waste yang dianalisis.</p>
            </div>

            <div v-else class="space-y-4">
                <div v-for="item in reasons" :key="item.reason" class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <WasteReasonBadge :reason="item.reason" :label="item.label" />
                            <span class="text-slate-400 text-[11px]">({{ item.count }} kali)</span>
                        </div>
                        <div class="flex items-center gap-2 font-medium">
                            <span class="text-slate-900 font-semibold">{{ formatCurrency(item.total_cost) }}</span>
                            <span class="text-slate-400 text-[11px]">({{ item.percentage }}%)</span>
                        </div>
                    </div>
                    <!-- Progress bar -->
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div
                            class="h-full rounded-full transition-all duration-500"
                            :class="{
                                'bg-red-500': item.reason === 'expired',
                                'bg-orange-500': item.reason === 'damaged',
                                'bg-amber-500': item.reason === 'cooking_loss',
                                'bg-purple-500': item.reason === 'wrong_preparation',
                                'bg-slate-400': item.reason === 'other',
                            }"
                            :style="{ width: `${Math.min(100, Math.max(2, item.percentage))}%` }"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Wasted Ingredients -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <AlertTriangle class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Bahan dengan Kerugian Tertinggi</h3>
                        <p class="text-xs text-slate-500">Fokus perhatian pengendalian stok & live vendor</p>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="space-y-3 py-2">
                <div v-for="i in 4" :key="i" class="h-10 bg-slate-100 animate-pulse rounded-lg"></div>
            </div>

            <div v-else-if="topIngredients.length === 0" class="py-10 text-center">
                <p class="text-xs text-slate-400">Belum ada bahan baku terbuang.</p>
            </div>

            <div v-else class="divide-y divide-slate-100">
                <div
                    v-for="(ing, idx) in topIngredients"
                    :key="ing.ingredient_id"
                    class="py-2.5 flex items-center justify-between text-xs first:pt-0 last:pb-0"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="w-5 h-5 rounded-full flex items-center justify-center font-bold text-[10px]"
                            :class="idx === 0 ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600'"
                        >
                            {{ idx + 1 }}
                        </span>
                        <div>
                            <p class="font-medium text-slate-900">{{ ing.ingredient_name }}</p>
                            <p class="text-[11px] text-slate-400">
                                {{ formatNumber(ing.total_quantity, 3) }} {{ ing.unit }} terbuang ({{ ing.count }}x)
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="font-bold text-red-600">{{ formatCurrency(ing.total_cost) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

