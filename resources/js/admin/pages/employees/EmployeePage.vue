<script setup>
import { onMounted, ref } from 'vue';
import { useEmployeeStore } from '../../stores/employee.store';
import EmployeeTable from './components/EmployeeTable.vue';
import EmployeeFormModal from './components/EmployeeFormModal.vue';
import {
    Plus,
    Search,
    RotateCcw,
    Users,
    UserCheck,
    UserX,
    Shield,
} from 'lucide-vue-next';

const employeeStore = useEmployeeStore();

const showModal = ref(false);
const editingEmployee = ref(null);
const toastMessage = ref('');

onMounted(async () => {
    await Promise.all([
        employeeStore.fetchMetaOptions(),
        employeeStore.fetchEmployees(1),
    ]);
});

function handleOpenCreate() {
    editingEmployee.value = null;
    showModal.value = true;
}

function handleOpenEdit(emp) {
    editingEmployee.value = emp;
    showModal.value = true;
}

async function handleSave({ payload, id }) {
    try {
        await employeeStore.saveEmployee(payload, id);
        showModal.value = false;
        showToast(id ? 'Data karyawan berhasil diperbarui.' : 'Karyawan baru berhasil ditambahkan.');
    } catch (err) {
        console.error('Error saving employee:', err);
    }
}

async function handleToggleStatus(emp) {
    const nextStatus = emp.status === 'active' ? 'inactive' : 'active';
    const actionLabel = nextStatus === 'active' ? 'mengaktifkan' : 'menonaktifkan';

    if (!confirm(`Apakah Anda yakin ingin ${actionLabel} akun ${emp.name}?`)) {
        return;
    }

    try {
        await employeeStore.updateStatus(emp.id, nextStatus);
        showToast(`Status ${emp.name} berhasil diubah menjadi ${nextStatus}.`);
    } catch (err) {
        alert(err.response?.data?.message || err.customMessage || 'Gagal mengubah status.');
    }
}

function handleSearch(e) {
    employeeStore.setFilter('search', e.target.value);
    employeeStore.fetchEmployees(1);
}

function handleRoleChange(e) {
    employeeStore.setFilter('role', e.target.value);
    employeeStore.fetchEmployees(1);
}

function handleStatusChange(e) {
    employeeStore.setFilter('status', e.target.value);
    employeeStore.fetchEmployees(1);
}

function handleOutletChange(e) {
    employeeStore.setFilter('outlet_id', e.target.value);
    employeeStore.fetchEmployees(1);
}

function resetFilters() {
    employeeStore.resetFilters();
    employeeStore.fetchEmployees(1);
}

function showToast(msg) {
    toastMessage.value = msg;
    setTimeout(() => {
        toastMessage.value = '';
    }, 3500);
}
</script>

