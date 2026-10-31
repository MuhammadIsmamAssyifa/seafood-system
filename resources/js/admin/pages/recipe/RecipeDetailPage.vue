<script setup>
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ArrowLeft, ChefHat, Edit, Trash2, Package, Clock, FileText } from 'lucide-vue-next';
import { useRecipeStore } from '../../stores/recipe.store';
import RecipeCostCard from './components/RecipeCostCard.vue';
import RecipeFormModal from './components/RecipeFormModal.vue';
import { formatCurrency, formatDate } from '../../utils/formatter';

const route = useRoute();
const router = useRouter();
const recipeStore = useRecipeStore();

const isEditModalOpen = ref(false);

onMounted(async () => {
    const id = route.params.id;
    await recipeStore.fetchDetail(id);
    await recipeStore.fetchOptions();
});

function goBack() {
    router.push('/recipes');
}

function openEdit() {
    isEditModalOpen.value = true;
}

function closeEdit() {
    isEditModalOpen.value = false;
}

async function handleUpdate(formData) {
    try {
        await recipeStore.updateRecipe(recipeStore.selectedRecipe.id, formData);
        await recipeStore.fetchDetail(recipeStore.selectedRecipe.id);
        closeEdit();
    } catch (err) {
        // handled in store
    }
}

async function handleDelete() {
    if (confirm(`Apakah Anda yakin ingin menghapus resep '${recipeStore.selectedRecipe?.name}'?`)) {
        await recipeStore.deleteRecipe(recipeStore.selectedRecipe.id);
        router.push('/recipes');
    }
}
</script>

<template>
    <div class="space-y-6 max-w-5xl mx-auto pb-16">
        <!-- Top Navigation & Actions -->
        <div class="flex items-center justify-between">
            <button
                @click="goBack"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition"
            >
                <ArrowLeft class="w-4 h-4" />
                Kembali ke Daftar Resep
            </button>

            <div v-if="recipeStore.selectedRecipe" class="flex items-center gap-2">
                <button
                    @click="openEdit"
                    class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 transition inline-flex items-center gap-1.5"
                >
                    <Edit class="w-3.5 h-3.5" />
                    Edit Resep
                </button>
                <button
                    @click="handleDelete"
                    class="px-3.5 py-1.5 text-xs font-bold rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition inline-flex items-center gap-1.5"
                >
                    <Trash2 class="w-3.5 h-3.5" />
                    Hapus
                </button>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="recipeStore.detailLoading" class="p-12 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
            <span class="w-5 h-5 border-2 border-sky-500 border-t-transparent rounded-full animate-spin inline-block mb-2"></span>
            <p class="text-xs">Memuat rincian resep & biaya bahan...</p>
        </div>

        <div v-else-if="recipeStore.selectedRecipe" class="space-y-6">
            <!-- Header Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                        <ChefHat class="w-7 h-7" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">
                            {{ recipeStore.selectedRecipe.name }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-500">
                            <span class="font-semibold text-slate-700">
                                Menu: {{ recipeStore.selectedRecipe.menu_variant?.menu_name }}
                            </span>
                            <span>•</span>
                            <span>Varian: {{ recipeStore.selectedRecipe.menu_variant?.name }}</span>
                            <span>•</span>
                            <span>Cabang: {{ recipeStore.selectedRecipe.menu_variant?.outlet_name || 'Cabang Utama' }}</span>
                        </div>
                    </div>
                </div>

                <div class="text-xs text-slate-400 flex items-center gap-1.5">
                    <Clock class="w-4 h-4" />
                    Dibuat: {{ formatDate(recipeStore.selectedRecipe.created_at) }}
                </div>
            </div>

            <!-- Cost Summary Cards -->
            <RecipeCostCard
                v-if="recipeStore.selectedRecipe.cost_calculation"
                :cost-data="recipeStore.selectedRecipe.cost_calculation"
            />

            <!-- Ingredients Breakdown Table -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Rincian Komposisi Bahan Baku</h3>
                        <p class="text-xs text-slate-500">Takaran taksiran dan biaya per bahan berdasarkan batch inventori aktif</p>
                    </div>
                    <span class="text-xs font-bold text-slate-600 bg-white px-2.5 py-1 rounded-lg border border-slate-200">
                        {{ recipeStore.selectedRecipe.cost_calculation?.items?.length || 0 }} Bahan
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50/50 text-xs font-semibold uppercase text-slate-500 border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-6">Nama Bahan Baku</th>
                                <th class="py-3 px-4 text-right">Takaran per Porsi</th>
                                <th class="py-3 px-4 text-right">Harga Modal per Satuan</th>
                                <th class="py-3 px-6 text-right">Subtotal Biaya</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <tr
                                v-for="item in recipeStore.selectedRecipe.cost_calculation?.items"
                                :key="item.id"
                                class="hover:bg-slate-50/80 transition"
                            >
                                <td class="py-3 px-6 font-semibold text-slate-800 flex items-center gap-2">
                                    <Package class="w-3.5 h-3.5 text-slate-400" />
                                    {{ item.ingredient_name }}
                                </td>
                                <td class="py-3 px-4 text-right font-medium">
                                    {{ item.quantity }} {{ item.unit }}
                                </td>
                                <td class="py-3 px-4 text-right font-medium text-slate-500">
                                    {{ formatCurrency(item.unit_cost) }} / {{ item.ingredient_unit }}
                                </td>
                                <td class="py-3 px-6 text-right font-bold text-slate-800">
                                    {{ formatCurrency(item.subtotal_cost) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50 font-bold text-xs text-slate-800">
                            <tr>
                                <td colspan="3" class="py-3 px-6 text-right uppercase">Total Modal Bahan (Food Cost):</td>
                                <td class="py-3 px-6 text-right font-extrabold text-amber-600 text-sm">
                                    {{ formatCurrency(recipeStore.selectedRecipe.cost_calculation?.total_cost || 0) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Description / Cooking Instructions -->
            <div v-if="recipeStore.selectedRecipe.description" class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm space-y-2">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                    <FileText class="w-3.5 h-3.5" />
                    Catatan Resep & Instruksi Memasak
                </h4>
                <p class="text-sm text-slate-700 leading-relaxed font-normal">
                    {{ recipeStore.selectedRecipe.description }}
                </p>
            </div>
        </div>

        <!-- Edit Modal -->
        <RecipeFormModal
            :is-open="isEditModalOpen"
            :recipe="recipeStore.selectedRecipe"
            :variants="recipeStore.options.variants"
            :ingredients="recipeStore.options.ingredients"
            :saving="recipeStore.saving"
            @close="closeEdit"
            @submit="handleUpdate"
        />
    </div>
</template>
