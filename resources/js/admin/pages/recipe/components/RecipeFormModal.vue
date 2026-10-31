<script setup>
import { ref, watch, computed } from 'vue';
import { X, ChefHat, Save, AlertCircle } from 'lucide-vue-next';
import IngredientSelector from './IngredientSelector.vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    recipe: {
        type: Object,
        default: null,
    },
    variants: {
        type: Array,
        default: () => [],
    },
    ingredients: {
        type: Array,
        default: () => [],
    },
    saving: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'submit']);

const form = ref({
    menu_variant_id: '',
    name: '',
    description: '',
    items: [],
});

const error = ref(null);
const isEditing = computed(() => !!props.recipe?.id);

watch(
    () => props.recipe,
    (val) => {
        if (val) {
            form.value = {
                menu_variant_id: val.menu_variant?.id || val.menu_variant_id || '',
                name: val.name || '',
                description: val.description || '',
                items: val.items ? val.items.map((i) => ({
                    ingredient_id: i.ingredient_id,
                    quantity: Number(i.quantity),
                    unit: i.unit,
                })) : [],
            };
        } else {
            form.value = {
                menu_variant_id: '',
                name: '',
                description: '',
                items: [],
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

    if (!isEditing.value && !form.value.menu_variant_id) {
        error.value = 'Silakan pilih varian menu terlebih dahulu.';
        return;
    }

    if (!form.value.items.length) {
        error.value = 'Resep wajib memiliki minimal 1 bahan baku.';
        return;
    }

    for (const item of form.value.items) {
        if (!item.ingredient_id) {
            error.value = 'Semua baris bahan baku wajib dipilih bahannya.';
            return;
        }
        if (!item.quantity || item.quantity <= 0) {
            error.value = 'Jumlah takaran setiap bahan harus lebih besar dari 0.';
            return;
        }
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
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <!-- Header -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                        <ChefHat class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">
                            {{ isEditing ? 'Edit Resep Menu' : 'Tambah Resep Menu Baru' }}
                        </h3>
                        <p class="text-xs text-slate-500">Tentukan takaran bahan baku untuk menghitung modal & margin</p>
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
            <form @submit.prevent="submit" class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
                <!-- Error Alert -->
                <div v-if="error" class="p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs flex items-center gap-2">
                    <AlertCircle class="w-4 h-4 flex-shrink-0" />
                    <span>{{ error }}</span>
                </div>

                <!-- Menu Variant Selector (Only on create) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Pilih Varian Menu <span class="text-rose-500">*</span>
                    </label>
                    <select
                        v-if="!isEditing"
                        v-model="form.menu_variant_id"
                        class="w-full text-sm py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 font-medium"
                    >
                        <option value="" disabled>Pilih varian menu yang akan dibuatkan resep...</option>
                        <option
                            v-for="v in variants"
                            :key="v.id"
                            :value="v.id"
                            :disabled="v.has_recipe"
                        >
                            {{ v.name }} {{ v.has_recipe ? '(Sudah Ada Resep)' : '' }}
                        </option>
                    </select>

                    <div v-else class="p-3 bg-slate-100 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700">
                        {{ recipe.menu_variant?.menu_name }} - {{ recipe.menu_variant?.name }}
                    </div>
                </div>

                <!-- Recipe Name -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nama / Judul Resep
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Contoh: Resep Standar Cumi Saus Padang"
                        class="w-full text-sm py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 font-medium"
                    />
                </div>

                <!-- Description / Notes -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Catatan & Cara Masak
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="2"
                        placeholder="Catatan tambahan takaran bumbu khusus atau instruksi memasak..."
                        class="w-full text-sm py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500"
                    ></textarea>
                </div>

                <!-- Ingredients Component -->
                <IngredientSelector
                    v-model="form.items"
                    :ingredients="ingredients"
                />

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
                        Simpan Resep
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