<template>
    <div class="space-y-6">
        <!-- Toast Feedback -->
        <div
            v-if="toastMessage"
            class="fixed top-5 right-5 z-50 bg-indigo-600 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 text-xs font-medium animate-bounce"
        >
            <span>{{ toastMessage }}</span>
        </div>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Employee Management</h1>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-700">
                        Staff & Access
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Kelola data staf restoran, peran operasional (Kasir, Kitchen, Admin), dan penugasan cabang.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <button
                    @click="handleOpenCreate"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors"
                >
                    <Plus class="w-4 h-4" />
                    <span>Tambah Karyawan</span>
                </button>
            </div>
        </div>

        <!-- Metric Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Employees -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Karyawan</span>
                    <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <Users class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div v-if="employeeStore.loading" class="h-8 w-16 bg-slate-100 animate-pulse rounded"></div>
                    <div v-else class="text-2xl font-bold text-slate-900 tracking-tight">
                        {{ employeeStore.summary.total || 0 }}
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-1">Terdaftar di seluruh sistem</p>
            </div>

            <!-- Active Employees -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Karyawan Aktif</span>
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <UserCheck class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div v-if="employeeStore.loading" class="h-8 w-16 bg-slate-100 animate-pulse rounded"></div>
                    <div v-else class="text-2xl font-bold text-emerald-600 tracking-tight">
                        {{ employeeStore.summary.active || 0 }}
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-1">Dapat login & bertransaksi</p>
            </div>

            <!-- Inactive Employees -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nonaktif</span>
                    <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                        <UserX class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3">
                    <div v-if="employeeStore.loading" class="h-8 w-16 bg-slate-100 animate-pulse rounded"></div>
                    <div v-else class="text-2xl font-bold text-slate-600 tracking-tight">
                        {{ employeeStore.summary.inactive || 0 }}
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-1">Akses dinonaktifkan</p>
            </div>

            <!-- Role Distribution Summary -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Distribusi Role</span>
                    <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <Shield class="w-5 h-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between text-xs font-medium">
                    <div class="text-center">
                        <div class="text-slate-900 font-bold text-base">{{ employeeStore.summary.role_counts?.cashier || 0 }}</div>
                        <div class="text-[10px] text-slate-400">Kasir</div>
                    </div>
                    <div class="text-center">
                        <div class="text-slate-900 font-bold text-base">{{ employeeStore.summary.role_counts?.kitchen || 0 }}</div>
                        <div class="text-[10px] text-slate-400">Dapur</div>
                    </div>
                    <div class="text-center">
                        <div class="text-slate-900 font-bold text-base">{{ employeeStore.summary.role_counts?.admin || 0 }}</div>
                        <div class="text-[10px] text-slate-400">Admin</div>
                    </div>
                    <div class="text-center">
                        <div class="text-slate-900 font-bold text-base">{{ employeeStore.summary.role_counts?.owner || 0 }}</div>
                        <div class="text-[10px] text-slate-400">Owner</div>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-1">Total komposisi tim</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <!-- Search Input -->
                <div class="relative w-64">
                    <input
                        :value="employeeStore.filters.search"
                        @input="handleSearch"
                        type="text"
                        placeholder="Cari nama, username, email..."
                        class="w-full text-xs pl-8 pr-3 py-2 border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <Search class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" />
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Filter Role -->
                    <select
                        :value="employeeStore.filters.role"
                        @change="handleRoleChange"
                        class="text-xs py-2 pl-3 pr-8 border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 bg-white"
                    >
                        <option value="">Semua Role</option>
                        <option v-for="r in employeeStore.roles" :key="r.name" :value="r.name">
                            {{ r.label || r.name }}
                        </option>
                    </select>

                    <!-- Filter Status -->
                    <select
                        :value="employeeStore.filters.status"
                        @change="handleStatusChange"
                        class="text-xs py-2 pl-3 pr-8 border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 bg-white"
                    >
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                    </select>

                    <!-- Filter Outlet -->
                    <select
                        :value="employeeStore.filters.outlet_id"
                        @change="handleOutletChange"
                        class="text-xs py-2 pl-3 pr-8 border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 bg-white"
                    >
                        <option value="">Semua Cabang</option>
                        <option v-for="o in employeeStore.outlets" :key="o.id" :value="o.id">
                            {{ o.name }}
                        </option>
                    </select>

                    <!-- Reset Button -->
                    <button
                        @click="resetFilters"
                        class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors"
                        title="Reset Filter"
                    >
                        <RotateCcw class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Employee Table -->
        <EmployeeTable
            :employees="employeeStore.employees"
            :pagination="employeeStore.pagination"
            :loading="employeeStore.loading"
            @page-change="(p) => employeeStore.fetchEmployees(p)"
            @edit="handleOpenEdit"
            @toggle-status="handleToggleStatus"
        />

        <!-- Form Modal -->
        <EmployeeFormModal
            v-model="showModal"
            :employee="editingEmployee"
            :roles="employeeStore.roles"
            :outlets="employeeStore.outlets"
            @saved="handleSave"
        />
    </div>
</template>

