import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useAuthStore } from './auth.store';

export const useUserStore = defineStore('user', () => {
    const authStore = useAuthStore();
    const activeOutlet = ref({
        id: 1,
        name: 'Outlet Utama',
    });

    function setOutlet(outlet) {
        activeOutlet.value = outlet;
    }

    return {
        activeOutlet,
        setOutlet,
    };
});

