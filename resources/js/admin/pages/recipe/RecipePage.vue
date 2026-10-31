<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { ChefHat, Plus, Search, RefreshCw, Layers } from 'lucide-vue-next';
import { useRecipeStore } from '../../stores/recipe.store';
import RecipeTable from './components/RecipeTable.vue';
import RecipeFormModal from './components/RecipeFormModal.vue';

const router = useRouter();
const recipeStore = useRecipeStore();

const isModalOpen = ref(false);
const editingRecipe = ref(null);

onMounted(() => {
    recipeStore.fetchRecipes(1);
    recipeStore.fetchOptions();
});

function handleSearch() {
    recipeStore.fetchRecipes(1);
}

function openCreateModal() {
    editingRecipe.value = null;
    isModalOpen.value = true;
    recipeStore.fetchOptions();
}

function openEditModal(recipe) {
    editingRecipe.value = recipe;
    isModalOpen.value = true;
    recipeStore.fetchOptions();
}

function closeModal() {
    isModalOpen.value = false;
    editingRecipe.value = null;
}

async function handleFormSubmit(formData) {
    try {
        if (editingRecipe.value) {
            await recipeStore.updateRecipe(editingRecipe.value.id, formData);
        } else {
            await recipeStore.createRecipe(formData);
        }
        closeModal();
    } catch (err) {
        // error handled in store
    }
}

function viewDetail(recipe) {
    router.push(`/recipes/${recipe.id}`);
}

async function handleDelete(recipe) {
    if (confirm(`Apakah Anda yakin ingin menghapus resep '${recipe.name || recipe.menu_variant?.name}'?`)) {
        await recipeStore.deleteRecipe(recipe.id);
    }
}

function changePage(page) {
    if (page >= 1 && page <= recipeStore.pagination.last_page) {
        recipeStore.fetchRecipes(page);
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <ChefHat class="w-7 h-7 text-sky-600" />
                    Recipe Management (Resep & Food Cost)
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Kelola komposisi bahan baku menu, analisis persentase food cost, dan estimasi modal porsi
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button
                    @click="recipeStore.fetchRecipes(recipeStore.pagination.current_page)"
                    :disabled="recipeStore.loading"
                    class="p-2 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl shadow-sm transition"
                    title="Segarkan Data"
                >
                    <RefreshCw :class="['w-4 h-4', recipeStore.loading ? 'animate-spin' : '']" />
                </button>

                <button
                    @click="openCreateModal"
                    class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-sm font-bold shadow-sm transition inline-flex items-center gap-1.5"
                >
                    <Plus class="w-4 h-4" />
                    Tambah Resep
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-sm flex items-center justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    v-model="recipeStore.filters.search"
                    @keyup.enter="handleSearch"
                    type="text"
                    placeholder="Cari resep menu atau varian..."
                    class="w-full pl-9 pr-3 py-1.5 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500 focus:bg-white transition"
                />
            </div>
        </div>

        <!-- Table -->
        <RecipeTable
            :recipes="recipeStore.recipes"
            :loading="recipeStore.loading"
            @view="viewDetail"
            @edit="openEditModal"
            @delete="handleDelete"
        />

        <!-- Pagination -->
        <div
            v-if="recipeStore.pagination.last_page > 1"
            class="flex items-center justify-between bg-white px-4 py-3 rounded-2xl border border-slate-200/80 shadow-sm"
        >
            <div class="text-xs text-slate-500">
                Menampilkan halaman <span class="font-bold text-slate-700">{{ recipeStore.pagination.current_page }}</span> dari
                <span class="font-bold text-slate-700">{{ recipeStore.pagination.last_page }}</span>
                (Total {{ recipeStore.pagination.total }} resep)
            </div>

            <div class="flex items-center gap-2">
                <button
                    @click="changePage(recipeStore.pagination.current_page - 1)"
                    :disabled="recipeStore.pagination.current_page <= 1 || recipeStore.loading"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition"
                >
                    Sebelumnya
                </button>

                <button
                    @click="changePage(recipeStore.pagination.current_page + 1)"
                    :disabled="recipeStore.pagination.current_page >= recipeStore.pagination.last_page || recipeStore.loading"
                    class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition"
                >
                    Selanjutnya
                </button>
            </div>
        </div>

        <!-- Create / Edit Form Modal -->
        <RecipeFormModal
            :is-open="isModalOpen"
            :recipe="editingRecipe"
            :variants="recipeStore.options.variants"
            :ingredients="recipeStore.options.ingredients"
            :saving="recipeStore.saving"
            @close="closeModal"
            @submit="handleFormSubmit"
        />
    </div>
</template>

