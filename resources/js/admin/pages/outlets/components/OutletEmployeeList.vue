<script setup>
import { ref, onMounted } from 'vue';
import { Users, UserPlus, Check, X, Shield } from 'lucide-vue-next';
import { employeeService } from '../../../services/employee.service';

const props = defineProps({
    outlet: {
        type: Object,
        required: true,
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['save-assignment']);

const isAssignModalOpen = ref(false);
const allEmployees = ref([]);
const selectedUserIds = ref([]);

onMounted(async () => {
    try {
        const res = await employeeService.getEmployees({ per_page: 100 });
        allEmployees.value = res.data || [];
    } catch (e) {
        console.error('Failed to load employees for assignment', e);
    }
});

function openAssignModal() {
    selectedUserIds.value = (props.outlet.users || []).map((u) => u.id);
    isAssignModalOpen.value = true;
}

function toggleUser(id) {
    if (selectedUserIds.value.includes(id)) {
        selectedUserIds.value = selectedUserIds.value.filter((uId) => uId !== id);
    } else {
        selectedUserIds.value.push(id);
    }
}

function save() {
    emit('save-assignment', selectedUserIds.value);
    isAssignModalOpen.value = false;
}
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <Users class="w-4 h-4 text-sky-600" />
                <h3 class="text-sm font-bold text-slate-800">Daftar Karyawan Ditempatkan</h3>
            </div>
            <button
                @click="openAssignModal"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 transition"
            >
                <UserPlus class="w-3.5 h-3.5" />
                Atur Penugasan Karyawan
            </button>
        </div>

        <div v-if="!outlet.users || !outlet.users.length" class="p-8 text-center text-slate-400 text-xs">
            Belum ada karyawan yang ditempatkan pada cabang ini. Klik tombol di atas untuk menugaskan karyawan.
        </div>

        <div v-else class="divide-y divide-slate-100">
            <div
                v-for="user in outlet.users"
                :key="user.id"
                class="p-4 flex items-center justify-between hover:bg-slate-50 transition"
            >
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs uppercase">
                        {{ user.name.charAt(0) }}
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-800 block">{{ user.name }}</span>
                        <span class="text-[11px] text-slate-400">{{ user.email }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                        <Shield class="w-3 h-3 text-slate-400" />
                        {{ user.role }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Assignment Modal -->
        <div
            v-if="isAssignModalOpen"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            @click.self="isAssignModalOpen = false"
        >
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Pilih Karyawan untuk {{ outlet.name }}</h4>
                        <p class="text-xs text-slate-500">Centang karyawan yang diberi akses operasional ke cabang ini</p>
                    </div>
                    <button @click="isAssignModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <div class="p-6 max-h-[60vh] overflow-y-auto space-y-2">
                    <div
                        v-for="emp in allEmployees"
                        :key="emp.id"
                        @click="toggleUser(emp.id)"
                        :class="[
                            'p-3 rounded-xl border flex items-center justify-between cursor-pointer transition',
                            selectedUserIds.includes(emp.id)
                                ? 'bg-sky-50/70 border-sky-300 text-sky-900'
                                : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'
                        ]"
                    >
                        <div>
                            <span class="text-xs font-bold block">{{ emp.name }}</span>
                            <span class="text-[11px] text-slate-400">{{ emp.roles?.[0]?.name || 'Staff' }} • {{ emp.email }}</span>
                        </div>
                        <div
                            :class="[
                                'w-5 h-5 rounded-md flex items-center justify-center border transition',
                                selectedUserIds.includes(emp.id)
                                    ? 'bg-sky-600 border-sky-600 text-white'
                                    : 'border-slate-300 bg-white'
                            ]"
                        >
                            <Check v-if="selectedUserIds.includes(emp.id)" class="w-3.5 h-3.5" />
                        </div>
                    </div>
                </div>

                <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button
                        @click="isAssignModalOpen = false"
                        class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700"
                    >
                        Batal
                    </button>
                    <button
                        @click="save"
                        :disabled="saving"
                        class="px-5 py-2 text-xs font-bold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm disabled:opacity-50"
                    >
                        Simpan Penugasan
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

