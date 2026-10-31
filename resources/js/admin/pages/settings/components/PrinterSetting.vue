<script setup>
import { Printer, ChefHat, Receipt } from 'lucide-vue-next';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div class="space-y-6">
        <div>
            <h3 class="text-base font-bold text-slate-800">Konfigurasi Perangkat Printer</h3>
            <p class="text-xs text-slate-500">Hubungkan printer thermal kasir untuk cetak struk dan printer dapur untuk tiket kitchen.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Cashier Printer Card -->
            <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center">
                            <Receipt class="w-5 h-5" />
                        </div>
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">Printer Kasir (Struk)</span>
                            <span class="text-xs text-slate-500">Mencetak bukti pembayaran pelanggan</span>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="modelValue.cashier.enabled"
                            class="sr-only peer"
                        />
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-600"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nama Perangkat / Driver Printer Kasir <span v-if="modelValue.cashier.enabled" class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <Printer class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input
                            v-model="modelValue.cashier.printer_name"
                            :disabled="!modelValue.cashier.enabled"
                            type="text"
                            placeholder="Contoh: EPSON TM-T82"
                            class="w-full pl-9 pr-3 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none disabled:bg-slate-100 disabled:cursor-not-allowed transition font-medium"
                        />
                    </div>
                </div>
            </div>

            <!-- Kitchen Printer Card -->
            <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                            <ChefHat class="w-5 h-5" />
                        </div>
                        <div>
                            <span class="text-sm font-bold text-slate-800 block">Printer Dapur (Kitchen Ticket)</span>
                            <span class="text-xs text-slate-500">Mencetak tiket pesanan masak ke stasiun dapur</span>
                        </div>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="modelValue.kitchen.enabled"
                            class="sr-only peer"
                        />
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-600"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Nama Perangkat / Driver Printer Kitchen <span v-if="modelValue.kitchen.enabled" class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <Printer class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input
                            v-model="modelValue.kitchen.printer_name"
                            :disabled="!modelValue.kitchen.enabled"
                            type="text"
                            placeholder="Contoh: Kitchen Printer / Star TSP"
                            class="w-full pl-9 pr-3 py-2 text-sm bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none disabled:bg-slate-100 disabled:cursor-not-allowed transition font-medium"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

