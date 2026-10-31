<script setup>
import { ref, watch, computed } from 'vue';
import { X, Building2, Save, AlertCircle, Phone, MapPin, Tag } from 'lucide-vue-next';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    outlet: {
        type: Object,
        default: null,
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'submit']);

const form = ref({
    name: '',
    code: '',
    address: '',
    phone: '',
    status: 'active',
});

const error = ref(null);
const isEditing = computed(() => !!props.outlet?.id);

watch(
    () => props.outlet,
    (val) => {
        if (val) {
            form.value = {
                name: val.name || '',
                code: val.code || '',
                address: val.address || '',
                phone: val.phone || '',
                status: val.status || 'active',
            };
        } else {
            form.value = {
                name: '',
                code: '',
                address: '',
                phone: '',
                status: 'active',
            };
        }
        error.value = null;
    },
    { immediate: true }
);

function close() {
    emit('close');
}

function submit() {
    error.value = null;

    if (!form.value.name.trim()) {
        error.value = 'Nama outlet cabang wajib diisi.';
        return;
    }

    if (!form.value.code.trim()) {
        error.value = 'Kode cabang wajib diisi.';
        return;
    }

    emit('submit', { ...form.value });
}
</script>

<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
        @click.self="close"
    >
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <!-- Header -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                        <Building2 class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">
                            {{ isEditing ? 'Edit Data Cabang' : 'Tambah Cabang Baru' }}
                        </h3>
                        <p class="text-xs text-slate-500">Kelola identitas outlet operasional restoran</p>
                    </div>
                </div>
                <button
                    @click="close"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 transition"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Body -->
            <form @submit.prevent="submit" class="p-6 space-y-4">
                <!-- Error Alert -->
                <div v-if="error" class="p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs flex items-center gap-2">
                    <AlertCircle class="w-4 h-4 flex-shrink-0" />
                    <span>{{ error }}</span>
                </div>

                <!-- Outlet Name -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nama Cabang / Outlet <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Contoh: Seafood Bahari Jakarta Selatan"
                        class="w-full text-sm py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 font-medium"
                    />
                </div>

                <!-- Outlet Code -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Kode Unik Cabang <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <Tag class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input
                            v-model="form.code"
                            type="text"
                            placeholder="Contoh: JKT-01"
                            class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 font-mono uppercase"
                        />
                    </div>
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nomor Telepon Cabang
                    </label>
                    <div class="relative">
                        <Phone class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input
                            v-model="form.phone"
                            type="text"
                            placeholder="Contoh: 021-7654321"
                            class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500"
                        />
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Alamat Lengkap Cabang
                    </label>
                    <div class="relative">
                        <MapPin class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                        <textarea
                            v-model="form.address"
                            rows="2"
                            placeholder="Contoh: Jl. Fatmawati Raya No. 45"
                            class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500"
                        ></textarea>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Status Operasional
                    </label>
                    <select
                        v-model="form.status"
                        class="w-full text-sm py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 font-medium"
                    >
                        <option value="active">Aktif (Beroperasi)</option>
                        <option value="inactive">Nonaktif (Tutup Sementara)</option>
                    </select>
                </div>

                <!-- Footer Actions -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        @click="close"
                        class="px-4 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="px-5 py-2 text-xs font-bold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition disabled:opacity-50 inline-flex items-center gap-1.5"
                    >
                        <span v-if="saving" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <Save v-else class="w-3.5 h-3.5" />
                        Simpan Cabang
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

