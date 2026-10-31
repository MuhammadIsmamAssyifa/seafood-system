import { defineStore } from 'pinia';
import { ref } from 'vue';
import { roleService } from '../services/role.service';

export const useRoleStore = defineStore('role', () => {
    const roles = ref([]);
    const permissions = ref([]);
    const groupedPermissions = ref({});
    const matrix = ref([]);
    const matrixRoles = ref([]);

    const loading = ref(false);
    const matrixLoading = ref(false);
    const submitting = ref(false);
    const error = ref(null);

    async function fetchRoles() {
        loading.value = true;
        error.value = null;
        try {
            roles.value = await roleService.getRoles();
        } catch (err) {
            error.value = err.customMessage || 'Gagal memuat daftar role.';
        } finally {
            loading.value = false;
        }
    }

    async function fetchPermissions() {
        try {
            const res = await roleService.getPermissions();
            permissions.value = res.data || [];
            groupedPermissions.value = res.grouped || {};
        } catch (err) {
            console.error('Failed to load permissions catalog', err);
        }
    }

    async function fetchMatrix() {
        matrixLoading.value = true;
        try {
            const res = await roleService.getPermissionMatrix();
            matrixRoles.value = res.roles || [];
            matrix.value = res.matrix || [];
        } catch (err) {
            console.error('Failed to load permission matrix', err);
        } finally {
            matrixLoading.value = false;
        }
    }

    async function createRole(payload) {
        submitting.value = true;
        error.value = null;
        try {
            const res = await roleService.createRole(payload);
            await Promise.all([fetchRoles(), fetchMatrix()]);
            return res;
        } catch (err) {
            throw err;
        } finally {
            submitting.value = false;
        }
    }

    async function updateRole(id, payload) {
        submitting.value = true;
        error.value = null;
        try {
            const res = await roleService.updateRole(id, payload);
            await Promise.all([fetchRoles(), fetchMatrix()]);
            return res;
        } catch (err) {
            throw err;
        } finally {
            submitting.value = false;
        }
    }

    return {
        roles,
        permissions,
        groupedPermissions,
        matrix,
        matrixRoles,
        loading,
        matrixLoading,
        submitting,
        error,
        fetchRoles,
        fetchPermissions,
        fetchMatrix,
        createRole,
        updateRole,
    };
});

export default useRoleStore;

