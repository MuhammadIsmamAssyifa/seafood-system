import apiClient from '@/shared/api/axios';

export const kitchenService = {
    /**
     * Fetch kitchen queue orders/batches
     * @param {Object} params - optional query parameters (e.g. { status: 'waiting' })
     */
    async getOrders(params = {}) {
        const response = await apiClient.get('/kitchen/orders', { params });
        return response.data;
    },

    /**
     * Fetch kitchen order detail
     */
    async getOrderDetail(id) {
        const response = await apiClient.get(`/kitchen/orders/${id}`);
        return response.data;
    },

    /**
     * Accept batch into cooking
     */
    async acceptBatch(batchId) {
        const response = await apiClient.patch(`/kitchen/batches/${batchId}/accept`);
        return response.data;
    },

    /**
     * Mark item as preparing
     */
    async prepareItem(itemId) {
        const response = await apiClient.patch(`/kitchen/items/${itemId}/prepare`);
        return response.data;
    },

    /**
     * Mark item as ready
     */
    async readyItem(itemId) {
        const response = await apiClient.patch(`/kitchen/items/${itemId}/ready`);
        return response.data;
    },

    /**
     * Mark item as served
     */
    async serveItem(itemId) {
        const response = await apiClient.patch(`/kitchen/items/${itemId}/serve`);
        return response.data;
    },

    /**
     * Fetch catalog stations
     */
    async getStations() {
        const response = await apiClient.get('/catalog/stations');
        return response.data;
    },
};

export default kitchenService;

