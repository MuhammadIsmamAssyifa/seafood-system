<script setup>
import { Eye, Clock, User, ShieldAlert, Laptop } from 'lucide-vue-next';
import ActivityBadge from './ActivityBadge.vue';
import { formatDate } from '../../../utils/formatter';

const props = defineProps({
    logs: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['view-detail']);

function onViewDetail(log) {
    emit('view-detail', log);
}
</script>

<template>
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-200/80">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">Aktivitas</th>
                        <th class="py-3 px-4">Modul</th>
                        <th class="py-3 px-4">Deskripsi</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <!-- Loading state -->
                    <tr v-if="loading">
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <div class="inline-flex items-center gap-2">
                                <span class="w-4 h-4 border-2 border-sky-500 border-t-transparent rounded-full animate-spin"></span>
                                Memuat catatan audit log...
                            </div>
                        </td>
                    </tr>

                    <!-- Empty state -->
                    <tr v-else-if="!logs.length">
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            Tidak ada aktivitas audit log yang sesuai dengan filter.
                        </td>
                    </tr>

                    <!-- Data rows -->
                    <tr
                        v-for="log in logs"
                        :key="log.id"
                        class="hover:bg-slate-50/80 transition group"
                    >
                        <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-500">
                            <div class="flex items-center gap-1.5 font-medium text-slate-700">
                                <Clock class="w-3.5 h-3.5 text-slate-400" />
                                {{ formatDate(log.created_at) }}
                            </div>
                            <span class="text-[11px] text-slate-400 block mt-0.5">{{ log.ip_address || '127.0.0.1' }}</span>
                        </td>

                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-xs uppercase">
                                    {{ (log.user?.name || 'S').charAt(0) }}
                                </div>
                                <div>
                                    <span class="font-semibold text-slate-800 text-xs block">{{ log.user?.name || 'Sistem' }}</span>
                                    <span class="text-[11px] text-slate-400">{{ log.user?.email || log.user?.username || '-' }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <ActivityBadge :action="log.action" />
                        </td>

                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                {{ log.module }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-xs text-slate-700 max-w-xs md:max-w-md truncate" :title="log.description">
                            {{ log.description }}
                        </td>

                        <td class="py-3.5 px-4 whitespace-nowrap text-center">
                            <button
                                @click="onViewDetail(log)"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200/60 transition"
                            >
                                <Eye class="w-3.5 h-3.5" />
                                Rincian
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
