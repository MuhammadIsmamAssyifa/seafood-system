<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import {
    Search,
    X,
    ShoppingBag,
    UtensilsCrossed,
    Boxes,
    Users,
    ChefHat,
    Building2,
    Loader2,
    ArrowRight,
    CornerDownLeft,
} from 'lucide-vue-next';
import { searchService } from '../../services/search.service';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const router = useRouter();
const query = ref('');
const loading = ref(false);
const results = ref({
    orders: [],
    menus: [],
    inventory: [],
    employees: [],
    recipes: [],
    outlets: [],
});
const totalResults = ref(0);
const searchInput = ref(null);
let debounceTimeout = null;

watch(
    () => props.modelValue,
    (val) => {
        if (val) {
            nextTick(() => {
                searchInput.value?.focus();
            });
        } else {
            query.value = '';
            results.value = { orders: [], menus: [], inventory: [], employees: [], recipes: [], outlets: [] };
            totalResults.value = 0;
        }
    }
);

function handleInput() {
    clearTimeout(debounceTimeout);
    if (query.value.trim().length < 2) {
        results.value = { orders: [], menus: [], inventory: [], employees: [], recipes: [], outlets: [] };
        totalResults.value = 0;
        loading.value = false;
        return;
    }

    loading.value = true;
    debounceTimeout = setTimeout(async () => {
        try {
            const data = await searchService.search(query.value.trim());
            results.value = data.results || {};
            totalResults.value = data.total_results || 0;
        } catch (err) {
            console.error('Search error:', err);
        } finally {
            loading.value = false;
        }
    }, 250);
}

function close() {
    emit('update:modelValue', false);
}

function navigateTo(url) {
    close();
    router.push(url);
}

function handleKeydown(e) {
    if (e.key === 'Escape') {
        close();
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    clearTimeout(debounceTimeout);
});
</script>

