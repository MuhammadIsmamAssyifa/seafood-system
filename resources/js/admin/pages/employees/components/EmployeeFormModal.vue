<script setup>
import { ref, reactive, watch, computed } from 'vue';
import { UserPlus, UserCheck, X, AlertCircle, KeyRound, ShieldAlert } from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    employee: {
        type: Object,
        default: null,
    },
    roles: {
        type: Array,
        default: () => [],
    },
    outlets: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue', 'saved']);

const isEdit = computed(() => !!props.employee);
const submitting = ref(false);
const errorMsg = ref('');

const form = reactive({
    name: '',
    username: '',
    email: '',
    password: '',
    pin: '',
    role: 'cashier',
    outlet_ids: [],
    status: 'active',
});

watch(
    () => props.modelValue,
    (val) => {
        if (val) {
            errorMsg.value = '';
            if (props.employee) {
                form.name = props.employee.name || '';
                form.username = props.employee.username || '';
                form.email = props.employee.email || '';
                form.password = '';
                form.pin = '';
                form.role = props.employee.role || props.employee.roles?.[0] || 'cashier';
                form.outlet_ids = props.employee.outlets ? props.employee.outlets.map((o) => o.id) : [];
                form.status = props.employee.status || 'active';
            } else {
                form.name = '';
                form.username = '';
                form.email = '';
                form.password = '';
                form.pin = '';
                form.role = 'cashier';
                form.outlet_ids = props.outlets.length > 0 ? [props.outlets[0].id] : [];
                form.status = 'active';
            }
        }
    }
);

function close() {
    emit('update:modelValue', false);
}

function toggleOutlet(id) {
    const idx = form.outlet_ids.indexOf(id);
    if (idx > -1) {
        form.outlet_ids.splice(idx, 1);
    } else {
        form.outlet_ids.push(id);
    }
}

async function handleSubmit() {
    errorMsg.value = '';

    if (!form.name?.trim()) {
        errorMsg.value = 'Nama lengkap wajib diisi.';
        return;
    }
    if (!form.username?.trim()) {
        errorMsg.value = 'Username wajib diisi.';
        return;
    }
    if (!form.email?.trim()) {
        errorMsg.value = 'Alamat email wajib diisi.';
        return;
    }
    if (!isEdit.value && (!form.password || form.password.length < 6)) {
        errorMsg.value = 'Password minimal 6 karakter untuk akun baru.';
        return;
    }
    if (form.pin && (form.pin.length < 4 || form.pin.length > 6 || isNaN(form.pin))) {
        errorMsg.value = 'PIN kasir/dapur harus berupa 4-6 digit angka.';
        return;
    }

    const payload = {
        name: form.name.trim(),
        username: form.username.trim(),
        email: form.email.trim(),
        role: form.role,
        outlet_ids: form.outlet_ids,
        status: form.status,
    };

    if (form.password) {
        payload.password = form.password;
    }
    if (form.pin) {
        payload.pin = form.pin;
    }

    submitting.value = true;
    try {
        emit('saved', { payload, id: props.employee?.id || null });
    } catch (err) {
        errorMsg.value = err.response?.data?.message || err.customMessage || 'Gagal menyimpan data karyawan.';
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div
        v-if="modelValue"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div class="relative bg-white rounded-2xl shadow-xl border border-slate-200 max-w-xl w-full p-6 transition-all">
            <!-- Header -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl flex items-center justify-center"
                        :class="isEdit ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-600'"
                    >
                        <UserCheck v-if="isEdit" class="w-5 h-5" />
                        <UserPlus v-else class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            {{ isEdit ? 'Ubah Data Karyawan' : 'Tambah Karyawan Baru' }}
                        </h3>
                        <p class="text-xs text-slate-500">
                            {{ isEdit ? 'Perbarui informasi akun, role, dan hak akses cabang' : 'Daftarkan akun karyawan untuk akses POS, KDS, atau Web Admin' }}
                        </p>
                    </div>
                </div>
                <button
                    @click="close"
                    class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Error Banner -->
            <div
                v-if="errorMsg"
                class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl flex items-start gap-2.5 text-red-700 text-xs"
            >
                <AlertCircle class="w-4 h-4 text-red-600 shrink-0 mt-0.5" />
                <span>{{ errorMsg }}</span>
            </div>

            <form @submit.prevent="handleSubmit" class="mt-4 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Full Name -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Contoh: Budi Santoso"
                            class="w-full text-xs border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                            required
                        />
                    </div>

                    <!-- Username -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Username Login <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.username"
                            type="text"
                            placeholder="Contoh: budi.kasir"
                            class="w-full text-xs border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                            required
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="budi@seafood.com"
                            class="w-full text-xs border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                            required
                        />
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Role / Jabatan <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.role"
                            class="w-full text-xs border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 bg-white"
                            required
                        >
                            <option v-for="r in roles" :key="r.name" :value="r.name">
                                {{ r.label || r.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700">
                                Password <span v-if="!isEdit" class="text-red-500">*</span>
                            </label>
                            <span v-if="isEdit" class="text-[10px] text-slate-400">Kosongkan jika tidak diubah</span>
                        </div>
                        <input
                            v-model="form.password"
                            type="password"
                            :placeholder="isEdit ? '••••••••' : 'Minimal 6 karakter'"
                            class="w-full text-xs border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                            :required="!isEdit"
                        />
                    </div>

                    <!-- Quick PIN -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-700">
                                PIN Quick Login (4-6 Digit)
                            </label>
                            <span class="text-[10px] text-slate-400">Untuk Kasir/KDS</span>
                        </div>
                        <input
                            v-model="form.pin"
                            type="password"
                            maxlength="6"
                            placeholder="Contoh: 1234"
                            class="w-full text-xs border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 font-mono"
                        />
                    </div>
                </div>

                <!-- Assigned Outlets -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Penugasan Cabang / Outlet
                    </label>
                    <div class="grid grid-cols-2 gap-2 max-h-32 overflow-y-auto p-1">
                        <div
                            v-for="o in outlets"
                            :key="o.id"
                            @click="toggleOutlet(o.id)"
                            :class="[
                                'p-2 rounded-lg border text-xs cursor-pointer flex items-center justify-between transition-colors',
                                form.outlet_ids.includes(o.id)
                                    ? 'border-indigo-500 bg-indigo-50 text-indigo-900 font-semibold'
                                    : 'border-slate-200 hover:border-slate-300 text-slate-600',
                            ]"
                        >
                            <span>{{ o.name }}</span>
                            <span class="text-[10px] font-mono opacity-70">{{ o.code }}</span>
                        </div>
                    </div>
                </div>

                <!-- Status -->
                <div v-if="isEdit">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Status Akun
                    </label>
                    <select
                        v-model="form.status"
                        class="w-full text-xs border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 bg-white"
                    >
                        <option value="active">Aktif (Dapat Login)</option>
                        <option value="inactive">Nonaktif (Dinonaktifkan / Berhenti)</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button
                        type="button"
                        @click="close"
                        class="px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100 rounded-xl transition-colors"
                        :disabled="submitting"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors shadow-xs flex items-center gap-2"
                        :disabled="submitting"
                    >
                        <span v-if="submitting" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span>{{ submitting ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Daftarkan Karyawan') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

