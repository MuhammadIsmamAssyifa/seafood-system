<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { RefreshCw, ArrowLeft, Eye, TrendingUp, DollarSign, AlertCircle } from 'lucide-vue-next';
import { useInventoryCostStore } from '../../stores/inventory-cost.store';
import { useNotificationStore } from '../../stores/notification.store';
import { formatCurrency, formatNumber } from '../../utils/formatter';

import CostSummaryCard from './components/CostSummaryCard.vue';
import BaseButton from '../../components/common/BaseButton.vue';
import BaseCard from '../../components/common/BaseCard.vue';
import BaseEmpty from '../../components/common/BaseEmpty.vue';

const router = useRouter();
const costStore = useInventoryCostStore();
const notificationStore = useNotificationStore();

onMounted(() => {
    costStore.fetchCostSummary();
});

async function handleRefresh() {
    await costStore.fetchCostSummary();
    if (!costStore.error) {
        notificationStore.success('Valuasi biaya persediaan berhasil diperbarui.');
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <router-link
                        to="/inventory"
                        class="text-xs text-blue-600 hover:underline flex items-center gap-1 font-medium"
                    >
                        <ArrowLeft class="w-3.5 h-3.5" />
                        <span>Kembali ke Stok Fisik (Inventory)</span>
                    </router-link>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    Valuasi Biaya Bahan Baku (Inventory Batch Cost)
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Analisis nilai modal bahan yang tersimpan, harga rata-rata tertimbang, dan antrean FIFO per batch.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <BaseButton
                    variant="outline"
                    size="sm"
                    @click="handleRefresh"
                    :loading="costStore.loading"
                    title="Segarkan Data"
                >
                    <RefreshCw class="w-3.5 h-3.5" :class="[costStore.loading ? 'animate-spin' : '']" />
                    <span class="hidden sm:inline">Segarkan Valuasi</span>
                </BaseButton>
            </div>
        </div>

        <!-- 4 Summary Cards -->
        <CostSummaryCard
            :summary="costStore.costSummary"
            :loading="costStore.loading"
        />

        <!-- Error Alert -->
        <div
            v-if="costStore.error"
            class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between gap-3 shadow-xs"
        >
            <div class="flex items-center gap-3">
                <AlertCircle class="w-5 h-5 text-rose-600 shrink-0" />
                <div>
                    <h4 class="text-sm font-bold">Gagal Mengambil Valuasi Biaya</h4>
                    <p class="text-xs text-rose-700 mt-0.5">{{ costStore.error }}</p>
                </div>
            </div>
            <BaseButton
                variant="danger"
                size="sm"
                @click="costStore.fetchCostSummary()"
                :loading="costStore.loading"
            >
                Coba Lagi
            </BaseButton>
        </div>

        <!-- Ingredients Cost Valuation Table -->
        <BaseCard padding="p-0">
            <div v-if="costStore.loading" class="p-6 space-y-3">
                <div v-for="i in 5" :key="i" class="h-12 bg-slate-100 rounded-lg animate-pulse"></div>
            </div>

            <div v-else-if="!costStore.costSummary.ingredients || costStore.costSummary.ingredients.length === 0" class="p-8">
                <BaseEmpty
                    title="Belum Ada Data Biaya Bahan"
                    message="Belum ada data bahan baku atau batch modal yang dicatat."
                />
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                    <thead>
                        <tr class="bg-slate-50/70 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="px-6 py-3.5">Nama Bahan Baku</th>
                            <th class="px-4 py-3.5 text-right">Sisa Stok Fisik</th>
                            <th class="px-4 py-3.5 text-right">Harga Beli Rata-Rata</th>
                            <th class="px-4 py-3.5 text-center">Batch Aktif</th>
                            <th class="px-4 py-3.5 text-right">Total Nilai Modal (Valuasi)</th>
                            <th class="px-6 py-3.5 text-center">Rincian Batch</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <tr
                            v-for="item in costStore.costSummary.ingredients"
                            :key="item.id"
                            class="hover:bg-slate-50/70 transition-colors cursor-pointer"
                            @click="router.push(`/inventory/cost/${item.id}`)"
                        >
                            <!-- Name -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-bold text-slate-800 text-sm hover:text-indigo-600 transition-colors">
                                    {{ item.name }}
                                </span>
                            </td>

                            <!-- Stock -->
                            <td class="px-4 py-4 whitespace-nowrap text-right font-mono font-bold text-slate-800">
                                {{ formatNumber(item.current_stock, 2) }} {{ item.unit }}
                            </td>

                            <!-- Average Cost -->
                            <td class="px-4 py-4 whitespace-nowrap text-right font-mono font-semibold text-indigo-700">
                                {{ formatCurrency(item.average_cost) }} / {{ item.unit }}
                            </td>

                            <!-- Batches Count -->
                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-700">
                                    {{ item.batches_count }} batch
                                </span>
                            </td>

                            <!-- Total Value -->
                            <td class="px-4 py-4 whitespace-nowrap text-right font-mono font-bold text-base text-emerald-600">
                                {{ formatCurrency(item.total_value) }}
                            </td>

                            <!-- Action -->
                            <td class="px-6 py-4 whitespace-nowrap text-center" @click.stop>
                                <router-link
                                    :to="`/inventory/cost/${item.id}`"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition-colors"
                                >
                                    <Eye class="w-3.5 h-3.5" />
                                    <span>Batch FIFO</span>
                                </router-link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </BaseCard>
    </div>
</template>

