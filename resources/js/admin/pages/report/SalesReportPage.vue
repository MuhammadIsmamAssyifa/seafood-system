<script setup>
import { onMounted } from 'vue';
import { RefreshCw, AlertCircle } from 'lucide-vue-next';
import { useReportStore } from '../../stores/report.store';
import { useNotificationStore } from '../../stores/notification.store';

import DateFilter from './components/DateFilter.vue';
import SalesSummaryCard from './components/SalesSummaryCard.vue';
import RevenueChart from './components/RevenueChart.vue';
import OrderStatistic from './components/OrderStatistic.vue';
import MenuPerformanceTable from './components/MenuPerformanceTable.vue';
import ProfitOverview from './components/ProfitOverview.vue';
import SalesComparison from './components/SalesComparison.vue';
import BaseButton from '../../components/common/BaseButton.vue';
import BaseEmpty from '../../components/common/BaseEmpty.vue';

const reportStore = useReportStore();
const notificationStore = useNotificationStore();

onMounted(() => {
    reportStore.fetchSalesReport();
});

async function handleRefresh() {
    await reportStore.fetchSalesReport();
    if (!reportStore.error) {
        notificationStore.success('Laporan penjualan berhasil diperbarui.');
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    Laporan Penjualan (Sales Reporting)
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    Analisis komprehensif performa pendapatan, HPP, margin laba, dan tren transaksi.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <BaseButton
                    variant="outline"
                    size="sm"
                    @click="handleRefresh"
                    :loading="reportStore.loading"
                    title="Segarkan Laporan"
                >
                    <RefreshCw class="w-3.5 h-3.5" :class="[reportStore.loading ? 'animate-spin' : '']" />
                    <span class="hidden sm:inline">Segarkan Data</span>
                </BaseButton>
            </div>
        </div>

        <!-- Filter Presets & Date Range -->
        <DateFilter />

        <!-- Error Alert -->
        <div
            v-if="reportStore.error"
            class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between gap-3 shadow-xs"
        >
            <div class="flex items-center gap-3">
                <AlertCircle class="w-5 h-5 text-rose-600 shrink-0" />
                <div>
                    <h4 class="text-sm font-bold">Gagal Mengambil Laporan Penjualan</h4>
                    <p class="text-xs text-rose-700 mt-0.5">{{ reportStore.error }}</p>
                </div>
            </div>
            <BaseButton
                variant="danger"
                size="sm"
                @click="reportStore.fetchSalesReport()"
                :loading="reportStore.loading"
            >
                Coba Lagi
            </BaseButton>
        </div>

        <!-- Sales Summary Metrics (4 Cards) -->
        <SalesSummaryCard
            :summary="reportStore.summary"
            :comparison="reportStore.comparison"
            :loading="reportStore.loading"
        />

        <!-- Empty State Notice if no transactions in range -->
        <div
            v-if="!reportStore.loading && !reportStore.hasData && !reportStore.error"
            class="py-6"
        >
            <BaseEmpty
                title="Tidak Ada Data Penjualan Pada Periode Ini"
                message="Silakan pilih rentang tanggal lain atau gunakan filter kustom di atas untuk melihat laporan historis."
            />
        </div>

        <!-- Main Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Revenue Chart & Menu Performance Table -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Daily Trend Chart -->
                <RevenueChart
                    :trends="reportStore.dailyTrends"
                    :loading="reportStore.loading"
                />

                <!-- Ranked Menu Performance Table -->
                <MenuPerformanceTable
                    :menus="reportStore.topMenus"
                    :loading="reportStore.loading"
                />
            </div>

            <!-- Right 1 Col: Profit Calculation, Guest Statistics & Comparison -->
            <div class="space-y-6">
                <!-- Profit & Margin Overview -->
                <ProfitOverview
                    :summary="reportStore.summary"
                    :loading="reportStore.loading"
                />

                <!-- Period-over-period Comparison -->
                <SalesComparison
                    :comparison="reportStore.comparison"
                    :current-sales="reportStore.summary.net_sales"
                    :current-orders="reportStore.summary.total_orders"
                    :loading="reportStore.loading"
                />

                <!-- Order & Guest Statistics -->
                <OrderStatistic
                    :summary="reportStore.summary"
                    :loading="reportStore.loading"
                />
            </div>
        </div>
    </div>
</template>

