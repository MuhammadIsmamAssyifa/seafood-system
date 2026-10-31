import apiClient from './api';

export const auditService = {
    /**
     * Get paginated audit logs
     * @param {{ action?: string, module?: string, user_id?: number, search?: string, start_date?: string, end_date?: string, page?: number, per_page?: number }} params
     */
    async getLogs(params = {}) {
        const response = await apiClient.get('/audit-logs', { params });
        return response.data;
    },

    /**
     * Get audit log metrics and summary
     * @param {{ outlet_id?: number }} params
     */
    async getSummary(params = {}) {
        const response = await apiClient.get('/audit-logs/summary', { params });
        return response.data.data || response.data;
    },

    /**
     * Get single audit log detail with diff
     * @param {number|string} id
     */
    async getDetail(id) {
        const response = await apiClient.get(`/audit-logs/${id}`);
        return response.data.data || response.data;
    },
};

export default auditService;

