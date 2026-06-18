<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
    LayoutDashboard, Calendar, DollarSign, Dumbbell, 
    Settings, LogOut, Bell, Menu, X, Shield, Users, FileText, CheckCircle, AlertTriangle,
    BarChart3
} from 'lucide-vue-next';
import axios from 'axios';

const page = usePage();
const user = page.props.auth.user;

const isSidebarOpen = ref(false);
const showProfileDropdown = ref(false);
const showNotificationsDropdown = ref(false);

const notifications = ref<any[]>([]);
const unreadCount = computed(() => notifications.value.filter((n: any) => !n.is_read).length);

const fetchNotifications = async () => {
    try {
        const res = await axios.get(route('notifications.index'));
        if (res.data && res.data.success) {
            notifications.value = res.data.all;
        }
    } catch (e) {
        console.error('Failed to load notifications', e);
    }
};

const markAsRead = async (id: number) => {
    try {
        await axios.patch(route('notifications.read', id));
        const notif = notifications.value.find((n: any) => n.id === id);
        if (notif) notif.is_read = true;
    } catch (e) {
        console.error(e);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.patch(route('notifications.read-all'));
        notifications.value.forEach((n: any) => n.is_read = true);
    } catch (e) {
        console.error(e);
    }
};

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const menuItems = computed(() => [
    { name: 'Dashboard', route: user.role === 'owner' ? 'owner.dashboard' : 'admin.dashboard', icon: LayoutDashboard },
    { name: 'Bookings', route: 'admin.bookings.index', icon: Calendar },
    { name: 'Courts', route: 'admin.courts.index', icon: Dumbbell },
    { name: 'Testimonials', route: 'admin.testimonials.index', icon: Users },
    { name: 'Payments', route: 'admin.payments.index', icon: DollarSign },
    { name: 'Expenses', route: 'admin.expenses.index', icon: FileText },
]);

onMounted(() => {
    fetchNotifications();
    
    // Connect to Echo private channel for real-time Reverb events
    if ((window as any).Echo) {
        (window as any).Echo.private(`App.Models.User.${user.id}`)
            .listen('.App\\Events\\RealTimeNotification', (data: any) => {
                notifications.value.unshift(data);
                if (notifications.value.length > 30) {
                    notifications.value.pop();
                }
            });
    }
});
</script>

