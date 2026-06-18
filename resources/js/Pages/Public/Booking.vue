<script setup lang="ts">
import { ref, watch, onMounted, nextTick } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { PageProps } from '@/types';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import BookingCalendar from '@/Features/booking/components/BookingCalendar.vue';
import SlotGrid from '@/Features/booking/components/SlotGrid.vue';
import BookingForm from '@/Features/booking/components/BookingForm.vue';
import { Dumbbell, Calendar, Clock, ChevronRight, AlertTriangle, X } from 'lucide-vue-next';
import axios from 'axios';
import gsap from 'gsap';

interface Photo {
    id: number;
    path: string;
}

interface Court {
    id: number;
    name: string;
    type: 'indoor' | 'outdoor';
    price: number;
    slot_duration: number;
    open_time: string;
    close_time: string;
    status: 'active' | 'inactive' | 'maintenance';
    photos?: Photo[];
}

interface Slot {
    start_time: string;
    end_time: string;
    formatted_time: string;
    price: number;
    status: 'available' | 'booked';
    booking?: any;
}

const props = defineProps<{
    courts: Court[];
}>();

const page = usePage<PageProps>();
const flashSuccess = ref(page.props.flash?.success);
const flashBookingNumber = ref(page.props.flash?.booking_number);
const flashTotalPrice = ref(page.props.flash?.total_price);

const isErrorModalOpen = ref(false);
const errorMessage = ref('');

// Watch for errors to display Failure Popup Modal
watch(() => page.props.errors, (newErrors) => {
    if (newErrors && Object.keys(newErrors).length > 0) {
        if (newErrors.slot_conflict) {
            errorMessage.value = newErrors.slot_conflict;
            isErrorModalOpen.value = true;
        } else {
            // Get first validation error message
            const firstErrKey = Object.keys(newErrors)[0];
            errorMessage.value = newErrors[firstErrKey];
            isErrorModalOpen.value = true;
        }
    }
}, { immediate: true, deep: true });

const selectedCourt = ref<Court | null>(props.courts.length > 0 ? props.courts[0] : null);
const selectedDate = ref(new Date().toISOString().slice(0, 10));
const slots = ref<Slot[]>([]);
const selectedSlot = ref<Slot | null>(null);
const isLoadingSlots = ref(false);
const fetchError = ref('');

const fetchSlots = async () => {
    if (!selectedCourt.value || !selectedDate.value) return;

    isLoadingSlots.value = true;
    fetchError.value = '';
    selectedSlot.value = null; // Reset selection

    try {
        const res = await axios.get(route('courts.slots', selectedCourt.value.id), {
            params: { date: selectedDate.value }
        });
        if (res.data && res.data.success) {
            slots.value = res.data.data.slots;
        } else {
            fetchError.value = 'Gagal memuat jadwal slot.';
        }
    } catch (e: any) {
        fetchError.value = e.response?.data?.message || 'Terjadi kesalahan koneksi saat memuat jadwal.';
    } finally {
        isLoadingSlots.value = false;
    }
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(price);
};

const selectCourt = (court: Court) => {
    selectedCourt.value = court;
};

const selectSlot = (slot: Slot) => {
    selectedSlot.value = slot;
};

// Fetch slots when court or date changes
watch([selectedCourt, selectedDate], () => {
    fetchSlots();
}, { immediate: true });

onMounted(() => {
    // 1. Title animations
    gsap.fromTo('.booking-title-anim',
        { y: -15, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.5, ease: 'power2.out' }
    );
    // 2. Step animations
    gsap.fromTo('.booking-step-anim',
        { y: 25, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.6, stagger: 0.1, ease: 'power3.out' }
    );
});

// Watch for success page changes to trigger GSAP reveal
watch(() => page.props.flash?.success, (val) => {
    if (val) {
        nextTick(() => {
            gsap.fromTo('.success-card',
                { scale: 0.95, opacity: 0 },
                { scale: 1, opacity: 1, duration: 0.6, ease: 'back.out(1.5)' }
            );
            gsap.fromTo('.success-check',
                { scale: 0, rotate: -30 },
                { scale: 1, rotate: 0, duration: 0.5, ease: 'back.out(1.8)', delay: 0.2 }
            );
            gsap.fromTo('.success-item',
                { y: 15, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.5, stagger: 0.08, ease: 'power2.out', delay: 0.3 }
            );
        });
    }
}, { immediate: true });

