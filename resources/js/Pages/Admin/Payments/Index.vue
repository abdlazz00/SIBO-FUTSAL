<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import gsap from 'gsap';
import { 
    Search, Calendar, CreditCard, DollarSign, Download, 
    RefreshCw, CheckCircle, AlertTriangle, X, Wallet, ArrowRight, User, Clock, Printer
} from 'lucide-vue-next';

interface Court {
    id: number;
    name: string;
    type: 'indoor' | 'outdoor';
}

interface User {
    id: number;
    name: string;
    role: string;
}

interface Booking {
    id: number;
    booking_number: string;
    customer_name: string;
    customer_phone: string;
    customer_email: string | null;
    date: string;
    start_time: string;
    end_time: string;
    total_price: number;
    status: 'confirmed' | 'completed' | 'cancelled';
    court: Court;
    user: User | null;
    is_manual: boolean;
}

interface Payment {
    id: number;
    booking_id: number;
    payment_method: 'cash' | 'transfer' | 'qris' | null;
    amount: number;
    refund_amount: number;
    refund_reason: string | null;
    confirmed_by: number | null;
    confirmed_at: string | null;
    booking: Booking;
    confirmed_by_user: User | null;
    cash_received: number | null;
    cash_change: number | null;
}

interface Summary {
    total_revenue: number;
    total_refunded: number;
    today_revenue: number;
    this_month_revenue: number;
    by_method: {
        cash: number;
        transfer: number;
        qris: number;
    };
}

const props = defineProps<{
    payments: {
        data: Payment[];
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
        per_page: number;
        links: { url: string | null; label: string; active: boolean }[];
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    unpaidBookings: Booking[];
    summary: Summary;
    filters: {
        search?: string;
        status?: string;
        payment_method?: string;
        start_date?: string;
        end_date?: string;
    };
    flash: {
        success?: string;
        error?: string;
        confirmed_payment?: any;
    };
    confirmedPayment?: any;
}>();

// Tabs state
const activeTab = ref<'transactions' | 'unpaid'>('transactions');

// Filter states
const searchInput = ref(props.filters.search ?? '');
const statusInput = ref(props.filters.status ?? '');
const methodInput = ref(props.filters.payment_method ?? '');
const startDateInput = ref(props.filters.start_date ?? '');
const endDateInput = ref(props.filters.end_date ?? '');

const applyFilters = () => {
    router.get(route('admin.payments.index'), {
        search: searchInput.value,
        status: statusInput.value,
        payment_method: methodInput.value,
        start_date: startDateInput.value,
        end_date: endDateInput.value
    }, {
        preserveState: true,
        replace: true
    });
};

const clearFilters = () => {
    searchInput.value = '';
    statusInput.value = '';
    methodInput.value = '';
    startDateInput.value = '';
    endDateInput.value = '';
    router.get(route('admin.payments.index'), {}, {
        preserveState: true,
        replace: true
    });
};

// Confirm Payment Modal
const selectedBooking = ref<Booking | null>(null);
const isConfirmModalOpen = ref(false);
const confirmForm = useForm({
    payment_method: 'cash',
    cash_received: 0
});

const openConfirmModal = (booking: Booking) => {
    selectedBooking.value = booking;
    confirmForm.payment_method = 'cash';
    confirmForm.cash_received = booking.total_price;
    isConfirmModalOpen.value = true;
};

const closeConfirmModal = () => {
    selectedBooking.value = null;
    isConfirmModalOpen.value = false;
};

// Receipt Modal State
const isReceiptModalOpen = ref(false);
const completedPaymentData = ref<any>(null);

const printReceipt = () => {
    window.print();
};

const submitConfirmPayment = () => {
    if (!selectedBooking.value) return;
    
    confirmForm.post(route('admin.payments.confirm', selectedBooking.value.id), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            closeConfirmModal();
        }
    });
};

const openReceiptModalFromPayment = (payment: Payment) => {
    completedPaymentData.value = {
        booking_number: payment.booking?.booking_number ?? '-',
        customer_name: payment.booking?.customer_name ?? '-',
        court_name: payment.booking?.court?.name ?? '-',
        date: payment.booking?.date ?? '-',
        start_time: payment.booking?.start_time ?? '00:00:00',
        end_time: payment.booking?.end_time ?? '00:00:00',
        total_price: payment.amount,
        payment_method: payment.payment_method,
        cash_received: payment.cash_received,
        cash_change: payment.cash_change,
        confirmed_at: payment.confirmed_at
    };
    isReceiptModalOpen.value = true;
};

// Refund Modal
const selectedPayment = ref<Payment | null>(null);
const isRefundModalOpen = ref(false);
const refundForm = useForm({
    amount: 0,
    reason: ''
});

