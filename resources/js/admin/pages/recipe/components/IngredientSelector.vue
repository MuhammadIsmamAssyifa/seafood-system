<script setup>
import { computed } from 'vue';
import { Plus, Trash2, Package } from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: Array,
        required: true,
    },
    ingredients: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);

function addItem() {
    const nextIngredient = props.ingredients.find(
        (ing) => !props.modelValue.some((item) => item.ingredient_id === ing.id)
    );

    const newItem = {
        ingredient_id: nextIngredient ? nextIngredient.id : (props.ingredients[0]?.id || ''),
        quantity: 1,
        unit: nextIngredient ? nextIngredient.unit : 'kg',
    };

    emit('update:modelValue', [...props.modelValue, newItem]);
}

function removeItem(index) {
    const updated = props.modelValue.filter((_, i) => i !== index);
    emit('update:modelValue', updated);
}

function updateField(index, field, value) {
    const updated = [...props.modelValue];
    updated[index][field] = value;

    // Auto-sync default unit if ingredient changed
    if (field === 'ingredient_id') {
        const found = props.ingredients.find((ing) => ing.id === Number(value));
        if (found) {
            updated[index].unit = found.unit;
        }
    }

    emit('update:modelValue', updated);
}
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                Komposisi Bahan Baku (Recipe Ingredients) <span class="text-rose-500">*</span>
            </label>
            <button
                type="button"
                @click="addItem"
                class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 transition"
            >
                <Plus class="w-3.5 h-3.5" />
                Tambah Bahan
            </button>
        </div>

        <div v-if="!modelValue.length" class="p-6 bg-slate-50 border border-dashed border-slate-300 rounded-xl text-center text-xs text-slate-400">
            Belum ada bahan baku dalam resep ini. Klik tombol "Tambah Bahan" di atas.
        </div>

        <div v-else class="space-y-2">
            <div
                v-for="(item, index) in modelValue"
                :key="index"
                class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 p-2.5 bg-slate-50/80 border border-slate-200 rounded-xl"
            >
                <!-- Ingredient selector -->
                <div class="flex-1">
                    <select
                        :value="item.ingredient_id"
                        @change="updateField(index, 'ingredient_id', Number($event.target.value))"
                        class="w-full text-xs py-2 px-2.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 font-medium"
                    >
                        <option value="" disabled>Pilih Bahan Baku...</option>
                        <option
                            v-for="ing in ingredients"
                            :key="ing.id"
                            :value="ing.id"
                            :disabled="modelValue.some((row, rIdx) => rIdx !== index && row.ingredient_id === ing.id)"
                        >
                            {{ ing.name }} (Stok: {{ ing.unit }})
                        </option>
                    </select>
                </div>

                <!-- Quantity input -->
                <div class="w-full sm:w-32">
                    <input
                        type="number"
                        step="0.001"
                        min="0.001"
                        :value="item.quantity"
                        @input="updateField(index, 'quantity', parseFloat($event.target.value) || 0)"
                        placeholder="Jumlah"
                        class="w-full text-xs py-2 px-2.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 font-medium text-right"
                    />
                </div>

                <!-- Unit selector -->
                <div class="w-full sm:w-28">
                    <select
                        :value="item.unit"
                        @change="updateField(index, 'unit', $event.target.value)"
                        class="w-full text-xs py-2 px-2.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 font-medium"
                    >
                        <option value="kg">kg</option>
                        <option value="gram">gram</option>
                        <option value="liter">liter</option>
                        <option value="ml">ml</option>
                        <option value="pcs">pcs</option>
                    </select>
                </div>

                <!-- Delete row button -->
                <button
                    type="button"
                    @click="removeItem(index)"
                    class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition"
                    title="Hapus Bahan"
                >
                    <Trash2 class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>
</template>

