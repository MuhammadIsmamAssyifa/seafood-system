import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { dashboardService } from '../services/dashboard.service';

export const useDashboardStore = defineStore('dashboard', () => {
    const summary = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const selectedDate = ref(null);
    const lastFetched = ref(null);

    const salesMetrics = computed(() => summary.value?.sales || {
        today_revenue: 0,
        today_cogs: 0,
        today_gross_profit: 0,
        today_profit_margin: 0,
        average_transaction: 0,
    });

    const orderMetrics = computed(() => summary.value?.orders || {
        total_today: 0,
        completed_today: 0,
        active_orders: 0,
        guests_today: 0,
        status_breakdown: {
            draft: 0,
            submitted: 0,
            preparing: 0,
            ready: 0,
            completed: 0,
            cancelled: 0,
        },
    });

    const paymentMetrics = computed(() => summary.value?.payments || {
        today_cash: 0,
        today_transfer: 0,
        today_qris: 0,
        total_collected: 0,
    });

    const tableMetrics = computed(() => summary.value?.tables || {
        total_tables: 0,
        occupied_tables: 0,
        available_tables: 0,
        occupancy_rate: 0,
    });

    const bestMenus = computed(() => summary.value?.top_selling_menus || []);
    const recentOrders = computed(() => summary.value?.recent_orders || []);
    const inventoryAlerts = computed(() => summary.value?.inventory_alerts || {
        low_stock_count: 0,
        low_stock_items: [],
    });

    const hasData = computed(() => {
        if (!summary.value) return false;
        return (
            orderMetrics.value.total_today > 0 ||
            salesMetrics.value.today_revenue > 0 ||
            recentOrders.value.length > 0
        );
    });

    async function fetchDashboard(date = null, force = false) {
        // Simple caching if already fetched recently with same date
        if (!force && summary.value && selectedDate.value === date && lastFetched.value) {
            const ageMs = Date.now() - lastFetched.value;
            if (ageMs < 10000) return; // 10 seconds cache window
        }

        loading.value = true;
        error.value = null;
        selectedDate.value = date;

        try {
            const data = await dashboardService.getOverview(date);
            summary.value = data;
            lastFetched.value = Date.now();
        } catch (err) {
            error.value = err.customMessage || 'Tidak dapat memuat data dashboard. Silakan coba lagi.';
        } finally {
            loading.value = false;
        }
    }

    return {
        summary,
        loading,
        error,
        selectedDate,
        lastFetched,
        salesMetrics,
        orderMetrics,
        paymentMetrics,
        tableMetrics,
        bestMenus,
        recentOrders,
        inventoryAlerts,
        hasData,
        fetchDashboard,
    };
});