const openRefundModal = (payment: Payment) => {
    selectedPayment.value = payment;
    refundForm.amount = Number(payment.amount) - Number(payment.refund_amount);
    refundForm.reason = '';
    isRefundModalOpen.value = true;
};

const closeRefundModal = () => {
    selectedPayment.value = null;
    isRefundModalOpen.value = false;
};

const submitRefund = () => {
    if (!selectedPayment.value) return;
    refundForm.post(route('admin.payments.refund', selectedPayment.value.id), {
        onSuccess: () => {
            closeRefundModal();
        }
    });
};

// Export CSV
const exportCsv = () => {
    const url = route('admin.payments.export', {
        search: searchInput.value,
        status: statusInput.value,
        payment_method: methodInput.value,
        start_date: startDateInput.value,
        end_date: endDateInput.value
    });
    window.location.href = url;
};

// Helpers
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
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatBookingDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

const formatBookingDateShort = (dateStr: string) => {
    if (!dateStr) return '-';
    const cleanDate = dateStr.split(/[T ]/)[0];
    const parts = cleanDate.split('-');
    if (parts.length === 3) {
        const [year, month, day] = parts;
        return `${day}/${month}/${year}`;
    }
    try {
        const date = new Date(dateStr);
        const d = String(date.getDate()).padStart(2, '0');
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const y = date.getFullYear();
        return `${d}/${m}/${y}`;
    } catch (e) {
        return dateStr;
    }
};

// ─── ANIMATION ───────────────────────────────────────────────────────────────

// Counter refs for stat cards
const animatedToday = ref(0);
const animatedMonth = ref(0);
const animatedTotal = ref(0);
const animatedRefund = ref(0);

const animateCounter = (refVal: { value: number }, target: number, delay = 0) => {
    const obj = { val: 0 };
    gsap.to(obj, {
        val: target,
        duration: 1.4,
        delay,
        ease: 'power2.out',
        onUpdate: () => { refVal.value = Math.round(obj.val); }
    });
};

const formatCounter = (val: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(val);
};

watch(() => props.confirmedPayment || props.flash?.confirmed_payment, (newPayment) => {
    if (newPayment) {
        completedPaymentData.value = newPayment;
        isReceiptModalOpen.value = true;
        
        // Clear query parameter from the URL to prevent the modal from opening again on refresh
        const url = new URL(window.location.href);
        if (url.searchParams.has('confirmed_booking')) {
            url.searchParams.delete('confirmed_booking');
            window.history.replaceState({}, '', url.pathname + url.search);
        }
    }
}, { immediate: true });

onMounted(() => {
    // 1. Entrance stagger for all sections
    const sections = document.querySelectorAll('.anim-section');
    gsap.fromTo(sections,
        { y: 28, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.5, stagger: 0.09, ease: 'power3.out', delay: 0.05 }
    );

    // 2. Counter animations
    animateCounter(animatedToday, Number(props.summary.today_revenue), 0.2);
    animateCounter(animatedMonth, Number(props.summary.this_month_revenue), 0.33);
    animateCounter(animatedTotal, Number(props.summary.total_revenue), 0.46);
    animateCounter(animatedRefund, Number(props.summary.total_refunded), 0.59);

    // 3. Hover lift effects on stat cards
    const statCards = document.querySelectorAll('.stat-card-hover');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            gsap.to(card, { y: -4, boxShadow: '6px 10px 0px 0px rgba(19,19,19,1)', duration: 0.2, ease: 'power2.out' });
        });
        card.addEventListener('mouseleave', () => {
            gsap.to(card, { y: 0, boxShadow: '4px 4px 0px 0px rgba(19,19,19,1)', duration: 0.2, ease: 'power2.out' });
        });
    });
});
</script>

