<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Plus, Trash2, Edit2, Save, X, Star } from 'lucide-vue-next';

interface Testimonial {
    id: number;
    customer_name: string;
    avatar: string | null;
    rating: number;
    content: string;
    is_active: boolean;
    sort_order: number;
}

const props = defineProps<{
    testimonials: Testimonial[];
}>();

const isEditing = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    customer_name: '',
    rating: 5,
    content: '',
    is_active: true,
    sort_order: 0
});

const startEdit = (testimonial: Testimonial) => {
    isEditing.value = true;
    editingId.value = testimonial.id;
    form.customer_name = testimonial.customer_name;
    form.rating = testimonial.rating;
    form.content = testimonial.content;
    form.is_active = testimonial.is_active;
    form.sort_order = testimonial.sort_order;
};

const cancelEdit = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value && editingId.value) {
        form.put(route('admin.testimonials.update', editingId.value), {
            onSuccess: () => cancelEdit()
        });
    } else {
        form.post(route('admin.testimonials.store'), {
            onSuccess: () => form.reset()
        });
    }
};

const deleteTestimonial = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus testimoni ini?')) {
        form.delete(route('admin.testimonials.destroy', id));
    }
};
</script>

<template>
    <Head title="Manajemen Testimoni" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header Card -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-verge-ultraviolet">Pengaturan Ulasan</span>
                <h1 class="text-3xl font-display font-bold uppercase mt-1">Kelola Testimoni</h1>
                <p class="text-xs text-verge-text-muted mt-1">Atur ulasan/testimoni pelanggan yang akan ditampilkan di Landing Page utama.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Testimonial Form -->
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] h-fit">
                    <h3 class="font-display text-lg font-bold uppercase border-b border-verge-text-primary/10 pb-2 mb-4">
                        {{ isEditing ? 'Edit Testimoni' : 'Tambah Testimoni' }}
                    </h3>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <!-- Customer Name -->
                        <div class="space-y-1">
                            <label for="customer_name" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Nama Pelanggan</label>
                            <input id="customer_name" v-model="form.customer_name" type="text" required class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet" placeholder="Contoh: Budi Santoso" />
                            <span v-if="form.errors.customer_name" class="text-[10px] font-mono text-red-600 block">{{ form.errors.customer_name }}</span>
                        </div>

                        <!-- Rating & Sort Order -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label for="rating" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Rating (1-5)</label>
                                <select id="rating" v-model="form.rating" required class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-mono focus:outline-none focus:border-verge-ultraviolet">
                                    <option :value="5">⭐⭐⭐⭐⭐ (5)</option>
                                    <option :value="4">⭐⭐⭐⭐ (4)</option>
                                    <option :value="3">⭐⭐⭐ (3)</option>
                                    <option :value="2">⭐⭐ (2)</option>
                                    <option :value="1">⭐ (1)</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label for="sort_order" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Urutan</label>
                                <input id="sort_order" v-model="form.sort_order" type="number" required min="0" class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-mono focus:outline-none focus:border-verge-ultraviolet" />
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="space-y-1">
                            <label for="content" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Isi Testimoni</label>
                            <textarea id="content" v-model="form.content" required rows="4" class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet text-xs" placeholder="Tulis testimoni pelanggan di sini..."></textarea>
                            <span v-if="form.errors.content" class="text-[10px] font-mono text-red-600 block">{{ form.errors.content }}</span>
                        </div>

                        <!-- Active Toggle -->
                        <div class="flex items-center gap-2">
                            <input id="is_active" v-model="form.is_active" type="checkbox" class="w-4.5 h-4.5 border-2 border-verge-text-primary rounded-sm accent-verge-ultraviolet" />
                            <label for="is_active" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted cursor-pointer">Tampilkan di Landing Page</label>
                        </div>

                        <!-- Actions -->
                        <div class="pt-2 flex gap-2">
                            <button type="submit" :disabled="form.processing" class="flex-1 flex items-center justify-center gap-1.5 px-4 py-2.5 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted font-mono text-xs uppercase font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,0.2)] transition-colors">
                                <Save class="w-4 h-4" />
                                <span>{{ isEditing ? 'Update' : 'Simpan' }}</span>
                            </button>
                            <button v-if="isEditing" type="button" @click="cancelEdit" class="flex items-center justify-center p-2.5 border-2 border-verge-text-primary hover:bg-verge-surface-light rounded-sm transition-colors text-verge-text-primary">
                                <X class="w-4.5 h-4.5" />
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Testimonials List -->
                <div class="lg:col-span-2 bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
                    <h3 class="font-display text-lg font-bold uppercase border-b border-verge-text-primary/10 pb-2 mb-4">Daftar Testimoni</h3>

                    <div v-if="testimonials.length === 0" class="p-8 text-center border border-dashed border-verge-text-primary/20 rounded bg-verge-surface-light font-mono text-[10px] text-verge-text-primary/30">
                        Belum ada testimoni terdaftar.
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="testimonial in testimonials" :key="testimonial.id" class="border-2 border-verge-text-primary p-4 rounded bg-verge-surface-light/40 hover:bg-verge-surface-light transition-colors relative flex flex-col justify-between gap-4">
                            <div>
                                <div class="flex items-center justify-between">
                                    <span class="font-sans font-bold text-sm text-verge-text-primary">{{ testimonial.customer_name }}</span>
                                    <span class="text-[9px] font-mono uppercase tracking-wider px-2 py-0.5 border rounded-sm font-bold" :class="testimonial.is_active ? 'bg-verge-jelly-mint text-verge-text-primary border-verge-text-primary/10' : 'bg-zinc-200 text-zinc-600 border-zinc-300'">
                                        {{ testimonial.is_active ? 'Aktif' : 'Draft' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1 mt-1 text-amber-500">
                                    <Star v-for="i in 5" :key="i" class="w-3.5 h-3.5" :class="i <= testimonial.rating ? 'fill-amber-500' : 'text-zinc-300'" />
                                    <span class="text-[9px] font-mono text-verge-text-muted ml-2">Urutan: {{ testimonial.sort_order }}</span>
                                </div>
                                <p class="text-xs text-verge-text-muted mt-2 font-sans italic">"{{ testimonial.content }}"</p>
                            </div>

                            <div class="flex justify-end gap-2 border-t border-verge-text-primary/10 pt-3">
                                <button @click="startEdit(testimonial)" class="flex items-center gap-1 px-3 py-1 border border-verge-text-primary hover:bg-verge-canvas-white font-mono text-[10px] uppercase font-bold rounded-sm transition-colors text-verge-text-primary">
                                    <Edit2 class="w-3 h-3" />
                                    <span>Edit</span>
                                </button>
                                <button @click="deleteTestimonial(testimonial.id)" class="flex items-center gap-1 px-3 py-1 bg-red-600 hover:bg-red-700 text-white font-mono text-[10px] uppercase font-bold rounded-sm transition-colors">
                                    <Trash2 class="w-3 h-3" />
                                    <span>Hapus</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