<template>
    <div
        v-if="modelValue"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-start justify-center p-4 sm:p-6 md:p-12"
        @click.self="close"
    >
        <div
            class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transition-all duration-200"
            role="dialog"
            aria-modal="true"
        >
            <!-- Search Bar Header -->
            <div class="relative flex items-center px-4 py-3.5 border-b border-slate-100 bg-slate-50/50">
                <Search class="w-5 h-5 text-slate-400 shrink-0 mr-3" />
                <input
                    ref="searchInput"
                    v-model="query"
                    @input="handleInput"
                    type="text"
                    placeholder="Cari order, menu, stok bahan, karyawan, resep, atau cabang..."
                    class="w-full bg-transparent text-sm text-slate-800 placeholder-slate-400 focus:outline-none"
                />
                <Loader2 v-if="loading" class="w-4 h-4 text-blue-600 animate-spin shrink-0 mr-2" />
                <button
                    type="button"
                    @click="close"
                    class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 transition-colors"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>

            <!-- Search Content Area -->
            <div class="max-h-[65vh] overflow-y-auto p-4 space-y-4">
                <!-- Hint State when query < 2 chars -->
                <div v-if="query.trim().length < 2" class="py-8 text-center text-slate-400">
                    <Search class="w-8 h-8 mx-auto mb-2 opacity-40" />
                    <p class="text-xs font-medium">Ketik minimal 2 karakter untuk mencari</p>
                    <p class="text-[11px] text-slate-400 mt-1">Tekan <kbd class="px-1.5 py-0.5 bg-slate-100 border border-slate-300 rounded text-slate-600 text-[10px] font-mono">ESC</kbd> untuk menutup</p>
                </div>

                <!-- Empty Result State -->
                <div v-else-if="!loading && totalResults === 0" class="py-10 text-center">
                    <p class="text-sm font-semibold text-slate-700">Tidak ada hasil ditemukan</p>
                    <p class="text-xs text-slate-400 mt-1">Coba kata kunci lain atau periksa kembali ejaan Anda.</p>
                </div>

                <!-- Results Group: Orders -->
                <div v-if="results.orders && results.orders.length > 0">
                    <div class="flex items-center gap-2 mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                        <ShoppingBag class="w-3.5 h-3.5 text-blue-500" />
                        <span>Pesanan ({{ results.orders.length }})</span>
                    </div>
                    <div class="divide-y divide-slate-100 rounded-xl border border-slate-100 bg-white overflow-hidden shadow-2xs">
                        <div
                            v-for="item in results.orders"
                            :key="item.id"
                            @click="navigateTo(item.url)"
                            class="p-3 hover:bg-blue-50/50 cursor-pointer flex items-center justify-between group transition-colors"
                        >
                            <div>
                                <h5 class="text-xs font-bold text-slate-900 group-hover:text-blue-600 transition-colors">{{ item.title }}</h5>
                                <p class="text-[11px] text-slate-500 mt-0.5">{{ item.subtitle }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs font-semibold text-slate-700">{{ item.meta }}</span>
                                <ArrowRight class="w-3.5 h-3.5 text-slate-300 group-hover:text-blue-500 group-hover:translate-x-0.5 transition-all" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Group: Menus -->
                <div v-if="results.menus && results.menus.length > 0">
                    <div class="flex items-center gap-2 mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                        <UtensilsCrossed class="w-3.5 h-3.5 text-amber-500" />
                        <span>Menu & Varian ({{ results.menus.length }})</span>
                    </div>
                    <div class="divide-y divide-slate-100 rounded-xl border border-slate-100 bg-white overflow-hidden shadow-2xs">
                        <div
                            v-for="item in results.menus"
                            :key="item.id"
                            @click="navigateTo(item.url)"
                            class="p-3 hover:bg-amber-50/50 cursor-pointer flex items-center justify-between group transition-colors"
                        >
                            <div>
                                <h5 class="text-xs font-bold text-slate-900 group-hover:text-amber-600 transition-colors">{{ item.title }}</h5>
                                <p class="text-[11px] text-slate-500 mt-0.5">{{ item.subtitle }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs font-semibold text-slate-700">{{ item.meta }}</span>
                                <ArrowRight class="w-3.5 h-3.5 text-slate-300 group-hover:text-amber-500 group-hover:translate-x-0.5 transition-all" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Group: Inventory -->
                <div v-if="results.inventory && results.inventory.length > 0">
                    <div class="flex items-center gap-2 mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                        <Boxes class="w-3.5 h-3.5 text-emerald-500" />
                        <span>Inventaris Bahan ({{ results.inventory.length }})</span>
                    </div>
                    <div class="divide-y divide-slate-100 rounded-xl border border-slate-100 bg-white overflow-hidden shadow-2xs">
                        <div
                            v-for="item in results.inventory"
                            :key="item.id"
                            @click="navigateTo(item.url)"
                            class="p-3 hover:bg-emerald-50/50 cursor-pointer flex items-center justify-between group transition-colors"
                        >
                            <div>
                                <h5 class="text-xs font-bold text-slate-900 group-hover:text-emerald-600 transition-colors">{{ item.title }}</h5>
                                <p class="text-[11px] text-slate-500 mt-0.5">{{ item.subtitle }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs font-semibold text-slate-700">{{ item.meta }}</span>
                                <ArrowRight class="w-3.5 h-3.5 text-slate-300 group-hover:text-emerald-500 group-hover:translate-x-0.5 transition-all" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Group: Employees -->
                <div v-if="results.employees && results.employees.length > 0">
                    <div class="flex items-center gap-2 mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                        <Users class="w-3.5 h-3.5 text-indigo-500" />
                        <span>Karyawan ({{ results.employees.length }})</span>
                    </div>
                    <div class="divide-y divide-slate-100 rounded-xl border border-slate-100 bg-white overflow-hidden shadow-2xs">
                        <div
                            v-for="item in results.employees"
                            :key="item.id"
                            @click="navigateTo(item.url)"
                            class="p-3 hover:bg-indigo-50/50 cursor-pointer flex items-center justify-between group transition-colors"
                        >
                            <div>
                                <h5 class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ item.title }}</h5>
                                <p class="text-[11px] text-slate-500 mt-0.5">{{ item.subtitle }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs font-semibold text-slate-700">{{ item.meta }}</span>
                                <ArrowRight class="w-3.5 h-3.5 text-slate-300 group-hover:text-indigo-500 group-hover:translate-x-0.5 transition-all" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Group: Recipes -->
                <div v-if="results.recipes && results.recipes.length > 0">
                    <div class="flex items-center gap-2 mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                        <ChefHat class="w-3.5 h-3.5 text-rose-500" />
                        <span>Resep ({{ results.recipes.length }})</span>
                    </div>
                    <div class="divide-y divide-slate-100 rounded-xl border border-slate-100 bg-white overflow-hidden shadow-2xs">
                        <div
                            v-for="item in results.recipes"
                            :key="item.id"
                            @click="navigateTo(item.url)"
                            class="p-3 hover:bg-rose-50/50 cursor-pointer flex items-center justify-between group transition-colors"
                        >
                            <div>
                                <h5 class="text-xs font-bold text-slate-900 group-hover:text-rose-600 transition-colors">{{ item.title }}</h5>
                                <p class="text-[11px] text-slate-500 mt-0.5">{{ item.subtitle }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs font-semibold text-slate-700">{{ item.meta }}</span>
                                <ArrowRight class="w-3.5 h-3.5 text-slate-300 group-hover:text-rose-500 group-hover:translate-x-0.5 transition-all" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Group: Outlets -->
                <div v-if="results.outlets && results.outlets.length > 0">
                    <div class="flex items-center gap-2 mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                        <Building2 class="w-3.5 h-3.5 text-sky-500" />
                        <span>Cabang / Outlet ({{ results.outlets.length }})</span>
                    </div>
                    <div class="divide-y divide-slate-100 rounded-xl border border-slate-100 bg-white overflow-hidden shadow-2xs">
                        <div
                            v-for="item in results.outlets"
                            :key="item.id"
                            @click="navigateTo(item.url)"
                            class="p-3 hover:bg-sky-50/50 cursor-pointer flex items-center justify-between group transition-colors"
                        >
                            <div>
                                <h5 class="text-xs font-bold text-slate-900 group-hover:text-sky-600 transition-colors">{{ item.title }}</h5>
                                <p class="text-[11px] text-slate-500 mt-0.5">{{ item.subtitle }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs font-semibold text-slate-700">{{ item.meta }}</span>
                                <ArrowRight class="w-3.5 h-3.5 text-slate-300 group-hover:text-sky-500 group-hover:translate-x-0.5 transition-all" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Shortcut Help -->
            <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-slate-600 font-mono shadow-2xs">ESC</kbd>
                        <span>tutup</span>
                    </span>
                    <span class="flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 bg-white border border-slate-200 rounded text-slate-600 font-mono shadow-2xs">Ctrl + K</kbd>
                        <span>buka pencarian</span>
                    </span>
                </div>
                <div v-if="totalResults > 0" class="font-medium text-slate-700">
                    {{ totalResults }} hasil ditemukan
                </div>
            </div>
        </div>
    </div>
</template>

