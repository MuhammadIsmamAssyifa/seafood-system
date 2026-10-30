<template>
    <button
        type="button"
        :class="[
            'flex items-center justify-between p-3.5 sm:p-4 rounded-2xl border transition-all text-left w-full select-none active:scale-[0.98]',
            isActive
                ? `${colorConfig.activeBorder} ${colorConfig.activeBg} shadow-lg ${colorConfig.glow}`
                : 'bg-slate-900/90 border-slate-800 hover:border-slate-700 hover:bg-slate-800/80',
        ]"
        @click="$emit('select', statusKey)"
    >
        <div class="flex items-center gap-3">
            <!-- Icon Box -->
            <div
                :class="[
                    'w-11 h-11 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center shrink-0 border transition-colors',
                    isActive ? colorConfig.iconActiveBg : 'bg-slate-800/80 border-slate-700/60 text-slate-400',
                ]"
            >
                <!-- All Icon -->
                <svg v-if="statusKey === 'all'" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>

                <!-- Waiting Icon -->
                <svg v-else-if="statusKey === 'waiting'" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <!-- Preparing Icon (Flame) -->
                <svg v-else-if="statusKey === 'preparing'" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                </svg>

                <!-- Ready Icon (Check bell) -->
                <svg v-else-if="statusKey === 'ready'" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>

                <!-- Completed / Default Icon -->
                <svg v-else class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <!-- Labels -->
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">
                    {{ title }}
                </p>
                <div class="flex items-baseline gap-1.5 mt-0.5">
                    <span :class="['text-xl sm:text-2xl font-black tracking-tight', isActive ? colorConfig.activeText : 'text-white']">
                        {{ count }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">Tiket</span>
                </div>
            </div>
        </div>

        <!-- Active Indicator Pill -->
        <div v-if="isActive" :class="['w-1.5 sm:w-2 h-7 sm:h-8 rounded-full', colorConfig.bar]"></div>
    </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    statusKey: {
        type: String,
        required: true, // 'all' | 'waiting' | 'preparing' | 'ready'
    },
    title: {
        type: String,
        required: true,
    },
    count: {
        type: Number,
        default: 0,
    },
    isActive: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['select']);

const colorConfig = computed(() => {
    switch (props.statusKey) {
        case 'all':
            return {
                activeBorder: 'border-blue-500/80',
                activeBg: 'bg-blue-950/30',
                activeText: 'text-blue-400',
                glow: 'shadow-blue-950/40',
                iconActiveBg: 'bg-blue-500/20 border-blue-500/40 text-blue-400',
                bar: 'bg-blue-400',
            };
        case 'waiting':
            return {
                activeBorder: 'border-amber-500/80',
                activeBg: 'bg-amber-950/30',
                activeText: 'text-amber-400',
                glow: 'shadow-amber-950/40',
                iconActiveBg: 'bg-amber-500/20 border-amber-500/40 text-amber-400',
                bar: 'bg-amber-400',
            };
        case 'preparing':
            return {
                activeBorder: 'border-sky-500/80',
                activeBg: 'bg-sky-950/30',
                activeText: 'text-sky-400',
                glow: 'shadow-sky-950/40',
                iconActiveBg: 'bg-sky-500/20 border-sky-500/40 text-sky-400',
                bar: 'bg-sky-400',
            };
        case 'ready':
            return {
                activeBorder: 'border-emerald-500/80',
                activeBg: 'bg-emerald-950/30',
                activeText: 'text-emerald-400',
                glow: 'shadow-emerald-950/40',
                iconActiveBg: 'bg-emerald-500/20 border-emerald-500/40 text-emerald-400',
                bar: 'bg-emerald-400',
            };
        case 'completed':
        default:
            return {
                activeBorder: 'border-slate-600',
                activeBg: 'bg-slate-800/60',
                activeText: 'text-slate-200',
                glow: 'shadow-slate-900/50',
                iconActiveBg: 'bg-slate-700/80 border-slate-600 text-slate-200',
                bar: 'bg-slate-400',
            };
    }
});
</script>
