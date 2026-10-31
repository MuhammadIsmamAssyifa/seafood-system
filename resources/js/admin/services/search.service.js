import api from './api';

export const searchService = {
    /**
     * Search across system models
     * @param {string} query
     * @param {number|null} outletId
     */
    async search(query, outletId = null) {
        const params = { q: query };
        if (outletId) {
            params.outlet_id = outletId;
        }
        const response = await api.get('/search', { params });
        return response.data?.data || { total_results: 0, results: {} };
    },
};

