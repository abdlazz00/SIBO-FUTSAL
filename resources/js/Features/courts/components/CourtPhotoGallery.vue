<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Trash2, Image } from 'lucide-vue-next';

interface Photo {
    id: number;
    path: string;
}

const props = defineProps<{
    photos: Photo[];
    courtId: number;
}>();

const deletePhoto = (photoId: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus foto ini?')) {
        router.delete(route('admin.courts.photos.destroy', photoId), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
        <h3 class="font-display text-lg font-bold uppercase border-b border-verge-text-primary/10 pb-2 mb-4">Daftar Foto Lapangan</h3>

        <div v-if="photos.length === 0" class="flex flex-col items-center justify-center p-8 border border-dashed border-verge-text-primary/20 rounded bg-verge-surface-light text-verge-text-primary/30">
            <Image class="w-8 h-8" />
            <span class="font-mono text-[10px] mt-2">Belum ada foto terunggah</span>
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div v-for="photo in photos" :key="photo.id" class="border-2 border-verge-text-primary rounded-sm overflow-hidden aspect-square relative group bg-verge-surface-light">
                <img :src="photo.path" class="w-full h-full object-cover" />
                
                <!-- Floating Delete Button -->
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-all">
                    <button type="button" @click="deletePhoto(photo.id)" class="bg-red-600 hover:bg-red-700 text-white p-2 rounded-full border border-white/20 transition-colors shadow-lg">
                        <Trash2 class="w-4.5 h-4.5" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
