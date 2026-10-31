<script setup>
import { computed } from 'vue';
import { Clock, CheckCircle2, XCircle, Users, Activity } from 'lucide-vue-next';
import { formatNumber } from '../../../utils/formatter';
import BaseCard from '../../../components/common/BaseCard.vue';

const props = defineProps({
    metrics: {
        type: Object,
        required: true,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const total = computed(() => props.metrics?.total_today || 0);
const active = computed(() => props.metrics?.active_orders || 0);
const completed = computed(() => props.metrics?.completed_today || 0);
const cancelled = computed(() => props.metrics?.status_breakdown?.cancelled || 0);
const guests = computed(() => props.metrics?.guests_today || 0);

const completionRate = computed(() => {
    if (total.value === 0) return 0;
    return Math.round((completed.value / total.value) * 100);
});
</script>

<template>
    <BaseCard title="Aktivitas Pesanan Hari Ini" subtitle="Status siklus hidup pesanan operasional">
        <div v-if="loading" class="animate-pulse space-y-4 py-2">
            <div class="h-4 bg-slate-200 rounded w-1/2"></div>
            <div class="grid grid-cols-2 gap-3">
                <div class="h-16 bg-slate-100 rounded-xl"></div>
                <div class="h-16 bg-slate-100 rounded-xl"></div>
            </div>
        </div>

        <div v-else class="space-y-4">
            <!-- Progress Bar -->
            <div>
                <div class="flex items-center justify-between text-xs mb-1.5 font-medium">
                    <span class="text-slate-600">Tingkat Penyelesaian</span>
                    <span class="text-emerald-600 font-bold font-mono">{{ completionRate }}% Selesai</span>
                </div>
                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden flex">
                    <div
                        class="bg-emerald-500 transition-all duration-500"
                        :style="{ width: `${completionRate}%` }"
                    ></div>
                    <div
                        class="bg-blue-500 transition-all duration-500"
                        :style="{ width: `${total > 0 ? (active / total) * 100 : 0}%` }"
                    ></div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                <!-- Active Orders -->
                <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-100">
                    <div class="flex items-center gap-1.5 text-xs text-blue-700 font-semibold mb-1">
                        <Activity class="w-4 h-4 text-blue-600" />
                        <span>Sedang Aktif</span>
                    </div>
                    <div class="text-xl font-bold text-blue-950 font-mono">
                        {{ formatNumber(active) }}
                    </div>
                    <span class="text-[11px] text-blue-600/80">antre & dimasak</span>
                </div>

                <!-- Completed Orders -->
                <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-100">
                    <div class="flex items-center gap-1.5 text-xs text-emerald-700 font-semibold mb-1">
                        <CheckCircle2 class="w-4 h-4 text-emerald-600" />
                        <span>Selesai</span>
                    </div>
                    <div class="text-xl font-bold text-emerald-950 font-mono">
                        {{ formatNumber(completed) }}
                    </div>
                    <span class="text-[11px] text-emerald-600/80">transaksi beres</span>
                </div>

                <!-- Cancelled Orders -->
                <div class="p-3.5 rounded-xl bg-rose-50/70 border border-rose-100">
                    <div class="flex items-center gap-1.5 text-xs text-rose-700 font-semibold mb-1">
                        <XCircle class="w-4 h-4 text-rose-600" />
                        <span>Batal</span>
                    </div>
                    <div class="text-xl font-bold text-rose-950 font-mono">
                        {{ formatNumber(cancelled) }}
                    </div>
                    <span class="text-[11px] text-rose-600/80">dibatalkan</span>
                </div>

                <!-- Guests Today -->
                <div class="p-3.5 rounded-xl bg-purple-50/70 border border-purple-100">
                    <div class="flex items-center gap-1.5 text-xs text-purple-700 font-semibold mb-1">
                        <Users class="w-4 h-4 text-purple-600" />
                        <span>Tamu / Pelanggan</span>
                    </div>
                    <div class="text-xl font-bold text-purple-950 font-mono">
                        {{ formatNumber(guests) }}
                    </div>
                    <span class="text-[11px] text-purple-600/80">orang dilayani</span>
                </div>
            </div>
        </div>
    </BaseCard>
</template>

