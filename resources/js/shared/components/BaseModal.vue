<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="modelValue"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/80 backdrop-blur-sm"
                @click.self="onBackdropClick"
            >
                <div
                    :class="[
                        'w-full bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]',
                        maxWidthClass,
                    ]"
                >
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-white tracking-wide">
                            <slot name="title">{{ title }}</slot>
                        </h3>
                        <button
                            v-if="showClose"
                            type="button"
                            class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition-colors"
                            @click="close"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 overflow-y-auto space-y-4 text-slate-300">
                        <slot />
                    </div>

                    <!-- Footer -->
                    <div v-if="$slots.footer" class="px-6 py-4 bg-slate-900/60 border-t border-slate-800 flex items-center justify-end gap-3">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    maxWidth: {
        type: String,
        default: 'md', // sm, md, lg, xl, full
    },
    closeOnBackdrop: {
        type: Boolean,
        default: true,
    },
    showClose: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['update:modelValue', 'close']);

const close = () => {
    emit('update:modelValue', false);
    emit('close');
};

const onBackdropClick = () => {
    if (props.closeOnBackdrop) {
        close();
    }
};

const maxWidthClass = computed(() => {
    switch (props.maxWidth) {
        case 'sm': return 'max-w-sm';
        case 'lg': return 'max-w-lg';
        case 'xl': return 'max-w-2xl';
        case '2xl': return 'max-w-4xl';
        case 'full': return 'max-w-6xl';
        case 'md':
        default:
            return 'max-w-md';
    }
});

// Lock body scroll when modal open
watch(() => props.modelValue, (isOpen) => {
    if (typeof document !== 'undefined') {
        if (isOpen) {
            document.body.classList.add('overflow-hidden');
        } else {
            document.body.classList.remove('overflow-hidden');
        }
    }
});
</script>

