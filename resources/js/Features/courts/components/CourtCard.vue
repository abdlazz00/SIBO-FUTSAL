<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Dumbbell, ShieldAlert, BadgeInfo } from 'lucide-vue-next';

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

const props = defineProps<{
    court: Court;
}>();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(price);
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-verge-jelly-mint text-verge-text-primary border-verge-text-primary/20';
        case 'inactive':
            return 'bg-red-100 text-red-800 border-red-200';
        case 'maintenance':
            return 'bg-amber-100 text-amber-800 border-amber-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const getCourtTypeLabel = (type: string) => {
    return type === 'indoor' ? 'Indoor (Sintetis/Vinil)' : 'Outdoor (Semen)';
};
</script>

<template>
    <div class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] overflow-hidden flex flex-col h-full group">
        <!-- Photo Container -->
        <div class="h-44 bg-verge-surface-light border-b-2 border-verge-text-primary relative overflow-hidden flex items-center justify-center">
            <template v-if="court.photos && court.photos.length > 0">
                <img :src="court.photos[0].path" :alt="court.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
            </template>
            <template v-else>
                <div class="flex flex-col items-center justify-center text-verge-text-primary/30">
                    <Dumbbell class="w-12 h-12 stroke-[1.5]" />
                    <span class="font-mono text-[10px] mt-2">NO PHOTO</span>
                </div>
            </template>

            <!-- Status Badge (Floating) -->
            <div class="absolute top-3 right-3">
                <span class="text-[9px] font-mono uppercase tracking-wider px-2 py-0.5 border rounded-sm font-bold shadow-[2px_2px_0px_0px_rgba(19,19,19,1)]" :class="getStatusClass(court.status)">
                    {{ court.status }}
                </span>
            </div>
        </div>

        <!-- Details -->
        <div class="p-5 flex-1 flex flex-col justify-between">
            <div>
                <span class="text-[9px] font-mono text-verge-text-muted uppercase tracking-widest block">{{ getCourtTypeLabel(court.type) }}</span>
                <h3 class="font-display text-xl font-bold uppercase mt-1 tracking-tight truncate">{{ court.name }}</h3>

                <!-- Time slots metadata -->
                <div class="mt-3 space-y-1 font-mono text-[10px] text-verge-text-muted">
                    <div class="flex justify-between">
                        <span>Jam Buka:</span>
                        <span class="text-verge-text-primary font-bold">{{ court.open_time.slice(0, 5) }} - {{ court.close_time.slice(0, 5) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Durasi Slot:</span>
                        <span class="text-verge-text-primary font-bold">{{ court.slot_duration }} Menit</span>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <!-- Price info -->
                <div class="flex items-baseline justify-between border-t border-verge-text-primary/10 pt-3 mb-4">
                    <span class="font-mono text-[9px] text-verge-text-muted uppercase">Harga Dasar</span>
                    <span class="font-display text-lg font-bold text-verge-ultraviolet">{{ formatPrice(court.price) }}<span class="text-[10px] text-verge-text-muted font-sans font-normal"> / Slot</span></span>
                </div>

                <!-- Action Button -->
                <Link :href="route('admin.courts.edit', court.id)" class="w-full text-center block px-4 py-2 border-2 border-verge-text-primary font-mono text-xs uppercase font-bold hover:bg-verge-surface-light rounded-sm transition-all shadow-[2px_2px_0px_0px_rgba(19,19,19,1)]">
                    Kelola Lapangan &rarr;
                </Link>
            </div>
        </div>
    </div>
</template>
