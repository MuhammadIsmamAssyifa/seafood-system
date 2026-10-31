import { defineStore } from 'pinia';
import { ref } from 'vue';
import { outletService } from '../services/outlet.service';

export const useOutletStore = defineStore('outlet', () => {
    const outlets = ref([]);
    const selectedOutlet = ref(null);
    const currentOutletId = ref(localStorage.getItem('selected_outlet_id') || '');
    const pagination = ref({
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
    });

    const loading = ref(false);
    const detailLoading = ref(false);
    const saving = ref(false);
    const error = ref(null);

    const filters = ref({
        search: '',
        status: '',
        page: 1,
    });

    async function fetchOutlets(page = 1) {
        loading.value = true;
        error.value = null;
        filters.value.page = page;

        try {
            const params = {
                page,
                per_page: pagination.value.per_page,
            };

            if (filters.value.search) params.search = filters.value.search;
            if (filters.value.status) params.status = filters.value.status;

            const res = await outletService.getOutlets(params);
            outlets.value = res.data || [];

            if (res.meta) {
                pagination.value = {
                    current_page: res.meta.current_page,
                    last_page: res.meta.last_page,
                    per_page: res.meta.per_page,
                    total: res.meta.total,
                };
            }

            // Default currentOutletId if not set yet
            if (!currentOutletId.value && outlets.value.length > 0) {
                setCurrentOutlet(outlets.value[0].id);
            }
        } catch (err) {
            console.error('Failed to load outlets', err);
            error.value = err.response?.data?.message || 'Gagal memuat cabang restoran.';
        } finally {
            loading.value = false;
        }
    }

    async function fetchDetail(id) {
        detailLoading.value = true;
        error.value = null;
        try {
            const data = await outletService.getDetail(id);
            selectedOutlet.value = data;
            return data;
        } catch (err) {
            console.error('Failed to load outlet detail', err);
            error.value = err.response?.data?.message || 'Gagal memuat rincian cabang.';
            throw err;
        } finally {
            detailLoading.value = false;
        }
    }

    async function createOutlet(data) {
        saving.value = true;
        error.value = null;
        try {
            const res = await outletService.createOutlet(data);
            await fetchOutlets(1);
            return res;
        } catch (err) {
            console.error('Failed to create outlet', err);
            error.value = err.response?.data?.message || 'Gagal membuat cabang baru.';
            throw err;
        } finally {
            saving.value = false;
        }
    }

    async function updateOutlet(id, data) {
        saving.value = true;
        error.value = null;
        try {
            const res = await outletService.updateOutlet(id, data);
            await fetchOutlets(pagination.value.current_page);
            if (selectedOutlet.value && selectedOutlet.value.id === id) {
                selectedOutlet.value = res.data || res;
            }
            return res;
        } catch (err) {
            console.error('Failed to update outlet', err);
            error.value = err.response?.data?.message || 'Gagal memperbarui data cabang.';
            throw err;
        } finally {
            saving.value = false;
        }
    }

    async function deleteOutlet(id) {
        saving.value = true;
        error.value = null;
        try {
            const res = await outletService.deleteOutlet(id);
            await fetchOutlets(pagination.value.current_page);
            return res;
        } catch (err) {
            console.error('Failed to delete outlet', err);
            error.value = err.response?.data?.message || 'Gagal menghapus cabang.';
            throw err;
        } finally {
            saving.value = false;
        }
    }

    async function assignUsers(id, userIds) {
        saving.value = true;
        error.value = null;
        try {
            const res = await outletService.assignUsers(id, userIds);
            if (selectedOutlet.value && selectedOutlet.value.id === id) {
                selectedOutlet.value = res.data || res;
            }
            return res;
        } catch (err) {
            console.error('Failed to assign users to outlet', err);
            error.value = err.response?.data?.message || 'Gagal menetapkan karyawan ke cabang.';
            throw err;
        } finally {
            saving.value = false;
        }
    }

    function setCurrentOutlet(id) {
        currentOutletId.value = String(id);
        localStorage.setItem('selected_outlet_id', String(id));
    }

    return {
        outlets,
        selectedOutlet,
        currentOutletId,
        pagination,
        loading,
        detailLoading,
        saving,
        error,
        filters,
        fetchOutlets,
        fetchDetail,
        createOutlet,
        updateOutlet,
        deleteOutlet,
        assignUsers,
        setCurrentOutlet,
    };
});

