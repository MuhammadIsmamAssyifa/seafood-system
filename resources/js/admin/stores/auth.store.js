import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { authService } from '../services/auth.service';
import { storageService } from '../services/storage.service';

export const useAuthStore = defineStore('auth', () => {
    const token = ref(storageService.getToken());
    const user = ref(storageService.getUser());
    const loading = ref(false);
    const error = ref(null);

    const isAuthenticated = computed(() => !!token.value);
    const userName = computed(() => user.value?.name || 'Administrator');
    const userRole = computed(() => user.value?.role || (user.value?.roles?.[0] ?? 'owner'));
    const isOwner = computed(() => userRole.value === 'owner');
    const userRestaurant = computed(() => user.value?.restaurant || null);
    const permissions = computed(() => user.value?.permissions || []);

    function hasPermission(permissionName) {
        if (!permissionName) return true;
        if (isOwner.value) return true;
        // Support pipe syntax e.g. "inventory.view|inventory.manage"
        if (permissionName.includes('|')) {
            const list = permissionName.split('|').map((p) => p.trim());
            return list.some((p) => permissions.value.includes(p));
        }
        return permissions.value.includes(permissionName);
    }

    function hasAnyPermission(permissionList = []) {
        if (!permissionList || permissionList.length === 0) return true;
        if (isOwner.value) return true;
        return permissionList.some((p) => hasPermission(p));
    }

    function hasRole(roleName) {
        if (!roleName) return true;
        if (isOwner.value && roleName === 'owner') return true;
        const currentRoles = user.value?.roles || [userRole.value];
        return currentRoles.includes(roleName);
    }

    function hasAnyRole(roleList = []) {
        if (!roleList || roleList.length === 0) return true;
        return roleList.some((r) => hasRole(r));
    }

    /**
     * Authenticate and initialize session
     */
    async function login(credentials, remember = false) {
        loading.value = true;
        error.value = null;

        try {
            const data = await authService.login(credentials);
            token.value = data.token;
            user.value = data.user;

            storageService.setToken(data.token, remember);
            storageService.setUser(data.user, remember);

            // Fetch fresh detailed profile (with permissions and relations)
            await fetchUser();

            return data;
        } catch (err) {
            error.value = err.customMessage || 'Login gagal. Periksa kembali kredensial Anda.';
            throw err;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Fetch user profile from /me
     */
    async function fetchUser() {
        if (!token.value) return null;

        try {
            const userData = await authService.fetchUser();
            user.value = userData;
            storageService.setUser(userData, storageService.isRemembered());
            return userData;
        } catch (err) {
            // If fetching profile fails with 401, clear session
            if (err.response?.status === 401) {
                clearSession();
            }
            throw err;
        }
    }

    /**
     * Terminate session on backend & locally
     */
    async function logout() {
        loading.value = true;
        try {
            if (token.value) {
                await authService.logout().catch(() => {});
            }
        } finally {
            clearSession();
            loading.value = false;
        }
    }

    /**
     * Local wipe
     */
    function clearSession() {
        token.value = null;
        user.value = null;
        error.value = null;
        storageService.clear();
    }

    return {
        token,
        user,
        loading,
        error,
        isAuthenticated,
        userName,
        userRole,
        isOwner,
        userRestaurant,
        permissions,
        hasPermission,
        hasAnyPermission,
        hasRole,
        hasAnyRole,
        login,
        fetchUser,
        logout,
        clearSession,
    };
});

