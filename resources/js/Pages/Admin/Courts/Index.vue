<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CourtCard from '@/Features/courts/components/CourtCard.vue';
import { Plus, Search, Dumbbell, X } from 'lucide-vue-next';

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
    courts: Court[];
    filters: {
        status?: string;
        search?: string;
    };
}>();

const searchInput = ref(props.filters.search ?? '');
const statusInput = ref(props.filters.status ?? '');

const applyFilters = () => {
    router.get(route('admin.courts.index'), {
        search: searchInput.value,
        status: statusInput.value
    }, {
        preserveState: true,
        replace: true
    });
};

const clearFilters = () => {
    searchInput.value = '';
    statusInput.value = '';
    router.get(route('admin.courts.index'), {}, {
        preserveState: true,
        replace: true
    });
};

watch([statusInput], () => {
    applyFilters();
});
</script>

<template>
    <Head title="Manajemen Lapangan" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header Card -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-verge-ultraviolet">Pengaturan Lapangan</span>
                    <h1 class="text-3xl font-display font-bold uppercase mt-1">Daftar Lapangan</h1>
                    <p class="text-xs text-verge-text-muted mt-1">Kelola data lapangan futsal, tipe, status, jadwal buka-tutup, dan galeri foto lapangan.</p>
                </div>
                <div>
                    <Link :href="route('admin.courts.create')" class="flex items-center justify-center gap-2 px-5 py-3 bg-verge-ultraviolet text-verge-canvas-white hover:bg-verge-deep-link-blue border-2 border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] transition-all">
                        <Plus class="w-4 h-4" />
                        <span>Tambah Lapangan</span>
                    </Link>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-4 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-verge-text-muted">
                        <Search class="w-4 h-4" />
                    </div>
                    <input v-model="searchInput" type="text" @keyup.enter="applyFilters" placeholder="Cari nama lapangan..." class="w-full border-2 border-verge-text-primary pl-9 pr-4 py-2 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet" />
                </div>

                <!-- Dropdown & Actions -->
                <div class="flex items-center gap-3">
                    <select v-model="statusInput" class="border-2 border-verge-text-primary px-3 py-2 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white min-w-36">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Nonaktif</option>
                        <option value="maintenance">Pemeliharaan</option>
                    </select>

                    <button @click="applyFilters" class="px-4 py-2 bg-verge-canvas-black text-verge-canvas-white border border-verge-text-primary hover:bg-verge-text-muted font-mono text-xs uppercase font-bold rounded-sm transition-colors">
                        Filter
                    </button>

                    <button v-if="searchInput || statusInput" @click="clearFilters" class="px-3 py-2 border border-verge-text-primary/20 hover:bg-verge-surface-light rounded-sm text-xs font-mono flex items-center gap-1 transition-colors">
                        <X class="w-3.5 h-3.5" />
                        <span>Reset</span>
                    </button>
                </div>
            </div>

            <!-- Bento Listing Grid -->
            <div v-if="courts.length === 0" class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] p-12 text-center flex flex-col items-center justify-center">
                <Dumbbell class="w-16 h-16 text-verge-text-primary/20" />
                <h3 class="font-display text-xl font-bold uppercase mt-4">Belum ada lapangan</h3>
                <p class="text-xs text-verge-text-muted mt-1 font-mono">Silakan buat lapangan futsal baru untuk memulai sistem sewa.</p>
                <Link :href="route('admin.courts.create')" class="mt-6 px-5 py-2.5 bg-verge-ultraviolet text-verge-canvas-white font-mono text-xs uppercase font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,1)]">
                    Tambah Lapangan Pertama &rarr;
                </Link>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="court in courts" :key="court.id">
                    <CourtCard :court="court" />
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
