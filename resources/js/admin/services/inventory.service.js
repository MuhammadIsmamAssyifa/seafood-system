import apiClient from './api';

export const inventoryService = {
    /**
     * Get ingredients list with stock status
     * @param {{ status?: string, search?: string }} params
     */
    async getIngredients(params = {}) {
        const response = await apiClient.get('/inventory', { params });
        return response.data;
    },

    /**
     * Get single ingredient detail with batches and movements
     * @param {number|string} id
     */
    async getIngredientDetail(id) {
        const response = await apiClient.get(`/inventory/${id}`);
        return response.data.data || response.data;
    },

    /**
     * Submit manual stock adjustment (in/out)
     * @param {{ ingredient_id: number, type: string, quantity: number, cost_per_unit?: number, note?: string }} data
     */
    async adjustStock(data) {
        const response = await apiClient.post('/inventory/adjustments', data);
        return response.data;
    },

    /**
     * Get stock movements log with pagination
     * @param {{ ingredient_id?: number, type?: string, page?: number, per_page?: number }} params
     */
    async getStockMovements(params = {}) {
        const response = await apiClient.get('/inventory/movements', { params });
        return response.data;
    },
};

export default inventoryService;

