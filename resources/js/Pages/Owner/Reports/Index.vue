<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';
import { 
    Calendar, DollarSign, Download, ArrowUpRight, ArrowDownRight, 
    TrendingUp, Award, PieChart, Receipt, RefreshCw, ChevronRight
} from 'lucide-vue-next';

interface CourtRevenue {
    court_id: number;
    court_name: string;
    bookings_count: number;
    revenue: number;
}

interface MonthTrend {
    month: number;
    name: string;
    revenue: number;
    expense: number;
    profit: number;
}

interface Payment {
    id: number;
    booking_id: number;
    payment_method: string | null;
    amount: number;
    refund_amount: number;
    confirmed_at: string | null;
    booking: {
        booking_number: string;
        customer_name: string;
        court: {
            name: string;
        };
    };
}

interface Summary {
    total_revenue: number;
    total_expense: number;
    net_profit: number;
}

const props = defineProps<{
    summary: Summary;
    revenueByMethod: {
        cash: number;
        transfer: number;
        qris: number;
    };
    revenueByCourt: CourtRevenue[];
    expenseByCategory: {
        utilities: number;
        maintenance: number;
        salaries: number;
        other: number;
    };
    monthlyTrend: MonthTrend[];
    payments: Payment[];
    filters: {
        period?: string;
        start_date?: string;
        end_date?: string;
    };
}>();

// Filter states
const periodInput = ref(props.filters.period ?? 'this_month');
const startDateInput = ref(props.filters.start_date ?? '');
const endDateInput = ref(props.filters.end_date ?? '');

const applyFilters = () => {
    router.get(route('owner.reports.index'), {
        period: periodInput.value,
        start_date: startDateInput.value,
        end_date: endDateInput.value
    }, {
        preserveState: true,
        replace: true
    });
};

const exportCsv = () => {
    const url = route('owner.reports.export', {
        period: periodInput.value,
        start_date: startDateInput.value,
        end_date: endDateInput.value
    });
    window.location.href = url;
};

// Calculations for Payment Method stacked bar
const totalMethodRevenue = computed(() => {
    const { cash, transfer, qris } = props.revenueByMethod;
    return Number(cash) + Number(transfer) + Number(qris) || 1;
});
const cashPercent = computed(() => Math.round((Number(props.revenueByMethod.cash) / totalMethodRevenue.value) * 100));
const transferPercent = computed(() => Math.round((Number(props.revenueByMethod.transfer) / totalMethodRevenue.value) * 100));
const qrisPercent = computed(() => Math.round((Number(props.revenueByMethod.qris) / totalMethodRevenue.value) * 100));

// Calculations for Expense Category stacked bar
const totalCategoryExpense = computed(() => {
    const { utilities, maintenance, salaries, other } = props.expenseByCategory;
    return Number(utilities) + Number(maintenance) + Number(salaries) + Number(other) || 1;
});
const utilPercent = computed(() => Math.round((Number(props.expenseByCategory.utilities) / totalCategoryExpense.value) * 100));
const maintPercent = computed(() => Math.round((Number(props.expenseByCategory.maintenance) / totalCategoryExpense.value) * 100));
const salariesPercent = computed(() => Math.round((Number(props.expenseByCategory.salaries) / totalCategoryExpense.value) * 100));
const otherPercent = computed(() => Math.round((Number(props.expenseByCategory.other) / totalCategoryExpense.value) * 100));

// Helper for Court Bar Height (Y-axis max height is 140px)
const maxCourtRevenue = computed(() => {
    const revenues = props.revenueByCourt.map(c => Number(c.revenue));
    return Math.max(...revenues, 1);
});
const getCourtBarHeight = (revenue: number) => {
    return Math.max(10, Math.round((Number(revenue) / maxCourtRevenue.value) * 140));
};

