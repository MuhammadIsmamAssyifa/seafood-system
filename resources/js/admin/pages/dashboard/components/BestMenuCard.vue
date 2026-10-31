<script setup>
import { computed } from 'vue';
import { Award, Flame } from 'lucide-vue-next';
import { formatCurrency, formatNumber } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';
import BaseEmpty from '../../../components/common/BaseEmpty.vue';

const props = defineProps({
    menus: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const maxQuantity = computed(() => {
    if (!props.menus.length) return 1;
    return Math.max(...props.menus.map((m) => m.quantity_sold || 0), 1);
});
</script>

<template>
    <BaseCard title="Menu Terlaris Hari Ini" subtitle="5 varian menu dengan penjualan tertinggi">
        <template #action>
            <span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">
                <Flame class="w-3.5 h-3.5 fill-amber-500 text-amber-500" />
                Top 5
            </span>
        </template>

        <!-- Loading Skeleton -->
        <div v-if="loading" class="space-y-4 py-2">
            <div v-for="i in 4" :key="i" class="animate-pulse flex items-center justify-between">
                <div class="space-y-1.5 w-2/3">
                    <div class="h-4 bg-slate-200 rounded w-40"></div>
                    <div class="h-3 bg-slate-100 rounded w-20"></div>
                </div>
                <div class="h-5 bg-slate-200 rounded w-24"></div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!menus || menus.length === 0" class="py-4">
            <BaseEmpty
                title="Belum Ada Penjualan Menu"
                message="Data menu terlaris akan otomatis terisi saat pesanan telah diselesaikan oleh kasir."
            />
        </div>

        <!-- Menu List -->
        <div v-else class="space-y-3.5">
            <div
                v-for="(item, index) in menus"
                :key="item.variant_id || index"
                class="p-3 rounded-xl bg-slate-50/70 border border-slate-100 hover:border-slate-200 transition-colors"
            >
                <div class="flex items-start justify-between gap-3 mb-1.5">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span
                            class="w-6 h-6 rounded-lg flex items-center justify-center font-bold font-mono text-xs shrink-0"
                            :class="[
                                index === 0
                                    ? 'bg-amber-400 text-amber-950 shadow-xs shadow-amber-400/40'
                                    : index === 1
                                    ? 'bg-slate-300 text-slate-800'
                                    : index === 2
                                    ? 'bg-amber-700/20 text-amber-800'
                                    : 'bg-slate-200 text-slate-600',
                            ]"
                        >
                            {{ index + 1 }}
                        </span>

                        <div class="min-w-0">
                            <h4 class="text-sm font-bold text-slate-800 truncate">
                                {{ item.menu_name || 'Menu' }}
                            </h4>
                            <p v-if="item.variant_name" class="text-xs text-slate-500 truncate">
                                {{ item.variant_name }}
                            </p>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <div class="text-xs font-bold text-slate-800 font-mono">
                            {{ formatNumber(item.quantity_sold) }} {{ item.sales_unit || 'porsi' }}
                        </div>
                        <div class="text-[11px] font-semibold text-emerald-600 font-mono">
                            {{ formatCurrency(item.total_revenue) }}
                        </div>
                    </div>
                </div>

                <!-- Relative Bar -->
                <div class="w-full bg-slate-200/70 h-1.5 rounded-full overflow-hidden">
                    <div
                        class="h-full rounded-full transition-all duration-500"
                        :class="[index === 0 ? 'bg-amber-500' : 'bg-blue-500']"
                        :style="{ width: `${((item.quantity_sold || 0) / maxQuantity) * 100}%` }"
                    ></div>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

