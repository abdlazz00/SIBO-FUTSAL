<script setup lang="ts">
import AdminLayout from './AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import { BarChart3, Users } from 'lucide-vue-next';
</script>

<template>
    <AdminLayout>
        <template #owner-menu>
            <div class="pt-4 mt-4 border-t border-verge-text-primary/10">
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

                <Link :href="route('owner.dashboard')" class="flex items-center gap-3 px-4 py-3 rounded-sm text-xs font-mono text-verge-text-muted hover:text-verge-text-primary hover:bg-verge-surface-light hover:border-verge-text-primary/10 border border-transparent uppercase tracking-wider transition-all">
                    <Users class="w-4.5 h-4.5" />
                    <span>Staff Management</span>
                </Link>
            </div>
        </template>

        <!-- Default slot passes down the main content -->
        <slot></slot>
    </AdminLayout>
</template>
