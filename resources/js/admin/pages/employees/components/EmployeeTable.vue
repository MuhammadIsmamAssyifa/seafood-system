<script setup>
import { useRouter } from 'vue-router';
import { formatDate } from '../../../utils/formatter';
import EmployeeStatusBadge from './EmployeeStatusBadge.vue';
import EmployeeRoleBadge from './EmployeeRoleBadge.vue';
import {
    Eye,
    Edit3,
    UserCheck,
    UserX,
    ChevronLeft,
    ChevronRight,
    Users,
} from 'lucide-vue-next';

const props = defineProps({
    employees: {
        type: Array,
        default: () => [],
    },
    pagination: {
        type: Object,
        default: () => ({
            current_page: 1,
            last_page: 1,
            total: 0,
            per_page: 15,
        }),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['page-change', 'edit', 'toggle-status']);
const router = useRouter();

function getInitials(name) {
    if (!name) return 'U';
    return name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

function goToDetail(id) {
    router.push(`/employees/${id}`);
}
</script>

<template>
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                        <th class="py-3 px-4">Karyawan</th>
                        <th class="py-3 px-4">Kontak & Akun</th>
                        <th class="py-3 px-4">Jabatan (Role)</th>
                        <th class="py-3 px-4">Cabang (Outlet)</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Terdaftar</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <!-- Loading Skeleton -->
                    <tr v-if="loading" v-for="i in 5" :key="i">
                        <td class="py-3.5 px-4"><div class="h-4 w-32 bg-slate-100 animate-pulse rounded"></div></td>
                        <td class="py-3.5 px-4"><div class="h-4 w-28 bg-slate-100 animate-pulse rounded"></div></td>
                        <td class="py-3.5 px-4"><div class="h-4 w-16 bg-slate-100 animate-pulse rounded"></div></td>
                        <td class="py-3.5 px-4"><div class="h-4 w-24 bg-slate-100 animate-pulse rounded"></div></td>
                        <td class="py-3.5 px-4"><div class="h-4 w-14 bg-slate-100 animate-pulse rounded"></div></td>
                        <td class="py-3.5 px-4"><div class="h-4 w-20 bg-slate-100 animate-pulse rounded"></div></td>
                        <td class="py-3.5 px-4 text-center"><div class="h-4 w-16 bg-slate-100 animate-pulse rounded mx-auto"></div></td>
                    </tr>

                    <!-- Empty State -->
                    <tr v-else-if="employees.length === 0">
                        <td colspan="7" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <Users class="w-10 h-10 stroke-[1.5] mb-2" />
                                <p class="text-sm font-medium text-slate-600">Tidak ada data karyawan</p>
                                <p class="text-xs text-slate-400 mt-0.5">Daftarkan karyawan baru melalui tombol di atas.</p>
                            </div>
                        </td>
                    </tr>

                    <!-- Data Rows -->
                    <tr
                        v-else
                        v-for="emp in employees"
                        :key="emp.id"
                        class="hover:bg-slate-50/60 transition-colors"
                    >
                        <!-- Name & Avatar -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                    :class="emp.status === 'active' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-200 text-slate-600'"
                                >
                                    {{ getInitials(emp.name) }}
                                </div>
                                <div>
                                    <span class="font-semibold text-slate-900 block hover:text-indigo-600 cursor-pointer" @click="goToDetail(emp.id)">
                                        {{ emp.name }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 font-mono">@{{ emp.username }}</span>
                                </div>
                            </div>
                        </td>

                        <!-- Account & Contact -->
                        <td class="py-3.5 px-4">
                            <div class="text-slate-800">{{ emp.email }}</div>
                            <div v-if="emp.has_pin" class="text-[10px] text-emerald-600 font-medium">PIN Aktif ✓</div>
                        </td>

                        <!-- Role -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <EmployeeRoleBadge :role="emp.role" />
                        </td>

                        <!-- Outlets -->
                        <td class="py-3.5 px-4">
                            <div v-if="emp.outlets && emp.outlets.length > 0" class="flex flex-wrap gap-1">
                                <span
                                    v-for="o in emp.outlets"
                                    :key="o.id"
                                    class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-[10px] font-medium"
                                >
                                    {{ o.name }}
                                </span>
                            </div>
                            <span v-else class="text-slate-400 text-[11px] italic">Semua Cabang</span>
                        </td>

                        <!-- Status -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <EmployeeStatusBadge :status="emp.status" />
                        </td>

                        <!-- Joined Date -->
                        <td class="py-3.5 px-4 whitespace-nowrap text-slate-500 text-[11px]">
                            {{ formatDate(emp.created_at, 'DD MMM YYYY') }}
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center gap-1.5">
                                <button
                                    @click="goToDetail(emp.id)"
                                    class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition-colors"
                                    title="Lihat Detail"
                                >
                                    <Eye class="w-3.5 h-3.5" />
                                </button>
                                <button
                                    @click="emit('edit', emp)"
                                    class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition-colors"
                                    title="Edit Karyawan"
                                >
                                    <Edit3 class="w-3.5 h-3.5" />
                                </button>
                                <button
                                    @click="emit('toggle-status', emp)"
                                    :class="[
                                        'p-1.5 rounded-lg transition-colors',
                                        emp.status === 'active'
                                            ? 'text-red-500 hover:bg-red-50 hover:text-red-700'
                                            : 'text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700',
                                    ]"
                                    :title="emp.status === 'active' ? 'Nonaktifkan Akun' : 'Aktifkan Akun'"
                                >
                                    <UserX v-if="emp.status === 'active'" class="w-3.5 h-3.5" />
                                    <UserCheck v-else class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div
            v-if="pagination.last_page > 1"
            class="px-4 py-3 bg-white border-t border-slate-100 flex items-center justify-between text-xs text-slate-500"
        >
            <div>
                Menampilkan halaman <strong class="text-slate-800">{{ pagination.current_page }}</strong> dari
                <strong class="text-slate-800">{{ pagination.last_page }}</strong> (Total {{ pagination.total }} karyawan)
            </div>
            <div class="flex items-center gap-1.5">
                <button
                    @click="emit('page-change', pagination.current_page - 1)"
                    :disabled="pagination.current_page <= 1"
                    class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:pointer-events-none transition-colors"
                >
                    <ChevronLeft class="w-4 h-4" />
                </button>
                <button
                    @click="emit('page-change', pagination.current_page + 1)"
                    :disabled="pagination.current_page >= pagination.last_page"
                    class="p-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:pointer-events-none transition-colors"
                >
                    <ChevronRight class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>

