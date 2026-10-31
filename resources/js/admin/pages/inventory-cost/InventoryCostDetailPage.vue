<script setup>
import { onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ArrowLeft, RefreshCw, DollarSign, Layers, AlertCircle } from 'lucide-vue-next';
import { useInventoryCostStore } from '../../stores/inventory-cost.store';
import { useNotificationStore } from '../../stores/notification.store';
import { formatCurrency, formatNumber } from '../../utils/formatter';

import StockValuation from './components/StockValuation.vue';
import BatchTable from './components/BatchTable.vue';
import CostHistoryChart from './components/CostHistoryChart.vue';
import BaseButton from '../../components/common/BaseButton.vue';
import BaseLoading from '../../components/common/BaseLoading.vue';

const route = useRoute();
const router = useRouter();
const costStore = useInventoryCostStore();
const notificationStore = useNotificationStore();

const ingredientId = computed(() => route.params.id);
const detail = computed(() => costStore.selectedCostDetail);

onMounted(() => {
    loadDetail();
});

async function loadDetail() {
    if (ingredientId.value) {
        await costStore.fetchIngredientCost(ingredientId.value).catch(() => {});
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- Top Nav & Actions -->
        <div class="flex items-center justify-between gap-4">
            <BaseButton
                variant="outline"
                size="sm"
                @click="router.push('/inventory/cost')"
            >
                <ArrowLeft class="w-4 h-4" />
                <span>Kembali ke Valuasi Biaya</span>
            </BaseButton>

            <BaseButton
                v-if="detail"
                variant="ghost"
                size="sm"
                @click="loadDetail"
                :loading="costStore.detailLoading"
            >
                <RefreshCw class="w-3.5 h-3.5" :class="[costStore.detailLoading ? 'animate-spin' : '']" />
                <span>Segarkan</span>
            </BaseButton>
        </div>

        <!-- Loading -->
        <div v-if="costStore.detailLoading" class="py-12">
            <BaseLoading text="Memuat analisis biaya batch bahan..." />
        </div>

        <!-- Error -->
        <div
            v-else-if="costStore.error || !detail"
            class="p-6 rounded-2xl bg-rose-50 border border-rose-200 text-center max-w-lg mx-auto space-y-4"
        >
            <AlertCircle class="w-12 h-12 text-rose-500 mx-auto" />
            <h3 class="text-base font-bold text-slate-800">Data Biaya Bahan Tidak Ditemukan</h3>
            <p class="text-xs text-slate-500">
                {{ costStore.error || 'Data biaya dan batch untuk bahan ini tidak tersedia.' }}
            </p>
            <BaseButton variant="primary" size="sm" @click="router.push('/inventory/cost')">
                Kembali ke Ringkasan Biaya
            </BaseButton>
        </div>

        <!-- Detail View -->
        <div v-else class="space-y-6">
            <!-- Header Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg">
                        <DollarSign class="w-6 h-6" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">
                            {{ detail.ingredient?.name }}
                        </h2>
                        <p class="text-xs text-slate-400">
                            Satuan persediaan: {{ detail.ingredient?.unit?.toUpperCase() }} &bull; Valuasi & Antrean FIFO
                        </p>
                    </div>
                </div>

                <!-- Grand Value Highlight -->
                <div class="text-left sm:text-right bg-slate-50 sm:bg-transparent p-3 sm:p-0 rounded-xl">
                    <span class="text-xs text-slate-500 block">Total Valuasi Modal Tersimpan</span>
                    <span class="text-2xl font-black font-mono text-emerald-600">
                        {{ formatCurrency(detail.total_stock_value) }}
                    </span>
                </div>
            </div>

            <!-- Math Valuation Card -->
            <StockValuation
                :stock="detail.stock"
                :unit="detail.ingredient?.unit"
                :average-cost="detail.average_cost"
                :total-value="detail.total_stock_value"
            />

            <!-- Cost History Trend Chart -->
            <CostHistoryChart :history="detail.cost_history || []" />

            <!-- Batches FIFO Table -->
            <BatchTable
                :batches="detail.batches || []"
                :unit="detail.ingredient?.unit"
            />
        </div>
    </div>
</template>

