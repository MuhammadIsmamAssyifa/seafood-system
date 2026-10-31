<script setup>
import { watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    size: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg', 'xl'].includes(v),
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['update:modelValue', 'close']);

function close() {
    if (props.closeable) {
        emit('update:modelValue', false);
        emit('close');
    }
}

function handleKeydown(e) {
    if (e.key === 'Escape' && props.modelValue && props.closeable) {
        close();
    }
}

watch(
    () => props.modelValue,
    (val) => {
        if (typeof document !== 'undefined') {
            document.body.style.overflow = val ? 'hidden' : '';
        }
    }
);

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    if (typeof document !== 'undefined') {
        document.body.style.overflow = '';
    }
});

const maxWidthClass = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-2xl',
}[props.size];
</script>

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
                class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
                @click.self="close"
            >
                <div
                    class="w-full bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all"
                    :class="maxWidthClass"
                >
                    <div v-if="title || $slots.header" class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <slot name="header">
                            <h3 class="text-lg font-semibold text-slate-800">{{ title }}</h3>
                        </slot>
                        <button
                            v-if="closeable"
                            type="button"
                            @click="close"
                            class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-6">
                        <slot />
                    </div>

                    <div v-if="$slots.footer" class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

