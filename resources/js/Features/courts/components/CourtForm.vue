<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Save, Loader } from 'lucide-vue-next';

interface Court {
    id?: number;
    name: string;
    type: 'indoor' | 'outdoor';
    price: number;
    slot_duration: number;
    open_time: string;
    close_time: string;
    status: 'active' | 'inactive' | 'maintenance';
}

const props = defineProps<{
    court?: Court;
    isEdit?: boolean;
}>();

const emit = defineEmits(['submit']);

const form = useForm({
    name: props.court?.name ?? '',
    type: props.court?.type ?? 'indoor',
    price: props.court?.price ?? 120000,
    slot_duration: props.court?.slot_duration ?? 60,
    open_time: props.court?.open_time ? props.court.open_time.slice(0, 5) : '08:00',
    close_time: props.court?.close_time ? props.court.close_time.slice(0, 5) : '22:00',
    status: props.court?.status ?? 'active',
    photos: [] as File[],
    _method: props.isEdit ? 'PUT' : 'POST' // Laravel requires PUT spoofing for file uploads, but wait, Inertia handle handles this
});

const fileInputRef = ref<HTMLInputElement | null>(null);
const filePreviews = ref<string[]>([]);

const handleFileChange = (event: Event) => {
    const files = (event.target as HTMLInputElement).files;
    if (files) {
        const fileList = Array.from(files);
        form.photos = fileList;

        // Generate previews
        filePreviews.value = [];
        fileList.forEach(file => {
            const reader = new FileReader();
            reader.onload = (e) => {
                if (e.target?.result) {
                    filePreviews.value.push(e.target.result as string);
                }
            };
            reader.readAsDataURL(file);
        });
    }
};

const submitForm = () => {
    // If it's edit and has files, we must use POST with _method=PUT because PHP doesn't parse multipart/form-data on PUT requests natively.
    if (props.isEdit) {
        // Send as POST request with _method=PUT to bypass PHP file upload limitations in PUT/PATCH requests
        form.transform((data) => ({
            ...data,
            _method: 'PUT'
        })).post(route('admin.courts.update', props.court!.id), {
            onSuccess: () => emit('submit'),
            forceFormData: true // Important for file uploads
        });
    } else {
        form.post(route('admin.courts.store'), {
            onSuccess: () => emit('submit'),
            forceFormData: true
        });
    }
};
</script>

<template>
    <form @submit.prevent="submitForm" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Side Inputs -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] space-y-4">
                <h3 class="font-display text-lg font-bold uppercase border-b border-verge-text-primary/10 pb-2 mb-4">Informasi Lapangan</h3>

                <!-- Name -->
                <div class="space-y-1">
                    <label for="name" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Nama Lapangan</label>
                    <input id="name" v-model="form.name" type="text" required class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet" placeholder="Contoh: Lapangan A (Vinil Premium)" />
                    <span v-if="form.errors.name" class="text-[10px] font-mono text-red-600 block">{{ form.errors.name }}</span>
                </div>

                <!-- Type & Status -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="type" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Tipe Lapangan</label>
                        <select id="type" v-model="form.type" required class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet">
                            <option value="indoor">Indoor</option>
                            <option value="outdoor">Outdoor</option>
                        </select>
                        <span v-if="form.errors.type" class="text-[10px] font-mono text-red-600 block">{{ form.errors.type }}</span>
                    </div>

                    <div class="space-y-1">
                        <label for="status" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Status</label>
                        <select id="status" v-model="form.status" required class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                            <option value="maintenance">Pemeliharaan</option>
                        </select>
                        <span v-if="form.errors.status" class="text-[10px] font-mono text-red-600 block">{{ form.errors.status }}</span>
                    </div>
                </div>

                <!-- Price & Duration -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="price" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Harga per Slot (Rp)</label>
                        <input id="price" v-model="form.price" type="number" required min="0" step="5000" class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet" />
                        <span v-if="form.errors.price" class="text-[10px] font-mono text-red-600 block">{{ form.errors.price }}</span>
                    </div>

                    <div class="space-y-1">
                        <label for="slot_duration" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Durasi per Slot (Menit)</label>
                        <select id="slot_duration" v-model="form.slot_duration" required class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet">
                            <option :value="30">30 Menit</option>
                            <option :value="60">60 Menit</option>
                            <option :value="90">90 Menit</option>
                            <option :value="120">120 Menit</option>
                        </select>
                        <span v-if="form.errors.slot_duration" class="text-[10px] font-mono text-red-600 block">{{ form.errors.slot_duration }}</span>
                    </div>
                </div>

                <!-- Open & Close Time -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="open_time" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Jam Buka</label>
                        <input id="open_time" v-model="form.open_time" type="time" required class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-mono focus:outline-none focus:border-verge-ultraviolet" />
                        <span v-if="form.errors.open_time" class="text-[10px] font-mono text-red-600 block">{{ form.errors.open_time }}</span>
                    </div>

                    <div class="space-y-1">
                        <label for="close_time" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Jam Tutup</label>
                        <input id="close_time" v-model="form.close_time" type="time" required class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-mono focus:outline-none focus:border-verge-ultraviolet" />
                        <span v-if="form.errors.close_time" class="text-[10px] font-mono text-red-600 block">{{ form.errors.close_time }}</span>
                    </div>
                </div>
            </div>

            <!-- Right Side Uploads -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between">
                <div class="space-y-4">
                    <h3 class="font-display text-lg font-bold uppercase border-b border-verge-text-primary/10 pb-2 mb-4">Galeri Foto</h3>

                    <div class="space-y-1">
                        <label class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Unggah Foto Baru (Multi-upload)</label>
                        <div class="border-2 border-dashed border-verge-text-primary/30 rounded-lg p-6 flex flex-col items-center justify-center bg-verge-surface-light text-center cursor-pointer hover:bg-verge-text-primary/5 transition-colors" @click="fileInputRef?.click()">
                            <span class="font-mono text-[10px] font-bold text-verge-ultraviolet uppercase">PILIH FILE FOTO</span>
                            <span class="text-[9px] text-verge-text-muted mt-1">Format: JPG, PNG, WEBP (Max 2MB per file)</span>
                            <input ref="fileInputRef" type="file" multiple accept="image/*" class="hidden" @change="handleFileChange" />
                        </div>
                        <span v-if="form.errors.photos" class="text-[10px] font-mono text-red-600 block">{{ form.errors.photos }}</span>
                    </div>

                    <!-- File Previews Grid -->
                    <div v-if="filePreviews.length > 0" class="grid grid-cols-3 gap-3 mt-4">
                        <div v-for="(preview, idx) in filePreviews" :key="idx" class="border border-verge-text-primary/20 aspect-square rounded-sm overflow-hidden relative">
                            <img :src="preview" class="w-full h-full object-cover" />
                            <div class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 flex items-center justify-center transition-opacity">
                                <span class="text-[8px] font-mono text-verge-canvas-white uppercase font-bold">New Photo {{ idx + 1 }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-6 border-t border-verge-text-primary/10 mt-6 flex justify-end">
                    <button type="submit" :disabled="form.processing" class="w-full md:w-auto flex items-center justify-center gap-2 px-6 py-3 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted font-mono text-xs uppercase font-bold rounded-sm shadow-[4px_4px_0px_0px_rgba(19,19,19,0.2)] disabled:opacity-50 transition-all">
                        <component :is="form.processing ? Loader : Save" class="w-4 h-4" :class="{ 'animate-spin': form.processing }" />
                        <span>{{ isEdit ? 'Simpan Perubahan' : 'Tambah Lapangan' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</template>
