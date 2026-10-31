<script setup>
import { Package, CheckCircle2, AlertTriangle, XCircle } from 'lucide-vue-next';
import { formatNumber } from '../../../utils/formatter';

defineProps({
    summary: {
        type: Object,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Bahan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Bahan Baku</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <Package class="w-4.5 h-4.5" />
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-800 tracking-tight">
                {{ formatNumber(summary.total_ingredients) }} Bahan
            </div>
            <div class="mt-2 text-xs text-slate-500">
                Akumulasi: {{ formatNumber(summary.total_stock_quantity, 1) }} unit/kg
            </div>
        </div>

        <!-- 2. Stok Aman -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Stok Tersedia</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <CheckCircle2 class="w-4.5 h-4.5" />
                </div>
            </div>
            <div class="text-2xl font-extrabold text-emerald-700 tracking-tight">
                {{ formatNumber(summary.available_count) }} Bahan
            </div>
            <div class="mt-2 text-xs text-emerald-600/80 font-medium">
                Siap digunakan untuk operasional
            </div>
        </div>

        <!-- 3. Stok Menipis -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Stok Menipis (&le; 5)</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <AlertTriangle class="w-4.5 h-4.5" />
                </div>
            </div>
            <div class="text-2xl font-extrabold text-amber-600 tracking-tight">
                {{ formatNumber(summary.low_stock_count) }} Bahan
            </div>
            <div class="mt-2 text-xs text-amber-600/80 font-medium">
                Perlu restock segera
            </div>
        </div>

        <!-- 4. Stok Kosong -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Habis / Out of Stock</span>
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <XCircle class="w-4.5 h-4.5" />
                </div>
            </div>
            <div class="text-2xl font-extrabold text-rose-600 tracking-tight">
                {{ formatNumber(summary.out_of_stock_count) }} Bahan
            </div>
            <div class="mt-2 text-xs text-rose-600/80 font-medium">
                Dapur tidak dapat menyajikan menu terkait
            </div>
        </div>
    </div>
</template>

