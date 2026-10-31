import { defineStore } from 'pinia';
import { ref } from 'vue';
import { auditService } from '../services/audit.service';

export const useAuditStore = defineStore('audit', () => {
    const logs = ref([]);
    const summary = ref({
        total_logs: 0,
        today_logs: 0,
        unique_users: 0,
        top_actions: {},
    });
    const selectedLog = ref(null);
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
    });

    const loading = ref(false);
    const summaryLoading = ref(false);
    const detailLoading = ref(false);
    const error = ref(null);

    const filters = ref({
        action: '',
        module: '',
        user_id: '',
        search: '',
        start_date: '',
        end_date: '',
        page: 1,
    });

    async function fetchSummary() {
        summaryLoading.value = true;
        try {
            const data = await auditService.getSummary();
            summary.value = data;
        } catch (err) {
            console.error('Failed to load audit summary', err);
        } finally {
            summaryLoading.value = false;
        }
    }

    async function fetchLogs(page = 1) {
        loading.value = true;
        error.value = null;
        filters.value.page = page;

        try {
            const params = {
                page,
                per_page: pagination.value.per_page,
            };

            if (filters.value.action) params.action = filters.value.action;
            if (filters.value.module) params.module = filters.value.module;
            if (filters.value.user_id) params.user_id = filters.value.user_id;
            if (filters.value.search) params.search = filters.value.search;
            if (filters.value.start_date) params.start_date = filters.value.start_date;
            if (filters.value.end_date) params.end_date = filters.value.end_date;

            const res = await auditService.getLogs(params);
            logs.value = res.data || [];

            if (res.meta) {
                pagination.value = {
                    current_page: res.meta.current_page,
                    last_page: res.meta.last_page,
                    per_page: res.meta.per_page,
                    total: res.meta.total,
                };
            }
        } catch (err) {
            console.error('Failed to load audit logs', err);
            error.value = err.response?.data?.message || 'Gagal memuat catatan audit log.';
        } finally {
            loading.value = false;
        }
    }

    async function fetchDetail(id) {
        detailLoading.value = true;
        try {
            const data = await auditService.getDetail(id);
            selectedLog.value = data;
            return data;
        } catch (err) {
            console.error('Failed to load audit detail', err);
            throw err;
        } finally {
            detailLoading.value = false;
        }
    }

    function resetFilters() {
        filters.value = {
            action: '',
            module: '',
            user_id: '',
            search: '',
            start_date: '',
            end_date: '',
            page: 1,
        };
        fetchLogs(1);
    }

    function setDatePreset(preset) {
        const today = new Date();
        const formatDate = (d) => d.toISOString().split('T')[0];

        if (preset === 'today') {
            const dateStr = formatDate(today);
            filters.value.start_date = dateStr;
            filters.value.end_date = dateStr;
        } else if (preset === 'week') {
            const start = new Date(today);
            start.setDate(today.getDate() - 7);
            filters.value.start_date = formatDate(start);
            filters.value.end_date = formatDate(today);
        } else if (preset === 'month') {
            const start = new Date(today);
            start.setDate(today.getDate() - 30);
            filters.value.start_date = formatDate(start);
            filters.value.end_date = formatDate(today);
        } else {
            filters.value.start_date = '';
            filters.value.end_date = '';
        }
        fetchLogs(1);
    }

    return {
        logs,
        summary,
        selectedLog,
        pagination,
        loading,
        summaryLoading,
        detailLoading,
        error,
        filters,
        fetchSummary,
        fetchLogs,
        fetchDetail,
        resetFilters,
        setDatePreset,
    };
});

