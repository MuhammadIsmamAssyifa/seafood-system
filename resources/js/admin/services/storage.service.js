const TOKEN_KEY = 'admin_token';
const USER_KEY = 'admin_user';
const REMEMBER_KEY = 'admin_remember';

export const storageService = {
    getToken() {
        return localStorage.getItem(TOKEN_KEY) || sessionStorage.getItem(TOKEN_KEY) || null;
    },

    setToken(token, remember = false) {
        if (!token) return;
        if (remember) {
            localStorage.setItem(TOKEN_KEY, token);
            localStorage.setItem(REMEMBER_KEY, 'true');
            sessionStorage.removeItem(TOKEN_KEY);
        } else {
            sessionStorage.setItem(TOKEN_KEY, token);
            localStorage.removeItem(TOKEN_KEY);
            localStorage.removeItem(REMEMBER_KEY);
        }
    },

    getUser() {
        const raw = localStorage.getItem(USER_KEY) || sessionStorage.getItem(USER_KEY);
        if (!raw) return null;
        try {
            return JSON.parse(raw);
        } catch {
            return null;
        }
    },

    setUser(user, remember = false) {
        if (!user) return;
        const serialized = JSON.stringify(user);
        if (remember || localStorage.getItem(REMEMBER_KEY) === 'true') {
            localStorage.setItem(USER_KEY, serialized);
        } else {
            sessionStorage.setItem(USER_KEY, serialized);
        }
    },

    clear() {
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(USER_KEY);
        localStorage.removeItem(REMEMBER_KEY);
        sessionStorage.removeItem(TOKEN_KEY);
        sessionStorage.removeItem(USER_KEY);
    },

    isRemembered() {
        return localStorage.getItem(REMEMBER_KEY) === 'true';
    },
};

export default storageService;