<template>
    <div class="min-h-screen bg-verge-canvas-white text-verge-text-primary font-sans flex flex-col">
        <!-- Topbar -->
        <header class="bg-verge-canvas-white border-b border-verge-text-primary/10 sticky top-0 z-40 h-16 flex items-center justify-between px-6">
            <div class="flex items-center gap-4">
                <button @click="toggleSidebar" class="p-2 md:hidden hover:bg-verge-surface-light border border-verge-text-primary/10 rounded-sm transition-colors">
                    <Menu v-if="!isSidebarOpen" class="w-5 h-5" />
                    <X v-else class="w-5 h-5" />
                </button>
                <div class="flex items-center gap-2">
                    <span class="font-display text-2xl font-bold tracking-tight uppercase">VITKA <span class="text-verge-ultraviolet">FUTSAL</span></span>
                    <span class="bg-verge-canvas-black text-verge-canvas-white font-mono text-[10px] px-2 py-0.5 rounded-sm uppercase tracking-widest font-bold">ADMIN PANEL</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <!-- Notifications Bell -->
                <div class="relative">
                    <button @click="showNotificationsDropdown = !showNotificationsDropdown" class="relative p-2 hover:bg-verge-surface-light border border-verge-text-primary/10 rounded-sm transition-colors">
                        <Bell class="w-5 h-5" />
                        <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 bg-verge-ultraviolet text-verge-canvas-white text-[9px] font-mono font-bold w-4.5 h-4.5 rounded-full flex items-center justify-center border border-verge-canvas-white">
                            {{ unreadCount }}
                        </span>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div v-if="showNotificationsDropdown" class="absolute right-0 mt-2 w-80 bg-verge-canvas-white border-2 border-verge-text-primary rounded-md shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] py-2 z-50">
                        <div class="px-4 py-2 border-b border-verge-text-primary/10 flex items-center justify-between font-mono text-[10px] font-bold">
                            <span>NOTIFIKASI</span>
                            <button v-if="unreadCount > 0" @click="markAllAsRead" class="text-verge-ultraviolet hover:underline text-[9px]">Tandai Semua Dibaca</button>
                        </div>
                        
                        <div class="max-h-72 overflow-y-auto divide-y divide-verge-text-primary/10">
                            <div v-if="notifications.length === 0" class="px-4 py-6 text-center text-verge-text-muted font-mono text-[10px]">
                                Tidak ada notifikasi.
                            </div>
                            
                            <div 
                                v-for="notif in notifications" 
                                :key="notif.id" 
                                @click="markAsRead(notif.id)"
                                class="px-4 py-2.5 hover:bg-verge-surface-light transition-colors cursor-pointer flex gap-3 text-xs",
                                :class="{ 'bg-verge-surface-light/40 font-bold': !notif.is_read }"
                            >
                                <div class="flex-shrink-0 mt-0.5">
                                    <CheckCircle v-if="notif.type === 'payment'" class="w-4 h-4 text-green-500" />
                                    <AlertTriangle v-else class="w-4 h-4 text-verge-ultraviolet" />
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate text-verge-text-primary text-[11px]">{{ notif.title }}</div>
                                    <div class="text-[10px] text-verge-text-muted mt-0.5 leading-snug">{{ notif.message }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="relative">
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
            </div>
        </header>

        <div class="flex flex-1 relative">
            <!-- Sidebar -->
            <aside :class="[
                'w-64 bg-verge-canvas-white border-r border-verge-text-primary/10 flex flex-col fixed md:sticky top-16 h-[calc(100vh-4rem)] z-30 transition-transform duration-300 md:transform-none',
                isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
            ]">
                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                    <div v-for="item in menuItems" :key="item.name">
                        <Link :href="route(item.route)" :class="[
                            'flex items-center gap-3 px-4 py-3 rounded-sm text-xs font-mono uppercase tracking-wider transition-all border border-transparent',
                            route().current(item.route)
                                ? 'bg-verge-ultraviolet text-verge-canvas-white font-bold border-verge-text-primary/20'
                                : 'hover:bg-verge-surface-light hover:border-verge-text-primary/10 text-verge-text-muted hover:text-verge-text-primary'
                        ]">
                            <component :is="item.icon" class="w-4.5 h-4.5" />
                            <span>{{ item.name }}</span>
                        </Link>
                    </div>

                    <!-- Owner-only menu extension -->
                    <div v-if="user.role === 'owner'" class="pt-4 mt-4 border-t border-verge-text-primary/10">
                        <span class="px-4 text-[9px] font-mono text-verge-text-muted uppercase tracking-widest block mb-2 font-bold">Owner Features</span>
                        
                        <Link :href="route('owner.reports.index')" :class="[
                            'flex items-center gap-3 px-4 py-3 rounded-sm text-xs font-mono uppercase tracking-wider transition-all border border-transparent',
                            route().current('owner.reports.index')
                                ? 'bg-verge-ultraviolet text-verge-canvas-white font-bold border-verge-text-primary/20'
                                : 'hover:bg-verge-surface-light hover:border-verge-text-primary/10 text-verge-text-muted hover:text-verge-text-primary'
                        ]">
                            <BarChart3 class="w-4.5 h-4.5" />
                            <span>Financial Reports</span>
                        </Link>

                        <Link :href="route('owner.staff.index')" :class="[
                            'flex items-center gap-3 px-4 py-3 rounded-sm text-xs font-mono uppercase tracking-wider transition-all border border-transparent',
                            route().current('owner.staff.index')
                                ? 'bg-verge-ultraviolet text-verge-canvas-white font-bold border-verge-text-primary/20'
                                : 'hover:bg-verge-surface-light hover:border-verge-text-primary/10 text-verge-text-muted hover:text-verge-text-primary'
                        ]">
                            <Users class="w-4.5 h-4.5" />
                            <span>Staff Management</span>
                        </Link>
                    </div>
                </nav>

                <!-- Footer info -->
                <div class="p-4 border-t border-verge-text-primary/10 bg-verge-surface-light font-mono text-[9px] text-verge-text-muted flex flex-col gap-1">
                    <div>Logged in as: <span class="text-verge-text-primary uppercase font-bold">{{ user.role }}</span></div>
                    <div>Vitka Futsal System v2.0</div>
                </div>
            </aside>

            <!-- Backdrop -->
            <div v-if="isSidebarOpen" @click="toggleSidebar" class="fixed inset-0 bg-black/20 z-20 md:hidden"></div>

            <!-- Content Area -->
            <main class="flex-1 p-6 md:p-8 bg-verge-surface-light overflow-y-auto max-w-full">
                <!-- Bento wrapper or normal grid slot -->
                <div class="mx-auto max-w-7xl">
                    <slot></slot>
                </div>
            </main>
        </div>
    </div>
</template>
