<script setup>
import { ref, reactive, watch, computed } from 'vue';
import { Shield, X, AlertCircle, Check } from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    role: {
        type: Object,
        default: null,
    },
    groupedPermissions: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['update:modelValue', 'saved']);

const isEdit = computed(() => !!props.role);
const submitting = ref(false);
const errorMsg = ref('');

const form = reactive({
    name: '',
    permissions: [],
});

watch(
    () => props.modelValue,
    (val) => {
        if (val) {
            errorMsg.value = '';
            if (props.role) {
                form.name = props.role.label || props.role.name;
                form.permissions = props.role.permissions ? [...props.role.permissions] : [];
            } else {
                form.name = '';
                form.permissions = [];
            }
        }
    }
);

function close() {
    emit('update:modelValue', false);
}

function togglePermission(name) {
    const idx = form.permissions.indexOf(name);
    if (idx > -1) {
        form.permissions.splice(idx, 1);
    } else {
        form.permissions.push(name);
    }
}

function toggleCategory(categoryItems) {
    const names = categoryItems.map((i) => i.name);
    const allSelected = names.every((n) => form.permissions.includes(n));

    if (allSelected) {
        form.permissions = form.permissions.filter((p) => !names.includes(p));
    } else {
        names.forEach((n) => {
            if (!form.permissions.includes(n)) {
                form.permissions.push(n);
            }
        });
    }
}

function isCategorySelected(categoryItems) {
    const names = categoryItems.map((i) => i.name);
    return names.length > 0 && names.every((n) => form.permissions.includes(n));
}

async function handleSubmit() {
    errorMsg.value = '';

    if (!isEdit.value && !form.name?.trim()) {
        errorMsg.value = 'Nama role wajib diisi.';
        return;
    }

    submitting.value = true;
    try {
        const payload = {
            permissions: form.permissions,
        };
        if (!isEdit.value) {
            payload.name = form.name.trim();
        }

        emit('saved', { payload, id: props.role?.id || null });
    } catch (err) {
        errorMsg.value = err.response?.data?.message || err.customMessage || 'Gagal menyimpan role.';
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
        <div class="relative bg-white rounded-2xl shadow-xl border border-slate-200 max-w-2xl w-full p-6 transition-all max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <Shield class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            {{ isEdit ? `Atur Hak Akses: ${role.label}` : 'Buat Role Baru' }}
                        </h3>
                        <p class="text-xs text-slate-500">
                            Pilih hak akses fitur yang diizinkan untuk peran ini
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
                class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl flex items-start gap-2.5 text-red-700 text-xs shrink-0"
            >
                <AlertCircle class="w-4 h-4 text-red-600 shrink-0 mt-0.5" />
                <span>{{ errorMsg }}</span>
            </div>

            <!-- Form Content -->
            <form @submit.prevent="handleSubmit" class="mt-4 flex-1 overflow-y-auto space-y-5 pr-1">
                <!-- Role Name (if new) -->
                <div v-if="!isEdit">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Nama Role <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Contoh: Supervisor, Waiter, Purchasing"
                        class="w-full text-xs border border-slate-200 rounded-xl focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2"
                        required
                    />
                </div>

                <!-- Permission Groups -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Daftar Hak Akses (Permissions)</span>
                        <span class="text-xs text-indigo-600 font-semibold">
                            {{ form.permissions.length }} dipilih
                        </span>
                    </div>

                    <div
                        v-for="(items, category) in groupedPermissions"
                        :key="category"
                        class="border border-slate-200 rounded-xl p-3.5 bg-slate-50/50"
                    >
                        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200/60">
                            <span class="text-xs font-bold text-slate-900">{{ category }}</span>
                            <button
                                type="button"
                                @click="toggleCategory(items)"
                                class="text-[11px] font-medium text-indigo-600 hover:text-indigo-800"
                            >
                                {{ isCategorySelected(items) ? 'Batal Pilih Semua' : 'Pilih Semua' }}
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div
                                v-for="item in items"
                                :key="item.name"
                                @click="togglePermission(item.name)"
                                :class="[
                                    'p-2 rounded-lg border text-xs cursor-pointer flex items-center justify-between transition-colors',
                                    form.permissions.includes(item.name)
                                        ? 'border-indigo-500 bg-indigo-50/60 text-indigo-950 font-medium'
                                        : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700',
                                ]"
                            >
                                <div>
                                    <div class="text-[11px] font-semibold">{{ item.label }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono">{{ item.name }}</div>
                                </div>
                                <div
                                    :class="[
                                        'w-4 h-4 rounded flex items-center justify-center border transition-colors shrink-0 ml-2',
                                        form.permissions.includes(item.name)
                                            ? 'bg-indigo-600 border-indigo-600 text-white'
                                            : 'border-slate-300 bg-white',
                                    ]"
                                >
                                    <Check v-if="form.permissions.includes(item.name)" class="w-3 h-3 stroke-[2.5]" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5 shrink-0 sticky bottom-0 bg-white pb-1">
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
                        <span>{{ submitting ? 'Menyimpan...' : (isEdit ? 'Perbarui Hak Akses' : 'Simpan Role') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

