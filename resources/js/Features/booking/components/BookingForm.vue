<script setup lang="ts">
import { ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { Calendar, Clock, User, Phone, Mail, CreditCard, Loader } from 'lucide-vue-next';
import { PageProps } from '@/types';

interface Slot {
    start_time: string;
    end_time: string;
    formatted_time: string;
    price: number;
}

const props = defineProps<{
    courtId: number;
    courtName: string;
    date: string;
    slot: Slot;
}>();

const page = usePage<PageProps>();
const authUser = page.props.auth?.user;

const form = useForm({
    court_id: props.courtId,
    customer_name: authUser?.name ?? '',
    customer_phone: authUser?.phone ?? '',
    customer_email: authUser?.email ?? '',
    date: props.date,
    start_time: props.slot.start_time,
    end_time: props.slot.end_time
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(price);
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const submitBooking = () => {
    // Update dates/slots in form in case they were changed in the parent before submit
    form.court_id = props.courtId;
    form.date = props.date;
    form.start_time = props.slot.start_time;
    form.end_time = props.slot.end_time;

    form.post(route('booking.store'), {
        preserveScroll: true
    });
};
</script>

<template>
    <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
        <h3 class="font-display text-lg font-bold uppercase border-b border-verge-text-primary/10 pb-2 mb-4">Detail Pemesan</h3>

        <form @submit.prevent="submitBooking" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Form Inputs -->
            <div class="space-y-4">
                <!-- Customer Name -->
                <div class="space-y-1">
                    <label for="customer_name" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted flex items-center gap-1">
                        <User class="w-3.5 h-3.5" />
                        <span>Nama Lengkap</span>
                    </label>
                    <input id="customer_name" v-model="form.customer_name" type="text" required class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet" placeholder="Contoh: Budi Santoso" />
                    <span v-if="form.errors.customer_name" class="text-[10px] font-mono text-red-600 block">{{ form.errors.customer_name }}</span>
                </div>

                <!-- Customer Phone -->
                <div class="space-y-1">
                    <label for="customer_phone" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted flex items-center gap-1">
                        <Phone class="w-3.5 h-3.5" />
                        <span>Nomor HP (WhatsApp)</span>
                    </label>
                    <input id="customer_phone" v-model="form.customer_phone" type="text" required class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet" placeholder="Contoh: 081234567890" />
                    <span v-if="form.errors.customer_phone" class="text-[10px] font-mono text-red-600 block">{{ form.errors.customer_phone }}</span>
                </div>

                <!-- Customer Email -->
                <div class="space-y-1">
                    <label for="customer_email" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted flex items-center gap-1">
                        <Mail class="w-3.5 h-3.5" />
                        <span>Alamat Email (Opsional)</span>
                    </label>
                    <input id="customer_email" v-model="form.customer_email" type="email" class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet" placeholder="Contoh: budi@example.com" />
                    <span v-if="form.errors.customer_email" class="text-[10px] font-mono text-red-600 block">{{ form.errors.customer_email }}</span>
                </div>
            </div>

            <!-- Booking Summary -->
            <div class="bg-verge-surface-light border-2 border-verge-text-primary p-5 rounded-md flex flex-col justify-between">
                <div class="space-y-4">
                    <span class="font-mono text-[9px] uppercase font-bold text-verge-ultraviolet tracking-widest block">Ringkasan Pesanan</span>
                    
                    <div class="space-y-2 font-sans text-xs">
                        <div class="flex justify-between border-b border-verge-text-primary/10 pb-2">
                            <span class="text-verge-text-muted">Lapangan:</span>
                            <span class="font-bold uppercase text-verge-text-primary">{{ courtName }}</span>
                        </div>
                        <div class="flex justify-between border-b border-verge-text-primary/10 pb-2">
                            <span class="text-verge-text-muted">Tanggal:</span>
                            <span class="font-mono font-bold text-verge-text-primary">{{ formatDate(date) }}</span>
                        </div>
                        <div class="flex justify-between border-b border-verge-text-primary/10 pb-2">
                            <span class="text-verge-text-muted">Waktu:</span>
                            <span class="font-mono font-bold text-verge-text-primary flex items-center gap-1">
                                <Clock class="w-3 h-3 text-verge-ultraviolet" />
                                <span>{{ slot.formatted_time }}</span>
                            </span>
                        </div>
                        <div class="flex justify-between pt-2">
                            <span class="text-verge-text-muted font-bold">Total Bayar:</span>
                            <span class="font-display text-lg font-bold text-verge-ultraviolet">{{ formatPrice(slot.price) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit" :disabled="form.processing" class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted font-mono text-xs uppercase font-bold rounded-sm shadow-[4px_4px_0px_0px_rgba(19,19,19,0.2)] disabled:opacity-50 transition-all">
                        <component :is="form.processing ? Loader : CreditCard" class="w-4 h-4" :class="{ 'animate-spin': form.processing }" />
                        <span>Konfirmasi Booking</span>
                    </button>
                    <span class="text-[9px] font-mono text-verge-text-muted block text-center mt-2">
                        Pembayaran dilakukan manual di lokasi (Cash/Transfer/QRIS) saat bermain.
                    </span>
                    <span v-if="page.props.errors.slot_conflict" class="text-[10px] font-mono text-red-600 block text-center mt-2">{{ page.props.errors.slot_conflict }}</span>
                </div>
            </div>
        </form>
    </div>
</template>
