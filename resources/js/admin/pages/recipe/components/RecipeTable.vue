<script setup>
import { Eye, Edit, Trash2, ChefHat, DollarSign } from 'lucide-vue-next';
import FoodCostBadge from './FoodCostBadge.vue';
import { formatCurrency } from '../../../utils/formatter';

const props = defineProps({
    recipes: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['view', 'edit', 'delete']);
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                    <tr>
                        <th class="py-3 px-4">Menu & Varian</th>
                        <th class="py-3 px-4">Jumlah Bahan</th>
                        <th class="py-3 px-4 text-right">Modal Bahan (Cost)</th>
                        <th class="py-3 px-4 text-right">Harga Jual</th>
                        <th class="py-3 px-4 text-center">Food Cost %</th>
                        <th class="py-3 px-4 text-right">Margin Keuntungan</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <!-- Loading state -->
                    <tr v-if="loading">
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <div class="inline-flex items-center gap-2">
                                <span class="w-4 h-4 border-2 border-sky-500 border-t-transparent rounded-full animate-spin"></span>
                                Memuat daftar resep menu...
                            </div>
                        </td>
                    </tr>

                    <!-- Empty state -->
                    <tr v-else-if="!recipes.length">
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            Belum ada data resep menu. Buat resep baru dengan klik tombol "Tambah Resep".
                        </td>
                    </tr>

                    <!-- Data rows -->
                    <tr
                        v-for="recipe in recipes"
                        :key="recipe.id"
                        class="hover:bg-slate-50/80 transition"
                    >
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                                    <ChefHat class="w-4 h-4" />
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800 text-sm block">
                                        {{ recipe.menu_variant?.menu_name || recipe.name }}
                                    </span>
                                    <span class="text-xs text-slate-400">
                                        Varian: {{ recipe.menu_variant?.name || '-' }}
                                    </span>
                                </div>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                {{ recipe.items_count || (recipe.items ? recipe.items.length : 0) }} Bahan
                            </span>
                        </td>

                        <td class="py-3.5 px-4 whitespace-nowrap text-right font-bold text-slate-800">
                            {{ formatCurrency(recipe.cost_calculation?.total_cost || 0) }}
                        </td>

                        <td class="py-3.5 px-4 whitespace-nowrap text-right text-slate-600 font-medium">
                            {{ formatCurrency(recipe.cost_calculation?.selling_price || recipe.menu_variant?.price || 0) }}
                        </td>

                        <td class="py-3.5 px-4 whitespace-nowrap text-center">
                            <FoodCostBadge :percentage="recipe.cost_calculation?.food_cost_percentage || 0" />
                        </td>

                        <td class="py-3.5 px-4 whitespace-nowrap text-right font-bold text-emerald-600">
                            {{ formatCurrency(recipe.cost_calculation?.gross_margin || 0) }}
                        </td>

                        <td class="py-3.5 px-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button
                                    @click="emit('view', recipe)"
                                    class="p-1.5 rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 transition"
                                    title="Rincian Resep"
                                >
                                    <Eye class="w-4 h-4" />
                                </button>
                                <button
                                    @click="emit('edit', recipe)"
                                    class="p-1.5 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 transition"
                                    title="Edit Resep"
                                >
                                    <Edit class="w-4 h-4" />
                                </button>
                                <button
                                    @click="emit('delete', recipe)"
                                    class="p-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition"
                                    title="Hapus Resep"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