<template>
    <Head title="Manajemen Transaksi & Pembayaran" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header Card -->
            <div class="anim-section bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-verge-ultraviolet">Keuangan Lapangan</span>
                    <h1 class="text-3xl font-display font-bold uppercase mt-1">Transaksi & Pembayaran</h1>
                    <p class="text-xs text-verge-text-muted mt-1">Konfirmasi bayar sewa lapangan, catat pengembalian dana (refund), dan pantau transaksi masuk harian.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="exportCsv" class="flex items-center justify-center gap-2 px-5 py-3 bg-verge-canvas-white hover:bg-verge-surface-light border-2 border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] transition-all">
                        <Download class="w-4 h-4" />
                        <span>Ekspor Laporan</span>
                    </button>
                </div>
            </div>

            <!-- Bento Stats Grid -->
            <div class="anim-section grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="stat-card-hover bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-32 cursor-default">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider">Pendapatan Hari Ini</span>
                        <DollarSign class="w-4 h-4 text-verge-ultraviolet" />
                    </div>
                    <span class="text-2xl font-display font-bold text-verge-text-primary mt-2">{{ formatCounter(animatedToday) }}</span>
                    <span class="text-[9px] font-mono text-green-600 mt-1">Pendapatan bersih hari ini</span>
                </div>

                <div class="stat-card-hover bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-32 cursor-default">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider">Pendapatan Bulan Ini</span>
                        <Wallet class="w-4 h-4 text-verge-ultraviolet" />
                    </div>
                    <span class="text-2xl font-display font-bold text-verge-text-primary mt-2">{{ formatCounter(animatedMonth) }}</span>
                    <span class="text-[9px] font-mono text-verge-text-muted mt-1">Akumulasi bulan berjalan</span>
                </div>

                <div class="stat-card-hover bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-32 cursor-default">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider">Total Pendapatan (Filter)</span>
                        <CheckCircle class="w-4 h-4 text-green-600" />
                    </div>
                    <span class="text-2xl font-display font-bold text-green-600 mt-2">{{ formatCounter(animatedTotal) }}</span>
                    <span class="text-[9px] font-mono text-verge-text-muted mt-1">Setelah dikurangi refund filter</span>
                </div>

                <div class="stat-card-hover bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-32 cursor-default">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider">Total Refund (Filter)</span>
                        <RefreshCw class="w-4 h-4 text-red-600" />
                    </div>
                    <span class="text-2xl font-display font-bold text-red-600 mt-2">{{ formatCounter(animatedRefund) }}</span>
                    <span class="text-[9px] font-mono text-verge-text-muted mt-1">Uang dikembalikan</span>
                </div>
            </div>

            <!-- Tab Switcher -->
            <div class="flex border-b-2 border-verge-text-primary gap-4 font-mono text-xs uppercase font-bold">
                <button 
                    @click="activeTab = 'transactions'"
                    :class="[
                        'pb-3 px-2 border-b-4 -mb-[3px] transition-colors',
                        activeTab === 'transactions' 
                            ? 'border-verge-ultraviolet text-verge-ultraviolet' 
                            : 'border-transparent text-verge-text-muted hover:text-verge-text-primary'
                    ]"
                >
                    Daftar Transaksi ({{ payments.total }})
                </button>
                <button 
                    @click="activeTab = 'unpaid'"
                    :class="[
                        'pb-3 px-2 border-b-4 -mb-[3px] transition-colors',
                        activeTab === 'unpaid' 
                            ? 'border-verge-ultraviolet text-verge-ultraviolet' 
                            : 'border-transparent text-verge-text-muted hover:text-verge-text-primary'
                    ]"
                >
                    Menunggu Pembayaran ({{ unpaidBookings.length }})
                </button>
            </div>

            <!-- TAB 1: Daftar Transaksi -->
            <div v-if="activeTab === 'transactions'" class="anim-section space-y-6">
                <!-- Filters -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-4 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col gap-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                        <!-- Search -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-verge-text-muted">
                                <Search class="w-3.5 h-3.5" />
                            </div>
                            <input v-model="searchInput" type="text" @keyup.enter="applyFilters" placeholder="No. Booking / Customer..." class="w-full border-2 border-verge-text-primary pl-9 pr-4 py-2 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet" />
                        </div>

                        <!-- Status -->
                        <select v-model="statusInput" @change="applyFilters" class="border-2 border-verge-text-primary px-3 py-2 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white">
                            <option value="">Semua Status</option>
                            <option value="confirmed">Confirmed (Lunas)</option>
                            <option value="pending">Pending Verifikasi</option>
                            <option value="refunded">Telah Direfund</option>
                        </select>

                        <!-- Method -->
                        <select v-model="methodInput" @change="applyFilters" class="border-2 border-verge-text-primary px-3 py-2 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white">
                            <option value="">Semua Metode</option>
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                        </select>

                        <!-- Start Date -->
                        <input v-model="startDateInput" type="date" @change="applyFilters" class="border-2 border-verge-text-primary px-3 py-1.5 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white" />

                        <!-- End Date -->
                        <input v-model="endDateInput" type="date" @change="applyFilters" class="border-2 border-verge-text-primary px-3 py-1.5 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white" />
                    </div>

                    <div class="flex justify-end gap-3">
                        <button v-if="searchInput || statusInput || methodInput || startDateInput || endDateInput" @click="clearFilters" class="px-4 py-2 border border-verge-text-primary hover:bg-verge-surface-light rounded-sm text-xs font-mono flex items-center gap-1 transition-colors">
                            <X class="w-3.5 h-3.5" />
                            <span>Reset Filter</span>
                        </button>
                        <button @click="applyFilters" class="px-5 py-2 bg-verge-canvas-black text-verge-canvas-white border border-verge-text-primary hover:bg-verge-text-muted font-mono text-xs uppercase font-bold rounded-sm transition-colors">
                            Terapkan Filter
                        </button>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-verge-surface-light border-b-2 border-verge-text-primary font-mono text-[10px] uppercase font-bold text-verge-text-muted">
                                    <th class="py-3 px-4">No. Booking</th>
                                    <th class="py-3 px-4">Customer</th>
                                    <th class="py-3 px-4">Lapangan</th>
                                    <th class="py-3 px-4">Jumlah Bayar</th>
                                    <th class="py-3 px-4">Metode</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4">Konfirmasi</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y border-verge-text-primary/10 text-xs">
                                <tr v-if="payments.data.length === 0">
                                    <td colspan="8" class="py-8 px-4 text-center font-mono text-verge-text-muted">
                                        Tidak ditemukan transaksi pembayaran yang sesuai.
                                    </td>
                                </tr>
                                <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-verge-surface-light/50 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-verge-ultraviolet">
                                        {{ payment.booking?.booking_number }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold">{{ payment.booking?.customer_name }}</div>
                                        <div class="text-[10px] text-verge-text-muted font-mono">{{ payment.booking?.customer_phone }}</div>
                                    </td>
                                    <td class="py-3.5 px-4 font-bold uppercase">
                                        {{ payment.booking?.court?.name ?? '-' }}
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-bold">
                                        <div>{{ formatPrice(payment.amount) }}</div>
                                        <div v-if="Number(payment.refund_amount) > 0" class="text-[9px] text-red-600">
                                            Refund: {{ formatPrice(payment.refund_amount) }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-mono text-[10px] font-bold uppercase bg-verge-surface-light border border-verge-text-primary/15 px-2 py-0.5 rounded-sm">
                                            {{ payment.payment_method ?? 'CASH' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span v-if="Number(payment.refund_amount) >= Number(payment.amount)" class="inline-flex items-center gap-1 font-mono text-[9px] uppercase font-bold text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 rounded-sm">
                                            Refunded Full
                                        </span>
                                        <span v-else-if="Number(payment.refund_amount) > 0" class="inline-flex items-center gap-1 font-mono text-[9px] uppercase font-bold text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-sm">
                                            Refunded Partial
                                        </span>
                                        <span v-else-if="payment.confirmed_at" class="inline-flex items-center gap-1 font-mono text-[9px] uppercase font-bold text-green-600 bg-green-50 border border-green-200 px-2 py-0.5 rounded-sm">
                                            Confirmed
                                        </span>
                                        <span v-else class="inline-flex items-center gap-1 font-mono text-[9px] uppercase font-bold text-verge-text-muted bg-verge-surface-light border border-verge-text-primary/10 px-2 py-0.5 rounded-sm">
                                            Pending
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono text-[10px] text-verge-text-muted">
                                        <div v-if="payment.confirmed_at">
                                            <div>By: {{ payment.confirmed_by_user?.name ?? 'Admin' }}</div>
                                            <div>{{ formatDate(payment.confirmed_at) }}</div>
                                        </div>
                                        <div v-else>-</div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button 
                                                v-if="payment.confirmed_at"
                                                @click="openReceiptModalFromPayment(payment)"
                                                class="px-2.5 py-1 bg-verge-canvas-white hover:bg-verge-surface-light text-verge-text-primary border border-verge-text-primary font-mono text-[10px] uppercase font-bold rounded-sm transition-colors flex items-center gap-1"
                                            >
                                                <Printer class="w-3 h-3" />
                                                <span>Struk</span>
                                            </button>
                                            <button 
                                                v-if="Number(payment.refund_amount) < Number(payment.amount)" 
                                                @click="openRefundModal(payment)" 
                                                class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-600 border border-red-300 font-mono text-[10px] uppercase font-bold rounded-sm transition-colors"
                                            >
                                                Refund
                                            </button>
                                            <span v-if="!payment.confirmed_at && Number(payment.refund_amount) >= Number(payment.amount)" class="text-[10px] font-mono text-verge-text-muted italic">No Action</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <div class="px-4 pb-4">
                        <Pagination :paginator="payments" />
                    </div>
                </div>
            </div>

            <!-- TAB 2: Menunggu Pembayaran -->
            <div v-if="activeTab === 'unpaid'" class="anim-section space-y-6">
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-verge-surface-light border-b-2 border-verge-text-primary font-mono text-[10px] uppercase font-bold text-verge-text-muted">
                                    <th class="py-3 px-4">No. Booking</th>
                                    <th class="py-3 px-4">Customer</th>
                                    <th class="py-3 px-4">Lapangan</th>
                                    <th class="py-3 px-4">Jadwal Main</th>
                                    <th class="py-3 px-4">Total Tagihan</th>
                                    <th class="py-3 px-4">Metode Bayar</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y border-verge-text-primary/10 text-xs">
                                <tr v-if="unpaidBookings.length === 0">
                                    <td colspan="7" class="py-8 px-4 text-center font-mono text-verge-text-muted">
                                        Tidak ada booking yang menunggu konfirmasi pembayaran.
                                    </td>
                                </tr>
                                <tr v-for="booking in unpaidBookings" :key="booking.id" class="hover:bg-verge-surface-light/50 transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-verge-ultraviolet">
                                        {{ booking.booking_number }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold flex items-center gap-1">
                                            <span>{{ booking.customer_name }}</span>
                                            <span v-if="booking.is_manual" class="bg-verge-canvas-black text-verge-canvas-white text-[8px] font-mono font-bold uppercase px-1 rounded-sm">
                                                Manual
                                            </span>
                                        </div>
                                        <div class="text-[10px] text-verge-text-muted font-mono flex items-center gap-1 mt-0.5">
                                            <User class="w-3 h-3" />
                                            <span>{{ booking.customer_phone }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 font-bold uppercase">
                                        {{ booking.court?.name ?? '-' }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-mono font-bold">{{ formatBookingDate(booking.date) }}</div>
                                        <div class="text-[10px] text-verge-text-muted font-mono flex items-center gap-1 mt-0.5">
                                            <Clock class="w-3 h-3 text-verge-ultraviolet" />
                                            <span>{{ booking.start_time.substring(0, 5) }} - {{ booking.end_time.substring(0, 5) }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-bold text-verge-ultraviolet">
                                        {{ formatPrice(booking.total_price) }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-mono text-[9px] uppercase font-bold text-amber-600 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-sm">
                                            BELUM BAYAR
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <button 
                                            @click="openConfirmModal(booking)" 
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-verge-jelly-mint text-verge-text-primary border-2 border-verge-text-primary font-mono text-[10px] uppercase font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] hover:bg-[#00e0a5] transition-all"
                                        >
                                            Konfirmasi Bayar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: Confirm Payment -->
        <div v-if="isConfirmModalOpen && selectedBooking" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-verge-canvas-black/50 backdrop-blur-xs">
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary max-w-md w-full rounded-lg shadow-[6px_6px_0px_0px_rgba(19,19,19,1)] overflow-hidden">
                <div class="bg-verge-surface-light border-b-2 border-verge-text-primary p-4 flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold uppercase">Konfirmasi Pembayaran</h3>
                    <button @click="closeConfirmModal" class="p-1 border border-verge-text-primary/10 hover:bg-verge-canvas-black/5 rounded-sm">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="submitConfirmPayment" class="p-5 space-y-4">
                    <!-- Detail summary -->
                    <div class="bg-verge-surface-light border-2 border-verge-text-primary p-4 rounded-md font-mono text-xs space-y-2">
                        <div class="flex justify-between border-b border-verge-text-primary/10 pb-1.5">
                            <span class="text-verge-text-muted">Booking No:</span>
                            <span class="font-bold text-verge-ultraviolet">{{ selectedBooking.booking_number }}</span>
                        </div>
                        <div class="flex justify-between border-b border-verge-text-primary/10 pb-1.5">
                            <span class="text-verge-text-muted">Nama Pelanggan:</span>
                            <span class="font-bold">{{ selectedBooking.customer_name }}</span>
                        </div>
                        <div class="flex justify-between border-b border-verge-text-primary/10 pb-1.5">
                            <span class="text-verge-text-muted">Lapangan:</span>
                            <span class="font-bold uppercase">{{ selectedBooking.court?.name }}</span>
                        </div>
                        <div class="flex justify-between pt-1">
                            <span class="text-verge-text-muted font-bold">Total Pembayaran:</span>
                            <span class="font-bold text-verge-ultraviolet text-sm">{{ formatPrice(selectedBooking.total_price) }}</span>
                        </div>
                    </div>

                    <!-- Payment Method Select -->
                    <div class="space-y-1">
                        <label class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Metode Pembayaran</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label 
                                class="border-2 border-verge-text-primary p-2.5 rounded-sm flex flex-col items-center justify-center gap-1 cursor-pointer font-mono text-[10px] uppercase font-bold text-center transition-all",
                                :class="[confirmForm.payment_method === 'cash' ? 'bg-verge-jelly-mint text-verge-text-primary border-verge-text-primary shadow-[2px_2px_0px_0px_rgba(19,19,19,1)]' : 'bg-verge-canvas-white hover:bg-verge-surface-light']"
                            >
                                <input type="radio" value="cash" v-model="confirmForm.payment_method" class="sr-only" />
                                <span>CASH</span>
                            </label>
                            <label 
                                class="border-2 border-verge-text-primary p-2.5 rounded-sm flex flex-col items-center justify-center gap-1 cursor-pointer font-mono text-[10px] uppercase font-bold text-center transition-all",
                                :class="[confirmForm.payment_method === 'transfer' ? 'bg-verge-jelly-mint text-verge-text-primary border-verge-text-primary shadow-[2px_2px_0px_0px_rgba(19,19,19,1)]' : 'bg-verge-canvas-white hover:bg-verge-surface-light']"
                            >
                                <input type="radio" value="transfer" v-model="confirmForm.payment_method" class="sr-only" />
                                <span>TRANSFER</span>
                            </label>
                            <label 
                                class="border-2 border-verge-text-primary p-2.5 rounded-sm flex flex-col items-center justify-center gap-1 cursor-pointer font-mono text-[10px] uppercase font-bold text-center transition-all",
                                :class="[confirmForm.payment_method === 'qris' ? 'bg-verge-jelly-mint text-verge-text-primary border-verge-text-primary shadow-[2px_2px_0px_0px_rgba(19,19,19,1)]' : 'bg-verge-canvas-white hover:bg-verge-surface-light']"
                            >
                                <input type="radio" value="qris" v-model="confirmForm.payment_method" class="sr-only" />
                                <span>QRIS</span>
                            </label>
                        </div>
                    </div>

                    <!-- Input Uang Diterima & Kembalian (Hanya jika metode Cash) -->
                    <div v-if="confirmForm.payment_method === 'cash'" class="space-y-3 p-3 bg-verge-surface-light border-2 border-verge-text-primary rounded-md animate-fade-in">
                        <div class="space-y-1">
                            <label for="cash_received" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Jumlah Uang Diterima (Rp)</label>
                            <input 
                                id="cash_received" 
                                type="number" 
                                v-model.number="confirmForm.cash_received" 
                                required 
                                :min="selectedBooking.total_price"
                                class="w-full border-2 border-verge-text-primary p-2 py-1.5 rounded-sm font-mono text-xs focus:outline-none focus:border-verge-ultraviolet bg-white" 
                            />
                            <span v-if="confirmForm.errors.cash_received" class="text-[10px] font-mono text-red-600 block">{{ confirmForm.errors.cash_received }}</span>
                        </div>

                        <!-- Tombol Cepat Nominal -->
                        <div class="space-y-1">
                            <span class="font-mono text-[9px] uppercase font-bold text-verge-text-muted block">Nominal Cepat:</span>
                            <div class="flex flex-wrap gap-1.5">
                                <button 
                                    type="button" 
                                    @click="confirmForm.cash_received = selectedBooking.total_price" 
                                    class="px-2 py-1 bg-verge-canvas-white hover:bg-verge-surface-light border border-verge-text-primary font-mono text-[9px] uppercase font-bold rounded-xs transition-colors"
                                >
                                    Uang Pas
                                </button>
                                <button 
                                    v-for="denom in [50000, 100000, 200000]" 
                                    :key="denom"
                                    type="button" 
                                    v-show="denom >= selectedBooking.total_price"
                                    @click="confirmForm.cash_received = denom" 
                                    class="px-2 py-1 bg-verge-canvas-white hover:bg-verge-surface-light border border-verge-text-primary font-mono text-[9px] uppercase font-bold rounded-xs transition-colors"
                                >
                                    {{ formatPrice(denom) }}
                                </button>
                            </div>
                        </div>

                        <!-- Info Kembalian -->
                        <div class="flex justify-between items-center pt-2 border-t border-verge-text-primary/10 font-mono text-xs">
                            <span class="text-verge-text-muted font-bold">Kembalian:</span>
                            <span class="font-bold" :class="confirmForm.cash_received >= selectedBooking.total_price ? 'text-green-600' : 'text-red-600'">
                                {{ formatPrice(Math.max(0, confirmForm.cash_received - selectedBooking.total_price)) }}
                            </span>
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end gap-3 font-mono text-xs uppercase font-bold">
                        <button type="button" @click="closeConfirmModal" class="px-4 py-2.5 border-2 border-verge-text-primary hover:bg-verge-surface-light rounded-sm">
                            Batal
                        </button>
                        <button type="submit" :disabled="confirmForm.processing" class="px-5 py-2.5 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted border border-verge-text-primary rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] disabled:opacity-50 flex items-center gap-2">
                            <span>Submit Konfirmasi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: Refund Payment -->
        <div v-if="isRefundModalOpen && selectedPayment" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-verge-canvas-black/50 backdrop-blur-xs">
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary max-w-md w-full rounded-lg shadow-[6px_6px_0px_0px_rgba(19,19,19,1)] overflow-hidden">
                <div class="bg-verge-surface-light border-b-2 border-verge-text-primary p-4 flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold uppercase">Proses Refund Transaksi</h3>
                    <button @click="closeRefundModal" class="p-1 border border-verge-text-primary/10 hover:bg-verge-canvas-black/5 rounded-sm">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="submitRefund" class="p-5 space-y-4">
                    <!-- Detail summary -->
                    <div class="bg-verge-surface-light border-2 border-verge-text-primary p-4 rounded-md font-mono text-xs space-y-2">
                        <div class="flex justify-between border-b border-verge-text-primary/10 pb-1.5">
                            <span class="text-verge-text-muted">Booking No:</span>
                            <span class="font-bold text-verge-ultraviolet">{{ selectedPayment.booking?.booking_number }}</span>
                        </div>
                        <div class="flex justify-between border-b border-verge-text-primary/10 pb-1.5">
                            <span class="text-verge-text-muted">Total Pembayaran:</span>
                            <span class="font-bold">{{ formatPrice(selectedPayment.amount) }}</span>
                        </div>
                        <div class="flex justify-between border-b border-verge-text-primary/10 pb-1.5">
                            <span class="text-verge-text-muted">Telah Direfund:</span>
                            <span class="font-bold text-red-600">{{ formatPrice(selectedPayment.refund_amount) }}</span>
                        </div>
                        <div class="flex justify-between pt-1">
                            <span class="text-verge-text-muted font-bold">Maksimal Sisa Refund:</span>
                            <span class="font-bold text-verge-text-primary">{{ formatPrice(Number(selectedPayment.amount) - Number(selectedPayment.refund_amount)) }}</span>
                        </div>
                    </div>

                    <!-- Refund Amount Input -->
                    <div class="space-y-1">
                        <label for="refund_amount" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Jumlah Refund (Rupiah)</label>
                        <input 
                            id="refund_amount" 
                            type="number" 
                            v-model="refundForm.amount" 
                            required 
                            :max="Number(selectedPayment.amount) - Number(selectedPayment.refund_amount)"
                            min="1"
                            class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet" 
                        />
                        <span v-if="refundForm.errors.amount" class="text-[10px] font-mono text-red-600 block">{{ refundForm.errors.amount }}</span>
                    </div>

                    <!-- Refund Reason Input -->
                    <div class="space-y-1">
                        <label for="refund_reason" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Alasan Refund (Minimal 10 Karakter)</label>
                        <textarea 
                            id="refund_reason" 
                            v-model="refundForm.reason" 
                            required 
                            rows="3"
                            placeholder="Tulis alasan pembatalan / pengembalian uang sewa..."
                            class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet text-xs"
                        ></textarea>
                        <span v-if="refundForm.errors.reason" class="text-[10px] font-mono text-red-600 block">{{ refundForm.errors.reason }}</span>
                    </div>

                    <div class="pt-2 flex justify-end gap-3 font-mono text-xs uppercase font-bold">
                        <button type="button" @click="closeRefundModal" class="px-4 py-2.5 border-2 border-verge-text-primary hover:bg-verge-surface-light rounded-sm">
                            Batal
                        </button>
                        <button type="submit" :disabled="refundForm.processing || refundForm.reason.length < 10" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-verge-canvas-white border-2 border-verge-text-primary rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] disabled:opacity-50">
                            Proses Refund
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <!-- MODAL: Receipt & Print -->
        <div v-if="isReceiptModalOpen && completedPaymentData" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-verge-canvas-black/50 backdrop-blur-xs print:static print:bg-transparent print:p-0 print:block">
            <!-- Modal Container (Hidden in print except receipt) -->
            <div class="bg-verge-canvas-white w-full max-w-sm rounded-sm border-2 border-verge-text-primary shadow-[6px_6px_0px_0px_rgba(19,19,19,1)] overflow-hidden print:border-none print:shadow-none print:max-w-full">
                <!-- Header (Hidden in print) -->
                <div class="p-4 border-b-2 border-verge-text-primary flex justify-between items-center bg-verge-surface-light print:hidden">
                    <h2 class="font-display font-bold text-lg uppercase tracking-tight">Pembayaran Sukses</h2>
                    <button @click="isReceiptModalOpen = false" class="text-verge-text-muted hover:text-verge-text-primary transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <!-- Receipt Body (Printable Area) -->
                <div id="printable-receipt" class="p-6 bg-white text-black print:p-0">
                    <div class="text-center border-b-2 border-dashed border-gray-400 pb-4 mb-4">
                        <h3 class="font-display font-bold text-2xl uppercase tracking-widest">VITKA FUTSAL</h3>
                        <p class="font-mono text-[10px] text-gray-500 mt-1 uppercase">Official Payment Receipt</p>
                        <p class="font-mono text-[10px] font-bold mt-2 border border-gray-300 inline-block px-2 py-1 bg-gray-50">
                            {{ completedPaymentData.booking_number }}
                        </p>
                    </div>

                    <div class="space-y-3 font-mono text-xs mb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tanggal:</span>
                            <span class="font-bold">{{ formatDate(completedPaymentData.confirmed_at) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Pelanggan:</span>
                            <span class="font-bold">{{ completedPaymentData.customer_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Lapangan:</span>
                            <span class="font-bold">{{ completedPaymentData.court_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Jadwal:</span>
                            <span class="font-bold">{{ formatBookingDateShort(completedPaymentData.date) }} ({{ completedPaymentData.start_time.slice(0, 5) }} - {{ completedPaymentData.end_time.slice(0, 5) }})</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Metode Bayar:</span>
                            <span class="font-bold uppercase">{{ completedPaymentData.payment_method }}</span>
                        </div>
                    </div>

                    <div class="border-t-2 border-dashed border-gray-400 pt-4 mb-6">
                        <div v-if="completedPaymentData.payment_method === 'cash'" class="flex justify-between font-mono text-xs mb-2">
                            <span class="text-gray-500 uppercase">Uang Diterima:</span>
                            <span class="font-bold">{{ formatPrice(completedPaymentData.cash_received) }}</span>
                        </div>
                        <div v-if="completedPaymentData.payment_method === 'cash'" class="flex justify-between font-mono text-xs mb-3">
                            <span class="text-gray-500 uppercase">Kembalian:</span>
                            <span class="font-bold text-green-600">{{ formatPrice(completedPaymentData.cash_change) }}</span>
                        </div>
                        <div class="flex justify-between items-end">
                            <span class="font-mono text-xs text-gray-500 uppercase">Total Lunas</span>
                            <span class="font-display text-2xl font-bold">{{ formatPrice(completedPaymentData.total_price) }}</span>
                        </div>
                    </div>

                    <div class="text-center font-mono text-[9px] text-gray-500">
                        <p>Terima kasih telah bermain di Vitka Futsal!</p>
                        <p>Simpan struk ini sebagai bukti pembayaran yang sah.</p>
                    </div>
                </div>

                <!-- Footer Actions (Hidden in print) -->
                <div class="p-4 bg-verge-surface-light border-t-2 border-verge-text-primary flex justify-end gap-3 print:hidden">
                    <button type="button" @click="isReceiptModalOpen = false" class="px-4 py-2 border-2 border-verge-text-primary bg-verge-canvas-white hover:bg-gray-50 font-mono text-xs uppercase font-bold text-verge-text-primary transition-colors">
                        Tutup
                    </button>
                    <button type="button" @click="printReceipt" class="px-4 py-2 bg-verge-ultraviolet hover:bg-verge-deep-link-blue text-verge-canvas-white font-mono text-xs uppercase font-bold border-2 border-verge-text-primary shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] transition-all flex items-center gap-2">
                        <Printer class="w-4 h-4" />
                        Cetak Struk
                    </button>
                </div>
            </div>
        </div>

    </AdminLayout>
</template>

<style>
@media print {
    /* Hide all layout wrappers, headers, sidebars, page content, and other modals */
    header, aside, nav, .print\:hidden, .space-y-6, .fixed.inset-0:not(.print\:block) {
        display: none !important;
    }
    
    /* Reset background colors, heights, and scrollbars for layout parents to make sure there are no blank pages */
    html, body, #app, .min-h-screen, main, .mx-auto.max-w-7xl {
        background: white !important;
        color: black !important;
        margin: 0 !important;
        padding: 0 !important;
        height: auto !important;
        min-height: 0 !important;
        width: 100% !important;
        overflow: visible !important;
        display: block !important;
    }

    /* Reset the modal container position for standard page printing */
    .fixed.inset-0.z-50.print\:block {
        position: static !important;
        display: block !important;
        background: transparent !important;
        backdrop-filter: none !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
        height: auto !important;
    }

    /* Reset the modal box border, shadow, and background */
    .fixed.inset-0.z-50.print\:block > div {
        border: none !important;
        box-shadow: none !important;
        background: white !important;
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        height: auto !important;
    }

    /* Print only the receipt */
    #printable-receipt {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 10px !important;
        background: white !important;
        color: black !important;
    }

    #printable-receipt * {
        visibility: visible !important;
    }
}
</style>
