import { defineStore } from 'pinia';
import { ref } from 'vue';
import { settingsService } from '../services/settings.service';

export const useSettingsStore = defineStore('settings', () => {
    const settings = ref({
        restaurant: {
            name: '',
            address: '',
            phone: '',
            footer_message: '',
            code: '',
            outlet_name: '',
        },
        pos: {
            allow_discount: true,
            require_customer_name: true,
            default_payment: 'cash',
        },
        printer: {
            cashier: {
                enabled: true,
                printer_name: 'EPSON TM-T82',
            },
            kitchen: {
                enabled: true,
                printer_name: 'Kitchen Printer',
            },
        },
        payment: {
            cash_enabled: true,
            transfer_enabled: true,
            qris_enabled: true,
        },
        inventory: {
            low_stock_threshold: 10,
        },
    });

    const loading = ref(false);
    const saving = ref(false);
    const error = ref(null);
    const successMessage = ref(null);

    async function fetchSettings(params = {}) {
        loading.value = true;
        error.value = null;
        try {
            const data = await settingsService.getSettings(params);
            settings.value = {
                restaurant: { ...settings.value.restaurant, ...data.restaurant },
                pos: { ...settings.value.pos, ...data.pos },
                printer: {
                    cashier: { ...settings.value.printer.cashier, ...(data.printer?.cashier || {}) },
                    kitchen: { ...settings.value.printer.kitchen, ...(data.printer?.kitchen || {}) },
                },
                payment: { ...settings.value.payment, ...data.payment },
                inventory: { ...settings.value.inventory, ...data.inventory },
            };
        } catch (err) {
            console.error('Failed to load settings', err);
            error.value = err.response?.data?.message || 'Gagal memuat pengaturan sistem.';
        } finally {
            loading.value = false;
        }
    }

    async function saveSettings() {
        saving.value = true;
        error.value = null;
        successMessage.value = null;

        try {
            const res = await settingsService.updateSettings(settings.value);
            successMessage.value = res.message || 'Pengaturan berhasil disimpan.';
            if (res.data) {
                settings.value = {
                    restaurant: { ...settings.value.restaurant, ...res.data.restaurant },
                    pos: { ...settings.value.pos, ...res.data.pos },
                    printer: {
                        cashier: { ...settings.value.printer.cashier, ...(res.data.printer?.cashier || {}) },
                        kitchen: { ...settings.value.printer.kitchen, ...(res.data.printer?.kitchen || {}) },
                    },
                    payment: { ...settings.value.payment, ...res.data.payment },
                    inventory: { ...settings.value.inventory, ...res.data.inventory },
                };
            }
            return true;
        } catch (err) {
            console.error('Failed to save settings', err);
            error.value = err.response?.data?.message || 'Gagal menyimpan pengaturan sistem.';
            return false;
        } finally {
            saving.value = false;
        }
    }

    return {
        settings,
        loading,
        saving,
        error,
        successMessage,
        fetchSettings,
        saveSettings,
    };
});

