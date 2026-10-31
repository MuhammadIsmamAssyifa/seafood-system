<script setup>
import { Store, MapPin, Phone, MessageSquare } from 'lucide-vue-next';

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
            <h3 class="text-base font-bold text-slate-800">Profil & Identitas Restoran</h3>
            <p class="text-xs text-slate-500">Informasi utama restoran yang ditampilkan pada struk nota dan laporan sistem.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Restaurant Name -->
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Nama Restoran <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <Store class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                        v-model="modelValue.name"
                        type="text"
                        placeholder="Contoh: Seafood Bahari POS"
                        class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white focus:outline-none transition font-medium"
                    />
                </div>
            </div>

            <!-- Phone -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Nomor Telepon / WhatsApp
                </label>
                <div class="relative">
                    <Phone class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input
                        v-model="modelValue.phone"
                        type="text"
                        placeholder="Contoh: 0812-3456-7890"
                        class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white focus:outline-none transition"
                    />
                </div>
            </div>

            <!-- Outlet info readonly badge -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Cabang / Outlet Terhubung
                </label>
                <input
                    :value="modelValue.outlet_name || 'Cabang Utama'"
                    type="text"
                    disabled
                    class="w-full px-3 py-2 text-sm bg-slate-100 border border-slate-200 text-slate-500 rounded-xl cursor-not-allowed font-medium"
                />
            </div>

            <!-- Address -->
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Alamat Lengkap
                </label>
                <div class="relative">
                    <MapPin class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                    <textarea
                        v-model="modelValue.address"
                        rows="2"
                        placeholder="Contoh: Jl. Pelabuhan Ikan No. 88, Muara Baru"
                        class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white focus:outline-none transition"
                    ></textarea>
                </div>
            </div>

            <!-- Receipt Footer Message -->
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Pesan Footer Struk Pembayaran (Receipt Footer)
                </label>
                <div class="relative">
                    <MessageSquare class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                    <textarea
                        v-model="modelValue.footer_message"
                        rows="3"
                        placeholder="Contoh: *** TERIMA KASIH ***&#10;Selamat Menikmati Hidangan Kami"
                        class="w-full pl-9 pr-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500 focus:bg-white focus:outline-none transition font-mono text-xs"
                    ></textarea>
                </div>
                <span class="text-[11px] text-slate-400 mt-1 block">Teks ini akan tercetak di bagian paling bawah struk kasir thermal.</span>
            </div>
        </div>
    </div>
</template>

