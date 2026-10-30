import { defineStore } from 'pinia';
import authService from '@/kitchen/services/auth.service';

export const useAuthStore = defineStore('kds-auth', {
    state: () => {
        let initialUser = null;
        try {
            const rawUser = localStorage.getItem('kds_user');
            if (rawUser) {
                initialUser = JSON.parse(rawUser);
            }
        } catch {
            initialUser = null;
        }

        const initialToken = localStorage.getItem('kds_token') || null;

        return {
            user: initialUser,
            token: initialToken,
            authenticated: !!initialToken,
            loading: false,
            error: null,
            selectedStation: localStorage.getItem('kds_station') || 'all',
        };
    },

    getters: {
        isAuthenticated: (state) => state.authenticated && !!state.token,
        userName: (state) => state.user?.name || 'Staff Dapur',
        userRole: (state) => {
            if (!state.user) return '';
            if (state.user.role) return state.user.role;
            if (Array.isArray(state.user.roles) && state.user.roles.length > 0) {
                return state.user.roles[0];
            }
            return 'kitchen';
        },
        hasKitchenAccess: (state) => {
            if (!state.user) return false;

            // Check permissions array if available
            const permissions = Array.isArray(state.user.permissions) ? state.user.permissions : [];
            if (permissions.includes('kitchen.view') || permissions.includes('kitchen.update')) {
                return true;
            }

            // Check roles
            const roles = [];
            if (state.user.role) roles.push(state.user.role.toLowerCase());
            if (Array.isArray(state.user.roles)) {
                roles.push(...state.user.roles.map((r) => (typeof r === 'string' ? r.toLowerCase() : r.name?.toLowerCase())));
            }

            const allowedRoles = ['kitchen', 'kitchen_head', 'kitchen_staff', 'chef', 'head_chef', 'admin', 'owner'];
            return roles.some((r) => allowedRoles.includes(r));
        },
    },

    actions: {
        setToken(token) {
            this.token = token;
            this.authenticated = !!token;
            if (token) {
                localStorage.setItem('kds_token', token);
            } else {
                localStorage.removeItem('kds_token');
            }
        },

        setUser(user) {
            this.user = user;
            if (user) {
                localStorage.setItem('kds_user', JSON.stringify(user));
            } else {
                localStorage.removeItem('kds_user');
            }
        },

        setStation(stationId) {
            this.selectedStation = stationId;
            localStorage.setItem('kds_station', stationId);
        },

        /**
         * Verify kitchen role after successful token generation
         */
        verifyKitchenAuthorization(user) {
            this.setUser(user);
            if (!this.hasKitchenAccess) {
                this.logout();
                throw new Error('Anda tidak memiliki izin akses ke Kitchen Display System.');
            }
        },

        /**
         * Login using email/username and password
         */
        async login(credentials) {
            this.loading = true;
            this.error = null;
            try {
                const data = await authService.login(credentials);
                const payload = data.data || data;
                const token = payload.token || payload.access_token;
                const user = payload.user;

                if (!token) {
                    throw new Error('Token otentikasi tidak ditemukan.');
                }

                this.setToken(token);
                this.verifyKitchenAuthorization(user);

                return { success: true, user, token };
            } catch (err) {
                const message =
                    err.response?.status === 401
                        ? 'Email atau password salah.'
                        : err.response?.status === 422
                        ? (err.response?.data?.message || 'Input tidak valid.')
                        : (err.message || 'Login gagal.');

                this.error = message;
                return { success: false, message };
            } finally {
                this.loading = false;
            }
        },

        /**
         * Login using Staff PIN
         */
        async loginWithPin(credentials) {
            this.loading = true;
            this.error = null;
            try {
                const data = await authService.staffLogin(credentials);
                const payload = data.data || data;
                const token = payload.token || payload.access_token;
                const user = payload.user;

                if (!token) {
                    throw new Error('Token otentikasi tidak ditemukan.');
                }

                this.setToken(token);
                this.verifyKitchenAuthorization(user);

                return { success: true, user, token };
            } catch (err) {
                const message =
                    err.response?.status === 401
                        ? 'Username atau PIN salah.'
                        : err.response?.status === 422
                        ? (err.response?.data?.message || 'Input tidak valid.')
                        : (err.message || 'Login staff gagal.');

                this.error = message;
                return { success: false, message };
            } finally {
                this.loading = false;
            }
        },

        /**
         * Restore session from /api/v1/me
         */
        async getProfile() {
            if (!this.token) return null;
            try {
                const data = await authService.getProfile();
                const user = data.data || data;
                this.setUser(user);

                if (!this.hasKitchenAccess) {
                    this.logout();
                    return null;
                }

                return user;
            } catch (err) {
                if (err.response?.status === 401 || err.response?.status === 403) {
                    await this.logout();
                }
                return null;
            }
        },

        /**
         * Check and restore authentication on app load or route transition
         */
        async checkAuth() {
            if (!this.token) {
                this.authenticated = false;
                return false;
            }

            if (!this.user) {
                const profile = await this.getProfile();
                return !!profile;
            }

            return this.hasKitchenAccess;
        },

        /**
         * Logout staff
         */
        async logout() {
            try {
                if (this.token) {
                    await authService.logout();
                }
            } catch {
                // Continue local cleanup even if API call fails
            } finally {
                this.setToken(null);
                this.setUser(null);
                this.authenticated = false;
                this.error = null;
            }
        },
    },
});
