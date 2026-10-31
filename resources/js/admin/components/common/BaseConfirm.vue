<script setup>
import BaseModal from './BaseModal.vue';
import BaseButton from './BaseButton.vue';

defineProps({
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
        <div class="text-sm text-slate-600">
            {{ message }}
        </div>

        <template #footer>
            <BaseButton variant="outline" size="sm" @click="cancel" :disabled="loading">
                {{ cancelText }}
            </BaseButton>
            <BaseButton :variant="variant" size="sm" @click="confirm" :loading="loading">
                {{ confirmText }}
            </BaseButton>
        </template>
    </BaseModal>
</template>

