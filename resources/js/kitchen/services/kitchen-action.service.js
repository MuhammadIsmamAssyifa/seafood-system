import apiClient from '@/shared/api/axios';

export const kitchenActionService = {
    /**
     * Accept order batch into cooking
     * @param {number|string} batchId
     */
    async acceptBatch(batchId) {
        const response = await apiClient.patch(`/kitchen/batches/${batchId}/accept`);
        return response.data;
    },

    /**
     * Mark an order item as being prepared (cooking)
     * @param {number|string} itemId
     */
    async prepareItem(itemId) {
        const response = await apiClient.patch(`/kitchen/items/${itemId}/prepare`);
        return response.data;
    },

    /**
     * Mark an order item as ready to serve
     * @param {number|string} itemId
     */
    async readyItem(itemId) {
        const response = await apiClient.patch(`/kitchen/items/${itemId}/ready`);
        return response.data;
    },

    /**
     * Mark an order item as served
     * @param {number|string} itemId
     */
    async serveItem(itemId) {
        const response = await apiClient.patch(`/kitchen/items/${itemId}/serve`);
        return response.data;
    },
};

export default kitchenActionService;

