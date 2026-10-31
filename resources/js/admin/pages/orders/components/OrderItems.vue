<script setup>
import { CookingPot, CheckCircle2, AlertCircle } from 'lucide-vue-next';
import { formatCurrency, formatNumber } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';
import BaseEmpty from '../../../components/common/BaseEmpty.vue';

const props = defineProps({
    batches: {
        type: Array,
        default: () => [],
    },
});

function getBatchStatusBadge(status) {
    switch (status?.toLowerCase()) {
        case 'completed':
        case 'served':
            return { label: 'Tersaji', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' };
        case 'ready':
            return { label: 'Siap Antar', class: 'bg-purple-50 text-purple-700 border-purple-200' };
        case 'preparing':
            return { label: 'Sedang Dimasak', class: 'bg-blue-50 text-blue-700 border-blue-200' };
        case 'accepted':
            return { label: 'Diterima Dapur', class: 'bg-amber-50 text-amber-700 border-amber-200' };
        default:
            return { label: status || 'Antre', class: 'bg-slate-100 text-slate-700 border-slate-200' };
    }
}
</script>

<template>
    <BaseCard title="Rincian Item Menu & Batch Pesanan" subtitle="Daftar item pesanan yang diproses oleh dapur dan kasir">
        <div v-if="!batches || batches.length === 0" class="py-4">
            <BaseEmpty
                title="Belum Ada Item"
                message="Pesanan ini belum memiliki batch item yang tercatat."
            />
        </div>

        <div v-else class="space-y-6">
            <div
                v-for="(batch, bIdx) in batches"
                :key="batch.id || bIdx"
                class="rounded-xl border border-slate-200/90 overflow-hidden bg-white"
            >
                <!-- Batch Header -->
                <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2">
                        <CookingPot class="w-4 h-4 text-slate-500" />
                        <span class="font-bold text-slate-800">Batch #{{ batch.batch_number || (bIdx + 1) }}</span>
                        <span class="text-slate-400">({{ (batch.items || []).length }} item)</span>
                    </div>

                    <span
                        class="px-2.5 py-0.5 rounded-full font-semibold border text-[11px]"
                        :class="getBatchStatusBadge(batch.status).class"
                    >
                        {{ getBatchStatusBadge(batch.status).label }}
                    </span>
                </div>

                <!-- Items Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50/50 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="px-4 py-2.5">Menu / Varian</th>
                                <th class="px-3 py-2.5">Metode Masak & Catatan</th>
                                <th class="px-3 py-2.5 text-center">Jumlah</th>
                                <th class="px-3 py-2.5 text-right">Harga</th>
                                <th class="px-4 py-2.5 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <tr v-for="item in (batch.items || [])" :key="item.id" class="hover:bg-slate-50/50">
                                <!-- Menu -->
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-800 text-sm">
                                        {{ item.menu_name_snapshot || item.menu_variant?.name || 'Menu' }}
                                    </div>
                                    <div v-if="item.variant_name_snapshot" class="text-[11px] text-slate-500">
                                        {{ item.variant_name_snapshot }}
                                    </div>
                                </td>

                                <!-- Cooking Method & Notes -->
                                <td class="px-3 py-3">
                                    <span v-if="item.cooking_method_snapshot" class="inline-block px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-[10px] font-semibold border border-blue-100 mb-0.5">
                                        {{ item.cooking_method_snapshot }}
                                    </span>
                                    <p v-if="item.notes" class="text-[11px] text-amber-700 italic">
                                        Catatan: {{ item.notes }}
                                    </p>
                                    <span v-if="!item.cooking_method_snapshot && !item.notes" class="text-slate-400">-</span>
                                </td>

                                <!-- Qty -->
                                <td class="px-3 py-3 text-center font-mono font-bold text-slate-800">
                                    {{ formatNumber(item.quantity) }} {{ item.sales_unit || 'porsi' }}
                                </td>

                                <!-- Price -->
                                <td class="px-3 py-3 text-right font-mono text-slate-600">
                                    {{ formatCurrency(item.price) }}
                                </td>

                                <!-- Subtotal -->
                                <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">
                                    {{ formatCurrency(item.subtotal) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

