import apiClient from './api';

export const dashboardService = {
    /**
     * Get dashboard summary and metrics
     * @param {string|null} date YYYY-MM-DD
     */
    async getOverview(date = null) {
        const params = date ? { date } : {};
        const response = await apiClient.get('/dashboard/overview', { params });
        return response.data.data || response.data;
    },
};

export default dashboardService;

