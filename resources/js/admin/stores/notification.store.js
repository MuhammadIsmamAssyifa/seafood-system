import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notification', () => {
    const toasts = ref([]);
    let counter = 0;

    function addToast({ message, type = 'info', title = null, duration = 4000 }) {
        const id = ++counter;
        const toast = { id, message, type, title, duration };
        toasts.value.push(toast);

        if (duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, duration);
        }
        return id;
    }

    function removeToast(id) {
        const index = toasts.value.findIndex((t) => t.id === id);
        if (index !== -1) {
            toasts.value.splice(index, 1);
        }
    }

    function success(message, title = 'Berhasil') {
        return addToast({ message, title, type: 'success' });
    }

    function error(message, title = 'Gagal') {
        return addToast({ message, title, type: 'error', duration: 5000 });
    }

    function warning(message, title = 'Peringatan') {
        return addToast({ message, title, type: 'warning' });
    }

    function info(message, title = 'Informasi') {
        return addToast({ message, title, type: 'info' });
    }

    return {
        toasts,
        addToast,
        removeToast,
        success,
        error,
        warning,
        info,
    };
});

