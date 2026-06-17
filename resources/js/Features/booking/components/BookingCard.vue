<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Calendar, Clock, DollarSign, Ban, X } from 'lucide-vue-next';
import BookingStatusBadge from './BookingStatusBadge.vue';

interface Court {
    name: string;
}

interface UserBrief {
    id: number;
    name: string;
}

interface PaymentBrief {
    id: number;
    payment_method: string | null;
    amount: number;
    refund_amount: number;
    refund_reason: string | null;
    confirmed_at: string | null;
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
    payment?: PaymentBrief | null;
    cancelled_by?: UserBrief | null;
}

const props = defineProps<{
    booking: Booking;
}>();

const isDetailModalOpen = ref(false);

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

const cancelBooking = () => {
    if (confirm('Apakah Anda yakin ingin membatalkan pemesanan ini?')) {
        router.delete(route('customer.bookings.cancel', props.booking.id));
    }
};
</script>

<template>
    <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-2">
            <!-- Header Row -->
            <div class="flex items-center gap-3">
                <span class="font-mono text-xs font-bold text-verge-ultraviolet border border-verge-text-primary/10 bg-verge-surface-light px-2 py-0.5 rounded-sm">{{ booking.booking_number }}</span>
                <span class="font-sans font-bold text-base text-verge-text-primary uppercase">{{ booking.court.name }}</span>
            </div>

            <!-- Details Row -->
            <div class="flex flex-wrap items-center gap-x-6 gap-y-1.5 font-mono text-[10px] text-verge-text-muted">
                <div class="flex items-center gap-1">
                    <Calendar class="w-3.5 h-3.5 text-verge-text-muted" />
                    <span>{{ formatDate(booking.date) }}</span>
                </div>
                <div class="flex items-center gap-1">
                    <Clock class="w-3.5 h-3.5 text-verge-text-muted" />
                    <span>{{ booking.start_time.slice(0, 5) }} - {{ booking.end_time.slice(0, 5) }}</span>
                </div>
                <div class="flex items-center gap-0.5">
                    <DollarSign class="w-3.5 h-3.5 text-verge-text-muted" />
                    <span class="font-sans font-bold text-verge-text-primary">{{ formatPrice(booking.total_price) }}</span>
                </div>
            </div>
        </div>

        <!-- Status & Actions -->
        <div class="flex items-center gap-2 border-t border-verge-text-primary/10 pt-4 md:border-t-0 md:pt-0 justify-between md:justify-end">
            <BookingStatusBadge :status="booking.status" />

            <div class="flex items-center gap-2">
                <button 
                    @click="isDetailModalOpen = true"
                    class="px-3 py-1.5 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted border border-verge-text-primary font-mono text-[9px] uppercase font-bold rounded-sm transition-colors"
                >
                    Detail
                </button>

                <button 
                    v-if="booking.status === 'confirmed'"
                    @click="cancelBooking"
                    class="flex items-center gap-1 px-3 py-1.5 border border-red-200 hover:bg-red-50 text-red-600 font-mono text-[9px] uppercase font-bold rounded-sm transition-colors"
                >
                    <Ban class="w-3 h-3" />
                    <span>Batalkan</span>
                </button>
            </div>
        </div>

        <!-- DETAIL BOOKING MODAL -->
        <div v-if="isDetailModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" @click="isDetailModalOpen = false"></div>
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[8px_8px_0px_0px_rgba(19,19,19,1)] w-full max-w-lg relative z-10 overflow-hidden flex flex-col text-left">
                <div class="bg-verge-surface-light border-b-2 border-verge-text-primary p-4 flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold uppercase text-verge-text-primary">Detail Booking: {{ booking.booking_number }}</h3>
                    <button @click="isDetailModalOpen = false" class="p-1.5 hover:bg-verge-text-primary/10 rounded-sm">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <div class="p-6 space-y-4 font-sans text-xs">
                    <!-- Status & Basic Info -->
                    <div class="flex items-center justify-between border-b border-verge-text-primary/10 pb-3">
                        <div>
                            <span class="text-[10px] font-mono text-verge-text-muted uppercase block">Status Sewa</span>
                            <span class="mt-1 block">
                                <BookingStatusBadge :status="booking.status" />
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-mono text-verge-text-muted uppercase block">Total Bayar</span>
                            <span class="font-mono font-bold text-sm text-verge-ultraviolet block">{{ formatPrice(booking.total_price) }}</span>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="space-y-2">
                        <h4 class="font-mono text-[9px] uppercase font-bold text-verge-ultraviolet tracking-wider">Identitas Pelanggan</h4>
                        <div class="grid grid-cols-2 gap-3 bg-verge-surface-light border border-verge-text-primary/10 p-3 rounded-sm font-mono text-[10px] text-verge-text-muted">
                            <div>
                                <span>Nama Lengkap:</span>
                                <strong class="text-verge-text-primary block mt-0.5">{{ booking.customer_name }}</strong>
                            </div>
                            <div>
                                <span>Nomor HP (WhatsApp):</span>
                                <strong class="text-verge-text-primary block mt-0.5">{{ booking.customer_phone }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Court Schedule Info -->
                    <div class="space-y-2">
                        <h4 class="font-mono text-[9px] uppercase font-bold text-verge-ultraviolet tracking-wider">Jadwal Penyewaan Lapangan</h4>
                        <div class="grid grid-cols-2 gap-3 bg-verge-surface-light border border-verge-text-primary/10 p-3 rounded-sm font-mono text-[10px] text-verge-text-muted">
                            <div>
                                <span>Lapangan:</span>
                                <strong class="text-verge-text-primary block mt-0.5 uppercase">{{ booking.court.name }}</strong>
                            </div>
                            <div>
                                <span>Tanggal Bermain:</span>
                                <strong class="text-verge-text-primary block mt-0.5">{{ formatDate(booking.date) }}</strong>
                            </div>
                            <div class="col-span-2">
                                <span>Waktu / Jam Bermain:</span>
                                <strong class="text-verge-text-primary block mt-0.5">
                                    {{ booking.start_time.substring(0, 5) }} s/d {{ booking.end_time.substring(0, 5) }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Details if Completed -->
                    <div v-if="booking.payment" class="space-y-2">
                        <h4 class="font-mono text-[9px] uppercase font-bold text-green-600 tracking-wider">Rincian Pembayaran</h4>
                        <div class="bg-green-50/50 border border-green-200 p-3 rounded-sm font-mono text-[10px] text-verge-text-muted space-y-1.5">
                            <div class="flex justify-between">
                                <span>Metode Pembayaran:</span>
                                <strong class="text-verge-text-primary uppercase">{{ booking.payment.payment_method ?? 'CASH' }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span>Jumlah Dibayar:</span>
                                <strong class="text-verge-text-primary">{{ formatPrice(booking.payment.amount) }}</strong>
                            </div>
                            <div v-if="Number(booking.payment.refund_amount) > 0" class="flex justify-between text-red-600">
                                <span>Jumlah Dana Direfund:</span>
                                <strong>{{ formatPrice(booking.payment.refund_amount) }}</strong>
                            </div>
                            <div v-if="booking.payment.refund_reason" class="border-t border-green-200/50 pt-1.5 mt-1.5 text-red-700">
                                <span>Alasan Refund:</span>
                                <p class="font-sans text-[10px] mt-0.5 leading-relaxed bg-red-50 p-1.5 rounded-sm border border-red-100">
                                    "{{ booking.payment.refund_reason }}"
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Cancel Details if Cancelled -->
                    <div v-if="booking.status === 'cancelled'" class="space-y-2">
                        <h4 class="font-mono text-[9px] uppercase font-bold text-red-600 tracking-wider">Informasi Pembatalan</h4>
                        <div class="bg-red-50 border border-red-200 p-3 rounded-sm font-mono text-[10px] text-red-800 space-y-1.5">
                            <div class="flex justify-between">
                                <span>Dibatalkan Oleh:</span>
                                <strong class="text-red-900 uppercase">{{ booking.cancelled_by?.name ?? 'Customer/System' }}</strong>
                            </div>
                            <div class="border-t border-red-200 pt-1.5 mt-1.5">
                                <span>Alasan Pembatalan:</span>
                                <p class="font-sans text-[10px] text-red-950 mt-0.5 leading-relaxed bg-white p-2 rounded border border-red-100 italic">
                                    "{{ booking.cancel_reason ?? 'Tanpa alasan spesifik.' }}"
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Instruction Details if Confirmed -->
                    <div v-if="booking.status === 'confirmed'" class="space-y-2">
                        <h4 class="font-mono text-[9px] uppercase font-bold text-verge-ultraviolet tracking-wider">Instruksi Pembayaran</h4>
                        <div class="bg-verge-surface-light border border-verge-text-primary/10 p-3 rounded-sm font-sans text-[10px] text-verge-text-muted leading-relaxed">
                            <p>• Silakan datang ke lokasi lapangan futsal minimal 10 menit sebelum waktu bermain.</p>
                            <p>• Konfirmasikan nomor booking <strong class="font-mono text-verge-ultraviolet font-bold">{{ booking.booking_number }}</strong> ke Admin penjaga lapangan.</p>
                            <p>• Pembayaran dapat diselesaikan di lokasi via Tunai (Cash), Transfer Bank, atau scan QRIS resmi yang disediakan Admin.</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-verge-surface-light border-t-2 border-verge-text-primary p-4 flex justify-end">
                    <button @click="isDetailModalOpen = false" class="px-5 py-2 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted border border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
