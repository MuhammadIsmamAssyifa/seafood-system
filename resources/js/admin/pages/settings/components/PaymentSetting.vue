<script setup>
import { Banknote, Building2, QrCode, AlertCircle } from 'lucide-vue-next';

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
            <h3 class="text-base font-bold text-slate-800">Metode Pembayaran Kasir</h3>
            <p class="text-xs text-slate-500">Aktifkan atau nonaktifkan jalur pembayaran yang dapat dipilih oleh kasir.</p>
        </div>

        <div class="space-y-4">
            <!-- Cash Option -->
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <Banknote class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="text-sm font-semibold text-slate-800 block">Tunai (Cash Payment)</span>
                        <span class="text-xs text-slate-500">Pembayaran langsung menggunakan uang fisik dengan kalkulasi kembalian.</span>
                    </div>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="modelValue.cash_enabled"
                        class="sr-only peer"
                    />
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                </label>
            </div>

            <!-- Transfer Option -->
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center">
                        <Building2 class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="text-sm font-semibold text-slate-800 block">Transfer Rekening Bank</span>
                        <span class="text-xs text-slate-500">Pembayaran via transfer antar bank dengan nomor referensi transfer.</span>
                    </div>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="modelValue.transfer_enabled"
                        class="sr-only peer"
                    />
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <!-- QRIS Option -->
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center">
                        <QrCode class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="text-sm font-semibold text-slate-800 block">QRIS (Quick Response Code Indonesian Standard)</span>
                        <span class="text-xs text-slate-500">Scan QRIS statis / dinamis dari dompet digital (GoPay, OVO, Dana, BCA Mobile, dll).</span>
                    </div>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="modelValue.qris_enabled"
                        class="sr-only peer"
                    />
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                </label>
            </div>

            <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200/60 rounded-xl text-xs text-amber-800">
                <AlertCircle class="w-4 h-4 text-amber-600 flex-shrink-0" />
                <span>Penting: Minimal salah satu opsi pembayaran wajib aktif agar kasir dapat memproses transaksi pesanan.</span>
            </div>
        </div>
    </div>
</template>

