<script setup>
import { reactive, watch, ref } from 'vue';
import { PlusCircle, MinusCircle, AlertCircle } from 'lucide-vue-next';
import { useNotificationStore } from '../../../stores/notification.store';
import BaseModal from '../../../components/common/BaseModal.vue';
import BaseButton from '../../../components/common/BaseButton.vue';
import BaseInput from '../../../components/common/BaseInput.vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    ingredients: {
        type: Array,
        default: () => [],
    },
    initialIngredient: {
        type: Object,
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'submit']);

const notificationStore = useNotificationStore();

const form = reactive({
    ingredient_id: '',
    type: 'in', // 'in' or 'out'
    quantity: '',
    cost_per_unit: '',
    note: '',
});

const error = ref('');

watch(
    () => props.initialIngredient,
    (val) => {
        if (val) {
            form.ingredient_id = val.id;
        }
    },
    { immediate: true }
);

function close() {
    emit('update:modelValue', false);
    error.value = '';
}

function handleSubmit() {
    error.value = '';
    if (!form.ingredient_id) {
        error.value = 'Silakan pilih bahan baku.';
        return;
    }
    const qty = parseFloat(form.quantity);
    if (isNaN(qty) || qty <= 0) {
        error.value = 'Jumlah kuantitas harus lebih dari 0.';
        return;
    }

    emit('submit', {
        ingredient_id: Number(form.ingredient_id),
        type: form.type,
        quantity: qty,
        cost_per_unit: form.cost_per_unit ? parseFloat(form.cost_per_unit) : null,
        note: form.note.trim() || undefined,
    });
}
</script>

<template>
    <BaseModal
        :model-value="modelValue"
        @update:model-value="emit('update:modelValue', $event)"
        title="Penyesuaian Stok Bahan Baku"
        size="md"
        @close="close"
    >
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- Error Banner -->
            <div v-if="error" class="p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                <AlertCircle class="w-4 h-4 shrink-0" />
                <span>{{ error }}</span>
            </div>

            <!-- Ingredient Select -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Bahan Baku <span class="text-rose-500">*</span>
                </label>
                <select
                    v-model="form.ingredient_id"
                    class="block w-full rounded-lg border border-slate-300 py-2.5 px-3 text-sm text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 cursor-pointer"
                >
                    <option value="" disabled>-- Pilih Bahan Baku --</option>
                    <option v-for="item in ingredients" :key="item.id" :value="item.id">
                        {{ item.name }} (Sisa: {{ item.current_stock }} {{ item.unit }})
                    </option>
                </select>
            </div>

            <!-- Type: IN or OUT -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Jenis Penyesuaian <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        @click="form.type = 'in'"
                        class="p-3 rounded-xl border flex items-center justify-center gap-2 text-xs font-bold transition-all cursor-pointer"
                        :class="[
                            form.type === 'in'
                                ? 'bg-emerald-50 border-emerald-500 text-emerald-800 shadow-2xs'
                                : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100',
                        ]"
                    >
                        <PlusCircle class="w-4 h-4 text-emerald-600" />
                        <span>Stok Masuk (+ Restock)</span>
                    </button>

                    <button
                        type="button"
                        @click="form.type = 'out'"
                        class="p-3 rounded-xl border flex items-center justify-center gap-2 text-xs font-bold transition-all cursor-pointer"
                        :class="[
                            form.type === 'out'
                                ? 'bg-rose-50 border-rose-500 text-rose-800 shadow-2xs'
                                : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100',
                        ]"
                    >
                        <MinusCircle class="w-4 h-4 text-rose-600" />
                        <span>Stok Keluar (- Koreksi)</span>
                    </button>
                </div>
            </div>

            <!-- Quantity (supports decimal) -->
            <BaseInput
                v-model="form.quantity"
                label="Jumlah Kuantitas (kg / unit)"
                type="number"
                step="0.001"
                placeholder="Contoh: 2.5 atau 10"
                hint="Dukungan nilai desimal untuk bahan timbangan (misal: 1.250 kg)"
                required
            />

            <!-- Cost per unit (visible for IN) -->
            <BaseInput
                v-if="form.type === 'in'"
                v-model="form.cost_per_unit"
                label="Harga Beli Satuan (Rp / unit)"
                type="number"
                placeholder="Contoh: 85000"
                hint="Opsional. Jika kosong, sistem menggunakan harga batch terakhir"
            />

            <!-- Note -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Alasan / Catatan Penyesuaian
                </label>
                <textarea
                    v-model="form.note"
                    rows="2"
                    placeholder="Contoh: Pembelian pasar ikan tambahan, stock opname bulanan, bahan rusak/tumpah"
                    class="block w-full rounded-lg border border-slate-300 py-2 px-3 text-sm text-slate-900 bg-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600"
                ></textarea>
            </div>
        </form>

        <template #footer>
            <BaseButton variant="outline" size="sm" @click="close" :disabled="loading">
                Batal
            </BaseButton>
            <BaseButton
                :variant="form.type === 'in' ? 'success' : 'danger'"
                size="sm"
                @click="handleSubmit"
                :loading="loading"
            >
                Simpan Penyesuaian
            </BaseButton>
        </template>
    </BaseModal>
</template>

