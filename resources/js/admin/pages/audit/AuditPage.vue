<script setup>
import { onMounted, ref } from 'vue';
import {
    ClipboardList,
    RefreshCw,
    Activity,
    Calendar,
    Users,
    TrendingUp
} from 'lucide-vue-next';
import { useAuditStore } from '../../stores/audit.store';
import AuditFilter from './components/AuditFilter.vue';
import AuditTable from './components/AuditTable.vue';
import AuditDetailModal from './components/AuditDetailModal.vue';

const auditStore = useAuditStore();
const isModalOpen = ref(false);
const currentLog = ref(null);

onMounted(() => {
    auditStore.fetchSummary();
    auditStore.fetchLogs(1);
});

function handleFilter() {
    auditStore.fetchLogs(1);
}

function handleReset() {
    auditStore.resetFilters();
}

function handlePreset(preset) {
    auditStore.setDatePreset(preset);
}

function openDetail(log) {
    currentLog.value = log;
    isModalOpen.value = true;
}

function closeDetail() {
    isModalOpen.value = false;
    currentLog.value = null;
}

function changePage(page) {
    if (page >= 1 && page <= auditStore.pagination.last_page) {
        auditStore.fetchLogs(page);
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <ClipboardList class="w-7 h-7 text-sky-600" />
                    Audit Trail & Aktivitas Sistem
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Histori lengkap seluruh perubahan data, keamanan, dan aktivitas operasional restoran
                </p>
            </div>

            <button
                @click="auditStore.fetchLogs(auditStore.pagination.current_page)"
                :disabled="auditStore.loading"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-sm font-semibold shadow-sm transition disabled:opacity-50"
            >
                <RefreshCw :class="['w-4 h-4', auditStore.loading ? 'animate-spin' : '']" />
                Segarkan
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                    <Activity class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Total Aktivitas</span>
                    <span class="text-xl font-extrabold text-slate-800">{{ auditStore.summary.total_logs.toLocaleString() }}</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                    <Calendar class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Aktivitas Hari Ini</span>
                    <span class="text-xl font-extrabold text-emerald-600">{{ auditStore.summary.today_logs.toLocaleString() }}</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold">
                    <Users class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Pengguna Terlibat</span>
                    <span class="text-xl font-extrabold text-purple-600">{{ auditStore.summary.unique_users }} user</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                    <TrendingUp class="w-6 h-6" />
                </div>
                <div>
                    <span class="text-xs text-slate-400 block font-medium">Aksi Terbanyak</span>
                    <span class="text-sm font-bold text-slate-800 truncate block max-w-[140px]">
                        {{ Object.keys(auditStore.summary.top_actions)[0] || 'Tidak ada' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <AuditFilter
            :filters="auditStore.filters"
            :loading="auditStore.loading"
            @filter="handleFilter"
            @reset="handleReset"
            @preset="handlePreset"
        />

        <!-- Table -->
        <AuditTable
            :logs="auditStore.logs"
            :loading="auditStore.loading"
            @view-detail="openDetail"
        />

        <!-- Pagination -->
        <div
            v-if="auditStore.pagination.last_page > 1"
            class="flex items-center justify-between bg-white px-4 py-3 rounded-2xl border border-slate-200/80 shadow-sm"
        >
            <div class="text-xs text-slate-500">
                Menampilkan halaman <span class="font-bold text-slate-700">{{ auditStore.pagination.current_page }}</span> dari
                <span class="font-bold text-slate-700">{{ auditStore.pagination.last_page }}</span>
                (Total {{ auditStore.pagination.total }} aktivitas)
            </div>

            <div class="flex items-center gap-2">
                <button
                    @click="changePage(auditStore.pagination.current_page - 1)"
                    :disabled="auditStore.pagination.current_page <= 1 || auditStore.loading"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition"
                >
                    Sebelumnya
                </button>

                <button
                    @click="changePage(auditStore.pagination.current_page + 1)"
                    :disabled="auditStore.pagination.current_page >= auditStore.pagination.last_page || auditStore.loading"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition"
                >
                    Selanjutnya
                </button>
            </div>
        </div>

        <!-- Detail Modal -->
        <AuditDetailModal
            :is-open="isModalOpen"
            :log="currentLog"
            @close="closeDetail"
        />
    </div>
</template>

