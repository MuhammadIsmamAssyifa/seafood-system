import apiClient from './api';

export const orderService = {
    /**
     * Get paginated list of orders
     * @param {{ status?: string, date?: string, search?: string, page?: number, per_page?: number }} params
     */
    async getOrders(params = {}) {
        const response = await apiClient.get('/orders', { params });
        return {
            data: response.data.data || [],
            meta: response.data.meta || {
                current_page: response.data.current_page || 1,
                last_page: response.data.last_page || 1,
                total: response.data.total || (response.data.data ? response.data.data.length : 0),
                per_page: response.data.per_page || 20,
            },
            links: response.data.links || {},
        };
    },

    /**
     * Get full details of a single order
     * @param {number|string} id
     */
    async getOrderDetail(id) {
        const response = await apiClient.get(`/orders/${id}`);
        return response.data.data || response.data;
    },

    /**
     * Get payments for an order
     * @param {number|string} id
     */
    async getOrderPayments(id) {
        const response = await apiClient.get(`/orders/${id}/payments`);
        return response.data.data || response.data;
    },
};

export default orderService;

