<template>
    <div
        class="pointer-events-auto p-4 rounded-2xl border border-amber-500/40 bg-slate-900/95 shadow-2xl shadow-amber-950/40 backdrop-blur-md transition-all flex items-start gap-3.5 max-w-sm w-full select-none"
    >
        <!-- Bell Icon Box with Pulse -->
        <div class="w-10 h-10 rounded-xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center text-amber-400 shrink-0 mt-0.5">
            <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-2">
                <h4 class="text-xs font-bold text-amber-400 uppercase tracking-wider">
                    Order Baru Masuk!
                </h4>
                <span class="text-[10px] text-slate-400 font-mono">Dapur</span>
            </div>

            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-base font-black text-white font-mono">
                    #{{ orderNumber }}
                </span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-950 text-blue-300 border border-blue-800">
                    Meja {{ tableNumber }}
                </span>
            </div>

            <p class="text-xs text-slate-300 font-semibold mt-1">
                {{ itemsCount }} Item Menu
            </p>
        </div>

        <!-- Close Button -->
        <button
            type="button"
            class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition-colors shrink-0"
            @click="$emit('close')"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>

<script setup>
defineProps({
    orderNumber: {
        type: String,
        default: '-',
    },
    tableNumber: {
        type: [String, Number],
        default: '-',
    },
    itemsCount: {
        type: [Number, String],
        default: 1,
    },
});

defineEmits(['close']);
</script>

