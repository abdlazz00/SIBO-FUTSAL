<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

interface PaginatorLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginator {
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    per_page: number;
    links: PaginatorLink[];
    prev_page_url: string | null;
    next_page_url: string | null;
}

defineProps<{
    paginator: Paginator;
}>();
</script>

<template>
    <div v-if="paginator.last_page > 1" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-verge-text-primary/10 font-mono text-xs">
        <!-- Info -->
        <span class="text-[10px] text-verge-text-muted uppercase tracking-wider">
            Menampilkan {{ paginator.from ?? 0 }}–{{ paginator.to ?? 0 }} dari {{ paginator.total }} data
        </span>

        <!-- Page Buttons -->
        <div class="flex items-center gap-1">
            <!-- Prev -->
            <Link
                v-if="paginator.prev_page_url"
                :href="paginator.prev_page_url"
                preserve-state
                class="flex items-center justify-center w-8 h-8 border-2 border-verge-text-primary rounded-sm bg-verge-canvas-white hover:bg-verge-surface-light shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] hover:shadow-[1px_1px_0px_0px_rgba(19,19,19,1)] hover:translate-x-[1px] hover:translate-y-[1px] transition-all"
            >
                <ChevronLeft class="w-3.5 h-3.5" />
            </Link>
            <span
                v-else
                class="flex items-center justify-center w-8 h-8 border-2 border-verge-text-primary/20 rounded-sm bg-verge-surface-light text-verge-text-muted cursor-not-allowed"
            >
                <ChevronLeft class="w-3.5 h-3.5" />
            </span>

            <!-- Numbered pages — only numeric pages and ellipsis; Prev/Next links are skipped -->
            <template v-for="link in paginator.links" :key="link.label">
                <!-- Skip « Previous and Next » -->
                <template v-if="!isNaN(Number(link.label))">
                    <!-- Active page -->
                    <span
                        v-if="link.active"
                        class="flex items-center justify-center w-8 h-8 border-2 border-verge-text-primary rounded-sm bg-verge-canvas-black text-verge-canvas-white text-[10px] font-bold shadow-[2px_2px_0px_0px_rgba(19,19,19,1)]"
                    >
                        {{ link.label }}
                    </span>
                    <!-- Clickable page -->
                    <Link
                        v-else-if="link.url"
                        :href="link.url"
                        preserve-state
                        class="flex items-center justify-center w-8 h-8 border-2 border-verge-text-primary/30 rounded-sm bg-verge-canvas-white hover:bg-verge-surface-light hover:border-verge-text-primary text-[10px] font-bold transition-all"
                    >
                        {{ link.label }}
                    </Link>
                </template>
                <!-- Ellipsis -->
                <span
                    v-else-if="link.label === '...'"
                    class="flex items-center justify-center w-6 h-8 text-verge-text-muted text-[10px]"
                >…</span>
            </template>

            <!-- Next -->
            <Link
                v-if="paginator.next_page_url"
                :href="paginator.next_page_url"
                preserve-state
                class="flex items-center justify-center w-8 h-8 border-2 border-verge-text-primary rounded-sm bg-verge-canvas-white hover:bg-verge-surface-light shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] hover:shadow-[1px_1px_0px_0px_rgba(19,19,19,1)] hover:translate-x-[1px] hover:translate-y-[1px] transition-all"
            >
                <ChevronRight class="w-3.5 h-3.5" />
            </Link>
            <span
                v-else
                class="flex items-center justify-center w-8 h-8 border-2 border-verge-text-primary/20 rounded-sm bg-verge-surface-light text-verge-text-muted cursor-not-allowed"
            >
                <ChevronRight class="w-3.5 h-3.5" />
            </span>
        </div>
    </div>
    <!-- Single page info -->
    <div v-else-if="paginator.total > 0" class="pt-4 border-t border-verge-text-primary/10 font-mono text-[10px] text-verge-text-muted text-right uppercase tracking-wider">
        Menampilkan semua {{ paginator.total }} data
    </div>
</template>
