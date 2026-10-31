import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { inventoryService } from '../services/inventory.service';

export const useInventoryStore = defineStore('inventory', () => {
    const ingredients = ref([]);
    const summary = ref({
        total_ingredients: 0,
        available_count: 0,
        low_stock_count: 0,
        out_of_stock_count: 0,
        total_stock_quantity: 0,
    });
    const selectedIngredient = ref(null);
    const movements = ref([]);
    const movementsMeta = ref({ current_page: 1, last_page: 1, total: 0 });

    const loading = ref(false);
    const detailLoading = ref(false);
    const adjusting = ref(false);
    const error = ref(null);

    const filters = ref({
        status: '',
        search: '',
    });

    const lowStockItems = computed(() => {
        return ingredients.value.filter((i) => i.status === 'low_stock' || i.status === 'out_of_stock');
    });

    async function fetchInventory() {
        loading.value = true;
        error.value = null;

        try {
            const params = {};
            if (filters.value.status) params.status = filters.value.status;
            if (filters.value.search) params.search = filters.value.search.trim();

            const result = await inventoryService.getIngredients(params);
            ingredients.value = result.data || [];
            if (result.summary) {
                summary.value = result.summary;
            }
        } catch (err) {
            error.value = err.customMessage || 'Gagal memuat data inventaris bahan baku.';
        } finally {
            loading.value = false;
        }
    }

    async function fetchIngredientDetail(id) {
        detailLoading.value = true;
        error.value = null;
        selectedIngredient.value = null;

        try {
            const data = await inventoryService.getIngredientDetail(id);
            selectedIngredient.value = data;
            return data;
        } catch (err) {
            error.value = err.customMessage || 'Data bahan baku tidak ditemukan.';
            throw err;
        } finally {
            detailLoading.value = false;
        }
    }

    async function adjustStock(payload) {
        adjusting.value = true;
        try {
            const result = await inventoryService.adjustStock(payload);
            // Refresh list and detail if active
            await fetchInventory();
            if (selectedIngredient.value && selectedIngredient.value.id === payload.ingredient_id) {
                await fetchIngredientDetail(payload.ingredient_id);
            }
            return result;
        } finally {
            adjusting.value = false;
        }
    }

    async function fetchMovements(params = {}) {
        try {
            const result = await inventoryService.getStockMovements(params);
            movements.value = result.data || [];
            movementsMeta.value = result.meta || { current_page: 1, last_page: 1, total: 0 };
        } catch (err) {
            // non-blocking
        }
    }

    return {
        ingredients,
        summary,
        selectedIngredient,
        movements,
        movementsMeta,
        loading,
        detailLoading,
        adjusting,
        error,
        filters,
        lowStockItems,
        fetchInventory,
        fetchIngredientDetail,
        adjustStock,
        fetchMovements,
    };
});

