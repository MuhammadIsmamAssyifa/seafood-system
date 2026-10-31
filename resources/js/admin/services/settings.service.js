import apiClient from './api';

export const settingsService = {
    /**
     * Get grouped system settings
     * @param {{ outlet_id?: number }} params
     */
    async getSettings(params = {}) {
        const response = await apiClient.get('/settings', { params });
        return response.data.data || response.data;
    },

    /**
     * Update system settings
     * @param {Object} data
     */
    async updateSettings(data) {
        const response = await apiClient.put('/settings', data);
        return response.data;
    },
};

export default settingsService;

