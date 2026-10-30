import apiClient from '@/shared/api/axios';

export const authService = {
    /**
     * Standard email/username & password login
     * @param {{ email?: string, username?: string, password: string, device_name?: string }} credentials
     */
    async login(credentials) {
        const payload = {
            device_name: credentials.device_name || 'kds-screen',
            password: credentials.password,
            ...(credentials.email ? { email: credentials.email } : {}),
            ...(credentials.username ? { username: credentials.username } : {}),
        };
        const response = await apiClient.post('/login', payload);
        return response.data;
    },

    /**
     * Staff PIN login
     * @param {{ username: string, pin: string, device_name?: string }} credentials
     */
    async staffLogin(credentials) {
        const payload = {
            username: credentials.username,
            pin: credentials.pin,
            device_name: credentials.device_name || 'kds-screen',
        };
        const response = await apiClient.post('/staff-login', payload);
        return response.data;
    },

    /**
     * Fetch authenticated staff profile
     * Calls GET /api/v1/me (or fallback to /user)
     */
    async getProfile() {
        try {
            const response = await apiClient.get('/me');
            return response.data;
        } catch {
            const fallbackResponse = await apiClient.get('/../user');
            return fallbackResponse.data;
        }
    },

    /**
     * Logout and invalidate Sanctum token
     */
    async logout() {
        return apiClient.post('/logout');
    },
};

export default authService;

