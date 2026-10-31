<script setup>
import { Award, TrendingUp } from 'lucide-vue-next';
import { formatCurrency, formatNumber } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';
import BaseEmpty from '../../../components/common/BaseEmpty.vue';

defineProps({
    menus: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <BaseCard title="Peringkat Kinerja Menu & Produk" subtitle="Evaluasi produk paling laris dan kontribusi omset terhadap bisnis">
        <div v-if="loading" class="animate-pulse space-y-3 py-2">
            <div v-for="i in 5" :key="i" class="h-10 bg-slate-100 rounded-lg"></div>
        </div>

        <div v-else-if="!menus || menus.length === 0" class="py-6">
            <BaseEmpty
                title="Belum Ada Data Penjualan Menu"
                message="Data kinerja menu akan muncul saat pesanan pada periode ini telah diselesaikan."
            />
        </div>

        <div v-else class="overflow-x-auto -mx-6">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                <thead>
                    <tr class="bg-slate-50/70 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Peringkat & Menu</th>
                        <th class="px-4 py-3 text-right">Porsi Terjual</th>
                        <th class="px-4 py-3 text-right">Total Omset</th>
                        <th class="px-6 py-3 text-right">Gross Profit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <tr
                        v-for="(item, idx) in menus"
                        :key="item.name + idx"
                        class="hover:bg-slate-50/60 transition-colors"
                    >
                        <!-- Menu Name & Rank -->
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <span
                                    class="w-6 h-6 rounded-md flex items-center justify-center font-mono text-xs font-bold shrink-0"
                                    :class="[
                                        idx === 0
                                            ? 'bg-amber-400 text-amber-950'
                                            : idx === 1
                                            ? 'bg-slate-300 text-slate-800'
                                            : idx === 2
                                            ? 'bg-amber-700/20 text-amber-800'
                                            : 'bg-slate-100 text-slate-500',
                                    ]"
                                >
                                    {{ idx + 1 }}
                                </span>
                                <div>
                                    <div class="font-bold text-slate-800 text-sm">
                                        {{ item.name }}
                                    </div>
                                    <div v-if="item.variant" class="text-xs text-slate-400">
                                        {{ item.variant }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Quantity -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono text-slate-800">
                            {{ formatNumber(item.quantity) }} {{ item.unit || 'porsi' }}
                        </td>

                        <!-- Revenue -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono font-bold text-blue-600">
                            {{ formatCurrency(item.revenue) }}
                        </td>

                        <!-- Profit -->
                        <td class="px-6 py-3.5 whitespace-nowrap text-right font-mono font-semibold text-emerald-600">
                            {{ formatCurrency(item.profit) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </BaseCard>
</template>

