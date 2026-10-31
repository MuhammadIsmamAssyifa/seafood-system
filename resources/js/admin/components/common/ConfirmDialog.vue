<script setup>
import { AlertTriangle, Info, CheckCircle2 } from 'lucide-vue-next';
import BaseModal from './BaseModal.vue';
import BaseButton from './BaseButton.vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Konfirmasi Tindakan',
    },
    message: {
        type: String,
        default: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    },
    confirmText: {
        type: String,
        default: 'Ya, Lanjutkan',
    },
    cancelText: {
        type: String,
        default: 'Batal',
    },
    variant: {
        type: String,
        default: 'danger',
        validator: (v) => ['danger', 'warning', 'primary', 'success'].includes(v),
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'confirm', 'cancel']);

function cancel() {
    emit('update:modelValue', false);
    emit('cancel');
}

function confirm() {
    emit('confirm');
}
</script>

<template>
    <BaseModal
        :model-value="modelValue"
        @update:model-value="emit('update:modelValue', $event)"
        :title="title"
        size="sm"
    >
        <div class="flex items-start gap-3.5">
            <div
                :class="[
                    'w-10 h-10 rounded-xl flex items-center justify-center shrink-0',
                    variant === 'danger' ? 'bg-rose-100 text-rose-600' :
                    variant === 'warning' ? 'bg-amber-100 text-amber-600' :
                    variant === 'success' ? 'bg-emerald-100 text-emerald-600' :
                    'bg-blue-100 text-blue-600'
                ]"
            >
                <AlertTriangle v-if="variant === 'danger' || variant === 'warning'" class="w-5 h-5" />
                <CheckCircle2 v-else-if="variant === 'success'" class="w-5 h-5" />
                <Info v-else class="w-5 h-5" />
            </div>

            <div class="space-y-1">
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    {{ message }}
                </p>
            </div>
        </div>

        <template #footer>
            <BaseButton
                variant="outline"
                size="sm"
                @click="cancel"
                :disabled="loading"
            >
                {{ cancelText }}
            </BaseButton>

            <BaseButton
                :variant="variant === 'warning' ? 'warning' : (variant === 'primary' ? 'primary' : 'danger')"
                size="sm"
                @click="confirm"
                :loading="loading"
            >
                {{ confirmText }}
            </BaseButton>
        </template>
    </BaseModal>
</template>

