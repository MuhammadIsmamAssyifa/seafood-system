<script setup>
import { DollarSign, Layers, Package, TrendingUp } from 'lucide-vue-next';
import { formatCurrency, formatNumber } from '../../../utils/formatter';

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
        <!-- 1. Total Valuasi Nilai Stok -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Nilai Modal Stok</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <DollarSign class="w-4.5 h-4.5" />
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-800 tracking-tight">
                {{ formatCurrency(summary.total_stock_value) }}
            </div>
            <div class="mt-2 text-xs text-slate-500">
                Nilai modal tersimpan dalam persediaan
            </div>
        </div>

        <!-- 2. Total Bahan Baku -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Bahan Baku Terdaftar</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <Package class="w-4.5 h-4.5" />
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-800 tracking-tight">
                {{ formatNumber(summary.ingredient_count) }} Bahan
            </div>
            <div class="mt-2 text-xs text-slate-500">
                Memiliki catatan persediaan aktif
            </div>
        </div>

        <!-- 3. Batch Aktif -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Batch Persediaan Aktif</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <Layers class="w-4.5 h-4.5" />
                </div>
            </div>
            <div class="text-2xl font-extrabold text-indigo-700 tracking-tight">
                {{ formatNumber(summary.active_batches_count) }} Batch
            </div>
            <div class="mt-2 text-xs text-slate-500">
                Memiliki sisa stok belum terpakai
            </div>
        </div>

        <!-- 4. Prinsip Perhitungan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Metode Penilaian HPP</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <TrendingUp class="w-4.5 h-4.5" />
                </div>
            </div>
            <div class="text-xl font-extrabold text-amber-800 tracking-tight">
                FIFO & Weighted
            </div>
            <div class="mt-2 text-xs text-slate-500">
                First-In, First-Out costing
            </div>
        </div>
    </div>
</template>

