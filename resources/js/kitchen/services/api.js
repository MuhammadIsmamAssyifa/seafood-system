import apiClient from '@/shared/api/axios';

export const authApi = {
    staffLogin(credentials) {
        return apiClient.post('/staff-login', credentials);
    },
    logout() {
        return apiClient.post('/logout');
    },
    getCurrentUser() {
        return apiClient.get('/../user');
    },
};

export const kitchenApi = {
    getOrders(params = {}) {
        return apiClient.get('/kitchen/orders', { params });
    },
    acceptBatch(batchId) {
        return apiClient.patch(`/kitchen/batches/${batchId}/accept`);
    },
    prepareItem(itemId) {
        return apiClient.patch(`/kitchen/items/${itemId}/prepare`);
    },
    readyItem(itemId) {
        return apiClient.patch(`/kitchen/items/${itemId}/ready`);
    },
    serveItem(itemId) {
        return apiClient.patch(`/kitchen/items/${itemId}/serve`);
    },
    getCatalogStations() {
        return apiClient.get('/catalog/stations');
    },
};

export default {
    auth: authApi,
    kitchen: kitchenApi,
};

