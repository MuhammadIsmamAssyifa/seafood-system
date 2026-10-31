import apiClient from './api';

export const inventoryCostService = {
    /**
     * Get overall inventory valuation & ingredient cost summary
     */
    async getCostSummary() {
        const response = await apiClient.get('/inventory/cost');
        return response.data.data || response.data;
    },

    /**
     * Get detailed batch cost breakdown for specific ingredient
     * @param {number|string} ingredientId
     */
    async getIngredientCost(ingredientId) {
        const response = await apiClient.get(`/inventory/cost/${ingredientId}`);
        return response.data.data || response.data;
    },
};

export default inventoryCostService;

