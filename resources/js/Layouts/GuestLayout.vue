<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Sparkles, Trophy, Shield, Clock, Star } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import gsap from 'gsap';

const leftPanelRef = ref<HTMLElement | null>(null);
const formCardRef = ref<HTMLElement | null>(null);

onMounted(() => {
    // Animate left panel elements staggered on mount
    if (leftPanelRef.value) {
        const children = leftPanelRef.value.querySelectorAll('.anim-item');
        gsap.fromTo(children,
            { x: -30, opacity: 0 },
            { x: 0, opacity: 1, duration: 0.6, stagger: 0.12, ease: 'power3.out', delay: 0.1 }
        );
    }
    // Animate form card entrance
    if (formCardRef.value) {
        gsap.fromTo(formCardRef.value,
            { y: 24, opacity: 0, scale: 0.97 },
            { y: 0, opacity: 1, scale: 1, duration: 0.55, ease: 'power3.out', delay: 0.15 }
        );
    }
});
</script>

<template>
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2 bg-verge-surface-light text-verge-text-primary">
        
        <!-- Left Side: Visual Brutalist Panel (Only visible on lg screens) -->
        <div class="hidden lg:flex flex-col justify-between p-12 bg-verge-canvas-black text-verge-canvas-white relative overflow-hidden border-r-4 border-verge-text-primary select-none">
            <!-- Background Futsal Court Image with dark overlay -->
            <div class="absolute inset-0 z-0">
                <img src="/images/indoor_futsal_court.jpg" alt="Futsal Court" class="w-full h-full object-cover opacity-35" />
                <div class="absolute inset-0 bg-gradient-to-br from-verge-canvas-black/85 via-verge-canvas-black/95 to-verge-ultraviolet/40"></div>
            </div>
            
            <!-- Decorative animated lines (subtle background detail) -->
            <div class="absolute inset-0 z-0 overflow-hidden opacity-10">
                <div class="absolute top-1/4 left-0 w-full h-px bg-white/30"></div>
                <div class="absolute top-2/4 left-0 w-full h-px bg-white/20"></div>
                <div class="absolute top-3/4 left-0 w-full h-px bg-white/10"></div>
                <div class="absolute top-0 left-1/3 w-px h-full bg-white/20"></div>
                <div class="absolute top-0 left-2/3 w-px h-full bg-white/10"></div>
            </div>

            <div ref="leftPanelRef" class="relative z-10 flex flex-col justify-between h-full">
                <!-- Top Header Logo -->
                <div class="anim-item flex items-center gap-2">
                    <Link href="/" class="flex items-center gap-2">
                        <span class="font-display text-2xl font-black tracking-tight uppercase text-white">
                            VITKA <span class="text-verge-jelly-mint">FUTSAL</span>
                        </span>
                    </Link>
                </div>

                <!-- Middle Headline Content -->
                <div class="space-y-8 my-auto">
                    <div class="anim-item inline-flex items-center gap-1.5 px-3 py-1 bg-verge-jelly-mint text-verge-text-primary font-mono text-[9px] uppercase font-bold rounded-sm border border-verge-text-primary shadow-[2px_2px_0px_0px_rgba(255,255,255,1)]">
                        <Trophy class="w-3.5 h-3.5" />
                        <span>Arena Futsal Terpopuler</span>
                    </div>
                    
                    <h1 class="anim-item font-display text-5xl xl:text-6xl font-black uppercase tracking-tight leading-none text-white">
                        SIAP <br />
                        <span class="text-verge-jelly-mint">TANDING?</span>
                    </h1>
                    
                    <p class="anim-item text-sm font-sans text-verge-text-muted leading-relaxed max-w-sm">
                        Sewa lapangan futsal premium standar internasional di Batam dengan sistem booking otomatis, praktis, dan real-time.
                    </p>

                    <!-- Feature Points -->
                    <div class="anim-item space-y-3">
                        <div class="flex items-center gap-3 text-white/70">
                            <div class="w-6 h-6 rounded-sm bg-verge-ultraviolet/30 border border-verge-ultraviolet/50 flex items-center justify-center flex-shrink-0">
                                <Shield class="w-3.5 h-3.5 text-verge-ultraviolet" />
                            </div>
                            <span class="font-mono text-[10px] uppercase font-bold tracking-wide">Booking Aman & Terpercaya</span>
                        </div>
                        <div class="flex items-center gap-3 text-white/70">
                            <div class="w-6 h-6 rounded-sm bg-verge-jelly-mint/20 border border-verge-jelly-mint/40 flex items-center justify-center flex-shrink-0">
                                <Clock class="w-3.5 h-3.5 text-verge-jelly-mint" />
                            </div>
                            <span class="font-mono text-[10px] uppercase font-bold tracking-wide">Lapangan Tersedia 24 Jam</span>
                        </div>
                        <div class="flex items-center gap-3 text-white/70">
                            <div class="w-6 h-6 rounded-sm bg-yellow-400/20 border border-yellow-400/40 flex items-center justify-center flex-shrink-0">
                                <Star class="w-3.5 h-3.5 text-yellow-400" />
                            </div>
                            <span class="font-mono text-[10px] uppercase font-bold tracking-wide">Rating 4.9 dari Pengguna</span>
                        </div>
                    </div>
                </div>

                <!-- Bottom Badges / Credits -->
                <div class="anim-item flex flex-wrap gap-3 font-mono text-[9px] text-verge-text-muted uppercase font-bold border-t border-white/10 pt-6">
                    <div class="flex items-center gap-1">
                        <Sparkles class="w-3.5 h-3.5 text-verge-jelly-mint" />
                        <span>System Online</span>
                    </div>
                    <div>•</div>
                    <div>Est. 2026</div>
                    <div>•</div>
                    <div>Batam, Kep. Riau</div>
                </div>
            </div>
        </div>

        <!-- Right Side: Forms Section -->
        <div class="flex flex-col justify-center items-center p-6 sm:p-12 md:p-16 min-h-screen relative overflow-y-auto">
            <!-- Small Header Logo (Mobile Only) -->
            <div class="lg:hidden mb-8 anim-item">
                <Link href="/" class="flex items-center gap-2">
                    <span class="font-display text-2xl font-black tracking-tight uppercase text-verge-text-primary">
                        VITKA <span class="text-verge-ultraviolet">FUTSAL</span>
                    </span>
                </Link>
            </div>

            <!-- Form Card Wrapper -->
            <div ref="formCardRef" class="auth-form-card w-full max-w-md bg-verge-canvas-white border-2 border-verge-text-primary p-6 sm:p-8 rounded-lg shadow-[8px_8px_0px_0px_rgba(19,19,19,1)] space-y-6 opacity-0">
                <slot />
            </div>
        </div>
    </div>
</template>
