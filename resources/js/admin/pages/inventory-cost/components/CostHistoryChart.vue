<script setup>
import { computed } from 'vue';
import { TrendingUp, Calendar } from 'lucide-vue-next';
import { formatCurrency, formatDate } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';
import BaseEmpty from '../../../components/common/BaseEmpty.vue';

const props = defineProps({
    history: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const maxCost = computed(() => {
    if (!props.history.length) return 1;
    return Math.max(...props.history.map((h) => Number(h.cost_per_unit || 0)), 1);
});
</script>

<template>
    <BaseCard title="Tren Fluktuasi Harga Beli Bahan" subtitle="Histori harga beli satuan per batch masuk dari waktu ke waktu">
        <div v-if="loading" class="p-6 space-y-3">
            <div class="h-28 bg-slate-100 rounded-xl animate-pulse"></div>
        </div>

        <div v-else-if="!history || history.length === 0" class="py-6">
            <BaseEmpty
                title="Belum Ada Histori Harga"
                message="Data perubahan harga beli bahan akan tampil saat tercatat histori batch pembelian."
            />
        </div>

        <div v-else class="space-y-4">
            <!-- Visual Price Trend Bars -->
            <div class="h-36 flex items-end gap-3 pt-6 pb-2 overflow-x-auto no-scrollbar">
                <div
                    v-for="(item, idx) in history"
                    :key="idx"
                    class="flex-1 min-w-[40px] max-w-[60px] flex flex-col items-center gap-1.5 group"
                >
                    <span class="text-[10px] font-mono font-semibold text-slate-700 opacity-0 group-hover:opacity-100 transition-opacity">
                        {{ formatCurrency(item.cost_per_unit) }}
                    </span>

                    <div class="w-full h-24 bg-slate-100 rounded-t-lg flex items-end p-0.5 overflow-hidden">
                        <div
                            class="w-full bg-indigo-500 rounded-t-md transition-all duration-300 group-hover:bg-indigo-600"
                            :style="{ height: `${Math.max(12, (Number(item.cost_per_unit || 0) / maxCost) * 100)}%` }"
                        ></div>
                    </div>

                    <span class="text-[10px] font-mono text-slate-400 whitespace-nowrap">
                        {{ formatDate(item.date, 'DD/MM') }}
                    </span>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

