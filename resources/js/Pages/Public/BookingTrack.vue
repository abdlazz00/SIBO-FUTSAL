<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import BookingStatusBadge from '@/Features/booking/components/BookingStatusBadge.vue';
import { Search, MapPin, Calendar, Clock, DollarSign, FileText } from 'lucide-vue-next';

interface Court {
    name: string;
    type: string;
}

interface Booking {
    id: number;
    booking_number: string;
    customer_name: string;
    customer_phone: string;
    date: string;
    start_time: string;
    end_time: string;
    total_price: number;
    status: 'confirmed' | 'completed' | 'cancelled';
    cancel_reason?: string | null;
    court: Court;
}

const props = defineProps<{
    booking?: Booking | null;
    searched?: boolean;
}>();

const form = useForm({
    booking_number: ''
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

const trackBooking = () => {
    form.post(route('booking.track.submit'), {
        preserveScroll: true
    });
};
</script>

<template>
    <Head title="Lacak Status Booking" />

    <PublicLayout>
        <div class="max-w-2xl mx-auto py-16 px-6 space-y-8">
            <div class="text-center space-y-2">
                <span class="font-mono text-[9px] uppercase font-bold text-verge-ultraviolet tracking-widest">Verifikasi Sewa</span>
                <h1 class="font-display text-4xl font-bold uppercase tracking-tight text-verge-text-primary">Lacak Booking</h1>
                <p class="text-xs text-verge-text-muted">Masukkan 8 digit kode booking unik Anda (VF-XXXXXX) untuk melacak status pembayaran dan waktu sewa.</p>
            </div>

            <!-- Search Form Bento Box -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
                <form @submit.prevent="trackBooking" class="flex gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-verge-text-muted">
                            <Search class="w-4.5 h-4.5" />
                        </div>
                        <input v-model="form.booking_number" type="text" required class="w-full border-2 border-verge-text-primary pl-10 pr-4 py-3 rounded-sm font-mono text-sm uppercase focus:outline-none focus:border-verge-ultraviolet" placeholder="Contoh: VF-K9A7PL" />
                    </div>
                    <button type="submit" :disabled="form.processing" class="px-6 py-3 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted font-mono text-xs uppercase font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,0.2)] transition-colors">
                        Lacak
                    </button>
                </form>
                <span v-if="form.errors.booking_number" class="text-[10px] font-mono text-red-600 block mt-2">{{ form.errors.booking_number }}</span>
            </div>

            <!-- Searched Results -->
            <div v-if="searched" class="space-y-6">
                <!-- If Not Found -->
                <div v-if="!booking" class="bg-verge-canvas-white border-2 border-verge-text-primary p-8 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] text-center text-xs font-mono text-verge-text-muted">
                    Maaf, nomor booking tidak ditemukan. Silakan periksa kembali kode booking Anda.
                </div>

                <!-- If Found -->
                <div v-else class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[6px_6px_0px_0px_rgba(19,19,19,1)] overflow-hidden">
                    <!-- Top header row -->
                    <div class="bg-verge-surface-light border-b-2 border-verge-text-primary p-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-xs font-bold text-verge-ultraviolet border border-verge-text-primary/10 bg-verge-canvas-white px-2 py-0.5 rounded-sm">{{ booking.booking_number }}</span>
                            <span class="font-sans font-bold text-sm text-verge-text-muted uppercase">Status Pemesanan</span>
                        </div>
                        <BookingStatusBadge :status="booking.status" />
                    </div>

                    <!-- Details panel -->
                    <div class="p-6 space-y-6">
                        <!-- Court Title & Customer Info -->
                        <div>
                            <span class="font-mono text-[9px] text-verge-text-muted uppercase tracking-widest block">{{ booking.court.type }} court</span>
                            <h3 class="font-display text-2xl font-bold uppercase tracking-tight text-verge-text-primary mt-1">{{ booking.court.name }}</h3>
                            <div class="mt-4 grid grid-cols-2 gap-4 font-mono text-[10px] text-verge-text-muted bg-verge-surface-light p-3 border border-verge-text-primary/10 rounded">
                                <div>Pemesan: <span class="text-verge-text-primary font-bold">{{ booking.customer_name }}</span></div>
                                <div>No. HP: <span class="text-verge-text-primary font-bold">{{ booking.customer_phone }}</span></div>
                            </div>
                        </div>

                        <!-- Time Details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-b border-verge-text-primary/10 py-6 font-mono text-xs">
                            <div class="flex items-center gap-2">
                                <Calendar class="w-4 h-4 text-verge-ultraviolet" />
                                <div class="flex flex-col">
                                    <span class="text-[9px] uppercase text-verge-text-muted">Tanggal Main</span>
                                    <span class="font-bold text-verge-text-primary mt-0.5">{{ formatDate(booking.date) }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Clock class="w-4 h-4 text-verge-ultraviolet" />
                                <div class="flex flex-col">
                                    <span class="text-[9px] uppercase text-verge-text-muted">Waktu / Jam</span>
                                    <span class="font-bold text-verge-text-primary mt-0.5">{{ booking.start_time.slice(0, 5) }} - {{ booking.end_time.slice(0, 5) }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <DollarSign class="w-4 h-4 text-verge-ultraviolet" />
                                <div class="flex flex-col">
                                    <span class="text-[9px] uppercase text-verge-text-muted">Total Pembayaran</span>
                                    <span class="font-sans font-bold text-verge-text-primary text-sm mt-0.5">{{ formatPrice(booking.total_price) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Instruction Details -->
                        <div class="space-y-2">
                            <span class="font-mono text-[9px] uppercase font-bold text-verge-text-primary tracking-widest block">Instruksi Pembayaran Lokal</span>
                            <div class="font-sans text-xs text-verge-text-muted leading-relaxed space-y-1">
                                <p>• Silakan datang ke lokasi lapangan futsal minimal 10 menit sebelum waktu bermain.</p>
                                <p>• Konfirmasikan nomor booking <strong class="font-mono text-verge-ultraviolet font-bold">{{ booking.booking_number }}</strong> ke Admin penjaga lapangan.</p>
                                <p>• Pembayaran dapat diselesaikan di lokasi via Tunai (Cash), Transfer Bank, atau scan QRIS resmi yang disediakan Admin.</p>
                            </div>
                        </div>

                        <!-- If Cancelled info -->
                        <div v-if="booking.status === 'cancelled'" class="bg-red-50 border border-red-200 p-4 rounded text-red-800 text-xs font-sans">
                            <div class="flex items-center gap-1.5 font-bold uppercase text-[9px] text-red-700 font-mono">
                                <FileText class="w-3.5 h-3.5" />
                                <span>Alasan Pembatalan:</span>
                            </div>
                            <p class="mt-1 font-medium">"{{ booking.cancel_reason || 'Tidak ada alasan spesifik' }}"</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
