import apiClient from './api';

export const outletService = {
    /**
     * Get paginated or full outlets list
     * @param {{ status?: string, search?: string, all?: boolean, page?: number, per_page?: number }} params
     */
    async getOutlets(params = {}) {
        const response = await apiClient.get('/outlets', { params });
        return response.data;
    },

    /**
     * Get single outlet detail
     * @param {number|string} id
     */
    async getDetail(id) {
        const response = await apiClient.get(`/outlets/${id}`);
        return response.data.data || response.data;
    },

    /**
     * Create new outlet
     * @param {{ name: string, code: string, address?: string, phone?: string, status?: string }} data
     */
    async createOutlet(data) {
        const response = await apiClient.post('/outlets', data);
        return response.data;
    },

    /**
     * Update outlet details
     * @param {number|string} id
     * @param {Object} data
     */
    async updateOutlet(id, data) {
        const response = await apiClient.put(`/outlets/${id}`, data);
        return response.data;
    },

    /**
     * Delete outlet
     * @param {number|string} id
     */
    async deleteOutlet(id) {
        const response = await apiClient.delete(`/outlets/${id}`);
        return response.data;
    },

    /**
     * Assign employees to outlet
     * @param {number|string} id
     * @param {Array<number>} userIds
     */
    async assignUsers(id, userIds) {
        const response = await apiClient.post(`/outlets/${id}/assign-users`, { user_ids: userIds });
        return response.data;
    },
};

export default outletService;

