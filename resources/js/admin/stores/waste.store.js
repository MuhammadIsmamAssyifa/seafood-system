import { defineStore } from 'pinia';
import { ref } from 'vue';
import { wasteService } from '../services/waste.service';

export const useWasteStore = defineStore('waste', () => {
    const records = ref([]);
    const summary = ref({
        total_cost: 0,
        total_quantity: 0,
        total_records: 0,
        waste_percentage: 0,
        inventory_value: 0,
        most_wasted_ingredient: null,
        top_wasted_ingredients: [],
        reason_breakdown: [],
    });
    const selectedRecord = ref(null);
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
    });

    const loading = ref(false);
    const summaryLoading = ref(false);
    const detailLoading = ref(false);
    const submitting = ref(false);
    const error = ref(null);

    const filters = ref({
        ingredient_id: '',
        reason: '',
        start_date: '',
        end_date: '',
        search: '',
        page: 1,
    });

    async function fetchSummary() {
        summaryLoading.value = true;
        try {
            const params = {};
            if (filters.value.start_date) params.start_date = filters.value.start_date;
            if (filters.value.end_date) params.end_date = filters.value.end_date;

            const res = await wasteService.getWasteSummary(params);
            summary.value = res;
        } catch (err) {
            console.error('Failed to load waste summary', err);
        } finally {
            summaryLoading.value = false;
        }
    }

    async function fetchRecords(page = 1) {
        loading.value = true;
        error.value = null;
        filters.value.page = page;

        try {
            const params = {
                page,
                per_page: pagination.value.per_page,
            };
            if (filters.value.ingredient_id) params.ingredient_id = filters.value.ingredient_id;
            if (filters.value.reason) params.reason = filters.value.reason;
            if (filters.value.start_date) params.start_date = filters.value.start_date;
            if (filters.value.end_date) params.end_date = filters.value.end_date;
            if (filters.value.search) params.search = filters.value.search.trim();

            const res = await wasteService.getWasteRecords(params);
            records.value = res.data || [];
            if (res.meta) {
                pagination.value = {
                    current_page: res.meta.current_page,
                    last_page: res.meta.last_page,
                    per_page: res.meta.per_page,
                    total: res.meta.total,
                };
            }
        } catch (err) {
            error.value = err.customMessage || 'Gagal memuat riwayat kehilangan bahan (waste).';
        } finally {
            loading.value = false;
        }
    }

    async function fetchDetail(id) {
        detailLoading.value = true;
        error.value = null;
        try {
            selectedRecord.value = await wasteService.getWasteDetail(id);
        } catch (err) {
            error.value = err.customMessage || 'Gagal memuat detail data waste.';
        } finally {
            detailLoading.value = false;
        }
    }

    async function recordWaste(payload) {
        submitting.value = true;
        error.value = null;
        try {
            const res = await wasteService.createWaste(payload);
            await Promise.all([fetchSummary(), fetchRecords(1)]);
            return res;
        } catch (err) {
            throw err;
        } finally {
            submitting.value = false;
        }
    }

    function setFilter(key, value) {
        filters.value[key] = value;
    }

    function resetFilters() {
        filters.value = {
            ingredient_id: '',
            reason: '',
            start_date: '',
            end_date: '',
            search: '',
            page: 1,
        };
    }

    return {
        records,
        summary,
        selectedRecord,
        pagination,
        loading,
        summaryLoading,
        detailLoading,
        submitting,
        error,
        filters,
        fetchSummary,
        fetchRecords,
        fetchDetail,
        recordWaste,
        setFilter,
        resetFilters,
    };
});

export default useWasteStore;

