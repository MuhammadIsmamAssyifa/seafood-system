<script setup>
import { ref, watch } from 'vue';
import { Store, Check, Save } from 'lucide-vue-next';

const props = defineProps({
    employee: {
        type: Object,
        required: true,
    },
    allOutlets: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['save']);

const selectedOutletIds = ref([]);
const isDirty = ref(false);

watch(
    () => props.employee,
    (emp) => {
        if (emp?.outlets) {
            selectedOutletIds.value = emp.outlets.map((o) => o.id);
            isDirty.value = false;
        }
    },
    { immediate: true, deep: true }
);

function toggleOutlet(outletId) {
    const idx = selectedOutletIds.value.indexOf(outletId);
    if (idx > -1) {
        selectedOutletIds.value.splice(idx, 1);
    } else {
        selectedOutletIds.value.push(outletId);
    }
    isDirty.value = true;
}

function handleSave() {
    emit('save', selectedOutletIds.value);
    isDirty.value = false;
}
</script>

<template>
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <Store class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Penugasan Outlet (Cabang)</h3>
                    <p class="text-xs text-slate-500">Karyawan hanya dapat memproses order & stok pada cabang yang ditugaskan</p>
                </div>
            </div>

            <button
                v-if="isDirty"
                @click="handleSave"
                :disabled="loading"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg shadow-xs transition-colors"
            >
                <Save class="w-3.5 h-3.5" />
                <span>Simpan Perubahan</span>
            </button>
        </div>

        <div v-if="allOutlets.length === 0" class="py-4 text-center text-xs text-slate-400">
            Tidak ada cabang terdaftar.
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div
                v-for="outlet in allOutlets"
                :key="outlet.id"
                @click="toggleOutlet(outlet.id)"
                :class="[
                    'p-3.5 rounded-xl border cursor-pointer transition-all flex items-center justify-between',
                    selectedOutletIds.includes(outlet.id)
                        ? 'border-indigo-500 bg-indigo-50/40 text-indigo-950 font-medium'
                        : 'border-slate-200 hover:border-slate-300 bg-slate-50/50 text-slate-700',
                ]"
            >
                <div class="flex items-center gap-3">
                    <div
                        :class="[
                            'w-5 h-5 rounded-md flex items-center justify-center border transition-colors',
                            selectedOutletIds.includes(outlet.id)
                                ? 'bg-indigo-600 border-indigo-600 text-white'
                                : 'border-slate-300 bg-white',
                        ]"
                    >
                        <Check v-if="selectedOutletIds.includes(outlet.id)" class="w-3.5 h-3.5" />
                    </div>
                    <div>
                        <div class="text-xs font-semibold">{{ outlet.name }}</div>
                        <div class="text-[11px] text-slate-400 font-mono">{{ outlet.code }}</div>
                    </div>
                </div>

                <span
                    v-if="selectedOutletIds.includes(outlet.id)"
                    class="text-[10px] uppercase font-bold text-indigo-600 bg-indigo-100/60 px-2 py-0.5 rounded"
                >
                    Ditugaskan
                </span>
            </div>
        </div>
    </div>
</template>

