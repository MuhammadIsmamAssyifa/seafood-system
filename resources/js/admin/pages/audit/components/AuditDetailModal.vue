<script setup>
import { computed } from 'vue';
import { X, User, Clock, Globe, ShieldAlert, FileText, ArrowRight } from 'lucide-vue-next';
import ActivityBadge from './ActivityBadge.vue';
import { formatDate } from '../../../utils/formatter';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    log: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

function close() {
    emit('close');
}

const hasDiff = computed(() => {
    return (props.log?.old_values && Object.keys(props.log.old_values).length > 0) ||
           (props.log?.new_values && Object.keys(props.log.new_values).length > 0);
});

// Extract comparison keys
const comparisonKeys = computed(() => {
    if (!props.log) return [];
    const oldKeys = props.log.old_values ? Object.keys(props.log.old_values) : [];
    const newKeys = props.log.new_values ? Object.keys(props.log.new_values) : [];
    return Array.from(new Set([...oldKeys, ...newKeys]));
});

function formatVal(val) {
    if (val === null || val === undefined) return '-';
    if (typeof val === 'boolean') return val ? 'true (aktif)' : 'false (nonaktif)';
    if (typeof val === 'object') return JSON.stringify(val, null, 2);
    return String(val);
}
</script>

<template>
    <div
        v-if="isOpen && log"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
        @click.self="close"
    >
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <!-- Header -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center font-bold">
                        <FileText class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Detail Audit Log #{{ log.id }}</h3>
                        <p class="text-xs text-slate-500">Histori aktivitas dan perbandingan perubahan data</p>
                    </div>
                </div>
                <button
                    @click="close"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200/60 transition"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
                <!-- Meta Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-xs text-slate-400 block mb-0.5">Pelaku Aktivitas (User)</span>
                        <div class="flex items-center gap-2">
                            <User class="w-4 h-4 text-slate-500" />
                            <span class="text-sm font-semibold text-slate-800">{{ log.user?.name || 'Sistem / Anonim' }}</span>
                            <span v-if="log.user?.email" class="text-xs text-slate-400">({{ log.user.email }})</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block mb-0.5">Waktu Tercatat</span>
                        <div class="flex items-center gap-2">
                            <Clock class="w-4 h-4 text-slate-500" />
                            <span class="text-sm font-medium text-slate-700">{{ formatDate(log.created_at) }}</span>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block mb-1">Aksi & Modul</span>
                        <div class="flex items-center gap-2">
                            <ActivityBadge :action="log.action" />
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-200/70 text-slate-700">
                                {{ log.module }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block mb-0.5">IP & Perangkat</span>
                        <div class="flex items-center gap-2 text-xs text-slate-600">
                            <Globe class="w-3.5 h-3.5 text-slate-400" />
                            <span>{{ log.ip_address || '127.0.0.1' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                        Deskripsi Aktivitas
                    </label>
                    <div class="p-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-700 leading-relaxed font-normal">
                        {{ log.description }}
                    </div>
                </div>

                <!-- Values Diff (Before & After) -->
                <div v-if="hasDiff" class="space-y-3">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        Perbandingan Data (Before vs After)
                    </label>

                    <div class="border border-slate-200 rounded-xl overflow-hidden divide-y divide-slate-100">
                        <div class="grid grid-cols-12 bg-slate-100/70 text-xs font-bold text-slate-600 px-4 py-2">
                            <div class="col-span-4">Field / Parameter</div>
                            <div class="col-span-4 text-rose-700">Sebelum (Old)</div>
                            <div class="col-span-4 text-emerald-700">Sesudah (New)</div>
                        </div>

                        <div
                            v-for="key in comparisonKeys"
                            :key="key"
                            class="grid grid-cols-12 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 items-center transition"
                        >
                            <div class="col-span-4 font-mono font-medium text-slate-600 truncate pr-2">
                                {{ key }}
                            </div>
                            <div class="col-span-4 font-mono bg-rose-50/50 text-rose-800 p-1.5 rounded mr-2 break-words">
                                {{ formatVal(log.old_values?.[key]) }}
                            </div>
                            <div class="col-span-4 font-mono bg-emerald-50/50 text-emerald-800 p-1.5 rounded break-words">
                                {{ formatVal(log.new_values?.[key]) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Raw JSON Accordion -->
                <details v-if="hasDiff" class="text-xs text-slate-500 border border-slate-100 rounded-lg p-2.5 bg-slate-50/50">
                    <summary class="cursor-pointer font-medium hover:text-slate-700">Lihat Format JSON Mentah</summary>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3 pt-2 border-t border-slate-200">
                        <div>
                            <span class="font-bold text-rose-700 block mb-1">Old Values (JSON)</span>
                            <pre class="bg-slate-900 text-slate-100 p-3 rounded-lg text-[11px] overflow-x-auto">{{ JSON.stringify(log.old_values, null, 2) }}</pre>
                        </div>
                        <div>
                            <span class="font-bold text-emerald-700 block mb-1">New Values (JSON)</span>
                            <pre class="bg-slate-900 text-slate-100 p-3 rounded-lg text-[11px] overflow-x-auto">{{ JSON.stringify(log.new_values, null, 2) }}</pre>
                        </div>
                    </div>
                </details>
            </div>

            <!-- Footer -->
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button
                    @click="close"
                    type="button"
                    class="px-4 py-2 text-sm font-semibold rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 transition"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>
