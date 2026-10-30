import { ref } from 'vue';

const toasts = ref([]);
let toastIdCounter = 0;

export const useToast = () => {
    const show = (message, type = 'info', duration = 4000) => {
        const id = ++toastIdCounter;
        const toast = { id, message, type };
        toasts.value.push(toast);

        if (duration > 0) {
            setTimeout(() => {
                remove(id);
            }, duration);
        }
        return id;
    };

    const success = (message, duration = 3500) => show(message, 'success', duration);
    const error = (message, duration = 5000) => show(message, 'error', duration);
    const warning = (message, duration = 4000) => show(message, 'warning', duration);
    const info = (message, duration = 3500) => show(message, 'info', duration);

    /**
     * Rich notification toast for new incoming kitchen order (Sprint 07)
     */
    const notifyOrder = ({ orderNumber, tableNumber, itemsCount, duration = 5000 }) => {
        const id = ++toastIdCounter;
        const toast = {
            id,
            type: 'order',
            isOrderNotification: true,
            orderNumber,
            tableNumber,
            itemsCount,
        };
        toasts.value.push(toast);

        if (duration > 0) {
            setTimeout(() => {
                remove(id);
            }, duration);
        }
        return id;
    };

    const remove = (id) => {
        const idx = toasts.value.findIndex((t) => t.id === id);
        if (idx !== -1) {
            toasts.value.splice(idx, 1);
        }
    };

    return {
        toasts,
        show,
        notifyOrder,
        success,
        error,
        warning,
        info,
        remove,
    };
};

