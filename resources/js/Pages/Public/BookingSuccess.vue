<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Check, Printer, Calendar, Clock, User, Phone, DollarSign, Home, Plus } from 'lucide-vue-next';

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
    customer_email?: string | null;
    date: string;
    start_time: string;
    end_time: string;
    total_price: number;
    status: string;
    court: Court;
}

const props = defineProps<{
    booking: Booking;
}>();

const isSuccessModalOpen = ref(true);

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

const printReceipt = () => {
    window.print();
};
</script>

<template>
    <Head title="Booking Berhasil - Vitka Futsal" />

    <PublicLayout>
        <!-- print:hide hides components during printer execution -->
        <div class="max-w-3xl mx-auto py-12 px-6 space-y-8 print:py-0 print:px-0">
            
            <!-- Breadcrumbs / Success Header (Hide on Print) -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-verge-text-primary/10 pb-4 print:hidden">
                <div>
                    <span class="font-mono text-[9px] uppercase font-bold text-verge-ultraviolet tracking-widest">Pemesanan Selesai</span>
                    <h1 class="font-display text-3xl font-bold uppercase tracking-tight mt-0.5">Tanda Terima Booking</h1>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="printReceipt" class="flex items-center gap-1.5 px-4 py-2 border-2 border-verge-text-primary hover:bg-verge-surface-light font-mono text-xs uppercase font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] transition-colors">
                        <Printer class="w-4 h-4" />
                        <span>Cetak Bukti</span>
                    </button>
                </div>
            </div>

            <!-- Receipt Ticket (Brutalist Style) -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[8px_8px_0px_0px_rgba(19,19,19,1)] overflow-hidden print:shadow-none print:border-2">
                <!-- Receipt Top Bar -->
                <div class="bg-verge-canvas-black text-verge-canvas-white p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b-2 border-verge-text-primary">
                    <div class="space-y-0.5">
                        <span class="font-mono text-[9px] text-verge-jelly-mint uppercase tracking-widest font-bold">KODE TIKET AKTIF</span>
                        <h2 class="font-mono text-2xl font-bold tracking-tight text-white uppercase">{{ booking.booking_number }}</h2>
                    </div>
                    <div class="text-left sm:text-right font-mono text-xs">
                        <p class="font-bold uppercase text-verge-jelly-mint">VITKA FUTSAL</p>
                        <p class="text-[9px] text-verge-canvas-white/60">Batam, Kepulauan Riau</p>
                    </div>
                </div>

                <div class="p-6 md:p-8 space-y-6">
                    <!-- Ticket Main Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-b-2 border-dashed border-verge-text-primary/20 pb-6">
                        
                        <!-- Left Side: Jadwal Lapangan -->
                        <div class="space-y-4">
                            <h3 class="font-mono text-[10px] uppercase font-bold text-verge-ultraviolet tracking-wider">Jadwal Sewa</h3>
                            
                            <div class="space-y-3 font-sans text-xs">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 p-1.5 border border-verge-text-primary/10 rounded-sm bg-verge-surface-light text-verge-text-muted">
                                        <Plus class="w-4 h-4 text-verge-text-primary" />
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-mono uppercase text-verge-text-muted">Lapangan</p>
                                        <p class="font-bold text-sm uppercase text-verge-text-primary mt-0.5">{{ booking.court.name }}</p>
                                        <p class="text-[10px] font-mono text-verge-text-muted uppercase">Tipe: {{ booking.court.type }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 p-1.5 border border-verge-text-primary/10 rounded-sm bg-verge-surface-light text-verge-text-muted">
                                        <Calendar class="w-4 h-4 text-verge-text-primary" />
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-mono uppercase text-verge-text-muted">Tanggal Bermain</p>
                                        <p class="font-bold text-sm text-verge-text-primary mt-0.5">{{ formatDate(booking.date) }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 p-1.5 border border-verge-text-primary/10 rounded-sm bg-verge-surface-light text-verge-text-muted">
                                        <Clock class="w-4 h-4 text-verge-text-primary" />
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-mono uppercase text-verge-text-muted">Waktu / Jam Sesi</p>
                                        <p class="font-bold text-sm font-mono text-verge-text-primary mt-0.5">
                                            {{ booking.start_time.slice(0, 5) }} - {{ booking.end_time.slice(0, 5) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side: Identitas Pemesan & Tagihan -->
                        <div class="space-y-4">
                            <h3 class="font-mono text-[10px] uppercase font-bold text-verge-ultraviolet tracking-wider">Identitas Pemesan</h3>
                            
                            <div class="space-y-3 font-sans text-xs">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 p-1.5 border border-verge-text-primary/10 rounded-sm bg-verge-surface-light text-verge-text-muted">
                                        <User class="w-4 h-4 text-verge-text-primary" />
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-mono uppercase text-verge-text-muted">Nama Pelanggan</p>
                                        <p class="font-bold text-sm text-verge-text-primary mt-0.5">{{ booking.customer_name }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 p-1.5 border border-verge-text-primary/10 rounded-sm bg-verge-surface-light text-verge-text-muted">
                                        <Phone class="w-4 h-4 text-verge-text-primary" />
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-mono uppercase text-verge-text-muted">Nomor WhatsApp HP</p>
                                        <p class="font-bold text-sm font-mono text-verge-text-primary mt-0.5">{{ booking.customer_phone }}</p>
                                    </div>
                                </div>

                                <div v-if="booking.customer_email" class="flex items-start gap-3">
                                    <div class="mt-0.5 p-1.5 border border-verge-text-primary/10 rounded-sm bg-verge-surface-light text-verge-text-muted">
                                        <span class="font-mono font-bold text-xs">@</span>
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-mono uppercase text-verge-text-muted">Alamat Email</p>
                                        <p class="font-bold text-sm text-verge-text-primary mt-0.5">{{ booking.customer_email }}</p>
                                    </div>
                                </div>

                                <!-- Jumlah Tagihan Section (Repositioned to the top right) -->
                                <div class="flex items-start gap-3 pt-3 mt-3 border-t border-verge-text-primary/10">
                                    <div class="mt-0.5 p-1.5 border border-verge-text-primary rounded-sm bg-verge-surface-light text-verge-text-muted">
                                        <DollarSign class="w-4 h-4 text-verge-ultraviolet stroke-[2.5]" />
                                    </div>
                                    <div>
                                        <p class="text-[9px] font-mono uppercase font-bold text-verge-text-muted">Jumlah Tagihan (Menunggu Bayar)</p>
                                        <p class="font-display text-xl font-bold text-verge-ultraviolet mt-0.5">{{ formatPrice(booking.total_price) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Instruction (Full Width for cleaner layout) -->
                    <div class="space-y-3 pt-4 border-t border-dashed border-verge-text-primary/20">
                        <h3 class="font-mono text-[10px] uppercase font-bold text-verge-text-primary tracking-wider">Langkah Pembayaran Selanjutnya</h3>
                        <div class="bg-verge-surface-light border border-verge-text-primary/10 p-4 rounded-sm text-xs text-verge-text-muted leading-relaxed space-y-1.5">
                            <p>1. Simpan nomor booking <strong class="font-mono text-verge-ultraviolet font-bold">{{ booking.booking_number }}</strong> ini dengan melakukan screenshot atau menekan tombol cetak bukti.</p>
                            <p>2. Lakukan penyelesaian pembayaran **sebelum jadwal bermain dimulai**.</p>
                            <p>3. Konfirmasikan kode booking ini kepada Admin di lapangan untuk pembayaran tunai (Cash), transfer bank, atau scan QRIS kasir resmi.</p>
                        </div>
                    </div>
                </div>

                <!-- Footer barcode effect -->
                <div class="bg-verge-surface-light border-t-2 border-verge-text-primary p-4 flex flex-col items-center justify-center space-y-2">
                    <div class="font-mono text-[8px] tracking-[0.4em] font-bold text-verge-text-muted select-none">
                        |||||||||||||||||| {{ booking.booking_number }} ||||||||||||||||||
                    </div>
                </div>
            </div>

            <!-- Action buttons bottom (Hide on Print) -->
            <div class="flex flex-col sm:flex-row justify-center gap-4 pt-4 print:hidden">
                <Link :href="route('landing')" class="flex items-center justify-center gap-2 px-6 py-3 border-2 border-verge-text-primary hover:bg-verge-surface-light font-mono text-xs uppercase font-bold rounded-sm shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] transition-all bg-verge-canvas-white">
                    <Home class="w-4 h-4" />
                    <span>Kembali ke Beranda</span>
                </Link>
                <Link :href="route('booking.create')" class="flex items-center justify-center gap-2 px-6 py-3 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted font-mono text-xs uppercase font-bold rounded-sm shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] transition-all">
                    <Plus class="w-4 h-4" />
                    <span>Pesan Lapangan Lagi</span>
                </Link>
            </div>
        </div>

        <!-- SUCCESS ANIMATION POPUP MODAL (ON LOAD) -->
        <div v-if="isSuccessModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 print:hidden">
            <div class="fixed inset-0 bg-black/40" @click="isSuccessModalOpen = false"></div>
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[8px_8px_0px_0px_rgba(19,19,19,1)] w-full max-w-md relative z-10 overflow-hidden flex flex-col p-6 text-center space-y-6 animate-scale-up">
                
                <!-- Large Success Icon -->
                <div class="w-16 h-16 bg-verge-jelly-mint text-verge-text-primary rounded-full flex items-center justify-center border-2 border-verge-text-primary mx-auto shadow-[2px_2px_0px_0px_rgba(19,19,19,1)]">
                    <Check class="w-8 h-8 stroke-[3]" />
                </div>

                <div class="space-y-2">
                    <h3 class="font-display text-xl font-bold uppercase tracking-tight">Booking Berhasil!</h3>
                    <p class="text-xs text-verge-text-muted font-mono leading-relaxed">
                        Pemesanan Anda untuk <strong class="text-verge-text-primary uppercase">{{ booking.court.name }}</strong> telah terdaftar.
                    </p>
                    <div class="bg-verge-surface-light border border-verge-text-primary/10 p-2.5 rounded-sm font-mono text-xs mt-3">
                        <span class="text-verge-text-muted">Kode Booking:</span>
                        <strong class="text-verge-ultraviolet block text-sm font-bold tracking-wide mt-0.5">{{ booking.booking_number }}</strong>
                    </div>
                </div>

                <div class="pt-2">
                    <button @click="isSuccessModalOpen = false" class="w-full px-5 py-3 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted border border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] transition-colors">
                        Lihat Bukti Penerimaan &rarr;
                    </button>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>

<style scoped>
@keyframes scaleUp {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
.animate-scale-up {
  animation: scaleUp 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@media print {
  /* Hide standard page layout headers and background elements in print */
  header, footer, nav, aside {
    display: none !important;
  }
}
</style>
