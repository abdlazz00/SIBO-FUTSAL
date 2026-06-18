<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';
import { 
    TrendingUp, Calendar, DollarSign, Clock, Users, ArrowUpRight, 
    ArrowDownRight, BarChart3, ShieldAlert, Sparkles, ChevronRight
} from 'lucide-vue-next';
import gsap from 'gsap';

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

interface AdminStats {
    bookings_today: number;
    revenue_today: number;
    active_courts: number;
    pending_payments: number;
}

interface OwnerStats {
    revenue_month: number;
    expense_month: number;
    profit_month: number;
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
    ownerStats: OwnerStats;
    adminStats: AdminStats;
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

onMounted(() => {
    // 1. Reveal page title/header card
    gsap.fromTo('.dash-header',
        { y: -20, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.5, ease: 'power2.out' }
    );

    // 2. Reveal stats grid cards
    gsap.fromTo('.stats-card',
        { y: 25, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.6, stagger: 0.08, ease: 'power3.out', delay: 0.1 }
    );

    // 3. Reveal bento boxes
    gsap.fromTo('.bento-card',
        { y: 30, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.7, stagger: 0.1, ease: 'power3.out', delay: 0.25 }
    );

    // 4. Animate occupancy progress bar width using scaleX
    gsap.fromTo('.occupancy-bar',
        { scaleX: 0 },
        { scaleX: 1, transformOrigin: 'left', duration: 1.2, ease: 'power2.out', delay: 0.45 }
    );
});
</script>

<template>
    <Head title="Owner Dashboard" />

    <OwnerLayout>
        <div class="space-y-6">
            <!-- Header Bento Card -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4 dash-header">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-verge-ultraviolet">Panel Pemilik (Owner)</span>
                    <h1 class="text-3xl font-display font-bold uppercase mt-1">Owner Dashboard</h1>
                    <p class="text-xs text-verge-text-muted mt-1">Pantau kinerja finansial, okupansi lapangan, dan laporan laba-rugi secara komprehensif.</p>
                </div>
                <div>
                    <span class="bg-verge-jelly-mint text-verge-text-primary px-4 py-2 border-2 border-verge-text-primary font-mono text-xs uppercase tracking-wider font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] text-[10px] flex items-center gap-1.5">
                        <Sparkles class="w-3.5 h-3.5" />
                        <span>Owner Access</span>
                    </span>
                </div>
            </div>

            <!-- Owner Monthly Cash Flow Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Revenue month -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex items-center justify-between stats-card">
                    <div>
                        <span class="text-[9px] font-mono text-verge-text-muted uppercase tracking-wider block">Omzet Bulan Ini</span>
                        <span class="text-2xl font-display font-bold text-green-600 block mt-1">{{ formatPrice(ownerStats.revenue_month) }}</span>
                        <span class="text-[8px] font-mono text-verge-text-muted block mt-0.5">Kas masuk terkonfirmasi</span>
                    </div>
                    <div class="w-10 h-10 border-2 border-verge-text-primary rounded-full bg-green-50 flex items-center justify-center">
                        <ArrowUpRight class="w-5 h-5 text-green-600" />
                    </div>
                </div>

                <!-- Expense month -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex items-center justify-between stats-card">
                    <div>
                        <span class="text-[9px] font-mono text-verge-text-muted uppercase tracking-wider block">Biaya Operasional</span>
                        <span class="text-2xl font-display font-bold text-red-600 block mt-1">{{ formatPrice(ownerStats.expense_month) }}</span>
                        <span class="text-[8px] font-mono text-verge-text-muted block mt-0.5">Listrik, perawatan, gaji staf</span>
                    </div>
                    <div class="w-10 h-10 border-2 border-verge-text-primary rounded-full bg-red-50 flex items-center justify-center">
                        <ArrowDownRight class="w-5 h-5 text-red-600" />
                    </div>
                </div>

                <!-- Net Profit month -->
                <div class="border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex items-center justify-between transition-colors stats-card",
                    :class="[ownerStats.profit_month >= 0 ? 'bg-verge-jelly-mint/10 border-verge-text-primary' : 'bg-red-50 border-verge-text-primary']"
                >
                    <div>
                        <span class="text-[9px] font-mono text-verge-text-muted uppercase tracking-wider block">Laba Bersih Estimasi</span>
                        <span 
                            class="text-2xl font-display font-bold block mt-1",
                            :class="[ownerStats.profit_month >= 0 ? 'text-verge-text-primary' : 'text-red-700']"
                        >
                            {{ formatPrice(ownerStats.profit_month) }}
                        </span>
                        <span class="text-[8px] font-mono text-verge-text-muted block mt-0.5">Laba bersih bulan berjalan</span>
                    </div>
                    <div 
                        class="w-10 h-10 border-2 border-verge-text-primary rounded-full flex items-center justify-center",
                        :class="[ownerStats.profit_month >= 0 ? 'bg-verge-jelly-mint' : 'bg-red-200']"
                    >
                        <TrendingUp class="w-5 h-5 text-verge-text-primary" />
                    </div>
                </div>
            </div>

            <!-- Quick Access Navigation & Today's Summary Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Quick Navigation Box -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between bento-card">
                    <div>
                        <div class="border-b border-verge-text-primary/10 pb-2 mb-4">
                            <span class="text-[9px] font-mono uppercase font-bold text-verge-text-muted block">Direct Navigation</span>
                            <h3 class="font-display text-lg font-bold uppercase mt-0.5">Akses Cepat Menu</h3>
                        </div>

