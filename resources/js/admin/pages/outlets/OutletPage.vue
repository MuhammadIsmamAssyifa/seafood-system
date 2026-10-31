<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { Building2, Plus, Search, RefreshCw, CheckCircle2, AlertCircle } from 'lucide-vue-next';
import { useOutletStore } from '../../stores/outlet.store';
import OutletTable from './components/OutletTable.vue';
import OutletFormModal from './components/OutletFormModal.vue';

const router = useRouter();
const outletStore = useOutletStore();

const isModalOpen = ref(false);
const editingOutlet = ref(null);
const successMessage = ref(null);

onMounted(() => {
    outletStore.fetchOutlets(1);
});

function handleSearch() {
    outletStore.fetchOutlets(1);
}

function openCreateModal() {
    editingOutlet.value = null;
    isModalOpen.value = true;
}

function openEditModal(outlet) {
    editingOutlet.value = outlet;
    isModalOpen.value = true;
}

function closeModal() {
    isModalOpen.value = false;
    editingOutlet.value = null;
}

async function handleFormSubmit(formData) {
    try {
        if (editingOutlet.value) {
            await outletStore.updateOutlet(editingOutlet.value.id, formData);
            successMessage.value = 'Data cabang berhasil diperbarui.';
        } else {
            await outletStore.createOutlet(formData);
            successMessage.value = 'Cabang baru berhasil ditambahkan.';
        }
        closeModal();
    } catch (err) {
        // error handled in store
    }
}

function viewDetail(outlet) {
    router.push(`/outlets/${outlet.id}`);
}

async function handleDelete(outlet) {
    if (confirm(`Apakah Anda yakin ingin menghapus cabang '${outlet.name}'?`)) {
        try {
            await outletStore.deleteOutlet(outlet.id);
            successMessage.value = 'Cabang berhasil dihapus.';
        } catch (err) {
            // error handled in store
        }
    }
}

function changePage(page) {
    if (page >= 1 && page <= outletStore.pagination.last_page) {
        outletStore.fetchOutlets(page);
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <Building2 class="w-7 h-7 text-sky-600" />
                    Outlet Management (Manajemen Cabang)
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Kelola data cabang restoran, isolasi data operasional, dan penugasan karyawan
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button
                    @click="outletStore.fetchOutlets(outletStore.pagination.current_page)"
                    :disabled="outletStore.loading"
                    class="p-2 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl shadow-sm transition"
                    title="Segarkan Data"
                >
                    <RefreshCw :class="['w-4 h-4', outletStore.loading ? 'animate-spin' : '']" />
                </button>

                <button
                    @click="openCreateModal"
                    class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-sm font-bold shadow-sm transition inline-flex items-center gap-1.5"
                >
                    <Plus class="w-4 h-4" />
                    Tambah Cabang
                </button>
            </div>
        </div>

        <!-- Success Alert -->
        <div
            v-if="successMessage"
            class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between text-sm animate-in fade-in"
        >
            <div class="flex items-center gap-2">
                <CheckCircle2 class="w-5 h-5 text-emerald-600 flex-shrink-0" />
                <span>{{ successMessage }}</span>
            </div>
            <button @click="successMessage = null" class="text-xs font-bold text-emerald-700 hover:text-emerald-900">
                ✕
            </button>
        </div>

        <!-- Error Alert -->
        <div
            v-if="outletStore.error"
            class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between text-sm animate-in fade-in"
        >
            <div class="flex items-center gap-2">
                <AlertCircle class="w-5 h-5 text-rose-600 flex-shrink-0" />
                <span>{{ outletStore.error }}</span>
            </div>
            <button @click="outletStore.error = null" class="text-xs font-bold text-rose-700 hover:text-rose-900">
                ✕
            </button>
        </div>

        <!-- Search Bar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    v-model="outletStore.filters.search"
                    @keyup.enter="handleSearch"
                    type="text"
                    placeholder="Cari nama, kode, atau alamat cabang..."
                    class="w-full pl-9 pr-3 py-1.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 focus:bg-white transition"
                />
            </div>

            <div>
                <select
                    v-model="outletStore.filters.status"
                    @change="handleSearch"
                    class="w-full sm:w-44 text-xs py-2 px-3 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 font-medium"
                >
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <OutletTable
            :outlets="outletStore.outlets"
            :loading="outletStore.loading"
            @view="viewDetail"
            @edit="openEditModal"
            @delete="handleDelete"
        />

        <!-- Pagination -->
        <div
            v-if="outletStore.pagination.last_page > 1"
            class="flex items-center justify-between bg-white px-4 py-3 rounded-2xl border border-slate-200/80 shadow-sm"
        >
            <div class="text-xs text-slate-500">
                Menampilkan halaman <span class="font-bold text-slate-700">{{ outletStore.pagination.current_page }}</span> dari
                <span class="font-bold text-slate-700">{{ outletStore.pagination.last_page }}</span>
                (Total {{ outletStore.pagination.total }} cabang)
            </div>

            <div class="flex items-center gap-2">
                <button
                    @click="changePage(outletStore.pagination.current_page - 1)"
                    :disabled="outletStore.pagination.current_page <= 1 || outletStore.loading"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition"
                >
                    Sebelumnya
                </button>

                <button
                    @click="changePage(outletStore.pagination.current_page + 1)"
                    :disabled="outletStore.pagination.current_page >= outletStore.pagination.last_page || outletStore.loading"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition"
                >
                    Selanjutnya
                </button>
            </div>
        </div>

        <!-- Create / Edit Form Modal -->
        <OutletFormModal
            :is-open="isModalOpen"
            :outlet="editingOutlet"
            :saving="outletStore.saving"
            @close="closeModal"
            @submit="handleFormSubmit"
        />
    </div>
</template>

