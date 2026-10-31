<script setup>
import { CheckCircle2, Clock, Layers } from 'lucide-vue-next';
import { formatNumber, formatCurrency, formatDate } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';
import BaseEmpty from '../../../components/common/BaseEmpty.vue';

defineProps({
    batches: {
        type: Array,
        default: () => [],
    },
    unit: {
        type: String,
        default: 'kg',
    },
    loading: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <BaseCard title="Daftar Batch Persediaan & Antrean FIFO" subtitle="Rincian harga modal per batch dan urutan pemotongan otomatis oleh dapur">
        <div v-if="loading" class="p-6 space-y-3">
            <div v-for="i in 4" :key="i" class="h-10 bg-slate-100 rounded-lg animate-pulse"></div>
        </div>

        <div v-else-if="!batches || batches.length === 0" class="py-6">
            <BaseEmpty
                title="Belum Ada Batch Persediaan"
                message="Data batch akan dicatat saat bahan baku masuk melalui pembelian atau penyesuaian."
            />
        </div>

        <div v-else class="overflow-x-auto -mx-6">
            <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/70 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="px-6 py-3">No. Batch / Tanggal</th>
                        <th class="px-4 py-3">Sumber / Vendor</th>
                        <th class="px-4 py-3 text-right">Kuantitas Masuk</th>
                        <th class="px-4 py-3 text-right">Sisa Stok</th>
                        <th class="px-4 py-3 text-right">Harga Beli / Satuan</th>
                        <th class="px-4 py-3 text-right">Nilai Modal Sisa</th>
                        <th class="px-6 py-3 text-center">Antrean FIFO</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <tr
                        v-for="batch in batches"
                        :key="batch.id"
                        class="hover:bg-slate-50/60"
                        :class="[batch.is_depleted ? 'opacity-40 bg-slate-50/40' : '']"
                    >
                        <!-- Batch & Date -->
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            <div class="font-mono font-bold text-slate-800">
                                {{ batch.batch_number }}
                            </div>
                            <div class="text-[10px] text-slate-400">
                                {{ formatDate(batch.received_at, 'DD/MM/YYYY') }}
                            </div>
                        </td>

                        <!-- Source -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-slate-600">
                            {{ batch.source }}
                        </td>

                        <!-- Initial Quantity -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono text-slate-600">
                            {{ formatNumber(batch.quantity, 2) }} {{ unit }}
                        </td>

                        <!-- Remaining Quantity -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono font-bold" :class="[batch.remaining_quantity > 0 ? 'text-blue-600' : 'text-slate-400']">
                            {{ formatNumber(batch.remaining_quantity, 2) }} {{ unit }}
                        </td>

                        <!-- Cost per Unit -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono text-slate-700">
                            {{ formatCurrency(batch.cost_per_unit) }}
                        </td>

                        <!-- Total Value -->
                        <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono font-bold text-emerald-600">
                            {{ formatCurrency(batch.value) }}
                        </td>

                        <!-- FIFO Order Badge -->
                        <td class="px-6 py-3.5 whitespace-nowrap text-center">
                            <span
                                v-if="!batch.is_depleted && batch.fifo_order"
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold font-mono bg-blue-50 text-blue-700 border border-blue-200"
                            >
                                Prioritas #{{ batch.fifo_order }}
                            </span>
                            <span
                                v-else
                                class="inline-block text-[10px] font-mono text-slate-400 bg-slate-100 px-2 py-0.5 rounded"
                            >
                                Habis Terpakai
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </BaseCard>
</template>

