<script setup>
import { onMounted, ref } from 'vue';
import {
    Settings,
    Store,
    ShoppingCart,
    Printer,
    CreditCard,
    Package,
    Save,
    CheckCircle2,
    AlertCircle,
    RotateCcw
} from 'lucide-vue-next';
import { useSettingsStore } from '../../stores/settings.store';
import RestaurantSetting from './components/RestaurantSetting.vue';
import PosSetting from './components/PosSetting.vue';
import PrinterSetting from './components/PrinterSetting.vue';
import PaymentSetting from './components/PaymentSetting.vue';

const settingsStore = useSettingsStore();
const activeTab = ref('restaurant');

const tabs = [
    { id: 'restaurant', name: 'Profil Restoran', icon: Store },
    { id: 'pos', name: 'Konfigurasi POS', icon: ShoppingCart },
    { id: 'printer', name: 'Printer Kasir & Dapur', icon: Printer },
    { id: 'payment', name: 'Metode Pembayaran', icon: CreditCard },
    { id: 'inventory', name: 'Ambang Stok', icon: Package },
];

onMounted(() => {
    settingsStore.fetchSettings();
});

async function handleSave() {
    await settingsStore.saveSettings();
}

function handleReset() {
    settingsStore.fetchSettings();
}
</script>

<template>
    <div class="space-y-6 max-w-5xl mx-auto pb-16">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <Settings class="w-7 h-7 text-sky-600" />
                    Pengaturan Sistem (System Settings)
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Kelola konfigurasi operasional restoran, printer thermal, POS, dan metode pembayaran
                </p>
            </div>

            <div class="flex items-center gap-2">
                <button
                    @click="handleReset"
                    :disabled="settingsStore.loading || settingsStore.saving"
                    type="button"
                    class="px-3.5 py-2 text-xs font-semibold rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 transition disabled:opacity-50 inline-flex items-center gap-1.5"
                >
                    <RotateCcw class="w-3.5 h-3.5" />
                    Batal Perubahan
                </button>

                <button
                    @click="handleSave"
                    :disabled="settingsStore.loading || settingsStore.saving"
                    type="button"
                    class="px-5 py-2 text-sm font-bold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition disabled:opacity-50 inline-flex items-center gap-2"
                >
                    <span v-if="settingsStore.saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <Save v-else class="w-4 h-4" />
                    Simpan Pengaturan
                </button>
            </div>
        </div>

        <!-- Success Alert -->
        <div
            v-if="settingsStore.successMessage"
            class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between text-sm animate-in fade-in"
        >
            <div class="flex items-center gap-2">
                <CheckCircle2 class="w-5 h-5 text-emerald-600 flex-shrink-0" />
                <span>{{ settingsStore.successMessage }}</span>
            </div>
            <button
                @click="settingsStore.successMessage = null"
                class="text-xs font-bold text-emerald-700 hover:text-emerald-900"
            >
                ✕
            </button>
        </div>

        <!-- Error Alert -->
        <div
            v-if="settingsStore.error"
            class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between text-sm animate-in fade-in"
        >
            <div class="flex items-center gap-2">
                <AlertCircle class="w-5 h-5 text-rose-600 flex-shrink-0" />
                <span>{{ settingsStore.error }}</span>
            </div>
            <button
                @click="settingsStore.error = null"
                class="text-xs font-bold text-rose-700 hover:text-rose-900"
            >
                ✕
            </button>
        </div>

        <!-- Loading Skeleton -->
        <div v-if="settingsStore.loading" class="bg-white rounded-2xl p-8 border border-slate-200 text-center text-slate-400">
            <span class="w-6 h-6 border-2 border-sky-500 border-t-transparent rounded-full animate-spin inline-block mb-2"></span>
            <p class="text-sm">Memuat preferensi pengaturan sistem...</p>
        </div>

        <!-- Content with Tabbed Navigation -->
        <div v-else class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <!-- Navigation Tabs -->
            <div class="flex border-b border-slate-200/80 bg-slate-50/50 overflow-x-auto">
                <button
                    v-for="tab in tabs"
                    :key="tab.id"
                    @click="activeTab = tab.id"
                    :class="[
                        'flex items-center gap-2 py-3.5 px-5 text-xs font-bold whitespace-nowrap border-b-2 transition -mb-px',
                        activeTab === tab.id
                            ? 'border-sky-600 text-sky-600 bg-white'
                            : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100/50'
                    ]"
                >
                    <component :is="tab.icon" class="w-4 h-4" />
                    {{ tab.name }}
                </button>
            </div>

            <!-- Tab Content -->
            <div class="p-6 md:p-8">
                <!-- 1. Restaurant Profile -->
                <RestaurantSetting
                    v-if="activeTab === 'restaurant'"
                    v-model="settingsStore.settings.restaurant"
                />

                <!-- 2. POS Configuration -->
                <PosSetting
                    v-else-if="activeTab === 'pos'"
                    v-model="settingsStore.settings.pos"
                />

                <!-- 3. Printer Configuration -->
                <PrinterSetting
                    v-else-if="activeTab === 'printer'"
                    v-model="settingsStore.settings.printer"
                />

                <!-- 4. Payment Configuration -->
                <PaymentSetting
                    v-else-if="activeTab === 'payment'"
                    v-model="settingsStore.settings.payment"
                />

                <!-- 5. Inventory Configuration -->
                <div v-else-if="activeTab === 'inventory'" class="space-y-6">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Ambang Batas Minimum Stok Bahan Baku</h3>
                        <p class="text-xs text-slate-500">Nilai acuan sistem dalam memberikan peringatan stok menipis (Low Stock Alert).</p>
                    </div>

                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200/80 max-w-md space-y-3">
                        <label class="block text-xs font-semibold text-slate-700">
                            Batas Minimum Peringatan (Unit)
                        </label>
                        <div class="relative">
                            <Package class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                            <input
                                v-model.number="settingsStore.settings.inventory.low_stock_threshold"
                                type="number"
                                min="1"
                                max="1000"
                                class="w-full pl-9 pr-3 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none font-bold"
                            />
                        </div>
                        <span class="text-[11px] text-slate-400 block">
                            Bahan dengan jumlah stok di bawah angka ini akan otomatis ditandai kuning/merah di menu inventori.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Bottom Action Footer -->
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-400">
                    Perubahan konfigurasi akan otomatis dicatat dalam modul Audit Log.
                </span>

                <button
                    @click="handleSave"
                    :disabled="settingsStore.saving"
                    type="button"
                    class="px-5 py-2 text-xs font-bold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition disabled:opacity-50 inline-flex items-center gap-2"
                >
                    <span v-if="settingsStore.saving" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <Save v-else class="w-3.5 h-3.5" />
                    Simpan Pengaturan
                </button>
            </div>
        </div>
    </div>
</template>

