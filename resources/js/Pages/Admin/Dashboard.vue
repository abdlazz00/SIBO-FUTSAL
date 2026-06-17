<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
    Dumbbell, Calendar, DollarSign, Clock, CheckCircle, 
    TrendingUp, User, ArrowRight, BarChart3, Star, Sparkles
} from 'lucide-vue-next';

interface Court {
    id: number;
    name: string;
    type: 'indoor' | 'outdoor';
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

interface Stats {
    bookings_today: number;
    revenue_today: number;
    active_courts: number;
    pending_payments: number;
}

interface Occupancy {
    court_id: number;
    court_name: string;
    booked_slots: number;
    total_slots: number;
    rate: number;
}

interface TopCourt {
    court_id: number;
    court_name: string;
    type: 'indoor' | 'outdoor';
    bookings_count: number;
    price: number;
}

interface PeakHour {
    hour: string;
    count: number;
}

const props = defineProps<{
    stats: Stats;
    recentBookings: Booking[];
    topCourts: TopCourt[];
    occupancyRates: Occupancy[];
    peakHours: PeakHour[];
}>();

// Helpers
const formatPrice = (price: number | string) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(Number(price));
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};

const formatTime = (timeStr: string) => {
    return timeStr.substring(0, 5);
};

// Calculate average occupancy
const averageOccupancy = computed(() => {
    if (props.occupancyRates.length === 0) return 0;
    const sum = props.occupancyRates.reduce((acc, curr) => acc + curr.rate, 0);
    return Math.round(sum / props.occupancyRates.length);
});

