<script setup>
import { Calculator, X, Equal, DollarSign, Layers } from 'lucide-vue-next';
import { formatCurrency, formatNumber } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';

defineProps({
    stock: {
        type: Number,
        default: 0,
    },
    unit: {
        type: String,
        default: 'kg',
    },
    averageCost: {
        type: Number,
        default: 0,
    },
    totalValue: {
        type: Number,
        default: 0,
    },
});
</script>

<template>
    <BaseCard title="Valuasi Modal & Harga Rata-Rata" subtitle="Kalkulasi total modal yang terikat pada stok fisik bahan saat ini">
        <div class="space-y-4">
            <!-- Math calculation row -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 grid grid-cols-1 sm:grid-cols-5 items-center gap-3 text-center">
                <!-- Stock Qty -->
                <div class="p-3 bg-white rounded-lg border border-slate-200">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Sisa Stok Fisik</span>
                    <span class="text-base font-extrabold font-mono text-slate-800">
                        {{ formatNumber(stock, 2) }} {{ unit }}
                    </span>
                </div>

                <div class="hidden sm:flex justify-center text-slate-400">
                    <X class="w-4 h-4" />
                </div>

                <!-- Weighted Avg Cost -->
                <div class="p-3 bg-white rounded-lg border border-slate-200">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Harga Beli Rata-Rata</span>
                    <span class="text-base font-extrabold font-mono text-indigo-700">
                        {{ formatCurrency(averageCost) }}
                    </span>
                </div>

                <div class="hidden sm:flex justify-center text-slate-400">
                    <Equal class="w-4 h-4" />
                </div>

                <!-- Total Valuation -->
                <div class="p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                    <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider block mb-1">Total Nilai Modal</span>
                    <span class="text-base font-extrabold font-mono text-emerald-700">
                        {{ formatCurrency(totalValue) }}
                    </span>
                </div>
            </div>

            <!-- Weighted Explanation Note -->
            <div class="text-xs text-slate-500 bg-white p-3 rounded-lg border border-slate-200/80 leading-relaxed">
                💡 <strong>Catatan Valuasi:</strong> Harga rata-rata dihitung menggunakan bobot kuantitas sisa tiap batch aktif:
                $\frac{\sum (\text{Sisa Batch} \times \text{Harga Beli})}{\sum \text{Total Sisa Stok}}$. Saat bahan digunakan untuk memasak, dapur secara otomatis memotong stok dari batch tertua terlebih dahulu (FIFO).
            </div>
        </div>
    </BaseCard>
</template>

