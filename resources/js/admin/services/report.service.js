import apiClient from './api';

export const reportService = {
    /**
     * Get Sales Report for specified period
     * @param {{ start_date?: string, end_date?: string }} params
     */
    async getSalesReport(params = {}) {
        const response = await apiClient.get('/reports/sales', { params });
        return response.data.data || response.data;
    },

    /**
     * Get Payment Report for specified period
     * @param {{ start_date?: string, end_date?: string }} params
     */
    async getPaymentReport(params = {}) {
        const response = await apiClient.get('/reports/payments', { params });
        return response.data.data || response.data;
    },

    /**
     * Get Menu Sales Ranking Report
     * @param {{ start_date?: string, end_date?: string }} params
     */
    async getMenuSalesReport(params = {}) {
        const response = await apiClient.get('/reports/menu-sales', { params });
        return response.data.data || response.data;
    },
};

export default reportService;

