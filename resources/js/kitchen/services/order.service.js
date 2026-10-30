import apiClient from '@/shared/api/axios';

export const orderService = {
    /**
     * Fetch order detail by order ID or batch ID
     * @param {string|number} id
     */
    async getOrderDetail(id) {
        const response = await apiClient.get(`/kitchen/orders/${id}`);
        return response.data;
    },
};

export default orderService;

