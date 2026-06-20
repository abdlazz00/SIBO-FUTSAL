<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { LogOut, User as UserIcon, Calendar, History, LayoutDashboard, Sun, Moon } from 'lucide-vue-next';
import { useTheme } from '@/Composables/useTheme';

const { isDark, initTheme, toggleTheme } = useTheme();

const page = usePage();
const user = page.props.auth.user;
const showProfileDropdown = ref(false);

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
                    <Link :href="route('customer.dashboard')" :class="[
                        'font-mono text-xs uppercase tracking-wider hover:text-verge-ultraviolet transition-colors',
                        route().current('customer.dashboard') ? 'text-verge-ultraviolet font-bold' : 'text-verge-text-muted'
                    ]">Dashboard</Link>
                    
                    <Link :href="route('booking.create')" class="font-mono text-xs uppercase tracking-wider text-verge-text-muted hover:text-verge-ultraviolet transition-colors">Book Now</Link>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <!-- Dark Mode Toggle Button -->
                <button @click="toggleTheme" class="p-2 hover:bg-verge-surface-light border border-verge-text-primary/10 rounded-sm transition-colors text-verge-text-primary flex items-center justify-center" aria-label="Toggle Theme">
                    <Sun v-if="isDark" class="w-4 h-4 text-amber-500 animate-pulse" />
                    <Moon v-else class="w-4 h-4" />
                </button>

                <!-- Profile Dropdown -->
                <div class="relative" v-if="user">
                    <button @click="showProfileDropdown = !showProfileDropdown" class="flex items-center gap-2 p-1.5 hover:bg-verge-surface-light border border-verge-text-primary/10 rounded-sm transition-colors text-sm font-medium">
                        <div class="w-7 h-7 bg-verge-ultraviolet text-verge-canvas-white rounded-full flex items-center justify-center font-bold text-xs">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <span class="hidden sm:inline font-mono">{{ user.name }}</span>
                    </button>

                    <div v-if="showProfileDropdown" class="absolute right-0 mt-2 w-48 bg-verge-canvas-white border-2 border-verge-text-primary rounded-md shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] py-1 z-50">
                        <div class="px-4 py-2 border-b border-verge-text-primary/10">
                            <p class="font-bold text-xs truncate">{{ user.name }}</p>
                            <p class="text-[10px] text-verge-text-muted truncate font-mono">{{ user.email }}</p>
                        </div>
                        <Link :href="route('profile.edit')" class="block px-4 py-2 text-xs font-mono hover:bg-verge-surface-light">Profile Settings</Link>
                        <Link :href="route('logout')" method="post" as="button" class="w-full text-left block px-4 py-2 text-xs font-mono hover:bg-red-50 text-red-600 border-t border-verge-text-primary/10">Log Out</Link>
                    </div>
                </div>

                <div v-else class="flex items-center gap-2">
                    <Link :href="route('login')" class="px-4 py-2 border-2 border-verge-text-primary font-mono text-xs uppercase hover:bg-verge-surface-light rounded-sm transition-all shadow-[2px_2px_0px_0px_rgba(19,19,19,1)]">Login</Link>
                    <Link :href="route('register')" class="px-4 py-2 bg-verge-canvas-black text-verge-canvas-white border-2 border-verge-text-primary font-mono text-xs uppercase hover:bg-verge-text-muted rounded-sm transition-all shadow-[2px_2px_0px_0px_rgba(19,19,19,1)]">Register</Link>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 bg-verge-surface-light p-6 md:p-10">
            <div class="max-w-6xl mx-auto">
                <slot></slot>
            </div>
        </main>
    </div>
</template>
