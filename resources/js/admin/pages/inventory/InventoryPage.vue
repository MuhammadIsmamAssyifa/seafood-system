<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Plus, RefreshCw, Search, DollarSign, Filter, AlertCircle } from 'lucide-vue-next';
import { useInventoryStore } from '../../stores/inventory.store';
import { useNotificationStore } from '../../stores/notification.store';

import StockCard from './components/StockCard.vue';
import LowStockAlert from './components/LowStockAlert.vue';
import InventoryTable from './components/InventoryTable.vue';
import StockAdjustmentModal from './components/StockAdjustmentModal.vue';
import BaseButton from '../../components/common/BaseButton.vue';

const router = useRouter();
const inventoryStore = useInventoryStore();
const notificationStore = useNotificationStore();

const isAdjustModalOpen = ref(false);
const targetIngredient = ref(null);
const searchInput = ref('');
let searchTimeout = null;

const statusTabs = [
    { value: '', label: 'Semua Bahan' },
    { value: 'available', label: 'Tersedia' },
    { value: 'low_stock', label: 'Menipis (≤ 5)' },
    { value: 'out_of_stock', label: 'Kosong (0)' },
];

onMounted(() => {
    inventoryStore.fetchInventory();
});

function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        inventoryStore.filters.search = searchInput.value;
        inventoryStore.fetchInventory();
    }, 400);
}

function onStatusSelect(status) {
    inventoryStore.filters.status = status;
    inventoryStore.fetchInventory();
}

function openAdjustModal(ingredient = null) {
    targetIngredient.value = ingredient;
    isAdjustModalOpen.value = true;
}

async function handleAdjustSubmit(payload) {
    try {
        await inventoryStore.adjustStock(payload);
        isAdjustModalOpen.value = false;
        notificationStore.success('Penyesuaian stok berhasil disimpan.');
    } catch (err) {
        notificationStore.error(err.customMessage || 'Gagal menyimpan penyesuaian stok.');
    }
}

async function handleRefresh() {
    await inventoryStore.fetchInventory();
    if (!inventoryStore.error) {
        notificationStore.success('Data stok bahan baku berhasil disegarkan.');
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    Manajemen Stok Bahan Baku (Inventory)
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Pantau ketersediaan fisik stok bahan seafood, peringatan stok menipis, dan lakukan penyesuaian stok.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <router-link
                    to="/inventory/cost"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition-colors"
                >
                    <DollarSign class="w-4 h-4 text-indigo-600" />
                    <span>Valuasi Biaya (Sprint 07)</span>
                </router-link>

                <BaseButton
                    variant="primary"
                    size="sm"
                    @click="openAdjustModal(null)"
                >
                    <Plus class="w-4 h-4" />
                    <span>Penyesuaian Stok</span>
                </BaseButton>

                <BaseButton
                    variant="outline"
                    size="sm"
                    @click="handleRefresh"
                    :loading="inventoryStore.loading"
                    title="Segarkan Stok"
                >
                    <RefreshCw class="w-3.5 h-3.5" :class="[inventoryStore.loading ? 'animate-spin' : '']" />
                </BaseButton>
            </div>
        </div>

        <!-- 4 Summary Stock Cards -->
        <StockCard
            :summary="inventoryStore.summary"
            :loading="inventoryStore.loading"
        />

        <!-- Low Stock Alert Banner -->
        <LowStockAlert
            :items="inventoryStore.lowStockItems"
            @adjust="openAdjustModal"
        />

        <!-- Filters: Status Tabs & Search -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0 no-scrollbar">
                <button
                    v-for="tab in statusTabs"
                    :key="tab.value"
                    type="button"
                    @click="onStatusSelect(tab.value)"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all shrink-0 cursor-pointer"
                    :class="[
                        inventoryStore.filters.status === tab.value
                            ? 'bg-blue-600 text-white shadow-xs'
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200',
                    ]"
                >
                    {{ tab.label }}
                </button>
            </div>

            <div class="relative w-full sm:w-72">
                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                <input
                    type="text"
                    v-model="searchInput"
                    @input="onSearchInput"
                    placeholder="Cari nama bahan baku..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-3 py-2 text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white"
                />
            </div>
        </div>

        <!-- Error Alert -->
        <div
            v-if="inventoryStore.error"
            class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between gap-3 shadow-xs"
        >
            <div class="flex items-center gap-3">
                <AlertCircle class="w-5 h-5 text-rose-600 shrink-0" />
                <div>
                    <h4 class="text-sm font-bold">Gagal Mengambil Data Inventaris</h4>
                    <p class="text-xs text-rose-700 mt-0.5">{{ inventoryStore.error }}</p>
                </div>
            </div>
            <BaseButton
                variant="danger"
                size="sm"
                @click="inventoryStore.fetchInventory()"
                :loading="inventoryStore.loading"
            >
                Coba Lagi
            </BaseButton>
        </div>

        <!-- Inventory Table -->
        <InventoryTable
            :ingredients="inventoryStore.ingredients"
            :loading="inventoryStore.loading"
            @adjust="openAdjustModal"
        />

        <!-- Stock Adjustment Modal -->
        <StockAdjustmentModal
            v-model="isAdjustModalOpen"
            :ingredients="inventoryStore.ingredients"
            :initial-ingredient="targetIngredient"
            :loading="inventoryStore.adjusting"
            @submit="handleAdjustSubmit"
        />
    </div>
</template>

