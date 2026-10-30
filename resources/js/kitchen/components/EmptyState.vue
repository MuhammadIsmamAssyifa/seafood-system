<template>
    <div class="flex flex-col items-center justify-center p-12 text-center h-full min-h-[350px] select-none">
        <div class="w-20 h-20 rounded-3xl bg-slate-800/80 border border-slate-700/60 flex items-center justify-center shadow-xl mb-4 text-3xl">
            🍽️
        </div>
        <h3 class="text-lg font-bold text-slate-100 tracking-wide">{{ title }}</h3>
        <p class="text-sm text-slate-400 mt-1 max-w-sm leading-relaxed">{{ description }}</p>
        <div v-if="$slots.action" class="mt-6">
            <slot name="action" />
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        default: 'Belum Ada Pesanan',
    },
    description: {
        type: String,
        default: 'Antrean dapur saat ini kosong. Pesanan baru dari POS akan langsung muncul di sini secara realtime.',
    },
});
</script>

