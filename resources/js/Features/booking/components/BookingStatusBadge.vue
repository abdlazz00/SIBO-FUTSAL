<script setup lang="ts">
const props = defineProps<{
    status: 'confirmed' | 'completed' | 'cancelled' | string;
}>();

const getStatusDetails = (status: string) => {
    switch (status) {
        case 'confirmed':
            return {
                label: 'Confirmed',
                class: 'bg-blue-100 text-blue-800 border-blue-200'
            };
        case 'completed':
            return {
                label: 'Completed',
                class: 'bg-verge-jelly-mint text-verge-text-primary border-verge-text-primary/20'
            };
        case 'cancelled':
            return {
                label: 'Cancelled',
                class: 'bg-red-100 text-red-800 border-red-200'
            };
        default:
            return {
                label: status,
                class: 'bg-zinc-100 text-zinc-800 border-zinc-200'
            };
    }
};
</script>

<template>
    <span class="text-[9px] font-mono uppercase tracking-wider px-2.5 py-0.5 border rounded-sm font-bold shadow-[2px_2px_0px_0px_rgba(19,19,19,0.15)]" :class="getStatusDetails(status).class">
        {{ getStatusDetails(status).label }}
    </span>
</template>