// Maximum peak count for bar chart calculations
const maxPeakCount = computed(() => {
    const counts = props.peakHours.map(p => p.count);
    return Math.max(...counts, 1);
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header Bento Card -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-verge-ultraviolet">Panel Administrasi</span>
                    <h1 class="text-3xl font-display font-bold uppercase mt-1">Admin Dashboard</h1>
                    <p class="text-xs text-verge-text-muted mt-1">Selamat datang kembali! Kelola booking, jadwal sewa, dan konfirmasi pembayaran di sini.</p>
                </div>
                <div>
                    <span class="bg-verge-jelly-mint text-verge-text-primary px-4 py-2 border-2 border-verge-text-primary font-mono text-xs uppercase tracking-wider font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] text-[10px] flex items-center gap-1.5">
                        <Sparkles class="w-3.5 h-3.5 animate-pulse" />
                        <span>System Online</span>
                    </span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Bookings Today -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-32">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider">Booking Hari Ini</span>
                        <Calendar class="w-4 h-4 text-verge-ultraviolet" />
                    </div>
                    <span class="text-3xl font-display font-bold mt-2">{{ stats.bookings_today }}</span>
                    <span class="text-[9px] font-mono text-verge-text-muted mt-1">Sesi bermain terjadwal hari ini</span>
                </div>

                <!-- Occupancy Rate -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-32">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider">Rata-rata Okupansi</span>
                        <TrendingUp class="w-4 h-4 text-verge-ultraviolet" />
                    </div>
                    <span class="text-3xl font-display font-bold mt-2">{{ averageOccupancy }}%</span>
                    <span class="text-[9px] font-mono text-verge-text-muted mt-1">Okupansi slot lapangan aktif</span>
                </div>

                <!-- Unpaid Bookings -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-32">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider">Menunggu Pembayaran</span>
                        <Clock class="w-4 h-4 text-amber-600" />
                    </div>
                    <span class="text-3xl font-display font-bold mt-2 text-verge-ultraviolet">{{ stats.pending_payments }}</span>
                    <span class="text-[9px] font-mono text-amber-600 mt-1">Verifikasi bayar manual secepatnya</span>
                </div>

                <!-- Revenue Today -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-32">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider">Pendapatan Hari Ini</span>
                        <DollarSign class="w-4 h-4 text-green-600" />
                    </div>
                    <span class="text-2xl font-display font-bold mt-2 text-green-600">{{ formatPrice(stats.revenue_today) }}</span>
                    <span class="text-[9px] font-mono text-verge-text-muted mt-1">Kas lunas terkonfirmasi hari ini</span>
                </div>
            </div>

            <!-- Bento Row 1: Occupancy Rates & Peak Hours -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Occupancy Rates Progress Bars -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] lg:col-span-1 flex flex-col justify-between">
                    <div>
                        <div class="border-b border-verge-text-primary/10 pb-2 mb-4">
                            <span class="text-[9px] font-mono uppercase font-bold text-verge-text-muted block">Slot Utilization</span>
                            <h3 class="font-display text-lg font-bold uppercase mt-0.5">Okupansi Lapangan</h3>
                        </div>

                        <div class="space-y-4 py-2">
                            <div v-for="court in occupancyRates" :key="court.court_id" class="space-y-1">
                                <div class="flex justify-between font-mono text-[10px] font-bold">
                                    <span class="uppercase">{{ court.court_name }}</span>
                                    <span>{{ court.booked_slots }}/{{ court.total_slots }} Slot ({{ court.rate }}%)</span>
                                </div>
                                <div class="h-3.5 bg-verge-surface-light border-2 border-verge-text-primary rounded-sm overflow-hidden flex">
                                    <div 
                                        :style="{ width: court.rate + '%' }" 
                                        class="bg-verge-ultraviolet border-r-2 border-verge-text-primary transition-all duration-500"
                                        :class="{ 'bg-verge-jelly-mint': court.rate >= 80 }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <span class="text-[9px] font-mono text-verge-text-muted mt-4 block">
                        *Slot dihitung berdasarkan jam buka-tutup dan durasi per sesi lapangan.
                    </span>
                </div>

                <!-- Peak Hours Heatmap Chart -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] lg:col-span-2">
                    <div class="border-b border-verge-text-primary/10 pb-2 mb-4">
                        <span class="text-[9px] font-mono uppercase font-bold text-verge-text-muted block">Busiest Hours</span>
                        <h3 class="font-display text-lg font-bold uppercase mt-0.5">Frekuensi Jam Ramah Main</h3>
                    </div>

                    <!-- CSS-based horizontal bar visualization for peak times -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 py-2">
                        <div 
                            v-for="peak in peakHours" 
                            :key="peak.hour"
                            class="border border-verge-text-primary/20 p-2.5 rounded-sm bg-verge-surface-light flex items-center justify-between"
                        >
                            <span class="font-mono text-xs font-bold">{{ peak.hour }}</span>
                            <span 
                                class="font-mono text-[10px] font-bold px-2 py-0.5 border border-verge-text-primary rounded-xs",
                                :class="[peak.count > 0 ? 'bg-verge-jelly-mint text-verge-text-primary' : 'bg-verge-canvas-white text-verge-text-muted']"
                            >
                                {{ peak.count }} book
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bento Row 2: Recent Bookings & Top Courts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recent Bookings Table -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] lg:col-span-2">
                    <div class="flex items-center justify-between border-b border-verge-text-primary/10 pb-2 mb-4">
                        <div>
                            <span class="text-[9px] font-mono uppercase font-bold text-verge-text-muted block">Real-time Activity</span>
                            <h3 class="font-display text-lg font-bold uppercase mt-0.5">Pemesanan Terbaru</h3>
                        </div>
                        <Link :href="route('admin.bookings.index')" class="flex items-center gap-1 font-mono text-[9px] uppercase font-bold text-verge-ultraviolet hover:text-verge-deep-link-blue">
                            <span>Lihat Semua</span>
                            <ArrowRight class="w-3.5 h-3.5" />
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-verge-surface-light border-b border-verge-text-primary/15 font-mono text-[9px] uppercase font-bold text-verge-text-muted">
                                    <th class="py-2 px-2">No. Booking</th>
                                    <th class="py-2 px-2">Customer</th>
                                    <th class="py-2 px-2">Lapangan</th>
                                    <th class="py-2 px-2">Tanggal</th>
                                    <th class="py-2 px-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-verge-text-primary/10">
                                <tr v-if="recentBookings.length === 0">
                                    <td colspan="5" class="py-4 text-center font-mono text-verge-text-muted">Belum ada booking masuk.</td>
                                </tr>
                                <tr v-for="booking in recentBookings" :key="booking.id" class="hover:bg-verge-surface-light/40 transition-colors">
                                    <td class="py-2.5 px-2 font-mono font-bold text-verge-ultraviolet">
                                        {{ booking.booking_number }}
                                    </td>
                                    <td class="py-2.5 px-2 font-medium">
                                        {{ booking.customer_name }}
                                    </td>
                                    <td class="py-2.5 px-2 uppercase font-bold text-[10px]">
                                        {{ booking.court?.name.split(' (')[0] }}
                                    </td>
                                    <td class="py-2.5 px-2 font-mono">
                                        {{ formatDate(booking.date) }} <span class="text-[10px] text-verge-text-muted">({{ formatTime(booking.start_time) }})</span>
                                    </td>
                                    <td class="py-2.5 px-2">
                                        <span v-if="booking.status === 'completed'" class="font-mono text-[8px] uppercase font-bold text-green-600 bg-green-50 border border-green-200 px-1.5 py-0.5 rounded-sm">
                                            Completed
                                        </span>
                                        <span v-else-if="booking.status === 'confirmed'" class="font-mono text-[8px] uppercase font-bold text-blue-600 bg-blue-50 border border-blue-200 px-1.5 py-0.5 rounded-sm">
                                            Confirmed
                                        </span>
                                        <span v-else class="font-mono text-[8px] uppercase font-bold text-red-600 bg-red-50 border border-red-200 px-1.5 py-0.5 rounded-sm">
                                            Cancelled
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Courts Leaderboard -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] lg:col-span-1 flex flex-col justify-between">
                    <div>
                        <div class="border-b border-verge-text-primary/10 pb-2 mb-4">
                            <span class="text-[9px] font-mono uppercase font-bold text-verge-text-muted block">Performance</span>
                            <h3 class="font-display text-lg font-bold uppercase mt-0.5">Lapangan Terlaris</h3>
                        </div>

                        <div class="space-y-4 py-1">
                            <div v-if="topCourts.length === 0" class="text-center py-6 font-mono text-xs text-verge-text-muted">
                                Belum ada data penyewaan.
                            </div>
                            <div 
                                v-for="(court, index) in topCourts" 
                                :key="court.court_id"
                                class="flex items-center gap-3 border border-verge-text-primary/10 p-2.5 rounded-sm bg-verge-surface-light hover:border-verge-text-primary transition-colors"
                            >
                                <span class="w-6 h-6 rounded-full border-2 border-verge-text-primary bg-verge-canvas-black text-verge-canvas-white flex items-center justify-center font-mono font-bold text-xs">
                                    {{ index + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-xs uppercase truncate">{{ court.court_name }}</div>
                                    <div class="font-mono text-[9px] text-verge-text-muted uppercase mt-0.5">{{ court.type }} &bull; {{ formatPrice(court.price) }}/Jam</div>
                                </div>
                                <div class="font-mono text-right">
                                    <div class="font-bold text-verge-ultraviolet text-xs">{{ court.bookings_count }}x</div>
                                    <div class="text-[8px] text-verge-text-muted uppercase">Booked</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <span class="text-[9px] font-mono text-verge-text-muted block mt-4">
                        *Diurutkan berdasarkan frekuensi booking non-cancelled.
                    </span>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
