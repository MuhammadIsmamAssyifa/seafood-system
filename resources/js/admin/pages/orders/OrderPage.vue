<script setup>
import { onMounted } from 'vue';
import { RefreshCw, ShoppingCart, AlertCircle } from 'lucide-vue-next';
import { useOrderStore } from '../../stores/order.store';
import { useNotificationStore } from '../../stores/notification.store';

import OrderFilter from './components/OrderFilter.vue';
import OrderTable from './components/OrderTable.vue';
import BaseButton from '../../components/common/BaseButton.vue';

const orderStore = useOrderStore();
const notificationStore = useNotificationStore();

onMounted(() => {
    orderStore.fetchOrders(1);
});

async function handleRefresh() {
    await orderStore.fetchOrders(orderStore.pagination.current_page);
    if (!orderStore.error) {
        notificationStore.success('Daftar pesanan berhasil disegarkan.');
    }
}

function handlePageChange(page) {
    orderStore.fetchOrders(page);
}
</script>

<template>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    Manajemen & Monitoring Pesanan (Orders)
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Pantau seluruh transaksi aktif, riwayat order selesai, dan status pembayaran pelanggan secara real-time.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <BaseButton
                    variant="outline"
                    size="sm"
                    @click="handleRefresh"
                    :loading="orderStore.loading"
                    title="Segarkan Data Pesanan"
                >
                    <RefreshCw class="w-3.5 h-3.5" :class="[orderStore.loading ? 'animate-spin' : '']" />
                    <span class="hidden sm:inline">Segarkan Data</span>
                </BaseButton>
            </div>
        </div>

        <!-- Filter Component -->
        <OrderFilter />

        <!-- Error Alert -->
        <div
            v-if="orderStore.error"
            class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between gap-3 shadow-xs"
        >
            <div class="flex items-center gap-3">
                <AlertCircle class="w-5 h-5 text-rose-600 shrink-0" />
                <div>
                    <h4 class="text-sm font-bold">Gagal Mengambil Data Pesanan</h4>
                    <p class="text-xs text-rose-700 mt-0.5">{{ orderStore.error }}</p>
                </div>
            </div>
            <BaseButton
                variant="danger"
                size="sm"
                @click="orderStore.fetchOrders(1)"
                :loading="orderStore.loading"
            >
                Coba Lagi
            </BaseButton>
        </div>

        <!-- Orders Table & Pagination -->
        <OrderTable
            :orders="orderStore.orders"
            :pagination="orderStore.pagination"
            :loading="orderStore.loading"
            @page-change="handlePageChange"
        />
    </div>
</template>

