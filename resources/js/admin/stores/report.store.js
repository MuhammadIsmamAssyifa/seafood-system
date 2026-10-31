import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import dayjs from 'dayjs';
import { reportService } from '../services/report.service';

export const useReportStore = defineStore('report', () => {
    const salesReport = ref(null);
    const loading = ref(false);
    const error = ref(null);

    // Filter state
    const selectedPreset = ref('this_month');
    const startDate = ref(dayjs().startOf('month').format('YYYY-MM-DD'));
    const endDate = ref(dayjs().format('YYYY-MM-DD'));

    const summary = computed(() => salesReport.value?.summary || {
        total_orders: 0,
        total_guests: 0,
        gross_sales: 0,
        total_discounts: 0,
        net_sales: 0,
        total_sales: 0,
        total_cogs: 0,
        gross_profit: 0,
        profit_margin: 0,
        average_order_value: 0,
        average_spend_per_guest: 0,
    });

    const comparison = computed(() => salesReport.value?.comparison || {
        previous_start_date: null,
        previous_end_date: null,
        previous_net_sales: 0,
        previous_orders_count: 0,
        sales_growth: 0,
        orders_growth: 0,
    });

    const dailyTrends = computed(() => salesReport.value?.daily_trends || []);
    const topMenus = computed(() => salesReport.value?.top_menus || []);

    const hasData = computed(() => {
        if (!salesReport.value) return false;
        return summary.value.total_orders > 0 || summary.value.net_sales > 0;
    });

    function setPreset(preset) {
        selectedPreset.value = preset;
        const now = dayjs();

        switch (preset) {
            case 'today':
                startDate.value = now.format('YYYY-MM-DD');
                endDate.value = now.format('YYYY-MM-DD');
                break;
            case 'yesterday':
                const yest = now.subtract(1, 'day');
                startDate.value = yest.format('YYYY-MM-DD');
                endDate.value = yest.format('YYYY-MM-DD');
                break;
            case 'this_week':
                startDate.value = now.startOf('week').format('YYYY-MM-DD');
                endDate.value = now.format('YYYY-MM-DD');
                break;
            case 'this_month':
                startDate.value = now.startOf('month').format('YYYY-MM-DD');
                endDate.value = now.format('YYYY-MM-DD');
                break;
            case 'last_month':
                const lastM = now.subtract(1, 'month');
                startDate.value = lastM.startOf('month').format('YYYY-MM-DD');
                endDate.value = lastM.endOf('month').format('YYYY-MM-DD');
                break;
            case 'custom':
                // retain custom values
                break;
        }

        return fetchSalesReport();
    }

    function setCustomRange(start, end) {
        selectedPreset.value = 'custom';
        startDate.value = start;
        endDate.value = end;
        return fetchSalesReport();
    }

    async function fetchSalesReport() {
        loading.value = true;
        error.value = null;

        try {
            const data = await reportService.getSalesReport({
                start_date: startDate.value,
                end_date: endDate.value,
            });
            salesReport.value = data;
        } catch (err) {
            error.value = err.customMessage || 'Gagal mengambil laporan penjualan. Silakan periksa koneksi dan coba lagi.';
        } finally {
            loading.value = false;
        }
    }

    return {
        salesReport,
        loading,
        error,
        selectedPreset,
        startDate,
        endDate,
        summary,
        comparison,
        dailyTrends,
        topMenus,
        hasData,
        setPreset,
        setCustomRange,
        fetchSalesReport,
    };
});

