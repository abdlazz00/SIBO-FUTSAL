<script setup lang="ts">
import { watch, nextTick } from 'vue';
import { Clock, Check } from 'lucide-vue-next';
import gsap from 'gsap';

interface Slot {
    start_time: string;
    end_time: string;
    formatted_time: string;
    price: number;
    status: 'available' | 'booked';
    booking?: {
        id: number;
        booking_number: string;
        customer_name: string;
        status: string;
    } | null;
}

const props = defineProps<{
    slots: Slot[];
    selectedSlot: Slot | null;
}>();

const emit = defineEmits(['select']);

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(price);
};

const selectSlot = (slot: Slot) => {
    if (slot.status === 'available') {
        emit('select', slot);
    }
};

const isSelected = (slot: Slot) => {
    return props.selectedSlot && props.selectedSlot.start_time === slot.start_time;
};

watch(() => props.slots, (newSlots) => {
    if (newSlots && newSlots.length > 0) {
        nextTick(() => {
            gsap.fromTo('.slot-btn',
                { scale: 0.92, opacity: 0, y: 10 },
                { scale: 1, opacity: 1, y: 0, duration: 0.4, stagger: 0.02, ease: 'power2.out' }
            );
        });
    }
}, { immediate: true });
</script>

<template>
    <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] space-y-4">
        <div class="flex items-center justify-between border-b border-verge-text-primary/10 pb-3">
            <h3 class="font-display text-lg font-bold uppercase flex items-center gap-2">
                <Clock class="w-5 h-5 text-verge-ultraviolet" />
                <span>Pilih Slot Waktu</span>
            </h3>
            
            <!-- Legend -->
            <div class="flex gap-4 font-mono text-[9px] uppercase font-bold">
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 bg-verge-jelly-mint border border-verge-text-primary/20 rounded-sm"></span>
                    <span>Tersedia</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 bg-verge-ultraviolet border border-verge-text-primary/20 rounded-sm"></span>
                    <span>Dipilih</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 bg-red-100 border border-red-200 rounded-sm"></span>
                    <span>Terisi</span>
                </div>
            </div>
        </div>

        <div v-if="slots.length === 0" class="text-center py-8 font-mono text-xs text-verge-text-muted">
            Pilih tanggal terlebih dahulu atau tidak ada slot operasional tersedia.
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <button 
                v-for="slot in slots" 
                :key="slot.start_time"
                type="button"
                @click="selectSlot(slot)"
                :disabled="slot.status === 'booked'"
                :class="[
                    'p-4 border-2 rounded-md font-mono text-left flex flex-col justify-between h-24 transition-all relative overflow-hidden group shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] slot-btn',
                    slot.status === 'booked' 
                        ? 'bg-red-50/50 border-red-200 text-red-400 cursor-not-allowed shadow-none' 
                        : isSelected(slot)
                            ? 'bg-verge-ultraviolet border-verge-text-primary text-verge-canvas-white scale-[1.02]'
                            : 'bg-verge-canvas-white border-verge-text-primary text-verge-text-primary hover:bg-verge-jelly-mint hover:border-verge-text-primary'
                ]"
            >
                <div>
                    <span class="text-xs font-bold">{{ slot.formatted_time }}</span>
                    <span class="text-[9px] block uppercase tracking-wider mt-1 opacity-70">
                        {{ slot.status === 'booked' ? 'Terisi' : 'Tersedia' }}
                    </span>
                </div>

                <div class="flex items-end justify-between w-full">
                    <span class="text-[10px] font-bold">
                        {{ formatPrice(slot.price) }}
                    </span>
                    <Check v-if="isSelected(slot)" class="w-4 h-4 text-verge-canvas-white stroke-[3]" />
                </div>

                <!-- Hover Details for Booked Slots (Admin View helper or tooltip placeholder) -->
                <div v-if="slot.status === 'booked' && slot.booking" class="absolute inset-0 bg-red-100 border border-red-200 text-red-800 p-2 flex flex-col justify-center opacity-0 hover:opacity-100 transition-opacity text-[9px] font-mono leading-tight">
                    <span class="font-bold text-red-900 truncate">{{ slot.booking.customer_name }}</span>
                    <span class="text-red-700 mt-1">{{ slot.booking.booking_number }}</span>
                    <span class="uppercase tracking-widest text-[7px] text-red-500 font-bold mt-1">{{ slot.booking.status }}</span>
                </div>
            </button>
        </div>
    </div>
</template>
