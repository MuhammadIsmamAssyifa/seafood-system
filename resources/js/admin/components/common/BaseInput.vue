<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number],
        default: '',
    },
    label: {
        type: String,
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    placeholder: {
        type: String,
        default: '',
    },
    error: {
        type: String,
        default: '',
    },
    hint: {
        type: String,
        default: '',
    },
    required: {
        type: Boolean,
        default: false,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    autocomplete: {
        type: String,
        default: 'off',
    },
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const hasError = computed(() => !!props.error);
</script>

<template>
    <div class="w-full">
        <label v-if="label" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
            {{ label }}
            <span v-if="required" class="text-rose-500">*</span>
        </label>

        <div class="relative rounded-lg shadow-sm">
            <div v-if="$slots.prefix" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <slot name="prefix" />
            </div>

            <input
                :type="type"
                :value="modelValue"
                :placeholder="placeholder"
                :disabled="disabled"
                :autocomplete="autocomplete"
                @input="emit('update:modelValue', $event.target.value)"
                @blur="emit('blur', $event)"
                @focus="emit('focus', $event)"
                class="block w-full rounded-lg text-sm text-slate-900 transition-colors placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:bg-slate-100 disabled:text-slate-500"
                :class="[
                    $slots.prefix ? 'pl-10' : 'pl-3.5',
                    $slots.suffix ? 'pr-10' : 'pr-3.5',
                    'py-2.5',
                    hasError
                        ? 'border border-rose-400 focus:border-rose-500 bg-rose-50/20'
                        : 'border border-slate-300 focus:border-blue-600 bg-white',
                ]"
            />

            <div v-if="$slots.suffix" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400">
                <slot name="suffix" />
            </div>
        </div>

        <p v-if="hasError" class="mt-1 text-xs text-rose-600 font-medium">
            {{ error }}
        </p>
        <p v-else-if="hint" class="mt-1 text-xs text-slate-500">
            {{ hint }}
        </p>
    </div>
</template>