                        <div class="space-y-3 font-mono text-xs uppercase font-bold">
                            <Link :href="route('owner.reports.index')" class="flex items-center justify-between border-2 border-verge-text-primary p-3 rounded-sm bg-verge-surface-light hover:bg-verge-canvas-white hover:shadow-[2px_2px_0px_0px_rgba(82,0,255,1)] hover:border-verge-ultraviolet transition-all">
                                <span class="flex items-center gap-2">
                                    <BarChart3 class="w-4 h-4 text-verge-ultraviolet" />
                                    <span>Laporan Keuangan</span>
                                </span>
                                <ChevronRight class="w-4 h-4" />
                            </Link>

                            <Link :href="route('admin.payments.index')" class="flex items-center justify-between border-2 border-verge-text-primary p-3 rounded-sm bg-verge-surface-light hover:bg-verge-canvas-white hover:shadow-[2px_2px_0px_0px_rgba(82,0,255,1)] hover:border-verge-ultraviolet transition-all">
                                <span class="flex items-center gap-2">
                                    <DollarSign class="w-4 h-4 text-verge-ultraviolet" />
                                    <span>Daftar Transaksi</span>
                                </span>
                                <ChevronRight class="w-4 h-4" />
                            </Link>
                        </div>
                    </div>

                    <span class="text-[9px] font-mono text-verge-text-muted mt-4 block">
                        *Gunakan modul Laporan Keuangan untuk analisis tren kas periode sebelumnya.
                    </span>
                </div>

                <!-- Today's Admin Metrics Summary -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] lg:col-span-2 bento-card">
                    <div class="border-b border-verge-text-primary/10 pb-2 mb-4">
                        <span class="text-[9px] font-mono uppercase font-bold text-verge-text-muted block">Today's Performance Overview</span>
                        <h3 class="font-display text-lg font-bold uppercase mt-0.5 font-bold">Status Aktivitas Lapangan Hari Ini</h3>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-2 font-mono text-xs">
                        <div class="border border-verge-text-primary/10 p-3 rounded bg-verge-surface-light text-center">
                            <div class="text-[9px] text-verge-text-muted mb-1">BOOKING HARI INI</div>
                            <div class="font-bold text-lg text-verge-text-primary">{{ adminStats.bookings_today }} Sesi</div>
                        </div>

                        <div class="border border-verge-text-primary/10 p-3 rounded bg-verge-surface-light text-center">
                            <div class="text-[9px] text-verge-text-muted mb-1">RATA OKUPANSI</div>
                            <div class="font-bold text-lg text-verge-text-primary">{{ averageOccupancy }}%</div>
                        </div>

                        <div class="border border-verge-text-primary/10 p-3 rounded bg-verge-surface-light text-center">
                            <div class="text-[9px] text-verge-text-muted mb-1">PENDAPATAN HARIAN</div>
                            <div class="font-bold text-lg text-green-600">{{ formatPrice(adminStats.revenue_today) }}</div>
                        </div>

                        <div class="border border-verge-text-primary/10 p-3 rounded bg-verge-surface-light text-center">
                            <div class="text-[9px] text-verge-text-muted mb-1">BELUM DIKONFIRMASI</div>
                            <div class="font-bold text-lg text-amber-600">{{ adminStats.pending_payments }} Booking</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bento Row 3: Occupancy & Busiest Courts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Occupancy Rates Progress Bars -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] lg:col-span-1 flex flex-col justify-between bento-card">
                    <div>
                        <div class="border-b border-verge-text-primary/10 pb-2 mb-4">
                            <span class="text-[9px] font-mono uppercase font-bold text-verge-text-muted block">Slot Utilization</span>
                            <h3 class="font-display text-lg font-bold uppercase mt-0.5">Kinerja Lapangan</h3>
                        </div>

                        <div class="space-y-4 py-2">
                            <div v-for="court in occupancyRates" :key="court.court_id" class="space-y-1">
                                <div class="flex justify-between font-mono text-[10px] font-bold">
                                    <span class="uppercase">{{ court.court_name }}</span>
                                    <span>{{ court.rate }}% Okupansi</span>
                                </div>
                                <div class="h-3.5 bg-verge-surface-light border-2 border-verge-text-primary rounded-sm overflow-hidden flex">
                                    <div 
                                        :style="{ width: court.rate + '%' }" 
                                        class="bg-verge-ultraviolet border-r-2 border-verge-text-primary occupancy-bar"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <span class="text-[9px] font-mono text-verge-text-muted mt-4 block">
                        *Data okupansi menunjukkan persentase slot terjadwal hari ini.
                    </span>
                </div>

                <!-- Recent Activity Table -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] lg:col-span-2 bento-card">
                    <div class="flex items-center justify-between border-b border-verge-text-primary/10 pb-2 mb-4">
                        <div>
                            <span class="text-[9px] font-mono uppercase font-bold text-verge-text-muted block">Live Feed</span>
                            <h3 class="font-display text-lg font-bold uppercase mt-0.5">Aktivitas Pemesanan Terkini</h3>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-verge-surface-light border-b border-verge-text-primary/15 font-mono text-[9px] uppercase font-bold text-verge-text-muted">
                                    <th class="py-2 px-2">No. Booking</th>
                                    <th class="py-2 px-2">Customer</th>
                                    <th class="py-2 px-2">Lapangan</th>
                                    <th class="py-2 px-2">Total Harga</th>
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
                                    <td class="py-2.5 px-2 font-mono font-bold">
                                        {{ formatPrice(booking.total_price) }}
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
            </div>
        </div>
    </OwnerLayout>
</template>
