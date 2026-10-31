<script setup>
import { ref } from 'vue';
import { Search, Calendar, Filter, X, RotateCcw } from 'lucide-vue-next';
import { useOrderStore } from '../../../stores/order.store';
import BaseButton from '../../../components/common/BaseButton.vue';

const orderStore = useOrderStore();

const searchInput = ref(orderStore.filters.search);
let searchTimeout = null;

const statusOptions = [
    { value: '', label: 'Semua Status' },
    { value: 'draft', label: 'Draft' },
    { value: 'submitted', label: 'Terkirim' },
    { value: 'preparing', label: 'Diproses' },
    { value: 'ready', label: 'Siap Saji' },
    { value: 'completed', label: 'Selesai' },
    { value: 'cancelled', label: 'Batal' },
];

function onSearchInput(e) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        orderStore.setSearch(searchInput.value);
    }, 400);
}

function clearSearch() {
    searchInput.value = '';
    orderStore.setSearch('');
}

function onStatusSelect(status) {
    orderStore.setStatus(status);
}

function onDateChange(e) {
    orderStore.setDate(e.target.value);
}

function handleReset() {
    searchInput.value = '';
    orderStore.resetFilters();
}
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs space-y-3">
        <!-- Status Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar">
            <span class="text-xs font-semibold text-slate-500 mr-1 shrink-0 flex items-center gap-1">
                <Filter class="w-3.5 h-3.5" />
                Status:
            </span>

            <button
                v-for="opt in statusOptions"
                :key="opt.value"
                type="button"
                @click="onStatusSelect(opt.value)"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all shrink-0 cursor-pointer"
                :class="[
                    orderStore.filters.status === opt.value
                        ? 'bg-blue-600 text-white shadow-xs'
                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900',
                ]"
            >
                {{ opt.label }}
            </button>
        </div>

        <!-- Search & Date Row -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-1 border-t border-slate-100">
            <!-- Search Input -->
            <div class="relative w-full sm:w-80">
                <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                <input
                    type="text"
                    v-model="searchInput"
                    @input="onSearchInput"
                    placeholder="Cari no. order atau nama customer..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-9 pr-8 py-2 text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 focus:bg-white"
                />
                <button
                    v-if="searchInput"
                    type="button"
                    @click="clearSearch"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5"
                >
                    <X class="w-3.5 h-3.5" />
                </button>
            </div>

            <!-- Date & Reset -->
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5">
                    <Calendar class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                    <input
                        type="date"
                        :value="orderStore.filters.date"
                        @change="onDateChange"
                        class="bg-transparent text-xs text-slate-700 font-medium focus:outline-none cursor-pointer"
                    />
                </div>

                <BaseButton
                    v-if="orderStore.filters.status || orderStore.filters.date || orderStore.filters.search"
                    variant="outline"
                    size="sm"
                    @click="handleReset"
                    title="Reset Filter"
                >
                    <RotateCcw class="w-3.5 h-3.5" />
                    <span class="hidden sm:inline">Reset</span>
                </BaseButton>
            </div>
        </div>
    </div>
</template>

