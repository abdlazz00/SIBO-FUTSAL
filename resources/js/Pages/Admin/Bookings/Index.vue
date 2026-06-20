<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import BookingStatusBadge from '@/Features/booking/components/BookingStatusBadge.vue';
import Pagination from '@/Components/Pagination.vue';
import { Search, Calendar as CalendarIcon, Clock, User, Phone, Plus, X, Ban, CalendarDays, Loader } from 'lucide-vue-next';
import axios from 'axios';

interface Court {
    id: number;
    name: string;
    type: 'indoor' | 'outdoor';
    slot_duration: number;
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
    customer_email: string | null;
    date: string;
    start_time: string;
    end_time: string;
    total_price: number;
    status: 'confirmed' | 'completed' | 'cancelled';
    cancel_reason: string | null;
    is_manual: boolean;
    court_id: number;
    court: Court;
    created_by?: UserBrief | null;
    cancelled_by?: UserBrief | null;
    payment?: PaymentBrief | null;
}

interface Slot {
    start_time: string;
    end_time: string;
    formatted_time: string;
    price: number;
    status: 'available' | 'booked';
}

const props = defineProps<{
    bookings: {
        data: Booking[];
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
    courts: Court[];
    filters: {
        search?: string;
        status?: string;
        date?: string;
    };
}>();

// Filter inputs
const searchInput = ref(props.filters.search ?? '');
const statusInput = ref(props.filters.status ?? '');
const dateInput = ref(props.filters.date ?? '');

const applyFilters = () => {
    router.get(route('admin.bookings.index'), {
        search: searchInput.value,
        status: statusInput.value,
        date: dateInput.value
    }, {
        preserveState: true,
        replace: true
    });
};

const clearFilters = () => {
    searchInput.value = '';
    statusInput.value = '';
    dateInput.value = '';
    router.get(route('admin.bookings.index'), {}, {
        preserveState: true,
        replace: true
    });
};

// Modal states
const activeBooking = ref<Booking | null>(null);
const isCancelModalOpen = ref(false);
const isRescheduleModalOpen = ref(false);
const isManualModalOpen = ref(false);
const isDetailModalOpen = ref(false);

const openDetailModal = (booking: Booking) => {
    activeBooking.value = booking;
    isDetailModalOpen.value = true;
};

// Cancel Form
const cancelForm = useForm({
    cancel_reason: ''
});

// Reschedule Form & Slots loading
const rescheduleForm = useForm({
    date: '',
    start_time: '',
    end_time: ''
});
const rescheduleSlots = ref<Slot[]>([]);
const isLoadingRescheduleSlots = ref(false);
const selectedRescheduleSlot = ref<Slot | null>(null);

// Manual Booking Form & Slots loading
const manualForm = useForm({
    court_id: props.courts.length > 0 ? props.courts[0].id : 0,
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    date: new Date().toISOString().slice(0, 10),
    start_time: '',
    end_time: ''
});
const manualSlots = ref<Slot[]>([]);
const isLoadingManualSlots = ref(false);
const selectedManualSlot = ref<Slot | null>(null);

// Trigger Cancel
const openCancelModal = (booking: Booking) => {
    activeBooking.value = booking;
    cancelForm.reset();
    isCancelModalOpen.value = true;
};

const submitCancel = () => {
    if (!activeBooking.value) return;
    cancelForm.patch(route('admin.bookings.cancel', activeBooking.value.id), {
        onSuccess: () => {
            isCancelModalOpen.value = false;
            activeBooking.value = null;
        }
    });
};

// Trigger Reschedule
const openRescheduleModal = (booking: Booking) => {
    activeBooking.value = booking;
    rescheduleForm.date = booking.date;
    rescheduleForm.start_time = '';
    rescheduleForm.end_time = '';
    selectedRescheduleSlot.value = null;
    rescheduleSlots.value = [];
    isRescheduleModalOpen.value = true;
    loadRescheduleSlots();
};

const loadRescheduleSlots = async () => {
    if (!activeBooking.value || !rescheduleForm.date) return;
    isLoadingRescheduleSlots.value = true;
    try {
        const res = await axios.get(route('courts.slots', activeBooking.value.court_id), {
            params: { date: rescheduleForm.date }
        });
        if (res.data?.success) {
            // Map slots. Note that the API endpoint returns all slots.
            // S3-BE-01 says: "getAvailableSlots: exclude current booking from conflict".
            // Since we reschedule, we want to know if slots are available.
            // Wait, we can fetch available slots via a custom endpoint or just check from response.
            // But wait, the public slots endpoint returns raw slots. S3-BE-01 has a service method
            // getAvailableSlots, but the API endpoint in LandingController returns all generated slots
            // (price is override or base). Wait! SSPRINT_PLAN F-02 / S3-BE-01 getAvailableSlots
            // returns slot availability status. Yes, our getAvailableSlots service method does that,
            // but in LandingController we just returned generated slots.
            // Wait, let's check LandingController's getCourtSlots:
            // `$slots = $this->courtService->generateSlots($court, $date);`
            // Ah! LandingController's getCourtSlots did NOT filter by bookings yet because booking checking
            // is in BookingService. But in a real app, the slots grid should show which slots are booked!
            // Let's modify LandingController's getCourtSlots or add a check.
            // Wait, let's check how slots are loaded. We can write a custom endpoint for slots checking or
            // update LandingController's getCourtSlots to call `BookingService->getAvailableSlots`.
            // Yes! That is perfect! `BookingService->getAvailableSlots` returns all slots with status
            // 'available' or 'booked'. That is exactly what we need!
            // Let's modify LandingController's getCourtSlots to use `BookingService->getAvailableSlots`.
            // But first, let's write the rescheduling load slots call. It will call the slots API.
            // Since the API will return available/booked statuses, the admin can select from the available ones.
            const slotsData = res.data.data.slots;
            // Let's verify: does the API return availability status?
            // Yes, we will update the LandingController to return available/booked status.
            rescheduleSlots.value = slotsData;
        }
    } catch (e) {
        console.error(e);
    } finally {
        isLoadingRescheduleSlots.value = false;
    }
};

watch(() => rescheduleForm.date, () => {
    loadRescheduleSlots();
});

const selectRescheduleSlot = (slot: Slot) => {
    if (slot.status === 'available') {
        selectedRescheduleSlot.value = slot;
        rescheduleForm.start_time = slot.start_time;
        rescheduleForm.end_time = slot.end_time;
    }
};

const submitReschedule = () => {
    if (!activeBooking.value || !selectedRescheduleSlot.value) return;
    rescheduleForm.patch(route('admin.bookings.reschedule', activeBooking.value.id), {
        onSuccess: () => {
            isRescheduleModalOpen.value = false;
            activeBooking.value = null;
        }
    });
};

// Trigger Manual Booking
const openManualModal = () => {
    manualForm.reset();
    selectedManualSlot.value = null;
    manualSlots.value = [];
    isManualModalOpen.value = true;
    loadManualSlots();
};

const loadManualSlots = async () => {
    if (!manualForm.court_id || !manualForm.date) return;
    isLoadingManualSlots.value = true;
    try {
        const res = await axios.get(route('courts.slots', manualForm.court_id), {
            params: { date: manualForm.date }
        });
        if (res.data?.success) {
            manualSlots.value = res.data.data.slots;
        }
    } catch (e) {
        console.error(e);
    } finally {
        isLoadingManualSlots.value = false;
    }
};

watch(() => [manualForm.court_id, manualForm.date], () => {
    loadManualSlots();
});

const selectManualSlot = (slot: Slot) => {
    if (slot.status === 'available') {
        selectedManualSlot.value = slot;
        manualForm.start_time = slot.start_time;
        manualForm.end_time = slot.end_time;
    }
};

const submitManualBooking = () => {
    if (!selectedManualSlot.value) return;
    manualForm.post(route('admin.bookings.store'), {
        onSuccess: () => {
            isManualModalOpen.value = false;
            manualForm.reset();
        }
    });
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(price);
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('id-ID', {
        dateStyle: 'medium'
    });
};

const exportCsv = () => {
    const url = route('admin.bookings.export', {
        search: searchInput.value,
        status: statusInput.value,
        date: dateInput.value
    });
    window.open(url, '_blank');
};
</script>

<template>
    <Head title="Manajemen Booking" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header Card -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-verge-ultraviolet">Operasional Sewa</span>
                    <h1 class="text-3xl font-display font-bold uppercase mt-1">Daftar Pemesanan</h1>
                    <p class="text-xs text-verge-text-muted mt-1">Kelola data sewa lapangan futsal, reschedule jadwal, pembatalan sewa, atau input pemesanan walk-in manual.</p>
                </div>
                <div class="flex gap-2">
                    <button @click="exportCsv" class="flex items-center justify-center gap-2 px-5 py-3 bg-verge-canvas-white hover:bg-verge-surface-light border-2 border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] transition-all">
                        <span>Ekspor CSV</span>
                    </button>
                    <button @click="openManualModal" class="flex items-center justify-center gap-1.5 px-4 py-3 bg-verge-ultraviolet text-verge-canvas-white hover:bg-verge-deep-link-blue border-2 border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] transition-all">
                        <Plus class="w-4 h-4" />
                        <span>Booking Manual</span>
                    </button>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-4 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center gap-4">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-verge-text-muted">
                        <Search class="w-4 h-4" />
                    </div>
                    <input v-model="searchInput" type="text" @keyup.enter="applyFilters" placeholder="Cari No. Booking, Nama, atau No. HP..." class="w-full border-2 border-verge-text-primary pl-9 pr-4 py-2 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet" />
                </div>

                <!-- Dropdown & Date & Actions -->
                <div class="flex flex-wrap items-center gap-3">
                    <input v-model="dateInput" type="date" @change="applyFilters" class="border-2 border-verge-text-primary px-3 py-2 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white" />

                    <select v-model="statusInput" @change="applyFilters" class="border-2 border-verge-text-primary px-3 py-2 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white min-w-36">
                        <option value="">Semua Status</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>

                    <button @click="applyFilters" class="px-4 py-2 bg-verge-canvas-black text-verge-canvas-white border border-verge-text-primary hover:bg-verge-text-muted font-mono text-xs uppercase font-bold rounded-sm transition-colors">
                        Filter
                    </button>

                    <button v-if="searchInput || statusInput || dateInput" @click="clearFilters" class="px-3 py-2 border border-verge-text-primary/20 hover:bg-verge-surface-light rounded-sm text-xs font-mono flex items-center gap-1 transition-colors">
                        <X class="w-3.5 h-3.5" />
                        <span>Reset</span>
                    </button>
                </div>
            </div>

            <!-- Bookings List Table -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] overflow-hidden">
                <div v-if="bookings.data.length === 0" class="p-12 text-center text-xs font-mono text-verge-text-muted">
                    Tidak ada pemesanan ditemukan.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left font-mono text-[11px] border-collapse">
                        <thead>
                            <tr class="border-b-2 border-verge-text-primary bg-verge-surface-light text-verge-text-muted uppercase text-[9px]">
                                <th class="p-4">No. Booking</th>
                                <th class="p-4">Customer</th>
                                <th class="p-4">Lapangan</th>
                                <th class="p-4">Tanggal Main</th>
                                <th class="p-4">Jam Sewa</th>
                                <th class="p-4 text-right">Total Bayar</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="booking in bookings.data" :key="booking.id" class="border-b border-verge-text-primary/10 hover:bg-verge-surface-light/30 transition-colors">
                                <td class="p-4 whitespace-nowrap font-bold text-verge-ultraviolet">{{ booking.booking_number }}</td>
                                <td class="p-4 whitespace-nowrap">
                                    <div class="font-sans font-bold text-verge-text-primary text-xs">{{ booking.customer_name }}</div>
                                    <div class="text-[9px] text-verge-text-muted mt-0.5">{{ booking.customer_phone }}</div>
                                </td>
                                <td class="p-4 whitespace-nowrap uppercase">{{ booking.court.name }}</td>
                                <td class="p-4 whitespace-nowrap">{{ formatDate(booking.date) }}</td>
                                <td class="p-4 whitespace-nowrap text-verge-text-muted">
                                    {{ booking.start_time.slice(0, 5) }} - {{ booking.end_time.slice(0, 5) }}
                                </td>
                                <td class="p-4 whitespace-nowrap text-right font-sans font-bold">{{ formatPrice(booking.total_price) }}</td>
                                <td class="p-4 whitespace-nowrap">
                                    <BookingStatusBadge :status="booking.status" />
                                </td>
                                <td class="p-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="openDetailModal(booking)" class="px-2.5 py-1 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted border border-verge-text-primary font-mono text-[9px] uppercase font-bold rounded-sm transition-colors">
                                            Detail
                                        </button>
                                        <button v-if="booking.status === 'confirmed'" @click="openRescheduleModal(booking)" class="px-2.5 py-1 border border-verge-text-primary hover:bg-verge-canvas-white font-mono text-[9px] uppercase font-bold rounded-sm transition-colors">
                                            Reschedule
                                        </button>
                                        <button v-if="booking.status === 'confirmed'" @click="openCancelModal(booking)" class="px-2.5 py-1 bg-red-600 hover:bg-red-700 text-white font-mono text-[9px] uppercase font-bold rounded-sm transition-colors">
                                            Cancel
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-4 pb-4">
                    <Pagination :paginator="bookings" />
                </div>
            </div>
        </div>

        <!-- 1. MANUAL BOOKING MODAL -->
        <div v-if="isManualModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" @click="isManualModalOpen = false"></div>
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[8px_8px_0px_0px_rgba(19,19,19,1)] w-full max-w-4xl relative z-10 overflow-hidden flex flex-col max-h-[90vh]">
                <div class="bg-verge-surface-light border-b-2 border-verge-text-primary p-4 flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold uppercase">Booking Manual (Walk-in)</h3>
                    <button @click="isManualModalOpen = false" class="p-1.5 hover:bg-verge-text-primary/10 rounded-sm">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto space-y-6 flex-1">
                    <!-- Step Selector fields -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label class="font-mono text-[9px] uppercase font-bold text-verge-text-muted">Pilih Lapangan</label>
                            <select v-model="manualForm.court_id" class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm text-xs font-mono focus:outline-none">
                                <option v-for="court in courts" :key="court.id" :value="court.id">{{ court.name }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="font-mono text-[9px] uppercase font-bold text-verge-text-muted">Pilih Tanggal</label>
                            <input v-model="manualForm.date" type="date" class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm text-xs font-mono focus:outline-none" />
                        </div>
                    </div>

                    <!-- Slots Grid Selector -->
                    <div class="space-y-2">
                        <span class="font-mono text-[9px] uppercase font-bold text-verge-text-muted block">Pilih Slot Waktu Tersedia</span>
                        <div v-if="isLoadingManualSlots" class="text-center py-6 font-mono text-xs text-verge-text-muted">Memuat slot...</div>
                        <div v-else-if="manualSlots.length === 0" class="text-center py-6 font-mono text-xs text-verge-text-muted">Tidak ada slot untuk kriteria terpilih.</div>
                        <div v-else class="grid grid-cols-3 sm:grid-cols-6 gap-2.5">
                            <button 
                                v-for="slot in manualSlots" 
                                :key="slot.start_time"
                                type="button"
                                @click="selectManualSlot(slot)"
                                :disabled="slot.status === 'booked'"
                                :class="[
                                    'p-2.5 border rounded-sm font-mono text-[10px] text-center flex flex-col justify-center gap-1 transition-all',
                                    slot.status === 'booked' 
                                        ? 'bg-red-50 text-red-300 border-red-100 cursor-not-allowed'
                                        : selectedManualSlot?.start_time === slot.start_time
                                            ? 'bg-verge-ultraviolet text-white border-verge-text-primary'
                                            : 'bg-verge-canvas-white border-verge-text-primary hover:bg-verge-jelly-mint'
                                ]"
                            >
                                <span class="font-bold">{{ slot.formatted_time }}</span>
                                <span>{{ slot.status === 'booked' ? 'Terisi' : formatPrice(slot.price) }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Customer Details Form -->
                    <div v-if="selectedManualSlot" class="bg-verge-surface-light p-4 rounded-md border border-verge-text-primary/10 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label class="font-mono text-[9px] uppercase font-bold text-verge-text-muted">Nama Pelanggan</label>
                            <input v-model="manualForm.customer_name" type="text" required class="w-full border border-verge-text-primary p-2.5 rounded-sm text-xs font-sans" placeholder="Budi Santoso" />
                        </div>
                        <div class="space-y-1">
                            <label class="font-mono text-[9px] uppercase font-bold text-verge-text-muted">Nomor WhatsApp HP</label>
                            <input v-model="manualForm.customer_phone" type="text" required class="w-full border border-verge-text-primary p-2.5 rounded-sm text-xs font-sans" placeholder="081234567890" />
                        </div>
                        <div class="space-y-1">
                            <label class="font-mono text-[9px] uppercase font-bold text-verge-text-muted">Alamat Email (Opsional)</label>
                            <input v-model="manualForm.customer_email" type="email" class="w-full border border-verge-text-primary p-2.5 rounded-sm text-xs font-sans" placeholder="budi@example.com" />
                        </div>
                    </div>
                </div>

                <div class="bg-verge-surface-light border-t-2 border-verge-text-primary p-4 flex justify-between items-center">
                    <span v-if="selectedManualSlot" class="font-mono text-[10px] text-verge-text-muted">
                        Terpilih: <strong class="text-verge-text-primary uppercase">{{ selectedManualSlot.formatted_time }}</strong> ({{ formatPrice(selectedManualSlot.price) }})
                    </span>
                    <span v-else class="font-mono text-[10px] text-red-500">Pilih slot terlebih dahulu</span>

                    <button @click="submitManualBooking" :disabled="!selectedManualSlot || manualForm.processing" class="px-6 py-2 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted font-mono text-xs uppercase font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,0.2)] disabled:opacity-50">
                        Buat Booking
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. RESCHEDULE MODAL -->
        <div v-if="isRescheduleModalOpen && activeBooking" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" @click="isRescheduleModalOpen = false"></div>
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[8px_8px_0px_0px_rgba(19,19,19,1)] w-full max-w-3xl relative z-10 overflow-hidden flex flex-col max-h-[90vh]">
                <div class="bg-verge-surface-light border-b-2 border-verge-text-primary p-4 flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold uppercase">Reschedule Booking: {{ activeBooking.booking_number }}</h3>
                    <button @click="isRescheduleModalOpen = false" class="p-1.5 hover:bg-verge-text-primary/10 rounded-sm">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <div class="p-6 overflow-y-auto space-y-6 flex-1">
                    <div>
                        <span class="font-mono text-[9px] text-verge-text-muted uppercase">Pemesanan Asli:</span>
                        <div class="font-mono text-xs font-bold">{{ formatDate(activeBooking.date) }} @ {{ activeBooking.start_time.slice(0, 5) }} - {{ activeBooking.end_time.slice(0, 5) }} ({{ activeBooking.customer_name }})</div>
                    </div>

                    <div class="space-y-1">
                        <label class="font-mono text-[9px] uppercase font-bold text-verge-text-muted">Pilih Tanggal Baru</label>
                        <input v-model="rescheduleForm.date" type="date" class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm text-xs font-mono focus:outline-none" />
                    </div>

                    <!-- Slots Selection -->
                    <div class="space-y-2">
                        <span class="font-mono text-[9px] uppercase font-bold text-verge-text-muted block">Slot Baru Tersedia</span>
                        <div v-if="isLoadingRescheduleSlots" class="text-center py-6 font-mono text-xs text-verge-text-muted">Memuat slot...</div>
                        <div v-else-if="rescheduleSlots.length === 0" class="text-center py-6 font-mono text-xs text-verge-text-muted">Pilih tanggal baru.</div>
                        <div v-else class="grid grid-cols-3 sm:grid-cols-5 gap-2.5">
                            <button 
                                v-for="slot in rescheduleSlots" 
                                :key="slot.start_time"
                                type="button"
                                @click="selectRescheduleSlot(slot)"
                                :disabled="slot.status === 'booked'"
                                :class="[
                                    'p-2.5 border rounded-sm font-mono text-[10px] text-center flex flex-col justify-center gap-1 transition-all',
                                    slot.status === 'booked' 
                                        ? 'bg-red-50 text-red-300 border-red-100 cursor-not-allowed'
                                        : selectedRescheduleSlot?.start_time === slot.start_time
                                            ? 'bg-verge-ultraviolet text-white border-verge-text-primary'
                                            : 'bg-verge-canvas-white border-verge-text-primary hover:bg-verge-jelly-mint'
                                ]"
                            >
                                <span class="font-bold">{{ slot.formatted_time }}</span>
                                <span>{{ slot.status === 'booked' ? 'Terisi' : formatPrice(slot.price) }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-verge-surface-light border-t-2 border-verge-text-primary p-4 flex justify-between items-center">
                    <span v-if="selectedRescheduleSlot" class="font-mono text-[10px] text-verge-text-muted">
                        Slot Baru: <strong class="text-verge-text-primary">{{ selectedRescheduleSlot.formatted_time }}</strong> ({{ formatPrice(selectedRescheduleSlot.price) }})
                    </span>
                    <span v-else class="font-mono text-[10px] text-red-500">Pilih slot tanggal & waktu baru</span>

                    <button @click="submitReschedule" :disabled="!selectedRescheduleSlot || rescheduleForm.processing" class="px-6 py-2 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted font-mono text-xs uppercase font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,0.2)] disabled:opacity-50">
                        Confirm Reschedule
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. CANCEL MODAL -->
        <div v-if="isCancelModalOpen && activeBooking" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" @click="isCancelModalOpen = false"></div>
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[8px_8px_0px_0px_rgba(19,19,19,1)] w-full max-w-md relative z-10 overflow-hidden flex flex-col">
                <div class="bg-verge-surface-light border-b-2 border-verge-text-primary p-4 flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold uppercase text-red-600">Batal Booking: {{ activeBooking.booking_number }}</h3>
                    <button @click="isCancelModalOpen = false" class="p-1.5 hover:bg-verge-text-primary/10 rounded-sm">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <form @submit.prevent="submitCancel" class="p-6 space-y-4">
                    <p class="text-xs text-verge-text-muted">Harap masukkan alasan pembatalan penyewaan ini. Alasan wajib diisi dengan **minimal 10 karakter**.</p>
                    
                    <div class="space-y-1">
                        <label for="cancel_reason" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Alasan Pembatalan</label>
                        <textarea id="cancel_reason" v-model="cancelForm.cancel_reason" required minlength="10" rows="4" class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-red-600 text-xs" placeholder="Contoh: Customer meminta batal karena bentrok acara keluarga..."></textarea>
                        <span v-if="cancelForm.errors.cancel_reason" class="text-[10px] font-mono text-red-600 block">{{ cancelForm.errors.cancel_reason }}</span>
                    </div>

                    <div class="pt-4 flex gap-2">
                        <button type="button" @click="isCancelModalOpen = false" class="flex-1 px-4 py-2.5 border-2 border-verge-text-primary hover:bg-verge-surface-light font-mono text-xs uppercase font-bold rounded-sm transition-colors text-center">
                            Batal
                        </button>
                        <button type="submit" :disabled="cancelForm.cancel_reason.length < 10 || cancelForm.processing" class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-mono text-xs uppercase font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,0.2)] disabled:opacity-50 transition-colors flex items-center justify-center gap-1">
                            <component :is="cancelForm.processing ? Loader : Ban" class="w-4 h-4" :class="{ 'animate-spin': cancelForm.processing }" />
                            <span>Batalkan Booking</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 4. DETAIL BOOKING MODAL -->
        <div v-if="isDetailModalOpen && activeBooking" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" @click="isDetailModalOpen = false"></div>
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[8px_8px_0px_0px_rgba(19,19,19,1)] w-full max-w-lg relative z-10 overflow-hidden flex flex-col">
                <div class="bg-verge-surface-light border-b-2 border-verge-text-primary p-4 flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold uppercase text-verge-text-primary">Detail Booking: {{ activeBooking.booking_number }}</h3>
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
                                <BookingStatusBadge :status="activeBooking.status" />
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-mono text-verge-text-muted uppercase block">Total Bayar</span>
                            <span class="font-mono font-bold text-sm text-verge-ultraviolet block">{{ formatPrice(activeBooking.total_price) }}</span>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="space-y-2">
                        <h4 class="font-mono text-[9px] uppercase font-bold text-verge-ultraviolet tracking-wider">Identitas Pelanggan</h4>
                        <div class="grid grid-cols-2 gap-3 bg-verge-surface-light border border-verge-text-primary/10 p-3 rounded-sm font-mono text-[10px] text-verge-text-muted">
                            <div>
                                <span>Nama Lengkap:</span>
                                <strong class="text-verge-text-primary block mt-0.5">{{ activeBooking.customer_name }}</strong>
                            </div>
                            <div>
                                <span>Nomor HP (WhatsApp):</span>
                                <strong class="text-verge-text-primary block mt-0.5">{{ activeBooking.customer_phone }}</strong>
                            </div>
                            <div class="col-span-2">
                                <span>Alamat Email:</span>
                                <strong class="text-verge-text-primary block mt-0.5">{{ activeBooking.customer_email ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Court Schedule Info -->
                    <div class="space-y-2">
                        <h4 class="font-mono text-[9px] uppercase font-bold text-verge-ultraviolet tracking-wider">Jadwal Penyewaan Lapangan</h4>
                        <div class="grid grid-cols-2 gap-3 bg-verge-surface-light border border-verge-text-primary/10 p-3 rounded-sm font-mono text-[10px] text-verge-text-muted">
                            <div>
                                <span>Lapangan:</span>
                                <strong class="text-verge-text-primary block mt-0.5 uppercase">{{ activeBooking.court.name }}</strong>
                            </div>
                            <div>
                                <span>Tanggal Bermain:</span>
                                <strong class="text-verge-text-primary block mt-0.5">{{ formatDate(activeBooking.date) }}</strong>
                            </div>
                            <div class="col-span-2">
                                <span>Waktu / Jam Bermain:</span>
                                <strong class="text-verge-text-primary block mt-0.5">
                                    {{ activeBooking.start_time.substring(0, 5) }} s/d {{ activeBooking.end_time.substring(0, 5) }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Details if Completed -->
                    <div v-if="activeBooking.payment" class="space-y-2">
                        <h4 class="font-mono text-[9px] uppercase font-bold text-green-600 tracking-wider">Rincian Pembayaran</h4>
                        <div class="bg-green-50/50 border border-green-200 p-3 rounded-sm font-mono text-[10px] text-verge-text-muted space-y-1.5">
                            <div class="flex justify-between">
                                <span>Metode Pembayaran:</span>
                                <strong class="text-verge-text-primary uppercase">{{ activeBooking.payment.payment_method ?? 'CASH' }}</strong>
                            </div>
                            <div class="flex justify-between">
                                <span>Jumlah Dibayar:</span>
                                <strong class="text-verge-text-primary">{{ formatPrice(activeBooking.payment.amount) }}</strong>
                            </div>
                            <div v-if="Number(activeBooking.payment.refund_amount) > 0" class="flex justify-between text-red-600">
                                <span>Jumlah Dana Direfund:</span>
                                <strong>{{ formatPrice(activeBooking.payment.refund_amount) }}</strong>
                            </div>
                            <div v-if="activeBooking.payment.refund_reason" class="border-t border-green-200/50 pt-1.5 mt-1.5 text-red-700">
                                <span>Alasan Refund:</span>
                                <p class="font-sans text-[10px] mt-0.5 leading-relaxed bg-red-50 p-1.5 rounded-sm border border-red-100">
                                    "{{ activeBooking.payment.refund_reason }}"
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Cancel Details if Cancelled -->
                    <div v-if="activeBooking.status === 'cancelled'" class="space-y-2">
                        <h4 class="font-mono text-[9px] uppercase font-bold text-red-600 tracking-wider">Informasi Pembatalan</h4>
                        <div class="bg-red-50 border border-red-200 p-3 rounded-sm font-mono text-[10px] text-red-800 space-y-1.5">
                            <div class="flex justify-between">
                                <span>Dibatalkan Oleh:</span>
                                <strong class="text-red-900 uppercase">{{ activeBooking.cancelled_by?.name ?? 'Customer/System' }}</strong>
                            </div>
                            <div class="border-t border-red-200 pt-1.5 mt-1.5">
                                <span>Alasan Pembatalan:</span>
                                <p class="font-sans text-[10px] text-red-950 mt-0.5 leading-relaxed bg-white p-2 rounded border border-red-100 italic">
                                    "{{ activeBooking.cancel_reason ?? 'Tanpa alasan spesifik.' }}"
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Created Info if Manual -->
                    <div v-if="activeBooking.is_manual && activeBooking.created_by" class="font-mono text-[9px] text-verge-text-muted flex justify-between pt-1">
                        <span>Pendaftaran Booking Walk-In Oleh:</span>
                        <span class="font-bold text-verge-text-primary uppercase">{{ activeBooking.created_by.name }}</span>
                    </div>
                </div>
                
                <div class="bg-verge-surface-light border-t-2 border-verge-text-primary p-4 flex justify-end">
                    <button @click="isDetailModalOpen = false" class="px-5 py-2 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted border border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
