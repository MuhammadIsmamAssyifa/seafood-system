import { defineStore } from 'pinia';
import { ref } from 'vue';
import { recipeService } from '../services/recipe.service';

export const useRecipeStore = defineStore('recipe', () => {
    const recipes = ref([]);
    const selectedRecipe = ref(null);
    const options = ref({
        variants: [],
        ingredients: [],
    });
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
    });

    const loading = ref(false);
    const detailLoading = ref(false);
    const saving = ref(false);
    const error = ref(null);

    const filters = ref({
        search: '',
        outlet_id: '',
        page: 1,
    });

    async function fetchRecipes(page = 1) {
        loading.value = true;
        error.value = null;
        filters.value.page = page;

        try {
            const params = {
                page,
                per_page: pagination.value.per_page,
            };

            if (filters.value.search) params.search = filters.value.search;
            if (filters.value.outlet_id) params.outlet_id = filters.value.outlet_id;

            const res = await recipeService.getRecipes(params);
            recipes.value = res.data || [];

            if (res.meta) {
                pagination.value = {
                    current_page: res.meta.current_page,
                    last_page: res.meta.last_page,
                    per_page: res.meta.per_page,
                    total: res.meta.total,
                };
            }
        } catch (err) {
            console.error('Failed to load recipes', err);
            error.value = err.response?.data?.message || 'Gagal memuat resep menu.';
        } finally {
            loading.value = false;
        }
    }

    async function fetchDetail(id) {
        detailLoading.value = true;
        error.value = null;
        try {
            const data = await recipeService.getDetail(id);
            selectedRecipe.value = data;
            return data;
        } catch (err) {
            console.error('Failed to load recipe detail', err);
            error.value = err.response?.data?.message || 'Gagal memuat rincian resep.';
            throw err;
        } finally {
            detailLoading.value = false;
        }
    }

    async function fetchOptions(outletId = null) {
        try {
            const data = await recipeService.getOptions({ outlet_id: outletId });
            options.value = data;
        } catch (err) {
            console.error('Failed to load recipe options', err);
        }
    }

    async function createRecipe(data) {
        saving.value = true;
        error.value = null;
        try {
            const res = await recipeService.createRecipe(data);
            await fetchRecipes(1);
            return res;
        } catch (err) {
            console.error('Failed to create recipe', err);
            error.value = err.response?.data?.message || 'Gagal membuat resep.';
            throw err;
        } finally {
            saving.value = false;
        }
    }

    async function updateRecipe(id, data) {
        saving.value = true;
        error.value = null;
        try {
            const res = await recipeService.updateRecipe(id, data);
            await fetchRecipes(pagination.value.current_page);
            if (selectedRecipe.value && selectedRecipe.value.id === id) {
                selectedRecipe.value = res.data || res;
            }
            return res;
        } catch (err) {
            console.error('Failed to update recipe', err);
            error.value = err.response?.data?.message || 'Gagal memperbarui resep.';
            throw err;
        } finally {
            saving.value = false;
        }
    }

    async function deleteRecipe(id) {
        saving.value = true;
        error.value = null;
        try {
            const res = await recipeService.deleteRecipe(id);
            await fetchRecipes(pagination.value.current_page);
            return res;
        } catch (err) {
            console.error('Failed to delete recipe', err);
            error.value = err.response?.data?.message || 'Gagal menghapus resep.';
            throw err;
        } finally {
            saving.value = false;
        }
    }

    return {
        recipes,
        selectedRecipe,
        options,
        pagination,
        loading,
        detailLoading,
        saving,
        error,
        filters,
        fetchRecipes,
        fetchDetail,
        fetchOptions,
        createRecipe,
        updateRecipe,
        deleteRecipe,
    };
});

