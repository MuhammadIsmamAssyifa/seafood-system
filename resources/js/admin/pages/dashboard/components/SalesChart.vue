<script setup>
import { computed } from 'vue';
import { CreditCard, Banknote, QrCode, ArrowDownRight, Layers } from 'lucide-vue-next';
import { formatCurrency, formatNumber } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';

const props = defineProps({
    sales: {
        type: Object,
        required: true,
    },
    payments: {
        type: Object,
        default: () => ({}),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const revenue = computed(() => Number(props.sales?.today_revenue || 0));
const cogs = computed(() => Number(props.sales?.today_cogs || 0));
const profit = computed(() => Number(props.sales?.today_gross_profit || 0));
const margin = computed(() => Number(props.sales?.today_profit_margin || 0));

const cash = computed(() => Number(props.payments?.today_cash || 0));
const transfer = computed(() => Number(props.payments?.today_transfer || 0));
const qris = computed(() => Number(props.payments?.today_qris || 0));
const totalCollected = computed(() => cash.value + transfer.value + qris.value);
</script>

<template>
    <BaseCard title="Ringkasan Arus Finansial Hari Ini" subtitle="Analisis pendapatan bersih, HPP (COGS), dan metode pembayaran">
        <div v-if="loading" class="animate-pulse space-y-4 py-2">
            <div class="h-24 bg-slate-100 rounded-xl"></div>
            <div class="grid grid-cols-3 gap-3">
                <div class="h-16 bg-slate-100 rounded-xl"></div>
                <div class="h-16 bg-slate-100 rounded-xl"></div>
                <div class="h-16 bg-slate-100 rounded-xl"></div>
            </div>
        </div>

        <div v-else class="space-y-5">
            <!-- Revenue vs COGS Visual Distribution -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <div class="flex items-center justify-between text-xs font-semibold mb-2">
                    <span class="text-slate-600">Komposisi Pendapatan Kotor</span>
                    <span class="text-blue-700 font-mono font-bold">{{ formatCurrency(revenue) }}</span>
                </div>

                <!-- Visual Ratio Bar -->
                <div class="w-full h-3 bg-slate-200 rounded-full overflow-hidden flex">
                    <div
                        class="bg-emerald-500 transition-all duration-500"
                        title="Profit Bersih"
                        :style="{ width: `${revenue > 0 ? (profit / revenue) * 100 : 0}%` }"
                    ></div>
                    <div
                        class="bg-amber-500 transition-all duration-500"
                        title="HPP / COGS"
                        :style="{ width: `${revenue > 0 ? (cogs / revenue) * 100 : 0}%` }"
                    ></div>
                </div>

                <div class="flex items-center justify-between text-xs mt-2 text-slate-500">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span>Gross Profit: <strong class="text-slate-800">{{ formatCurrency(profit) }}</strong> ({{ margin }}%)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span>COGS (HPP): <strong class="text-slate-800">{{ formatCurrency(cogs) }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Breakdown -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2.5">
                    Realisasi Pembayaran Kasir
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Cash -->
                    <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-2xs">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-semibold text-slate-600">Tunai / Cash</span>
                            <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <Banknote class="w-4 h-4" />
                            </div>
                        </div>
                        <div class="text-base font-bold text-slate-800 font-mono">
                            {{ formatCurrency(cash) }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">
                            {{ totalCollected > 0 ? Math.round((cash / totalCollected) * 100) : 0 }}% dari penerimaan
                        </div>
                    </div>

                    <!-- QRIS -->
                    <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-2xs">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-semibold text-slate-600">QRIS</span>
                            <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                <QrCode class="w-4 h-4" />
                            </div>
                        </div>
                        <div class="text-base font-bold text-slate-800 font-mono">
                            {{ formatCurrency(qris) }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">
                            {{ totalCollected > 0 ? Math.round((qris / totalCollected) * 100) : 0 }}% dari penerimaan
                        </div>
                    </div>

                    <!-- Bank Transfer / EDC -->
                    <div class="p-3.5 rounded-xl bg-white border border-slate-200 shadow-2xs">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-semibold text-slate-600">Transfer Bank</span>
                            <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                <CreditCard class="w-4 h-4" />
                            </div>
                        </div>
                        <div class="text-base font-bold text-slate-800 font-mono">
                            {{ formatCurrency(transfer) }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">
                            {{ totalCollected > 0 ? Math.round((transfer / totalCollected) * 100) : 0 }}% dari penerimaan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

