<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import BookingCard from '@/Features/booking/components/BookingCard.vue';
import { Calendar } from 'lucide-vue-next';

interface Court {
    name: string;
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
    court: Court;
}

const props = defineProps<{
    bookings: Booking[];
    filters: {
        status?: string;
    };
}>();
</script>

<template>
    <Head title="Histori Booking Saya" />

    <CustomerLayout>
        <div class="space-y-6">
            <!-- Header Card -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-verge-ultraviolet">Akun Saya</span>
                    <h1 class="text-3xl font-display font-bold uppercase mt-1">Histori Booking</h1>
                    <p class="text-xs text-verge-text-muted mt-1">Daftar lengkap riwayat pemesanan lapangan futsal Anda di Vitka Futsal.</p>
                </div>
                <div>
                    <Link :href="route('booking.create')" class="flex items-center justify-center gap-1.5 px-5 py-3 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted border-2 border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] transition-all">
                        <span>Pesan Lapangan Baru</span>
                    </Link>
                </div>
            </div>

            <!-- List bookings -->
            <div v-if="bookings.length === 0" class="bg-verge-canvas-white border-2 border-verge-text-primary p-12 rounded-lg text-center flex flex-col items-center justify-center shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
                <Calendar class="w-12 h-12 text-verge-text-primary/20" />
                <h3 class="font-display text-lg font-bold uppercase mt-4">Belum ada booking</h3>
                <p class="text-xs text-verge-text-muted mt-1 font-mono">Anda belum melakukan pemesanan lapangan futsal apapun.</p>
                <Link :href="route('booking.create')" class="mt-6 px-5 py-2.5 bg-verge-ultraviolet text-verge-canvas-white font-mono text-xs uppercase font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,1)]">
                    Mulai Booking Pertama &rarr;
                </Link>
            </div>

            <div v-else class="space-y-4">
                <div v-for="booking in bookings" :key="booking.id">
                    <BookingCard :booking="booking" />
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
