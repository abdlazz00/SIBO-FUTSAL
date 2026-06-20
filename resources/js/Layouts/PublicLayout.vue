<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Search, Sun, Moon } from 'lucide-vue-next';
import { useTheme } from '@/Composables/useTheme';

const { isDark, initTheme, toggleTheme } = useTheme();

const page = usePage();
const user = page.props.auth.user;

onMounted(() => {
    initTheme();
});
</script>

<template>
    <div class="min-h-screen bg-verge-canvas-white text-verge-text-primary font-sans flex flex-col">
        <!-- Top Navbar -->
        <nav class="bg-verge-canvas-white border-b border-verge-text-primary/10 h-16 flex items-center justify-between px-6 sticky top-0 z-40">
            <div class="flex items-center gap-8">
                <Link href="/" class="flex items-center gap-2">
                    <span class="font-display text-2xl font-bold tracking-tight uppercase">VITKA <span class="text-verge-ultraviolet">FUTSAL</span></span>
                </Link>

                <div class="hidden md:flex items-center gap-6">
                    <Link :href="route('booking.create')" class="font-mono text-xs uppercase tracking-wider text-verge-text-primary font-bold">Pesan Lapangan</Link>
                    <Link :href="route('booking.track.show')" class="font-mono text-xs uppercase tracking-wider text-verge-text-muted hover:text-verge-text-primary transition-colors flex items-center gap-1">
                        <Search class="w-3.5 h-3.5" />
                        <span>Lacak Booking</span>
                    </Link>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Dark Mode Toggle Button -->
                <button @click="toggleTheme" class="p-2 hover:bg-verge-surface-light border border-verge-text-primary/10 rounded-sm transition-colors text-verge-text-primary flex items-center justify-center" aria-label="Toggle Theme">
                    <Sun v-if="isDark" class="w-4 h-4 text-amber-500 animate-pulse" />
                    <Moon v-else class="w-4 h-4" />
                </button>

                <div v-if="user" class="flex items-center gap-3">
                    <Link :href="route('dashboard')" class="px-4 py-2 border-2 border-verge-text-primary font-mono text-xs uppercase hover:bg-verge-surface-light rounded-sm transition-all shadow-[2px_2px_0px_0px_rgba(19,19,19,1)]">Dashboard</Link>
                </div>
                <div v-else class="flex items-center gap-2">
                    <Link :href="route('login')" class="px-4 py-2 border-2 border-verge-text-primary font-mono text-xs uppercase hover:bg-verge-surface-light rounded-sm transition-all shadow-[2px_2px_0px_0px_rgba(19,19,19,1)]">Masuk</Link>
                    <Link :href="route('register')" class="px-4 py-2 bg-verge-canvas-black text-verge-canvas-white border-2 border-verge-text-primary font-mono text-xs uppercase hover:bg-verge-text-muted rounded-sm transition-all shadow-[2px_2px_0px_0px_rgba(19,19,19,1)]">Daftar</Link>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 bg-verge-canvas-white">
            <slot></slot>
        </main>

        <!-- Footer -->
        <footer class="bg-verge-canvas-black text-verge-canvas-white border-t border-verge-text-primary py-12 px-6">
            <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="font-display text-lg font-bold tracking-wider mb-4 uppercase">VITKA FUTSAL</h3>
                    <p class="font-sans text-xs text-zinc-400 leading-relaxed">Fasilitas olahraga futsal premium dengan lapangan indoor dan outdoor berstandar tinggi.</p>
                </div>
                <div>
                    <h4 class="font-mono text-xs uppercase tracking-widest text-verge-jelly-mint mb-4">Kontak & Operasional</h4>
                    <p class="font-mono text-xs text-zinc-400 leading-relaxed">Buka setiap hari: 08:00 - 23:00</p>
                    <p class="font-mono text-xs text-zinc-400 leading-relaxed mt-2">WhatsApp: +62 812-3456-7890</p>
                </div>
                <div>
                    <h4 class="font-mono text-xs uppercase tracking-widest text-zinc-400 mb-4">Lokasi</h4>
                    <p class="font-sans text-xs text-zinc-400 leading-relaxed">Jl. Gajah Mada No. 12, Kompleks Vitka, Batam, Indonesia</p>
                </div>
            </div>
            <div class="max-w-6xl mx-auto mt-12 pt-6 border-t border-zinc-800 text-center font-mono text-[10px] text-zinc-500">
                &copy; 2026 Vitka Futsal. All rights reserved. Designed in Hazard White style.
            </div>
        </footer>
    </div>
</template>
