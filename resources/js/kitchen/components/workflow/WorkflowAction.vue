<template>
    <div class="inline-flex items-center">
        <!-- Action Trigger Button -->
        <StatusButton
            :action-type="actionType"
            :label="label"
            :size="size"
            :loading="isLoading"
            :disabled="disabled || isLoading"
            @click="handleClick"
        />

        <!-- Confirmation Modal -->
        <BaseModal
            v-model="showConfirmModal"
            :title="confirmTitle"
            max-width="sm"
            @close="showConfirmModal = false"
        >
            <div class="space-y-3 text-sm">
                <div class="flex items-start gap-3">
                    <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0 border', iconContainerClass]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-white text-base">{{ confirmHeading }}</p>
                        <p class="text-xs text-slate-300 mt-1 leading-relaxed">{{ confirmMessage }}</p>
                    </div>
                </div>
            </div>

            <template #footer>
                <button
                    type="button"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-semibold transition-colors"
                    :disabled="isLoading"
                    @click="showConfirmModal = false"
                >
                    Batal
                </button>
                <button
                    type="button"
                    :class="['px-5 py-2 rounded-xl text-xs font-bold text-white shadow-md transition-all active:scale-95 disabled:opacity-50', confirmBtnClass]"
                    :disabled="isLoading"
                    @click="executeAction"
                >
                    <span v-if="isLoading">Memproses...</span>
                    <span v-else>Ya, Lanjutkan</span>
                </button>
            </template>
        </BaseModal>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useKitchenStore } from '@/kitchen/stores/kitchen.store';
import { useToast } from '@/kitchen/composables/useToast';
import StatusButton from './StatusButton.vue';
import BaseModal from '@/shared/components/BaseModal.vue';

const props = defineProps({
    actionType: {
        type: String,
        required: true, // 'accept' | 'prepare' | 'ready' | 'serve'
    },
    targetId: {
        type: [Number, String],
        required: true, // batchId or itemId
    },
    targetName: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        default: null,
    },
    size: {
        type: String,
        default: 'md',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    requireConfirm: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['success', 'error']);

const kitchenStore = useKitchenStore();
const toast = useToast();

const showConfirmModal = ref(false);

const actionKey = computed(() => {
    return props.actionType === 'accept' ? `batch-${props.targetId}` : `item-${props.targetId}`;
});

const isLoading = computed(() => {
    return kitchenStore.isActionPending(actionKey.value);
});

const handleClick = () => {
    // If confirmation required (or default for ready/serve/accept)
    if (props.requireConfirm || ['ready', 'serve'].includes(props.actionType)) {
        showConfirmModal.value = true;
    } else {
        executeAction();
    }
};

const executeAction = async () => {
    showConfirmModal.value = false;

    let res;
    if (props.actionType === 'accept') {
        res = await kitchenStore.acceptBatch(props.targetId);
        if (res.success) {
            toast.success(`Batch order berhasil diterima dapur!`);
            emit('success', res.data);
        } else {
            toast.error(res.message || 'Gagal menerima batch order.');
            emit('error', res.message);
        }
    } else if (props.actionType === 'prepare') {
        res = await kitchenStore.prepareItem(props.targetId);
        if (res.success) {
            toast.info(`Item "${props.targetName || 'Menu'}" mulai dimasak.`);
            emit('success', res.data);
        } else {
            toast.error(res.message || 'Gagal memulai memasak item.');
            emit('error', res.message);
        }
    } else if (props.actionType === 'ready') {
        res = await kitchenStore.readyItem(props.targetId);
        if (res.success) {
            toast.success(`Item "${props.targetName || 'Menu'}" selesai dan siap disajikan!`);
            emit('success', res.data);
        } else {
            toast.error(res.message || 'Gagal menandai item siap saji.');
            emit('error', res.message);
        }
    } else if (props.actionType === 'serve') {
        res = await kitchenStore.serveItem(props.targetId);
        if (res.success) {
            toast.success(`Item "${props.targetName || 'Menu'}" telah disajikan.`);
            emit('success', res.data);
        } else {
            toast.error(res.message || 'Gagal menandai item disajikan.');
            emit('error', res.message);
        }
    }
};

const confirmTitle = computed(() => {
    switch (props.actionType) {
        case 'accept': return 'Konfirmasi Terima Order';
        case 'ready': return 'Konfirmasi Masakan Selesai';
        case 'serve': return 'Konfirmasi Sajikan Makanan';
        default: return 'Konfirmasi Aksi';
    }
});

const confirmHeading = computed(() => {
    switch (props.actionType) {
        case 'accept': return 'Terima Order ke Dapur?';
        case 'ready': return 'Makanan Sudah Selesai Dimasak?';
        case 'serve': return 'Sajikan Makanan ke Pelanggan?';
        default: return 'Lanjutkan proses?';
    }
});

const confirmMessage = computed(() => {
    const name = props.targetName ? `"${props.targetName}"` : 'pesanan ini';
    switch (props.actionType) {
        case 'accept':
            return `Batch pesanan akan ditandai masuk proses memasak dan antrean dapur akan diperbarui.`;
        case 'ready':
            return `Item ${name} akan ditandai siap saji dan waiter/kasir dapat mengambilnya.`;
        case 'serve':
            return `Item ${name} akan ditandai selesai disajikan kepada pelanggan.`;
        default:
            return `Apakah Anda yakin ingin memproses ${name}?`;
    }
});

const iconContainerClass = computed(() => {
    switch (props.actionType) {
        case 'accept': return 'bg-blue-500/20 border-blue-500/40 text-blue-400';
        case 'ready': return 'bg-emerald-500/20 border-emerald-500/40 text-emerald-400';
        case 'serve': return 'bg-teal-500/20 border-teal-500/40 text-teal-400';
        default: return 'bg-amber-500/20 border-amber-500/40 text-amber-400';
    }
});

const confirmBtnClass = computed(() => {
    switch (props.actionType) {
        case 'accept': return 'bg-blue-600 hover:bg-blue-500 shadow-blue-900/40';
        case 'ready': return 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-950/40';
        case 'serve': return 'bg-teal-600 hover:bg-teal-500 shadow-teal-950/40';
        default: return 'bg-amber-600 hover:bg-amber-500 shadow-amber-950/40';
    }
});
</script>