// Helper for Trend Line Height (Y-axis max height is 160px)
const maxTrendAmount = computed(() => {
    let max = 1;
    props.monthlyTrend.forEach(t => {
        if (Number(t.revenue) > max) max = Number(t.revenue);
        if (Number(t.expense) > max) max = Number(t.expense);
    });
    return max;
});
const getTrendBarHeight = (amount: number) => {
    return Math.max(0, Math.round((Number(amount) / maxTrendAmount.value) * 160));
};

// General helpers
const formatPrice = (price: number | string) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(Number(price));
};

const formatDate = (dateStr: string | null) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Laporan Keuangan Owner" />

    <OwnerLayout>
        <div class="space-y-6">
            <!-- Header Card -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-verge-ultraviolet">Financial Overview</span>
                    <h1 class="text-3xl font-display font-bold uppercase mt-1">Laporan Analisis Keuangan</h1>
                    <p class="text-xs text-verge-text-muted mt-1">Laporan arus kas bersih lapangan, perbandingan omzet vs pengeluaran operasional, dan grafik profit margin.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="exportCsv" class="flex items-center justify-center gap-2 px-5 py-3 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted border-2 border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] transition-all">
                        <Download class="w-4 h-4" />
                        <span>Ekspor CSV</span>
                    </button>
                </div>
            </div>

            <!-- Period Filter Bar -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-4 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-col md:flex-row md:items-center gap-3 flex-1">
                    <div class="space-y-1">
                        <label class="font-mono text-[9px] uppercase font-bold text-verge-text-muted block">Pilih Periode</label>
                        <select v-model="periodInput" @change="applyFilters" class="border-2 border-verge-text-primary px-3 py-2 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white min-w-44">
                            <option value="today">Hari Ini</option>
                            <option value="last_7_days">7 Hari Terakhir</option>
                            <option value="last_30_days">30 Hari Terakhir</option>
                            <option value="this_month">Bulan Ini</option>
                            <option value="this_year">Tahun Ini</option>
                            <option value="custom">Rentang Tanggal Kustom</option>
                        </select>
                    </div>

                    <div v-if="periodInput === 'custom'" class="flex items-center gap-2">
                        <div class="space-y-1">
                            <label class="font-mono text-[9px] uppercase font-bold text-verge-text-muted block">Tanggal Mulai</label>
                            <input v-model="startDateInput" type="date" @change="applyFilters" class="border-2 border-verge-text-primary px-3 py-1.5 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white" />
                        </div>
                        <span class="mt-4 font-mono text-xs">s/d</span>
                        <div class="space-y-1">
                            <label class="font-mono text-[9px] uppercase font-bold text-verge-text-muted block">Tanggal Selesai</label>
                            <input v-model="endDateInput" type="date" @change="applyFilters" class="border-2 border-verge-text-primary px-3 py-1.5 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white" />
                        </div>
                    </div>
                </div>

                <div class="flex items-end">
                    <button @click="applyFilters" class="px-5 py-2.5 bg-verge-canvas-black text-verge-canvas-white border border-verge-text-primary hover:bg-verge-text-muted font-mono text-xs uppercase font-bold rounded-sm transition-colors">
                        Terapkan Filter
                    </button>
                </div>
            </div>

            <!-- Financial Cards Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Revenue Card -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex items-center justify-between">
                    <div class="space-y-1">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider block">Total Pendapatan (In)</span>
                        <span class="text-3xl font-display font-bold text-green-600 block">{{ formatPrice(summary.total_revenue) }}</span>
                        <span class="text-[9px] font-mono text-verge-text-muted block">Dari konfirmasi booking lunas</span>
                    </div>
                    <div class="w-12 h-12 rounded-full border-2 border-verge-text-primary bg-green-50 flex items-center justify-center">
                        <ArrowUpRight class="w-6 h-6 text-green-600" />
                    </div>
                </div>

                <!-- Expense Card -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex items-center justify-between">
                    <div class="space-y-1">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider block">Total Pengeluaran (Out)</span>
                        <span class="text-3xl font-display font-bold text-red-600 block">{{ formatPrice(summary.total_expense) }}</span>
                        <span class="text-[9px] font-mono text-verge-text-muted block">Utilitas, pemeliharaan & gaji</span>
                    </div>
                    <div class="w-12 h-12 rounded-full border-2 border-verge-text-primary bg-red-50 flex items-center justify-center">
                        <ArrowDownRight class="w-6 h-6 text-red-600" />
                    </div>
                </div>

                <!-- Profit Card -->
                <div class="border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex items-center justify-between transition-colors",
                    :class="[summary.net_profit >= 0 ? 'bg-verge-jelly-mint/10 border-verge-text-primary' : 'bg-red-50 border-verge-text-primary']"
                >
                    <div class="space-y-1">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider block">Keuntungan Bersih (Profit)</span>
                        <span 
                            class="text-3xl font-display font-bold block",
                            :class="[summary.net_profit >= 0 ? 'text-verge-text-primary' : 'text-red-700']"
                        >
                            {{ formatPrice(summary.net_profit) }}
                        </span>
                        <span class="text-[9px] font-mono text-verge-text-muted block">Arus kas bersih periode terpilih</span>
                    </div>
                    <div 
                        class="w-12 h-12 rounded-full border-2 border-verge-text-primary flex items-center justify-center",
                        :class="[summary.net_profit >= 0 ? 'bg-verge-jelly-mint' : 'bg-red-200']"
                    >
                        <TrendingUp class="w-6 h-6 text-verge-text-primary" />
                    </div>
                </div>
            </div>

            <!-- Visual Charts Row 1: Trend Keuangan Bulanan & Pendapatan Lapangan -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Trend Chart -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-verge-text-primary/10 pb-2 mb-4">
                            <h3 class="font-display text-lg font-bold uppercase flex items-center gap-1.5">
                                <TrendingUp class="w-4 h-4 text-verge-ultraviolet" />
                                <span>Tren Cash Flow Bulanan</span>
                            </h3>
                            <span class="font-mono text-[9px] bg-verge-surface-light border border-verge-text-primary/15 px-2 py-0.5 rounded-sm">
                                TAHUN JALAN
                            </span>
                        </div>
                        
                        <!-- Visual custom bar chart for trend -->
                        <div class="monthly-trend-chart py-4">
                            <div class="flex items-end justify-between h-56 pt-8 border-b-2 border-l-2 border-verge-text-primary px-2 font-mono text-[9px]">
                                <div v-for="month in monthlyTrend" :key="month.month" class="flex flex-col items-center flex-1 group">
                                    <div class="flex items-end gap-[3px]">
                                        <!-- Revenue Bar -->
                                        <div 
                                            class="w-3 bg-[#3cffd0] border border-verge-text-primary rounded-t-sm transition-all group-hover:bg-[#00e0a5] relative cursor-pointer" 
                                            :style="{ height: getTrendBarHeight(month.revenue) + 'px' }"
                                        >
                                            <span class="absolute -top-12 left-1/2 -translate-x-1/2 bg-verge-canvas-black text-verge-canvas-white text-[8px] p-1.5 rounded-sm border border-verge-text-primary opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10 font-mono">
                                                Revenue: {{ formatPrice(month.revenue) }}
                                            </span>
                                        </div>
                                        <!-- Expense Bar -->
                                        <div 
                                            class="w-3 bg-red-400 border border-verge-text-primary rounded-t-sm transition-all group-hover:bg-red-500 relative cursor-pointer" 
                                            :style="{ height: getTrendBarHeight(month.expense) + 'px' }"
                                        >
                                            <span class="absolute -top-12 left-1/2 -translate-x-1/2 bg-verge-canvas-black text-verge-canvas-white text-[8px] p-1.5 rounded-sm border border-verge-text-primary opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10 font-mono">
                                                Expense: {{ formatPrice(month.expense) }}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="mt-2 font-bold text-[9px]">{{ month.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4 pt-4 border-t border-verge-text-primary/10 font-mono text-[9px] font-bold uppercase">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 bg-verge-jelly-mint border border-verge-text-primary rounded-sm"></span>
                            <span>Pendapatan</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3 h-3 bg-red-400 border border-verge-text-primary rounded-sm"></span>
                            <span>Pengeluaran</span>
                        </div>
                        <span class="text-verge-text-muted ml-auto">*Hover grafik untuk melihat jumlah</span>
                    </div>
                </div>

                <!-- Court Breakdown Chart -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between border-b border-verge-text-primary/10 pb-2 mb-4">
                            <h3 class="font-display text-lg font-bold uppercase flex items-center gap-1.5">
                                <Award class="w-4 h-4 text-verge-ultraviolet" />
                                <span>Omzet per Lapangan</span>
                            </h3>
                            <span class="font-mono text-[9px] bg-verge-surface-light border border-verge-text-primary/15 px-2 py-0.5 rounded-sm">
                                PRODUKTIVITAS
                            </span>
                        </div>

                        <!-- Visual custom bar chart for courts -->
                        <div class="court-revenue-chart py-4">
                            <div class="flex items-end justify-around h-56 pt-8 border-b-2 border-l-2 border-verge-text-primary px-4 font-mono text-[9px]">
                                <div v-for="court in revenueByCourt" :key="court.court_id" class="flex flex-col items-center flex-1 group max-w-28">
                                    <!-- Bar -->
                                    <div 
                                        class="w-10 bg-verge-ultraviolet border-2 border-verge-text-primary rounded-t-sm transition-all group-hover:bg-verge-deep-link-blue relative cursor-pointer" 
                                        :style="{ height: getCourtBarHeight(court.revenue) + 'px' }"
                                    >
                                        <!-- Value indicator inside bar if tall enough -->
                                        <span class="absolute -top-12 left-1/2 -translate-x-1/2 bg-verge-canvas-black text-verge-canvas-white text-[8px] p-1.5 rounded-sm border border-verge-text-primary opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10 font-mono">
                                            {{ formatPrice(court.revenue) }} ({{ court.bookings_count }} Main)
                                        </span>
                                    </div>
                                    <!-- Label -->
                                    <span class="mt-2 text-center uppercase font-bold text-[8px] truncate max-w-full px-1" :title="court.court_name">
                                        {{ court.court_name.split(' (')[0] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-verge-text-primary/10 font-mono text-[9px] text-verge-text-muted flex justify-between">
                        <span>*Menampilkan omzet bersih dikurangi refund</span>
                        <span>*Berdasarkan durasi booking</span>
                    </div>
                </div>
            </div>

            <!-- Visual Charts Row 2: Metode Pembayaran & Kategori Pengeluaran -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Payment Method Breakdown -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
                    <div class="flex items-center justify-between border-b border-verge-text-primary/10 pb-2 mb-4">
                        <h3 class="font-display text-lg font-bold uppercase flex items-center gap-1.5">
                            <PieChart class="w-4 h-4 text-verge-ultraviolet" />
                            <span>Metode Pembayaran Terpopuler</span>
                        </h3>
                    </div>

                    <div class="space-y-6">
                        <!-- Stacked Bar representation -->
                        <div class="h-8 w-full border-2 border-verge-text-primary rounded-sm overflow-hidden flex font-mono text-[9px] font-bold text-center">
                            <div v-if="cashPercent > 0" :style="{ width: cashPercent + '%' }" class="bg-verge-jelly-mint text-verge-text-primary flex items-center justify-center border-r border-verge-text-primary">
                                CASH ({{ cashPercent }}%)
                            </div>
                            <div v-if="transferPercent > 0" :style="{ width: transferPercent + '%' }" class="bg-verge-ultraviolet text-verge-canvas-white flex items-center justify-center border-r border-verge-text-primary">
                                TRF ({{ transferPercent }}%)
                            </div>
                            <div v-if="qrisPercent > 0" :style="{ width: qrisPercent + '%' }" class="bg-amber-400 text-verge-text-primary flex items-center justify-center">
                                QRIS ({{ qrisPercent }}%)
                            </div>
                        </div>

                        <!-- Statistics details -->
                        <div class="grid grid-cols-3 gap-4 font-mono text-xs">
                            <div class="border-2 border-verge-text-primary p-3 rounded bg-verge-surface-light">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="w-2.5 h-2.5 bg-verge-jelly-mint border border-verge-text-primary rounded-xs"></span>
                                    <span class="text-[10px] text-verge-text-muted">CASH</span>
                                </div>
                                <div class="font-bold">{{ formatPrice(revenueByMethod.cash) }}</div>
                            </div>
                            <div class="border-2 border-verge-text-primary p-3 rounded bg-verge-surface-light">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="w-2.5 h-2.5 bg-verge-ultraviolet border border-verge-text-primary rounded-xs"></span>
                                    <span class="text-[10px] text-verge-text-muted">TRANSFER</span>
                                </div>
                                <div class="font-bold">{{ formatPrice(revenueByMethod.transfer) }}</div>
                            </div>
                            <div class="border-2 border-verge-text-primary p-3 rounded bg-verge-surface-light">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="w-2.5 h-2.5 bg-amber-400 border border-verge-text-primary rounded-xs"></span>
                                    <span class="text-[10px] text-verge-text-muted">QRIS</span>
                                </div>
                                <div class="font-bold">{{ formatPrice(revenueByMethod.qris) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expense Category Breakdown -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
                    <div class="flex items-center justify-between border-b border-verge-text-primary/10 pb-2 mb-4">
                        <h3 class="font-display text-lg font-bold uppercase flex items-center gap-1.5">
                            <PieChart class="w-4 h-4 text-red-600" />
                            <span>Alokasi Biaya Pengeluaran</span>
                        </h3>
                    </div>

                    <div class="space-y-6">
                        <!-- Stacked Bar representation -->
                        <div class="h-8 w-full border-2 border-verge-text-primary rounded-sm overflow-hidden flex font-mono text-[9px] font-bold text-center">
                            <div v-if="utilPercent > 0" :style="{ width: utilPercent + '%' }" class="bg-amber-300 text-verge-text-primary flex items-center justify-center border-r border-verge-text-primary">
                                UTIL ({{ utilPercent }}%)
                            </div>
                            <div v-if="maintPercent > 0" :style="{ width: maintPercent + '%' }" class="bg-blue-300 text-verge-text-primary flex items-center justify-center border-r border-verge-text-primary">
                                PERBAIKAN ({{ maintPercent }}%)
                            </div>
                            <div v-if="salariesPercent > 0" :style="{ width: salariesPercent + '%' }" class="bg-green-300 text-verge-text-primary flex items-center justify-center border-r border-verge-text-primary">
                                GAJI ({{ salariesPercent }}%)
                            </div>
                            <div v-if="otherPercent > 0" :style="{ width: otherPercent + '%' }" class="bg-purple-300 text-verge-text-primary flex items-center justify-center">
                                LAIN ({{ otherPercent }}%)
                            </div>
                        </div>

                        <!-- Statistics details -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 font-mono text-[10px]">
                            <div class="border border-verge-text-primary/30 p-2.5 rounded bg-verge-surface-light">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="w-2 h-2 bg-amber-300 border border-verge-text-primary rounded-xs"></span>
                                    <span class="text-[9px] text-verge-text-muted">LISTRIK/AIR</span>
                                </div>
                                <div class="font-bold text-xs">{{ formatPrice(expenseByCategory.utilities) }}</div>
                            </div>
                            <div class="border border-verge-text-primary/30 p-2.5 rounded bg-verge-surface-light">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="w-2 h-2 bg-blue-300 border border-verge-text-primary rounded-xs"></span>
                                    <span class="text-[9px] text-verge-text-muted">PERAWATAN</span>
                                </div>
                                <div class="font-bold text-xs">{{ formatPrice(expenseByCategory.maintenance) }}</div>
                            </div>
                            <div class="border border-verge-text-primary/30 p-2.5 rounded bg-verge-surface-light">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="w-2.5 h-2.5 bg-green-300 border border-verge-text-primary rounded-xs"></span>
                                    <span class="text-[9px] text-verge-text-muted">GAJI STAF</span>
                                </div>
                                <div class="font-bold text-xs">{{ formatPrice(expenseByCategory.salaries) }}</div>
                            </div>
                            <div class="border border-verge-text-primary/30 p-2.5 rounded bg-verge-surface-light">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="w-2.5 h-2.5 bg-purple-300 border border-verge-text-primary rounded-xs"></span>
                                    <span class="text-[9px] text-verge-text-muted">LAIN-LAIN</span>
                                </div>
                                <div class="font-bold text-xs">{{ formatPrice(expenseByCategory.other) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Ledger Transactions Table -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
                <div class="flex items-center justify-between border-b border-verge-text-primary/10 pb-2 mb-4">
                    <h3 class="font-display text-lg font-bold uppercase flex items-center gap-1.5">
                        <Receipt class="w-4 h-4 text-verge-ultraviolet" />
                        <span>Rincian Buku Kas Masuk (Lunas)</span>
                    </h3>
                    <span class="font-mono text-[9px] text-verge-text-muted">
                        Menampilkan {{ payments.length }} transaksi lunas
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-verge-surface-light border-b border-verge-text-primary/20 font-mono text-[10px] uppercase font-bold text-verge-text-muted">
                                <th class="py-2.5 px-3">Tanggal Bayar</th>
                                <th class="py-2.5 px-3">No. Booking</th>
                                <th class="py-2.5 px-3">Customer</th>
                                <th class="py-2.5 px-3">Lapangan</th>
                                <th class="py-2.5 px-3">Metode</th>
                                <th class="py-2.5 px-3">Gross Amount</th>
                                <th class="py-2.5 px-3">Refund</th>
                                <th class="py-2.5 px-3 text-right">Net Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y border-verge-text-primary/10 text-xs">
                            <tr v-if="payments.length === 0">
                                <td colspan="8" class="py-6 px-3 text-center font-mono text-verge-text-muted">
                                    Tidak ada riwayat kas masuk pada periode terpilih.
                                </td>
                            </tr>
                            <tr v-for="payment in payments" :key="payment.id" class="hover:bg-verge-surface-light/30 transition-colors">
                                <td class="py-2.5 px-3 font-mono">
                                    {{ formatDate(payment.confirmed_at) }}
                                </td>
                                <td class="py-2.5 px-3 font-mono font-bold text-verge-ultraviolet">
                                    {{ payment.booking?.booking_number }}
                                </td>
                                <td class="py-2.5 px-3 font-medium">
                                    {{ payment.booking?.customer_name }}
                                </td>
                                <td class="py-2.5 px-3 uppercase text-[10px]">
                                    {{ payment.booking?.court?.name }}
                                </td>
                                <td class="py-2.5 px-3 font-mono text-[10px] uppercase font-bold">
                                    {{ payment.payment_method }}
                                </td>
                                <td class="py-2.5 px-3 font-mono">
                                    {{ formatPrice(payment.amount) }}
                                </td>
                                <td class="py-2.5 px-3 font-mono text-red-600">
                                    {{ Number(payment.refund_amount) > 0 ? '-' + formatPrice(payment.refund_amount) : '-' }}
                                </td>
                                <td class="py-2.5 px-3 font-mono font-bold text-right text-green-600">
                                    {{ formatPrice(Number(payment.amount) - Number(payment.refund_amount)) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </OwnerLayout>
</template>
