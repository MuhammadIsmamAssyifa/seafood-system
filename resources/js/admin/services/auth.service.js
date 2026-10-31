import apiClient from './api';

export const authService = {
    /**
     * Submit login credentials
     * @param {{ username?: string, email?: string, password: string, device_name?: string }} credentials
     */
    async login(credentials) {
        const payload = {
            ...credentials,
            device_name: credentials.device_name || 'Admin-Web-Dashboard',
        };
        const response = await apiClient.post('/login', payload);
        return response.data;
    },

    /**
     * Get authenticated user profile & permissions
     */
    async fetchUser() {
        const response = await apiClient.get('/me');
        return response.data.data || response.data;
    },

    /**
     * Revoke current token
     */
    async logout() {
        const response = await apiClient.post('/logout');
        return response.data;
    },

    /**
     * Revoke all tokens across devices
     */
    async logoutAll() {
        const response = await apiClient.post('/logout-all');
        return response.data;
    },
};

export default authService;

