<script setup>
import { ref, reactive, watch, computed } from 'vue';
import { inventoryService } from '../../../services/inventory.service';
import { formatNumber, formatCurrency } from '../../../utils/formatter';
import { AlertCircle, Trash2, X } from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'saved']);

const ingredients = ref([]);
const loadingIngredients = ref(false);
const submitting = ref(false);
const errorMsg = ref('');

const form = reactive({
    ingredient_id: '',
    quantity: '',
    reason: 'damaged',
    note: '',
});

const reasons = [
    { value: 'damaged', label: 'Rusak / Busuk (Damaged)' },
    { value: 'expired', label: 'Kadaluarsa / Basi (Expired)' },
    { value: 'cooking_loss', label: 'Susut Masak / Handling (Cooking Loss)' },
    { value: 'wrong_preparation', label: 'Salah Olah / Resep (Wrong Preparation)' },
    { value: 'other', label: 'Lainnya (Other)' },
];

const selectedIngredient = computed(() => {
    if (!form.ingredient_id) return null;
    return ingredients.value.find((i) => i.id === Number(form.ingredient_id)) || null;
});

const isQuantityExceeded = computed(() => {
    if (!selectedIngredient.value || !form.quantity) return false;
    const currentStock = Number(selectedIngredient.value.current_stock || 0);
    return Number(form.quantity) > currentStock;
});

async function loadIngredients() {
    loadingIngredients.value = true;
    try {
        const res = await inventoryService.getIngredients();
        ingredients.value = res.data || [];
    } catch (e) {
        console.error('Failed to load ingredients for waste modal', e);
    } finally {
        loadingIngredients.value = false;
    }
}

watch(
    () => props.modelValue,
    (val) => {
        if (val) {
            form.ingredient_id = '';
            form.quantity = '';
            form.reason = 'damaged';
            form.note = '';
            errorMsg.value = '';
            loadIngredients();
        }
    }
);

function close() {
    emit('update:modelValue', false);
}

async function handleSubmit() {
    errorMsg.value = '';

    if (!form.ingredient_id) {
        errorMsg.value = 'Silakan pilih bahan baku terlebih dahulu.';
        return;
    }

    if (!form.quantity || Number(form.quantity) <= 0) {
        errorMsg.value = 'Kuantitas bahan terbuang harus lebih besar dari 0.';
        return;
    }

    if (isQuantityExceeded.value) {
        errorMsg.value = `Kuantitas melebihi stok yang tersedia (${selectedIngredient.value.current_stock} ${selectedIngredient.value.unit}).`;
        return;
    }

    submitting.value = true;
    try {
        emit('saved', {
            ingredient_id: Number(form.ingredient_id),
            quantity: Number(form.quantity),
            reason: form.reason,
            note: form.note?.trim() || null,
        });
    } catch (err) {
        errorMsg.value = err.response?.data?.message || err.customMessage || 'Gagal menyimpan catatan waste.';
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
        <div class="relative bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full p-6 transition-all">
            <!-- Header -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                        <Trash2 class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Catat Bahan Terbuang (Waste)</h3>
                        <p class="text-xs text-slate-500">Stok akan otomatis dikurangi dan dicatat dalam audit loss.</p>
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
                <!-- Select Ingredient -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Pilih Bahan Baku <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.ingredient_id"
                        class="w-full text-sm border-slate-200 rounded-xl focus:border-red-500 focus:ring-red-500 bg-white"
                        :disabled="loadingIngredients || submitting"
                    >
                        <option value="" disabled>-- Pilih Bahan Baku --</option>
                        <option
                            v-for="ing in ingredients"
                            :key="ing.id"
                            :value="ing.id"
                        >
                            {{ ing.name }} (Stok: {{ formatNumber(ing.current_stock, 3) }} {{ ing.unit }})
                        </option>
                    </select>

                    <!-- Stock Info Helper -->
                    <div v-if="selectedIngredient" class="mt-1.5 flex items-center justify-between text-xs px-1">
                        <span class="text-slate-500">
                            Stok Tersedia:
                            <strong class="text-slate-800">{{ formatNumber(selectedIngredient.current_stock, 3) }} {{ selectedIngredient.unit }}</strong>
                        </span>
                        <span class="text-slate-500">
                            Satuan: <strong class="text-slate-800 uppercase">{{ selectedIngredient.unit }}</strong>
                        </span>
                    </div>
                </div>

                <!-- Quantity -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-700">
                            Jumlah Terbuang <span class="text-red-500">*</span>
                        </label>
                        <span v-if="selectedIngredient" class="text-xs text-slate-400">
                            Satuan: {{ selectedIngredient.unit }}
                        </span>
                    </div>
                    <div class="relative">
                        <input
                            v-model="form.quantity"
                            type="number"
                            step="0.001"
                            min="0.001"
                            placeholder="Contoh: 1.500"
                            class="w-full text-sm border-slate-200 rounded-xl focus:border-red-500 focus:ring-red-500 pr-14"
                            :class="{ 'border-red-400 bg-red-50/30': isQuantityExceeded }"
                            :disabled="submitting"
                        />
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-xs text-slate-400 font-medium">
                            {{ selectedIngredient?.unit || 'unit' }}
                        </div>
                    </div>
                    <p v-if="isQuantityExceeded" class="text-xs text-red-600 mt-1">
                        Jumlah melebihi stok yang tersedia saat ini!
                    </p>
                </div>

                <!-- Reason -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Alasan / Penyebab Waste <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.reason"
                        class="w-full text-sm border-slate-200 rounded-xl focus:border-red-500 focus:ring-red-500 bg-white"
                        :disabled="submitting"
                    >
                        <option
                            v-for="r in reasons"
                            :key="r.value"
                            :value="r.value"
                        >
                            {{ r.label }}
                        </option>
                    </select>
                </div>

                <!-- Note -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        Catatan Kejadian (Opsional)
                    </label>
                    <textarea
                        v-model="form.note"
                        rows="2"
                        placeholder="Keterangan tambahan (misal: suhu freezer naik saat malam hari)..."
                        class="w-full text-sm border-slate-200 rounded-xl focus:border-red-500 focus:ring-red-500"
                        :disabled="submitting"
                    ></textarea>
                </div>

                <!-- Footer Actions -->
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
                        class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors shadow-xs flex items-center gap-2 disabled:opacity-50"
                        :disabled="submitting || isQuantityExceeded || !form.ingredient_id || !form.quantity"
                    >
                        <span v-if="submitting" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span>{{ submitting ? 'Menyimpan...' : 'Catat Waste Sekarang' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

