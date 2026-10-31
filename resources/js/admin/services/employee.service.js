import apiClient from './api';

export const employeeService = {
    /**
     * Get paginated employees
     * @param {{ search?: string, role?: string, status?: string, outlet_id?: number, page?: number, per_page?: number }} params
     */
    async getEmployees(params = {}) {
        const response = await apiClient.get('/employees', { params });
        return response.data;
    },

    /**
     * Get single employee detail
     * @param {number|string} id
     */
    async getEmployeeDetail(id) {
        const response = await apiClient.get(`/employees/${id}`);
        return response.data.data || response.data;
    },

    /**
     * Create employee
     * @param {Object} data
     */
    async createEmployee(data) {
        const response = await apiClient.post('/employees', data);
        return response.data;
    },

    /**
     * Update employee
     * @param {number|string} id
     * @param {Object} data
     */
    async updateEmployee(id, data) {
        const response = await apiClient.put(`/employees/${id}`, data);
        return response.data;
    },

    /**
     * Update employee status (active / inactive)
     * @param {number|string} id
     * @param {string} status
     */
    async updateStatus(id, status) {
        const response = await apiClient.patch(`/employees/${id}/status`, { status });
        return response.data;
    },

    /**
     * Assign outlets to employee
     * @param {number|string} id
     * @param {number[]} outletIds
     */
    async assignOutlets(id, outletIds) {
        const response = await apiClient.patch(`/employees/${id}/outlets`, { outlet_ids: outletIds });
        return response.data;
    },

    /**
     * Get roles and outlets options for form dropdowns
     */
    async getMetaOptions() {
        const response = await apiClient.get('/employees/meta/options');
        return response.data;
    },
};

export default employeeService;

