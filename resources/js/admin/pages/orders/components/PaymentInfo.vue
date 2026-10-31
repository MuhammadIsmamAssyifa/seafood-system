<script setup>
import { CreditCard, Banknote, QrCode, CheckCircle2, AlertCircle, Clock } from 'lucide-vue-next';
import { formatCurrency, formatTime, formatDate } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';
import BaseEmpty from '../../../components/common/BaseEmpty.vue';

defineProps({
    payments: {
        type: Array,
        default: () => [],
    },
    subtotal: {
        type: Number,
        default: 0,
    },
    discount: {
        type: Number,
        default: 0,
    },
    grandTotal: {
        type: Number,
        default: 0,
    },
});

function getMethodIcon(method) {
    const m = (method?.value || method || '').toLowerCase();
    if (m === 'cash') return Banknote;
    if (m === 'qris') return QrCode;
    return CreditCard;
}

function getMethodLabel(method) {
    const m = (method?.value || method || '').toLowerCase();
    if (m === 'cash') return 'Tunai / Cash';
    if (m === 'qris') return 'QRIS';
    if (m === 'transfer') return 'Transfer Bank / EDC';
    return method || 'Lainnya';
}
</script>

<template>
    <BaseCard title="Rincian Pembayaran & Finansial" subtitle="Catatan pelunasan dan ringkasan nilai transaksi">
        <div class="space-y-4">
            <!-- Summary Cost Calculation -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                <div class="flex justify-between items-center text-slate-600">
                    <span>Subtotal Pesanan:</span>
                    <span class="font-mono font-medium">{{ formatCurrency(subtotal) }}</span>
                </div>
                <div v-if="discount > 0" class="flex justify-between items-center text-rose-600">
                    <span>Potongan / Diskon:</span>
                    <span class="font-mono font-medium">-{{ formatCurrency(discount) }}</span>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between items-center text-sm font-bold text-slate-900">
                    <span>Grand Total:</span>
                    <span class="font-mono text-base text-blue-700">{{ formatCurrency(grandTotal) }}</span>
                </div>
            </div>

            <!-- Payment Transactions List -->
            <div>
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2.5">
                    Transaksi Pembayaran Masuk
                </h4>

                <div v-if="!payments || payments.length === 0" class="py-2">
                    <BaseEmpty
                        title="Belum Ada Pembayaran"
                        message="Belum ada transaksi pembayaran yang dicatat untuk pesanan ini."
                    />
                </div>

                <div v-else class="space-y-2.5">
                    <div
                        v-for="p in payments"
                        :key="p.id"
                        class="p-3.5 rounded-xl border border-slate-200 bg-white flex items-center justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <component :is="getMethodIcon(p.method)" class="w-5 h-5" />
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-800">
                                    {{ getMethodLabel(p.method) }}
                                </div>
                                <div class="text-[10px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                    <Clock class="w-3 h-3" />
                                    <span>{{ formatTime(p.paid_at || p.created_at) }}</span>
                                    <span v-if="p.created_by?.name">oleh {{ p.created_by.name }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-sm font-bold font-mono text-emerald-600 block">
                                {{ formatCurrency(p.amount) }}
                            </span>
                            <span class="inline-block text-[10px] uppercase font-bold text-emerald-700 px-1.5 py-0.2 rounded bg-emerald-50">
                                {{ p.status?.value || p.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

