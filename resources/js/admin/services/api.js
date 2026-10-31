import axios from 'axios';
import { storageService } from './storage.service';

const baseURL =
    (typeof window !== 'undefined' && window.__APP_CONFIG__?.apiBaseUrl) ||
    import.meta.env.VITE_API_URL ||
    '/api/v1';

const apiClient = axios.create({
    baseURL,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    timeout: 20000,
});

// Request Interceptor: Inject Bearer Token
apiClient.interceptors.request.use(
    (config) => {
        const token = storageService.getToken();
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

        if (typeof document !== 'undefined') {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                config.headers['X-CSRF-TOKEN'] = csrfToken;
            }
        }

        return config;
    },
    (error) => Promise.reject(error)
);

// Response Interceptor: Handle status codes & standard error formats
apiClient.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response ? error.response.status : null;

        if (status === 401) {
            storageService.clear();

            if (typeof window !== 'undefined' && window.location.pathname.startsWith('/admin')) {
                if (!window.location.pathname.includes('/login')) {
                    sessionStorage.setItem('flash_error', 'Sesi Anda telah berakhir. Silakan login kembali.');
                    window.location.href = '/admin/login';
                }
            }
        }

        // Standardize error message
        let errorMessage = 'Terjadi kesalahan pada sistem.';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.message === 'Network Error') {
            errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi Anda.';
        } else if (error.code === 'ECONNABORTED') {
            errorMessage = 'Koneksi ke server timeout. Silakan coba lagi.';
        }

        error.customMessage = errorMessage;
        return Promise.reject(error);
    }
);

export default apiClient;

