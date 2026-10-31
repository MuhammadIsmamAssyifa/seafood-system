<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ArrowLeft, Plus, DollarSign, Layers, Clock, AlertCircle } from 'lucide-vue-next';
import { useInventoryStore } from '../../stores/inventory.store';
import { useNotificationStore } from '../../stores/notification.store';
import { formatNumber, formatCurrency, formatDate } from '../../utils/formatter';

import StockAdjustmentModal from './components/StockAdjustmentModal.vue';
import StockMovementTable from './components/StockMovementTable.vue';
import BaseButton from '../../components/common/BaseButton.vue';
import BaseCard from '../../components/common/BaseCard.vue';
import BaseLoading from '../../components/common/BaseLoading.vue';

const route = useRoute();
const router = useRouter();
const inventoryStore = useInventoryStore();
const notificationStore = useNotificationStore();

const ingredientId = computed(() => route.params.id);
const ingredient = computed(() => inventoryStore.selectedIngredient);

const isAdjustModalOpen = ref(false);

onMounted(() => {
    loadDetail();
});

async function loadDetail() {
    if (ingredientId.value) {
        await inventoryStore.fetchIngredientDetail(ingredientId.value).catch(() => {});
    }
}

async function handleAdjustSubmit(payload) {
    try {
        await inventoryStore.adjustStock(payload);
        isAdjustModalOpen.value = false;
        notificationStore.success('Penyesuaian stok berhasil disimpan.');
        await loadDetail();
    } catch (err) {
        notificationStore.error(err.customMessage || 'Gagal menyimpan penyesuaian stok.');
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
                @click="router.push('/inventory')"
            >
                <ArrowLeft class="w-4 h-4" />
                <span>Kembali ke Inventaris</span>
            </BaseButton>

            <div v-if="ingredient" class="flex items-center gap-2">
                <router-link
                    :to="`/inventory/cost/${ingredient.id}`"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition-colors"
                >
                    <DollarSign class="w-3.5 h-3.5 text-indigo-600" />
                    <span>Analisis Biaya (Cost)</span>
                </router-link>

                <BaseButton
                    variant="primary"
                    size="sm"
                    @click="isAdjustModalOpen = true"
                >
                    <Plus class="w-3.5 h-3.5" />
                    <span>Penyesuaian Stok</span>
                </BaseButton>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="inventoryStore.detailLoading" class="py-12">
            <BaseLoading text="Memuat rincian stok bahan..." />
        </div>

        <!-- Error -->
        <div
            v-else-if="inventoryStore.error || !ingredient"
            class="p-6 rounded-2xl bg-rose-50 border border-rose-200 text-center max-w-lg mx-auto space-y-4"
        >
            <AlertCircle class="w-12 h-12 text-rose-500 mx-auto" />
            <h3 class="text-base font-bold text-slate-800">Bahan Baku Tidak Ditemukan</h3>
            <p class="text-xs text-slate-500">
                {{ inventoryStore.error || 'Data bahan baku tidak tersedia.' }}
            </p>
            <BaseButton variant="primary" size="sm" @click="router.push('/inventory')">
                Kembali ke Inventaris
            </BaseButton>
        </div>

        <!-- Detail Content -->
        <div v-else class="space-y-6">
            <!-- Header Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                            <Layers class="w-6 h-6" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">{{ ingredient.name }}</h2>
                            <p class="text-xs text-slate-400">Satuan dasar: {{ ingredient.unit?.toUpperCase() }}</p>
                        </div>
                    </div>
                </div>

                <!-- Stock Balance -->
                <div class="text-left sm:text-right bg-slate-50 sm:bg-transparent p-3 sm:p-0 rounded-xl">
                    <span class="text-xs text-slate-500 block">Sisa Stok Fisik</span>
                    <span class="text-2xl font-black font-mono" :class="[ingredient.current_stock <= 0 ? 'text-rose-600' : 'text-slate-900']">
                        {{ formatNumber(ingredient.current_stock, 2) }} <span class="text-sm font-normal text-slate-500">{{ ingredient.unit }}</span>
                    </span>
                </div>
            </div>

            <!-- Active Batches Table -->
            <BaseCard title="Daftar Batch Pembelian Aktif" subtitle="Histori batch persediaan bahan baku yang masih memiliki sisa stok">
                <div v-if="!ingredient.batches || ingredient.batches.length === 0" class="py-4 text-xs text-slate-400 italic">
                    Belum ada batch persediaan aktif untuk bahan ini.
                </div>
                <div v-else class="overflow-x-auto -mx-6">
                    <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="px-6 py-2.5">Tanggal Diterima</th>
                                <th class="px-4 py-2.5">Sumber / Vendor</th>
                                <th class="px-4 py-2.5 text-right">Kuantitas Awal</th>
                                <th class="px-4 py-2.5 text-right">Sisa Stok</th>
                                <th class="px-4 py-2.5 text-right">Harga Beli / Unit</th>
                                <th class="px-6 py-2.5 text-right">Nilai Modal Sisa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <tr v-for="b in ingredient.batches" :key="b.id" class="hover:bg-slate-50/60" :class="[!b.is_active ? 'opacity-50' : '']">
                                <td class="px-6 py-3 whitespace-nowrap">
                                    {{ formatDate(b.received_at, 'DD/MM/YYYY HH:mm') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">
                                    {{ b.source || 'Pembelian Pasar Ikan' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right font-mono">
                                    {{ formatNumber(b.quantity, 2) }} {{ ingredient.unit }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right font-mono font-bold" :class="[b.remaining_quantity > 0 ? 'text-blue-600' : 'text-slate-400']">
                                    {{ formatNumber(b.remaining_quantity, 2) }} {{ ingredient.unit }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right font-mono text-slate-600">
                                    {{ formatCurrency(b.cost_per_unit) }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-right font-mono font-bold text-emerald-600">
                                    {{ formatCurrency(b.total_value) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </BaseCard>

            <!-- Stock Movement History Table -->
            <StockMovementTable :movements="ingredient.recent_movements || []" />

            <!-- Adjustment Modal -->
            <StockAdjustmentModal
                v-model="isAdjustModalOpen"
                :ingredients="[ingredient]"
                :initial-ingredient="ingredient"
                :loading="inventoryStore.adjusting"
                @submit="handleAdjustSubmit"
            />
        </div>
    </div>
</template>

