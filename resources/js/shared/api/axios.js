import axios from 'axios';

const apiBaseUrl =
    import.meta.env.VITE_API_URL ||
    (typeof window !== 'undefined' && window.__KDS_CONFIG__?.apiBaseUrl) ||
    '/api/v1';

const apiClient = axios.create({
    baseURL: apiBaseUrl,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    timeout: 15000,
});

// Request interceptor: Inject Bearer token
apiClient.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('kds_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

        // Attach CSRF token if meta tag exists
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

// Response interceptor: Global error handler
apiClient.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response ? error.response.status : null;

        if (status === 401) {
            // Unauthorized - clear token
            localStorage.removeItem('kds_token');
            localStorage.removeItem('kds_user');

            // If we are on kitchen routes, navigate to kitchen login
            if (typeof window !== 'undefined' && window.location.pathname.startsWith('/kitchen')) {
                if (window.location.pathname !== '/kitchen/login') {
                    window.location.href = '/kitchen/login';
                }
            }
        }

        // Standardize error message
        const message =
            error.response?.data?.message ||
            error.message ||
            'Terjadi kesalahan pada sistem.';

        error.customMessage = message;

        return Promise.reject(error);
    }
);

export default apiClient;

