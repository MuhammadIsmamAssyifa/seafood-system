import { defineStore } from 'pinia';
import { ref } from 'vue';
import { orderService } from '../services/order.service';

export const useOrderStore = defineStore('order', () => {
    const orders = ref([]);
    const currentOrder = ref(null);
    const loading = ref(false);
    const detailLoading = ref(false);
    const error = ref(null);

    const pagination = ref({
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 15,
    });

    const filters = ref({
        status: '',
        date: '',
        search: '',
    });

    async function fetchOrders(page = 1) {
        loading.value = true;
        error.value = null;

        try {
            const params = {
                page,
                per_page: pagination.value.per_page,
            };

            if (filters.value.status) params.status = filters.value.status;
            if (filters.value.date) params.date = filters.value.date;
            if (filters.value.search) params.search = filters.value.search.trim();

            const result = await orderService.getOrders(params);
            orders.value = result.data;
            pagination.value = {
                current_page: result.meta.current_page || page,
                last_page: result.meta.last_page || 1,
                total: result.meta.total || result.data.length,
                per_page: result.meta.per_page || 15,
            };
        } catch (err) {
            error.value = err.customMessage || 'Gagal mengambil data pesanan.';
        } finally {
            loading.value = false;
        }
    }

    async function fetchOrderDetail(id) {
        detailLoading.value = true;
        error.value = null;
        currentOrder.value = null;

        try {
            const data = await orderService.getOrderDetail(id);
            currentOrder.value = data;
            return data;
        } catch (err) {
            error.value = err.customMessage || 'Pesanan tidak ditemukan atau gagal dimuat.';
            throw err;
        } finally {
            detailLoading.value = false;
        }
    }

    function setStatus(status) {
        filters.value.status = status;
        return fetchOrders(1);
    }

    function setSearch(search) {
        filters.value.search = search;
        return fetchOrders(1);
    }

    function setDate(date) {
        filters.value.date = date;
        return fetchOrders(1);
    }

    function resetFilters() {
        filters.value.status = '';
        filters.value.date = '';
        filters.value.search = '';
        return fetchOrders(1);
    }

    return {
        orders,
        currentOrder,
        loading,
        detailLoading,
        error,
        pagination,
        filters,
        fetchOrders,
        fetchOrderDetail,
        setStatus,
        setSearch,
        setDate,
        resetFilters,
    };
});

