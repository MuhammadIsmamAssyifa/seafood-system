import apiClient from './api';

export const recipeService = {
    /**
     * Get paginated recipes list
     * @param {{ outlet_id?: number, search?: string, page?: number, per_page?: number }} params
     */
    async getRecipes(params = {}) {
        const response = await apiClient.get('/recipes', { params });
        return response.data;
    },

    /**
     * Get single recipe detail with cost analysis
     * @param {number|string} id
     */
    async getDetail(id) {
        const response = await apiClient.get(`/recipes/${id}`);
        return response.data.data || response.data;
    },

    /**
     * Create new recipe
     * @param {{ menu_variant_id: number, name?: string, description?: string, items: Array<{ ingredient_id: number, quantity: number, unit: string }> }} data
     */
    async createRecipe(data) {
        const response = await apiClient.post('/recipes', data);
        return response.data;
    },

    /**
     * Update existing recipe
     * @param {number|string} id
     * @param {Object} data
     */
    async updateRecipe(id, data) {
        const response = await apiClient.put(`/recipes/${id}`, data);
        return response.data;
    },

    /**
     * Delete recipe
     * @param {number|string} id
     */
    async deleteRecipe(id) {
        const response = await apiClient.delete(`/recipes/${id}`);
        return response.data;
    },

    /**
     * Recalculate cost for recipe
     * @param {number|string} id
     */
    async calculateCost(id) {
        const response = await apiClient.get(`/recipes/${id}/calculate`);
        return response.data.data || response.data;
    },

    /**
     * Get options for dropdowns (variants & ingredients)
     * @param {{ outlet_id?: number }} params
     */
    async getOptions(params = {}) {
        const response = await apiClient.get('/recipes/options', { params });
        return response.data.data || response.data;
    },
};

export default recipeService;

