<template>
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-lg">
        <div class="flex items-center gap-2 mb-2.5">
            <div class="w-7 h-7 rounded-lg bg-amber-500/20 border border-amber-500/30 flex items-center justify-center text-amber-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
            </div>
            <h4 class="text-xs font-bold text-slate-200 uppercase tracking-wider">
                Catatan Khusus Pesanan
            </h4>
        </div>

        <div
            v-if="hasNotes"
            class="p-3.5 bg-amber-950/30 border border-amber-800/40 rounded-xl text-sm text-amber-200 leading-relaxed font-medium"
        >
            {{ noteText }}
        </div>

        <div v-else class="text-xs text-slate-500 italic p-2">
            Tidak ada catatan khusus untuk pesanan ini.
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    note: {
        type: [String, Array],
        default: null,
    },
});

const noteText = computed(() => {
    if (Array.isArray(props.note)) {
        return props.note.filter(Boolean).join(' • ');
    }
    return props.note || '';
});

const hasNotes = computed(() => {
    return !!noteText.value.trim();
});
</script>