// Watch for error modal visibility to trigger GSAP reveal
watch(isErrorModalOpen, (val) => {
    if (val) {
        nextTick(() => {
            gsap.fromTo('.error-modal-card',
                { scale: 0.95, opacity: 0 },
                { scale: 1, opacity: 1, duration: 0.45, ease: 'back.out(1.5)' }
            );
            gsap.fromTo('.error-modal-icon',
                { scale: 0, y: -15 },
                { scale: 1, y: 0, duration: 0.5, ease: 'back.out(2)', delay: 0.1 }
            );
        });
    }
});
</script>

<template>
    <Head title="Booking Lapangan Futsal" />

    <PublicLayout>
        <!-- If Success Screen after Booking -->
        <div v-if="page.props.flash?.success" class="max-w-xl mx-auto py-16 px-6">
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-8 rounded-lg shadow-[6px_6px_0px_0px_rgba(19,19,19,1)] text-center space-y-6 success-card">
                <div class="w-16 h-16 bg-verge-jelly-mint text-verge-text-primary rounded-full flex items-center justify-center border-2 border-verge-text-primary mx-auto shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] success-check">
                    <span class="font-bold text-2xl">✓</span>
                </div>

                <div class="space-y-2 success-item">
                    <h2 class="font-display text-2xl font-bold uppercase tracking-tight">Booking Berhasil!</h2>
                    <p class="text-xs text-verge-text-muted">Simpan nomor booking di bawah untuk verifikasi pembayaran manual di lokasi atau pelacakan status.</p>
                </div>

                <!-- Booking Details Bento Box -->
                <div class="bg-verge-surface-light border-2 border-verge-text-primary p-5 rounded-md font-mono text-xs text-left space-y-2 success-item">
                    <div class="flex justify-between border-b border-verge-text-primary/10 pb-1.5">
                        <span class="text-verge-text-muted">Nomor Booking:</span>
                        <span class="font-bold text-verge-ultraviolet text-sm">{{ page.props.flash.booking_number }}</span>
                    </div>
                    <div class="flex justify-between pt-1.5">
                        <span class="text-verge-text-muted">Total Bayar:</span>
                        <span class="font-sans font-bold text-verge-text-primary text-sm">{{ formatPrice(page.props.flash.total_price ?? 0) }}</span>
                    </div>
                </div>

                <div class="pt-4 flex flex-col gap-2 success-item">
                    <Link :href="route('landing')" class="px-6 py-3 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted border border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] transition-colors block">
                        Kembali ke Beranda
                    </Link>
                    <Link :href="route('booking.track.show')" class="px-6 py-3 bg-verge-canvas-white text-verge-text-primary hover:bg-verge-surface-light border border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] transition-colors block">
                        Lacak Booking Saya
                    </Link>
                </div>
            </div>
        </div>

        <!-- Normal Booking Step Flow -->
        <div v-else class="max-w-6xl mx-auto py-12 px-6 space-y-8">
            <div class="border-b border-verge-text-primary/10 pb-4 booking-title-anim">
                <span class="font-mono text-[9px] uppercase font-bold text-verge-ultraviolet tracking-widest">Sewa Mandiri</span>
                <h1 class="font-display text-3xl md:text-4xl font-bold uppercase tracking-tight">Booking Online</h1>
            </div>

            <!-- Step 1: Court Selection -->
            <div class="space-y-4 booking-step-anim">
                <h2 class="font-display text-xl font-bold uppercase flex items-center gap-2">
                    <span class="bg-verge-canvas-black text-verge-canvas-white w-6 h-6 rounded-full flex items-center justify-center font-mono text-xs shadow-[2px_2px_0px_0px_rgba(82,0,255,1)]">1</span>
                    <span>Pilih Lapangan</span>
                </h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div 
                        v-for="court in courts" 
                        :key="court.id"
                        @click="selectCourt(court)"
                        :class="[
                            'cursor-pointer p-5 border-2 rounded-lg transition-all flex flex-col justify-between h-40 shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]',
                            selectedCourt?.id === court.id
                                ? 'bg-verge-canvas-white border-verge-ultraviolet scale-[1.01] shadow-[4px_4px_0px_0px_rgba(82,0,255,1)]'
                                : 'bg-verge-canvas-white border-verge-text-primary hover:bg-verge-surface-light'
                        ]"
                    >
                        <div>
                            <span class="font-mono text-[8px] uppercase tracking-widest text-verge-text-muted block">{{ court.type }}</span>
                            <h3 class="font-display text-lg font-bold uppercase mt-1 tracking-tight">{{ court.name }}</h3>
                            <span class="font-mono text-[10px] text-verge-text-muted block mt-1">Buka: {{ court.open_time.slice(0, 5) }} - {{ court.close_time.slice(0, 5) }}</span>
                        </div>
                        <div class="flex items-baseline justify-between border-t border-verge-text-primary/10 pt-3">
                            <span class="font-mono text-[8px] text-verge-text-muted uppercase">Harga per jam</span>
                            <span class="font-display text-sm font-bold text-verge-ultraviolet">{{ formatPrice(court.price) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Date & Slots Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 booking-step-anim" v-if="selectedCourt">
                <!-- Datepicker Column -->
                <div class="space-y-4">
                    <h2 class="font-display text-xl font-bold uppercase flex items-center gap-2">
                        <span class="bg-verge-canvas-black text-verge-canvas-white w-6 h-6 rounded-full flex items-center justify-center font-mono text-xs shadow-[2px_2px_0px_0px_rgba(82,0,255,1)]">2</span>
                        <span>Pilih Tanggal</span>
                    </h2>
                    <BookingCalendar v-model="selectedDate" />
                </div>

                <!-- Slots Grid Column -->
                <div class="lg:col-span-2 space-y-4">
                    <h2 class="font-display text-xl font-bold uppercase flex items-center gap-2">
                        <span class="bg-verge-canvas-black text-verge-canvas-white w-6 h-6 rounded-full flex items-center justify-center font-mono text-xs shadow-[2px_2px_0px_0px_rgba(82,0,255,1)]">3</span>
                        <span>Pilih Jam</span>
                    </h2>

                    <!-- Loading / Error states -->
                    <div v-if="isLoadingSlots" class="bg-verge-canvas-white border-2 border-verge-text-primary p-12 rounded-lg text-center font-mono text-xs text-verge-text-muted shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
                        Memuat slot jadwal kosong...
                    </div>
                    <div v-else-if="fetchError" class="bg-red-50 border-2 border-red-200 p-6 rounded-lg text-center font-mono text-xs text-red-600 shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col items-center justify-center">
                        <AlertTriangle class="w-8 h-8 mb-2" />
                        <span>{{ fetchError }}</span>
                    </div>
                    <SlotGrid 
                        v-else 
                        :slots="slots" 
                        :selected-slot="selectedSlot" 
                        @select="selectSlot" 
                    />
                </div>
            </div>

            <!-- Step 3: Booking Form (Checkout) -->
            <div class="space-y-4 booking-step-anim" v-if="selectedCourt && selectedSlot">
                <h2 class="font-display text-xl font-bold uppercase flex items-center gap-2">
                    <span class="bg-verge-canvas-black text-verge-canvas-white w-6 h-6 rounded-full flex items-center justify-center font-mono text-xs shadow-[2px_2px_0px_0px_rgba(82,0,255,1)]">4</span>
                    <span>Lengkapi Data Pemesanan</span>
                </h2>
                <BookingForm 
                    :court-id="selectedCourt.id" 
                    :court-name="selectedCourt.name" 
                    :date="selectedDate" 
                    :slot="selectedSlot" 
                />
            </div>
        </div>

        <!-- ERROR/FAILURE POPUP MODAL -->
        <div v-if="isErrorModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40" @click="isErrorModalOpen = false"></div>
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[8px_8px_0px_0px_rgba(19,19,19,1)] w-full max-w-md relative z-10 overflow-hidden flex flex-col p-6 text-center space-y-6 error-modal-card">
                <!-- Large Cross Icon -->
                <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center border-2 border-red-600 mx-auto shadow-[2px_2px_0px_0px_rgba(220,38,38,1)] error-modal-icon">
                    <X class="w-8 h-8 stroke-[3]" />
                </div>

                <div class="space-y-2">
                    <h3 class="font-display text-xl font-bold uppercase tracking-tight text-red-600">Booking Gagal!</h3>
                    <p class="text-xs text-verge-text-muted font-mono leading-relaxed bg-red-50/50 p-3 border border-red-100 rounded-sm">
                        {{ errorMessage }}
                    </p>
                </div>

                <div class="pt-2">
                    <button @click="isErrorModalOpen = false" class="w-full px-5 py-3 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted border border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] transition-colors">
                        Tutup & Coba Lagi
                    </button>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
/* No static keyframes needed, handled dynamically by GSAP */
</style>
