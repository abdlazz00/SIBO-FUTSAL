<script setup lang="ts">
import { ref, watch } from 'vue';
import { Calendar as CalendarIcon } from 'lucide-vue-next';

const props = defineProps<{
    modelValue: string;
}>();

const emit = defineEmits(['update:modelValue']);

const today = new Date().toISOString().slice(0, 10);
const selectedDate = ref(props.modelValue || today);

watch(selectedDate, (newVal) => {
    emit('update:modelValue', newVal);
});
</script>

<template>
    <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
        <label for="booking_date" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted flex items-center gap-1.5 mb-2">
            <CalendarIcon class="w-4 h-4 text-verge-ultraviolet" />
            <span>PILIH TANGGAL SEWA</span>
        </label>
        
        <input 
            id="booking_date" 
            v-model="selectedDate" 
            type="date" 
            :min="today" 
            class="w-full border-2 border-verge-text-primary p-3 rounded-sm font-mono text-sm focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white" 
        />
    </div>
</template>
