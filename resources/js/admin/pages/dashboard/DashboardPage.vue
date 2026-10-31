<script setup>
import { ref, onMounted, computed } from 'vue';
import {
    RefreshCw,
    Calendar,
    AlertCircle,
    Store,
    CookingPot,
    TrendingUp,
} from 'lucide-vue-next';
import { useDashboardStore } from '../../stores/dashboard.store';
import { useAuthStore } from '../../stores/auth.store';
import { useNotificationStore } from '../../stores/notification.store';
import { formatDate } from '../../utils/formatter';

import SummaryCard from './components/SummaryCard.vue';
import OrderSummary from './components/OrderSummary.vue';
import SalesChart from './components/SalesChart.vue';
import BestMenuCard from './components/BestMenuCard.vue';
import RecentOrders from './components/RecentOrders.vue';
import QuickStatistics from './components/QuickStatistics.vue';
import BaseButton from '../../components/common/BaseButton.vue';
import BaseEmpty from '../../components/common/BaseEmpty.vue';

const dashboardStore = useDashboardStore();
const authStore = useAuthStore();
const notificationStore = useNotificationStore();

// Default to today
const today = new Date().toISOString().split('T')[0];
const selectedDate = ref(today);

onMounted(() => {
    loadData();
});

async function loadData(force = false) {
    try {
        await dashboardStore.fetchDashboard(selectedDate.value, force);
    } catch (err) {
        // Error is handled in store
    }
}

async function handleRefresh() {
    await loadData(true);
    if (!dashboardStore.error) {
        notificationStore.success('Data dashboard berhasil diperbarui.');
    }
}

function handleDateChange(e) {
    selectedDate.value = e.target.value;
    loadData(true);
}
</script>

<template>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    Dashboard Overview
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Ringkasan performa penjualan dan operasional restoran secara real-time.
                </p>
            </div>

            <!-- Controls: Date Picker & Refresh -->
            <div class="flex items-center gap-2.5">
                <div class="relative">
                    <input
                        type="date"
                        :value="selectedDate"
                        @change="handleDateChange"
                        class="bg-white border border-slate-200 text-xs text-slate-700 font-medium rounded-lg px-3 py-2 shadow-2xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:outline-none cursor-pointer"
                    />
                </div>

                <BaseButton
                    variant="outline"
                    size="sm"
                    @click="handleRefresh"
                    :loading="dashboardStore.loading"
                    title="Segarkan Data"
                >
                    <RefreshCw class="w-3.5 h-3.5" :class="[dashboardStore.loading ? 'animate-spin' : '']" />
                    <span class="hidden sm:inline">Segarkan</span>
                </BaseButton>
            </div>
        </div>

        <!-- Error State Banner -->
        <div
            v-if="dashboardStore.error"
            class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs"
        >
            <div class="flex items-center gap-3">
                <AlertCircle class="w-5 h-5 text-rose-600 shrink-0" />
                <div>
                    <h4 class="text-sm font-bold">Gagal Mengambil Data Dashboard</h4>
                    <p class="text-xs text-rose-700 mt-0.5">{{ dashboardStore.error }}</p>
                </div>
            </div>
            <BaseButton
                variant="danger"
                size="sm"
                @click="loadData(true)"
                :loading="dashboardStore.loading"
            >
                Coba Lagi
            </BaseButton>
        </div>

        <!-- 4 Key Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Total Penjualan -->
            <SummaryCard
                title="Penjualan Hari Ini"
                :value="dashboardStore.salesMetrics.today_revenue"
                type="sales"
                subtitle="Total omset dari pesanan selesai"
                :loading="dashboardStore.loading"
            />

            <!-- 2. Jumlah Transaksi -->
            <SummaryCard
                title="Total Pesanan"
                :value="dashboardStore.orderMetrics.total_today"
                type="orders"
                :subtitle="`${dashboardStore.orderMetrics.completed_today} selesai, ${dashboardStore.orderMetrics.active_orders} aktif`"
                :loading="dashboardStore.loading"
            />

            <!-- 3. Estimasi Gross Profit -->
            <SummaryCard
                title="Estimasi Gross Profit"
                :value="dashboardStore.salesMetrics.today_gross_profit"
                type="profit"
                :badge="`${dashboardStore.salesMetrics.today_profit_margin}% margin`"
                subtitle="Pendapatan dikurangi HPP (COGS)"
                :loading="dashboardStore.loading"
            />

            <!-- 4. Rata-rata Order -->
            <SummaryCard
                title="Rata-rata Transaksi"
                :value="dashboardStore.salesMetrics.average_transaction"
                type="avg"
                subtitle="Nilai belanja rata-rata per nota"
                :loading="dashboardStore.loading"
            />
        </div>

        <!-- Empty State Notice if completely fresh day with 0 transactions -->
        <div
            v-if="!dashboardStore.loading && !dashboardStore.hasData && !dashboardStore.error"
            class="bg-blue-50/60 border border-blue-200/80 rounded-2xl p-6 text-center"
        >
            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-3">
                <Store class="w-6 h-6" />
            </div>
            <h3 class="text-base font-bold text-slate-800">Belum Ada Transaksi Tercatat Hari Ini</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                Data pesanan, menu terlaris, dan rincian omset akan otomatis ditampilkan begitu kasir membuat pesanan pertama.
            </p>
        </div>

        <!-- Main Dashboard Split Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Order Summary, Financial Breakdown, Recent Orders -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Lifecycle & Guest Count -->
                <OrderSummary
                    :metrics="dashboardStore.orderMetrics"
                    :loading="dashboardStore.loading"
                />

                <!-- Revenue Breakdown & Payment Methods -->
                <SalesChart
                    :sales="dashboardStore.salesMetrics"
                    :payments="dashboardStore.paymentMetrics"
                    :loading="dashboardStore.loading"
                />

                <!-- Recent Orders Table -->
                <RecentOrders
                    :orders="dashboardStore.recentOrders"
                    :loading="dashboardStore.loading"
                />
            </div>

            <!-- Right 1 Col: Best Menus & Quick Operational Stats -->
            <div class="space-y-6">
                <!-- Top 5 Best Selling Menus -->
                <BestMenuCard
                    :menus="dashboardStore.bestMenus"
                    :loading="dashboardStore.loading"
                />

                <!-- Table Occupancy & Low Stock Alert -->
                <QuickStatistics
                    :tables="dashboardStore.tableMetrics"
                    :inventory-alerts="dashboardStore.inventoryAlerts"
                    :loading="dashboardStore.loading"
                />
            </div>
        </div>
    </div>
</template>

