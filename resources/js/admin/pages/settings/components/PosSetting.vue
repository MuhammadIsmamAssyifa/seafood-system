<script setup>
import { ShoppingCart, Percent, UserCheck, CreditCard } from 'lucide-vue-next';

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
            <h3 class="text-base font-bold text-slate-800">Konfigurasi Point of Sale (POS)</h3>
            <p class="text-xs text-slate-500">Atur aturan validasi transaksi, diskon kasir, dan pembayaran standar.</p>
        </div>

        <div class="space-y-4">
            <!-- Allow Discount Toggle -->
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center">
                        <Percent class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="text-sm font-semibold text-slate-800 block">Izinkan Diskon Kasir (Allow Discount)</span>
                        <span class="text-xs text-slate-500">Memungkinkan kasir memasukkan potongan diskon manual saat checkout pesanan.</span>
                    </div>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="modelValue.allow_discount"
                        class="sr-only peer"
                    />
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-600"></div>
                </label>
            </div>

            <!-- Customer Name Required Toggle -->
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-200/80">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center">
                        <UserCheck class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="text-sm font-semibold text-slate-800 block">Wajib Nama Pelanggan (Require Customer Name)</span>
                        <span class="text-xs text-slate-500">Kasir wajib mengisi nama pelanggan sebelum order dapat dibuat atau dikirim ke kitchen.</span>
                    </div>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        v-model="modelValue.require_customer_name"
                        class="sr-only peer"
                    />
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sky-600"></div>
                </label>
            </div>

            <!-- Default Payment Method -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <CreditCard class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="text-sm font-semibold text-slate-800 block">Metode Pembayaran Default (Default Payment)</span>
                        <span class="text-xs text-slate-500">Pilihan pembayaran yang langsung terpilih secara default di layar checkout kasir.</span>
                    </div>
                </div>

                <div class="pt-2">
                    <select
                        v-model="modelValue.default_payment"
                        class="w-full sm:w-64 text-sm py-2 px-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 focus:outline-none font-medium"
                    >
                        <option value="cash">Tunai (Cash)</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS Standar</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>

