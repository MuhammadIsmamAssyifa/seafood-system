<script setup>
import { Clock, History, CheckCircle, ArrowRight } from 'lucide-vue-next';
import { formatTime, formatDate } from '../../../utils/formatter';
import { getStatusBadge } from '../../../utils/helper';
import BaseCard from '../../../components/common/BaseCard.vue';
import BaseEmpty from '../../../components/common/BaseEmpty.vue';

defineProps({
    histories: {
        type: Array,
        default: () => [],
    },
    createdAt: {
        type: [String, Date],
        default: null,
    },
});
</script>

<template>
    <BaseCard title="Histori & Jejak Audit Status" subtitle="Kronologi perubahan status pesanan sejak dibuat">
        <div v-if="!histories || histories.length === 0" class="py-4">
            <div class="text-xs text-slate-500 flex items-center gap-2 p-3 bg-slate-50 rounded-xl">
                <CheckCircle class="w-4 h-4 text-emerald-600 shrink-0" />
                <span>Pesanan dibuat pada <strong>{{ formatDate(createdAt, 'DD MMMM YYYY HH:mm') }}</strong>. Belum ada catatan perubahan riwayat lain.</span>
            </div>
        </div>

        <div v-else class="space-y-4">
            <div class="relative pl-6 space-y-5 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                <div
                    v-for="(h, idx) in histories"
                    :key="h.id || idx"
                    class="relative group"
                >
                    <!-- Dot -->
                    <span
                        class="absolute -left-6 top-1.5 w-3 h-3 rounded-full border-2 border-white ring-2 transition-all"
                        :class="[
                            idx === histories.length - 1
                                ? 'bg-blue-600 ring-blue-200'
                                : 'bg-slate-300 ring-slate-100',
                        ]"
                    ></span>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <div class="flex items-center gap-1.5">
                                <span v-if="h.from_status" class="text-slate-400 font-mono text-[11px]">
                                    {{ getStatusBadge(h.from_status?.value || h.from_status).label }}
                                </span>
                                <ArrowRight v-if="h.from_status" class="w-3 h-3 text-slate-400" />
                                <span class="font-bold text-slate-800" :class="getStatusBadge(h.status?.value || h.status).class">
                                    {{ getStatusBadge(h.status?.value || h.status).label }}
                                </span>
                            </div>

                            <div class="text-[10px] text-slate-400 flex items-center gap-1 font-mono">
                                <Clock class="w-3 h-3" />
                                <span>{{ formatTime(h.created_at) }}</span>
                            </div>
                        </div>

                        <p v-if="h.note" class="text-slate-600 mt-1 italic">
                            "{{ h.note }}"
                        </p>

                        <div v-if="h.changed_by" class="text-[10px] text-slate-400 mt-1">
                            Diperbarui oleh: <span class="font-semibold text-slate-600">{{ h.changed_by }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

