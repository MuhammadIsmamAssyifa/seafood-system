import { defineStore } from 'pinia';
import { ref } from 'vue';
import { employeeService } from '../services/employee.service';

export const useEmployeeStore = defineStore('employee', () => {
    const employees = ref([]);
    const selectedEmployee = ref(null);
    const summary = ref({
        total: 0,
        active: 0,
        inactive: 0,
        role_counts: {},
    });
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
    });

    const roles = ref([]);
    const outlets = ref([]);

    const loading = ref(false);
    const detailLoading = ref(false);
    const submitting = ref(false);
    const error = ref(null);

    const filters = ref({
        search: '',
        role: '',
        status: '',
        outlet_id: '',
        page: 1,
    });

    async function fetchMetaOptions() {
        try {
            const res = await employeeService.getMetaOptions();
            roles.value = res.roles || [];
            outlets.value = res.outlets || [];
        } catch (err) {
            console.error('Failed to load employee meta options', err);
        }
    }

    async function fetchEmployees(page = 1) {
        loading.value = true;
        error.value = null;
        filters.value.page = page;

        try {
            const params = {
                page,
                per_page: pagination.value.per_page,
            };
            if (filters.value.search) params.search = filters.value.search.trim();
            if (filters.value.role) params.role = filters.value.role;
            if (filters.value.status) params.status = filters.value.status;
            if (filters.value.outlet_id) params.outlet_id = filters.value.outlet_id;

            const res = await employeeService.getEmployees(params);
            employees.value = res.data || [];
            if (res.meta) {
                pagination.value = {
                    current_page: res.meta.current_page,
                    last_page: res.meta.last_page,
                    per_page: res.meta.per_page,
                    total: res.meta.total,
                };
            }
            if (res.summary) {
                summary.value = res.summary;
            }
        } catch (err) {
            error.value = err.customMessage || 'Gagal memuat daftar karyawan.';
        } finally {
            loading.value = false;
        }
    }

    async function fetchDetail(id) {
        detailLoading.value = true;
        error.value = null;
        try {
            selectedEmployee.value = await employeeService.getEmployeeDetail(id);
        } catch (err) {
            error.value = err.customMessage || 'Gagal memuat rincian karyawan.';
        } finally {
            detailLoading.value = false;
        }
    }

    async function saveEmployee(payload, id = null) {
        submitting.value = true;
        error.value = null;
        try {
            let res;
            if (id) {
                res = await employeeService.updateEmployee(id, payload);
            } else {
                res = await employeeService.createEmployee(payload);
            }
            await fetchEmployees(filters.value.page);
            return res;
        } catch (err) {
            throw err;
        } finally {
            submitting.value = false;
        }
    }

    async function updateStatus(id, status) {
        submitting.value = true;
        error.value = null;
        try {
            const res = await employeeService.updateStatus(id, status);
            const emp = employees.value.find((e) => e.id === id);
            if (emp) emp.status = status;
            if (selectedEmployee.value?.id === id) selectedEmployee.value.status = status;
            return res;
        } catch (err) {
            throw err;
        } finally {
            submitting.value = false;
        }
    }

    async function assignOutlets(id, outletIds) {
        submitting.value = true;
        error.value = null;
        try {
            const res = await employeeService.assignOutlets(id, outletIds);
            await fetchDetail(id);
            return res;
        } catch (err) {
            throw err;
        } finally {
            submitting.value = false;
        }
    }

    function setFilter(key, value) {
        filters.value[key] = value;
    }

    function resetFilters() {
        filters.value = {
            search: '',
            role: '',
            status: '',
            outlet_id: '',
            page: 1,
        };
    }

    return {
        employees,
        selectedEmployee,
        summary,
        pagination,
        roles,
        outlets,
        loading,
        detailLoading,
        submitting,
        error,
        filters,
        fetchMetaOptions,
        fetchEmployees,
        fetchDetail,
        saveEmployee,
        updateStatus,
        assignOutlets,
        setFilter,
        resetFilters,
    };
});

export default useEmployeeStore;

