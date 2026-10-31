import apiClient from './api';

export const wasteService = {
    /**
     * Get waste summary metrics
     * @param {{ start_date?: string, end_date?: string }} params
     */
    async getWasteSummary(params = {}) {
        const response = await apiClient.get('/waste/summary', { params });
        return response.data;
    },

    /**
     * Get paginated waste records
     * @param {{ ingredient_id?: number, reason?: string, start_date?: string, end_date?: string, search?: string, page?: number, per_page?: number }} params
     */
    async getWasteRecords(params = {}) {
        const response = await apiClient.get('/waste', { params });
        return response.data;
    },

    /**
     * Get waste record detail by ID
     * @param {number|string} id
     */
    async getWasteDetail(id) {
        const response = await apiClient.get(`/waste/${id}`);
        return response.data.data || response.data;
    },

    /**
     * Record a new waste entry
     * @param {{ ingredient_id: number, quantity: number, reason: string, note?: string }} data
     */
    async createWaste(data) {
        const response = await apiClient.post('/waste', data);
        return response.data;
    },
};

export default wasteService;

