<script setup>
import { Users, Receipt, Percent, Tag, CheckCircle2 } from 'lucide-vue-next';
import { formatCurrency, formatNumber } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';

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
    <BaseCard title="Statistik Transaksi & Tamu" subtitle="Performa volume kunjungan dan efisiensi nota">
        <div v-if="loading" class="animate-pulse space-y-3 py-2">
            <div v-for="i in 4" :key="i" class="h-10 bg-slate-100 rounded-lg"></div>
        </div>

        <div v-else class="space-y-3">
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center">
                        <Users class="w-4 h-4" />
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-700">Total Tamu Restoran</span>
                        <p class="text-[10px] text-slate-400">Total pengunjung yang dilayani</p>
                    </div>
                </div>
                <span class="text-base font-bold font-mono text-slate-800">
                    {{ formatNumber(summary.total_guests) }} Orang
                </span>
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <Receipt class="w-4 h-4" />
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-700">Belanja Rata-rata per Tamu</span>
                        <p class="text-[10px] text-slate-400">Spend per customer</p>
                    </div>
                </div>
                <span class="text-base font-bold font-mono text-emerald-600">
                    {{ formatCurrency(summary.average_spend_per_guest) }}
                </span>
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                        <Tag class="w-4 h-4" />
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-700">Total Potongan & Diskon</span>
                        <p class="text-[10px] text-slate-400">Akumulasi diskon nota transaksi</p>
                    </div>
                </div>
                <span class="text-base font-bold font-mono text-rose-600">
                    -{{ formatCurrency(summary.total_discounts) }}
                </span>
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center">
                        <CheckCircle2 class="w-4 h-4" />
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-700">Total Penjualan Kotor (Gross)</span>
                        <p class="text-[10px] text-slate-400">Sebelum diskon transaksi</p>
                    </div>
                </div>
                <span class="text-base font-bold font-mono text-slate-800">
                    {{ formatCurrency(summary.gross_sales) }}
                </span>
            </div>
        </div>
    </BaseCard>
</template>

