<script setup>
import { computed } from 'vue';
import { Armchair, AlertTriangle, CheckCircle, Store } from 'lucide-vue-next';
import { formatNumber } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';

const props = defineProps({
    tables: {
        type: Object,
        default: () => ({}),
    },
    inventoryAlerts: {
        type: Object,
        default: () => ({}),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const totalTables = computed(() => props.tables?.total_tables || 0);
const occupied = computed(() => props.tables?.occupied_tables || 0);
const available = computed(() => props.tables?.available_tables || 0);
const occupancyRate = computed(() => Number(props.tables?.occupancy_rate || 0));

const lowStockCount = computed(() => props.inventoryAlerts?.low_stock_count || 0);
const lowStockItems = computed(() => props.inventoryAlerts?.low_stock_items || []);
</script>

<template>
    <BaseCard title="Kapasitas & Peringatan Bahan Baku" subtitle="Kondisi meja makan dan stok inventaris restoran">
        <div v-if="loading" class="animate-pulse space-y-4 py-2">
            <div class="h-20 bg-slate-100 rounded-xl"></div>
            <div class="h-20 bg-slate-100 rounded-xl"></div>
        </div>

        <div v-else class="space-y-4">
            <!-- Table Occupancy Card -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center">
                            <Armchair class="w-4 h-4" />
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate-800">Okupansi Meja</span>
                            <p class="text-[10px] text-slate-500">{{ occupied }} dari {{ totalTables }} meja terisi</p>
                        </div>
                    </div>

                    <span class="text-base font-extrabold font-mono text-blue-700">
                        {{ occupancyRate }}%
                    </span>
                </div>

                <!-- Occupancy bar -->
                <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                    <div
                        class="h-full bg-blue-600 rounded-full transition-all duration-500"
                        :style="{ width: `${occupancyRate}%` }"
                    ></div>
                </div>

                <div class="flex justify-between items-center text-xs mt-2 text-slate-600">
                    <span>Meja Tersedia: <strong class="text-emerald-600 font-mono">{{ available }}</strong></span>
                    <span>Meja Terisi: <strong class="text-blue-600 font-mono">{{ occupied }}</strong></span>
                </div>
            </div>

            <!-- Inventory Low Stock Alert -->
            <div
                class="p-4 rounded-xl border transition-colors"
                :class="[
                    lowStockCount > 0
                        ? 'bg-amber-50/60 border-amber-200'
                        : 'bg-emerald-50/50 border-emerald-200',
                ]"
            >
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-2.5">
                        <div
                            class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                            :class="[lowStockCount > 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700']"
                        >
                            <AlertTriangle v-if="lowStockCount > 0" class="w-4 h-4" />
                            <CheckCircle v-else class="w-4 h-4" />
                        </div>
                        <div>
                            <span class="text-xs font-bold text-slate-800">
                                {{ lowStockCount > 0 ? 'Peringatan Stok Menipis' : 'Stok Bahan Baku Aman' }}
                            </span>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                {{
                                    lowStockCount > 0
                                        ? `${lowStockCount} bahan baku berada di bawah batas aman (&le; 5)`
                                        : 'Semua bahan baku dalam level ketersediaan aman'
                                }}
                            </p>
                        </div>
                    </div>

                    <span
                        v-if="lowStockCount > 0"
                        class="px-2 py-0.5 rounded font-mono text-xs font-bold bg-amber-200 text-amber-900"
                    >
                        {{ lowStockCount }}
                    </span>
                </div>

                <!-- First 3 items list if any -->
                <div v-if="lowStockItems.length > 0" class="mt-3 pt-2.5 border-t border-amber-200/60 space-y-1">
                    <div
                        v-for="item in lowStockItems.slice(0, 3)"
                        :key="item.ingredient_id"
                        class="flex justify-between items-center text-xs text-amber-900 font-medium"
                    >
                        <span class="truncate">{{ item.name }}</span>
                        <span class="font-mono text-rose-600 font-bold shrink-0">
                            {{ formatNumber(item.current_stock) }} {{ item.unit }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

