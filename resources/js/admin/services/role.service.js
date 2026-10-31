import apiClient from './api';

export const roleService = {
    /**
     * Get all roles with permission and user counts
     */
    async getRoles() {
        const response = await apiClient.get('/roles');
        return response.data.data || response.data;
    },

    /**
     * Get all available permissions grouped by category
     */
    async getPermissions() {
        const response = await apiClient.get('/roles/permissions');
        return response.data;
    },

    /**
     * Get permission matrix
     */
    async getPermissionMatrix() {
        const response = await apiClient.get('/roles/matrix');
        return response.data;
    },

    /**
     * Create new role
     * @param {{ name: string, permissions: string[] }} data
     */
    async createRole(data) {
        const response = await apiClient.post('/roles', data);
        return response.data;
    },

    /**
     * Update role permissions
     * @param {number|string} id
     * @param {{ permissions: string[] }} data
     */
    async updateRole(id, data) {
        const response = await apiClient.put(`/roles/${id}`, data);
        return response.data;
    },
};

export default roleService;

