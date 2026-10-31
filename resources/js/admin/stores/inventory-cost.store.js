import { defineStore } from 'pinia';
import { ref } from 'vue';
import { inventoryCostService } from '../services/inventory-cost.service';

export const useInventoryCostStore = defineStore('inventoryCost', () => {
    const costSummary = ref({
        total_stock_value: 0,
        ingredient_count: 0,
        active_batches_count: 0,
        ingredients: [],
    });
    const selectedCostDetail = ref(null);
    const loading = ref(false);
    const detailLoading = ref(false);
    const error = ref(null);

    async function fetchCostSummary() {
        loading.value = true;
        error.value = null;

        try {
            const data = await inventoryCostService.getCostSummary();
            costSummary.value = data;
        } catch (err) {
            error.value = err.customMessage || 'Gagal memuat valuasi biaya persediaan.';
        } finally {
            loading.value = false;
        }
    }

    async function fetchIngredientCost(id) {
        detailLoading.value = true;
        error.value = null;
        selectedCostDetail.value = null;

        try {
            const data = await inventoryCostService.getIngredientCost(id);
            selectedCostDetail.value = data;
            return data;
        } catch (err) {
            error.value = err.customMessage || 'Gagal memuat rincian batch biaya bahan.';
            throw err;
        } finally {
            detailLoading.value = false;
        }
    }

    return {
        costSummary,
        selectedCostDetail,
        loading,
        detailLoading,
        error,
        fetchCostSummary,
        fetchIngredientCost,
    };
});

