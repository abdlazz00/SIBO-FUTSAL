<script setup lang="ts">
import { onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Dumbbell, Star, MessageSquare, ArrowRight, CheckCircle2, MapPin } from 'lucide-vue-next';
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

interface Testimonial {
    id: number;
    customer_name: string;
    rating: number;
    content: string;
}

const props = defineProps<{
    courts: Court[];
    testimonials: Testimonial[];
}>();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(price);
};

onMounted(() => {
    // 1. Hero animations
    gsap.fromTo('.hero-anim', 
        { y: 30, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.8, stagger: 0.15, ease: 'power3.out' }
    );

    // 2. Court bento cards animations
    gsap.fromTo('.court-card',
        { y: 40, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.8, stagger: 0.1, ease: 'power3.out', delay: 0.3 }
    );

    // 3. Testimonial cards animations
    gsap.fromTo('.testimonial-card',
        { y: 35, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.8, stagger: 0.1, ease: 'power3.out', delay: 0.5 }
    );

    // 4. Map element animation
    gsap.fromTo('.map-anim',
        { scale: 0.96, opacity: 0 },
        { scale: 1, opacity: 1, duration: 1, ease: 'power3.out', delay: 0.7 }
    );
});
</script>

<template>
    <Head title="Vitka Futsal - Booking Online Futsal Batam" />

    <PublicLayout>
        <!-- Hero Section -->
        <section class="border-b-2 border-verge-text-primary bg-verge-surface-light/40 py-20 px-6 relative overflow-hidden">
            <div class="max-w-6xl mx-auto flex flex-col items-center text-center space-y-6 relative z-10">
                <span class="bg-verge-ultraviolet text-verge-canvas-white font-mono text-[10px] uppercase font-bold tracking-widest px-3 py-1 rounded-sm border border-verge-text-primary/10 shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] hero-anim">
                    #1 Futsal Booking Batam
                </span>

                <h1 class="font-display text-5xl md:text-7xl font-bold uppercase tracking-tight text-verge-text-primary max-w-4xl leading-none hero-anim">
                    SEWA LAPANGAN FUTSAL <span class="text-verge-ultraviolet">TANPA RIBET</span>
                </h1>

                <p class="font-sans text-sm md:text-base text-verge-text-muted max-w-xl leading-relaxed hero-anim">
                    Sistem booking online mandiri 24/7. Cek jadwal kosong lapangan secara real-time, pesan langsung, dan nikmati fasilitas futsal premium kami.
                </p>

                <!-- CTA Buttons -->
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4 w-full max-w-md hero-anim">
                    <!-- Book Now Button -->
                    <Link :href="route('booking.create')" class="w-full sm:w-auto px-8 py-4 bg-verge-ultraviolet text-verge-canvas-white hover:bg-verge-deep-link-blue border-2 border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex items-center justify-center gap-2 transition-all">
                        <span>Pesan Lapangan</span>
                        <ArrowRight class="w-4 h-4" />
                    </Link>

                    <!-- Track Booking Button -->
                    <Link :href="route('booking.track.show')" class="w-full sm:w-auto px-8 py-4 bg-verge-canvas-white text-verge-text-primary hover:bg-verge-surface-light border-2 border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex items-center justify-center gap-2 transition-all">
                        <span>Lacak Booking</span>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Courts Grid Section -->
        <section class="py-20 px-6 max-w-6xl mx-auto space-y-12">
            <div class="text-center md:text-left flex flex-col md:flex-row md:items-end justify-between border-b border-verge-text-primary/10 pb-6">
                <div>
                    <span class="font-mono text-[10px] uppercase font-bold text-verge-ultraviolet tracking-widest">Fasilitas Terbaik</span>
                    <h2 class="font-display text-3xl md:text-4xl font-bold uppercase tracking-tight text-verge-text-primary mt-1">Daftar Lapangan Kami</h2>
                </div>
                <p class="text-xs text-verge-text-muted mt-2 md:mt-0 max-w-xs font-mono">
                    Tersedia lapangan indoor dengan rumput sintetis maupun vinil premium berstandar nasional.
                </p>
            </div>

            <!-- Courts Bento Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div v-for="court in courts" :key="court.id" class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[6px_6px_0px_0px_rgba(19,19,19,1)] overflow-hidden flex flex-col justify-between group h-full court-card">
                    <!-- Photo -->
                    <div class="h-48 bg-verge-surface-light border-b-2 border-verge-text-primary relative overflow-hidden flex items-center justify-center">
                        <img v-if="court.photos && court.photos.length > 0" :src="court.photos[0].path" :alt="court.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <div v-else class="flex flex-col items-center text-verge-text-primary/20">
                            <Dumbbell class="w-12 h-12" />
                            <span class="font-mono text-[9px] mt-1">NO PHOTO</span>
                        </div>
                        <span class="absolute top-3 right-3 text-[9px] font-mono uppercase tracking-wider px-2.5 py-1 bg-verge-canvas-black text-verge-canvas-white border border-verge-text-primary rounded-sm font-bold shadow-[2px_2px_0px_0px_rgba(19,19,19,1)]">
                            {{ court.type }}
                        </span>
                    </div>

                    <!-- Details -->
                    <div class="p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-display text-2xl font-bold uppercase tracking-tight">{{ court.name }}</h3>
                            <div class="mt-4 space-y-1.5 font-mono text-[10px] text-verge-text-muted border-t border-verge-text-primary/10 pt-3">
                                <div class="flex justify-between">
                                    <span>Jam Operasional:</span>
                                    <span class="text-verge-text-primary font-bold">{{ court.open_time.slice(0, 5) }} - {{ court.close_time.slice(0, 5) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Durasi Sewa:</span>
                                    <span class="text-verge-text-primary font-bold">{{ court.slot_duration }} Menit / Slot</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 border-t border-verge-text-primary/10 pt-4">
                            <div class="flex items-baseline justify-between mb-4">
                                <span class="font-mono text-[9px] text-verge-text-muted uppercase">Tarif Dasar</span>
                                <span class="font-display text-xl font-bold text-verge-ultraviolet">{{ formatPrice(court.price) }}<span class="text-[10px] text-verge-text-muted font-normal font-sans">/slot</span></span>
                            </div>
                            <Link :href="route('booking.create')" class="w-full text-center block px-4 py-3 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted border border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] transition-colors">
                                Book Now
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="border-t-2 border-b-2 border-verge-text-primary bg-verge-surface-light/30 py-20 px-6">
            <div class="max-w-6xl mx-auto space-y-12">
                <div class="text-center space-y-2">
                    <span class="font-mono text-[10px] uppercase font-bold text-verge-ultraviolet tracking-widest">Kepuasan Pelanggan</span>
                    <h2 class="font-display text-3xl md:text-4xl font-bold uppercase tracking-tight text-verge-text-primary">Apa Kata Mereka?</h2>
                </div>

                <!-- Testimonials Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div v-for="testimonial in testimonials" :key="testimonial.id" class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between testimonial-card">
                        <div>
                            <div class="flex items-center gap-0.5 text-amber-500 mb-3">
                                <Star v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= testimonial.rating ? 'fill-amber-500' : 'text-zinc-300'" />
                            </div>
                            <p class="font-sans text-xs text-verge-text-muted leading-relaxed italic">
                                "{{ testimonial.content }}"
                            </p>
                        </div>
                        <div class="flex items-center gap-2 mt-6 border-t border-verge-text-primary/10 pt-3">
                            <div class="w-7 h-7 bg-verge-ultraviolet text-verge-canvas-white rounded-full flex items-center justify-center font-bold text-xs">
                                {{ testimonial.customer_name.charAt(0).toUpperCase() }}
                            </div>
                            <span class="font-mono text-[11px] font-bold text-verge-text-primary">{{ testimonial.customer_name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Location Section -->
        <section class="py-20 px-6 max-w-6xl mx-auto space-y-12">
            <div class="text-center space-y-2">
                <span class="font-mono text-[10px] uppercase font-bold text-verge-ultraviolet tracking-widest">Alamat Kami</span>
                <h2 class="font-display text-3xl md:text-4xl font-bold uppercase tracking-tight text-verge-text-primary">Lokasi Vitka Futsal</h2>
            </div>
            
            <!-- Map Bento Box -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-4 rounded-lg shadow-[6px_6px_0px_0px_rgba(19,19,19,1)] h-96 relative overflow-hidden map-anim flex items-center justify-center">
                <!-- Fallback Map Card (Visible if offline / DNS error) -->
                <div class="absolute inset-0 bg-verge-surface-light p-8 flex flex-col justify-center items-center text-center space-y-4 select-none z-0">
                    <MapPin class="w-12 h-12 text-verge-ultraviolet" />
                    <div>
                        <h4 class="font-display text-lg font-bold uppercase text-verge-text-primary">Vitka Futsal Tiban</h4>
                        <p class="font-sans text-[11px] text-verge-text-muted mt-1 max-w-sm">
                            Jl. Gajah Mada, Kompleks The Vitka City, Tiban, Sekupang, Batam.
                        </p>
                    </div>
                    <a href="https://maps.app.goo.gl/wK5zoRe6YoUCZ2GA7" target="_blank" class="px-5 py-2.5 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted dark:bg-verge-canvas-white dark:text-verge-canvas-black border border-verge-text-primary font-mono text-[10px] uppercase font-bold rounded-sm shadow-[3px_3px_0px_0px_rgba(19,19,19,0.15)] transition-colors">
                        Buka di Google Maps
                    </a>
                </div>

                <!-- Google Maps Iframe -->
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.0531580977464!2d103.9782522!3d1.1111666!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31d98b8dfaccab27%3A0xa988646eb2338a7f!2sVitka%20Futsal%20Tiban!5e0!3m2!1sid!2sid!4v1718630000000!5m2!1sid!2sid" class="w-full h-full border-0 rounded-sm relative z-10 bg-transparent" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </section>
    </PublicLayout>
</template>
