<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useEmployeeStore } from '../../stores/employee.store';
import { formatDate } from '../../utils/formatter';
import EmployeeStatusBadge from './components/EmployeeStatusBadge.vue';
import EmployeeRoleBadge from './components/EmployeeRoleBadge.vue';
import OutletAssignment from './components/OutletAssignment.vue';
import {
    ArrowLeft,
    User,
    Mail,
    KeyRound,
    Calendar,
    Shield,
    CheckCircle2,
    Store,
    UserCheck,
    UserX,
} from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const employeeStore = useEmployeeStore();

const successMsg = ref('');

onMounted(async () => {
    const id = route.params.id;
    if (id) {
        await Promise.all([
            employeeStore.fetchMetaOptions(),
            employeeStore.fetchDetail(id),
        ]);
    }
});

async function handleToggleStatus() {
    const emp = employeeStore.selectedEmployee;
    if (!emp) return;
    const nextStatus = emp.status === 'active' ? 'inactive' : 'active';

    try {
        await employeeStore.updateStatus(emp.id, nextStatus);
        showToast(`Status berhasil diubah menjadi ${nextStatus}.`);
    } catch (err) {
        alert(err.response?.data?.message || err.customMessage || 'Gagal mengubah status.');
    }
}

async function handleSaveOutlets(outletIds) {
    const emp = employeeStore.selectedEmployee;
    if (!emp) return;

    try {
        await employeeStore.assignOutlets(emp.id, outletIds);
        showToast('Penugasan cabang berhasil diperbarui.');
    } catch (err) {
        alert(err.response?.data?.message || err.customMessage || 'Gagal memperbarui cabang.');
    }
}

function showToast(msg) {
    successMsg.value = msg;
    setTimeout(() => {
        successMsg.value = '';
    }, 3500);
}
</script>

<template>
    <div class="space-y-6">
        <!-- Toast Feedback -->
        <div
            v-if="successMsg"
            class="fixed top-5 right-5 z-50 bg-indigo-600 text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 text-xs font-medium animate-bounce"
        >
            <span>{{ successMsg }}</span>
        </div>

        <!-- Back Navigation -->
        <div>
            <button
                @click="router.push('/employees')"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 transition-colors"
            >
                <ArrowLeft class="w-4 h-4" />
                <span>Kembali ke Daftar Karyawan</span>
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="employeeStore.detailLoading" class="bg-white rounded-2xl border border-slate-200 p-8 text-center">
            <div class="w-8 h-8 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
            <p class="text-xs text-slate-500">Memuat rincian karyawan...</p>
        </div>

        <!-- Detail Content -->
        <div v-else-if="employeeStore.selectedEmployee" class="space-y-6">
            <!-- Header Banner -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold shrink-0">
                        {{ employeeStore.selectedEmployee.name?.charAt(0) || 'U' }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                                {{ employeeStore.selectedEmployee.name }}
                            </h1>
                            <EmployeeStatusBadge :status="employeeStore.selectedEmployee.status" />
                            <EmployeeRoleBadge :role="employeeStore.selectedEmployee.role" />
                        </div>
                        <p class="text-xs text-slate-500 mt-1 flex items-center gap-2">
                            <span>@{{ employeeStore.selectedEmployee.username }}</span>
                            <span>•</span>
                            <span>{{ employeeStore.selectedEmployee.email }}</span>
                            <span>•</span>
                            <span>ID #{{ employeeStore.selectedEmployee.id }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <button
                        @click="handleToggleStatus"
                        :class="[
                            'px-4 py-2 text-xs font-semibold rounded-xl transition-colors shadow-xs flex items-center gap-2',
                            employeeStore.selectedEmployee.status === 'active'
                                ? 'bg-red-50 text-red-700 hover:bg-red-100 border border-red-200'
                                : 'bg-emerald-600 text-white hover:bg-emerald-700',
                        ]"
                    >
                        <UserX v-if="employeeStore.selectedEmployee.status === 'active'" class="w-4 h-4" />
                        <UserCheck v-else class="w-4 h-4" />
                        <span>
                            {{ employeeStore.selectedEmployee.status === 'active' ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Profile Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
                    <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold uppercase">
                        <User class="w-4 h-4 text-indigo-500" />
                        <span>Identitas Akun</span>
                    </div>
                    <div class="mt-3 space-y-2 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Username</span>
                            <span class="font-mono font-medium text-slate-900">{{ employeeStore.selectedEmployee.username }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Email</span>
                            <span class="font-medium text-slate-900">{{ employeeStore.selectedEmployee.email }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
                    <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold uppercase">
                        <KeyRound class="w-4 h-4 text-amber-500" />
                        <span>Otentikasi & Keamanan</span>
                    </div>
                    <div class="mt-3 space-y-2 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">PIN Quick Login</span>
                            <span v-if="employeeStore.selectedEmployee.has_pin" class="text-emerald-600 font-semibold flex items-center gap-1">
                                <CheckCircle2 class="w-3.5 h-3.5" /> Terpasang
                            </span>
                            <span v-else class="text-slate-400 italic">Belum diatur</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Terdaftar Pada</span>
                            <span class="font-medium text-slate-900">{{ formatDate(employeeStore.selectedEmployee.created_at, 'DD MMMM YYYY') }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
                    <div class="flex items-center gap-2 text-slate-500 text-xs font-semibold uppercase">
                        <Store class="w-4 h-4 text-emerald-500" />
                        <span>Restoran</span>
                    </div>
                    <div class="mt-3 space-y-2 text-xs">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Nama Usaha</span>
                            <span class="font-medium text-slate-900">{{ employeeStore.selectedEmployee.restaurant?.name || 'Seafood Resto POS' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Jumlah Cabang Aktif</span>
                            <span class="font-semibold text-indigo-600">{{ employeeStore.selectedEmployee.outlets?.length || 0 }} cabang</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Outlet Assignment Component -->
            <OutletAssignment
                :employee="employeeStore.selectedEmployee"
                :all-outlets="employeeStore.outlets"
                :loading="employeeStore.submitting"
                @save="handleSaveOutlets"
            />

            <!-- Permissions Granted via Role -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Shield class="w-4 h-4 text-slate-600" />
                        <h3 class="text-sm font-semibold text-slate-900">Hak Akses Efektif (Permissions)</h3>
                    </div>
                    <span class="text-xs text-slate-400">
                        {{ employeeStore.selectedEmployee.permissions?.length || 0 }} hak akses aktif via role {{ employeeStore.selectedEmployee.role }}
                    </span>
                </div>

                <div v-if="employeeStore.selectedEmployee.permissions?.length === 0" class="py-4 text-xs text-slate-400">
                    Tidak ada permission khusus.
                </div>

                <div v-else class="flex flex-wrap gap-2 pt-1">
                    <span
                        v-for="perm in employeeStore.selectedEmployee.permissions"
                        :key="perm"
                        class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-mono text-[11px] rounded-lg border border-slate-200/60 transition-colors"
                    >
                        {{ perm }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

