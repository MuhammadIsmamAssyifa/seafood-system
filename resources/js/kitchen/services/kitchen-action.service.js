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
    /**
     * Mark all waiting items in a batch as cooking
     * @param {number|string} batchId
     */
    async prepareAll(batchId) {
        const response = await apiClient.patch(`/kitchen/batches/${batchId}/prepare-all`);
        return response.data;
    },

    /**
     * Mark all preparing items in a batch as ready
     * @param {number|string} batchId
     */
    async readyAll(batchId) {
        const response = await apiClient.patch(`/kitchen/batches/${batchId}/ready-all`);
        return response.data;
    },

    /**
     * Mark all ready items in a batch as served
     * @param {number|string} batchId
     */
    async serveAll(batchId) {
        const response = await apiClient.patch(`/kitchen/batches/${batchId}/serve-all`);
        return response.data;
    },

    /**
     * Report an item issue (out of stock / weight difference) to cashier
     * @param {number|string} itemId
     * @param {Object} data { type: 'cancel'|'quantity_change', reason: string, quantity?: number }
     */
    async reportItemIssue(itemId, data) {
        const response = await apiClient.post(`/order-items/${itemId}/changes`, data);
        return response.data;
    },
};

export default kitchenActionService;

