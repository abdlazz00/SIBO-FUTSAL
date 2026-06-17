<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';
import { Search, X, Shield, Phone, Mail, Calendar, Eye } from 'lucide-vue-next';

interface Staff {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    is_active: boolean;
    created_at: string;
}

const props = defineProps<{
    staff: Staff[];
    filters: {
        search?: string;
        status?: string;
    };
}>();

// Filter inputs
const searchInput = ref(props.filters.search ?? '');
const statusInput = ref(props.filters.status ?? '');

const applyFilters = () => {
    router.get(route('owner.staff.index'), {
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
    router.get(route('owner.staff.index'), {}, {
        preserveState: true,
        replace: true
    });
};

// Date helper
const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Manajemen Staf Admin" />

    <OwnerLayout>
        <div class="space-y-6">
            <!-- Header Card -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-verge-ultraviolet">Staff Directories</span>
                    <h1 class="text-3xl font-display font-bold uppercase mt-1">Daftar Staf Administrasi</h1>
                    <p class="text-xs text-verge-text-muted mt-1">Daftar staf admin yang aktif mengelola operasional lapangan dan menyetujui transaksi harian di sistem.</p>
                </div>
                <div>
                    <span class="bg-verge-surface-light text-verge-text-muted px-4 py-2 border border-verge-text-primary/30 font-mono text-xs uppercase tracking-wider font-bold rounded-sm flex items-center gap-1.5">
                        <Eye class="w-3.5 h-3.5" />
                        <span>Read Only Directory</span>
                    </span>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-4 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-verge-text-muted">
                        <Search class="w-4 h-4" />
                    </div>
                    <input v-model="searchInput" type="text" @keyup.enter="applyFilters" placeholder="Cari nama atau email staf..." class="w-full border-2 border-verge-text-primary pl-9 pr-4 py-2 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet" />
                </div>

                <!-- Dropdown & Actions -->
                <div class="flex items-center gap-3">
                    <select v-model="statusInput" @change="applyFilters" class="border-2 border-verge-text-primary px-3 py-2 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white min-w-36">
                        <option value="">Semua Status</option>
                        <option value="active">Staf Aktif</option>
                        <option value="inactive">Staf Nonaktif</option>
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

            <!-- Staff Grid Cards -->
            <div v-if="staff.length === 0" class="bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] p-12 text-center flex flex-col items-center justify-center">
                <Shield class="w-16 h-16 text-verge-text-primary/20" />
                <h3 class="font-display text-xl font-bold uppercase mt-4">Tidak Ada Staf</h3>
                <p class="text-xs text-verge-text-muted mt-1 font-mono">Tidak ditemukan staf admin yang cocok dengan kriteria pencarian.</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="member in staff" 
                    :key="member.id"
                    class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-48"
                >
                    <div>
                        <!-- Header / Name / Badge -->
                        <div class="flex items-start justify-between border-b border-verge-text-primary/10 pb-2 mb-3">
                            <div class="min-w-0">
                                <h3 class="font-display text-md font-bold uppercase truncate" :title="member.name">
                                    {{ member.name }}
                                </h3>
                                <span class="bg-verge-canvas-black text-verge-canvas-white text-[8px] font-mono font-bold uppercase px-1 rounded-sm">
                                    ADMINISTRATOR
                                </span>
                            </div>
                            <span 
                                class="font-mono text-[9px] uppercase font-bold px-2 py-0.5 border rounded-sm",
                                :class="[member.is_active ? 'text-green-600 bg-green-50 border-green-200' : 'text-red-600 bg-red-50 border-red-200']"
                            >
                                {{ member.is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <!-- Details -->
                        <div class="space-y-1.5 font-mono text-[10px] text-verge-text-muted">
                            <div class="flex items-center gap-2">
                                <Mail class="w-3.5 h-3.5 text-verge-ultraviolet flex-shrink-0" />
                                <span class="truncate">{{ member.email }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <Phone class="w-3.5 h-3.5 text-verge-ultraviolet flex-shrink-0" />
                                <span>{{ member.phone ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer / Joined Date -->
                    <div class="pt-2 border-t border-verge-text-primary/10 flex items-center justify-between font-mono text-[9px] text-verge-text-muted">
                        <div class="flex items-center gap-1">
                            <Calendar class="w-3 h-3 text-verge-text-muted/65" />
                            <span>Bergabung:</span>
                        </div>
                        <span class="font-bold">{{ formatDate(member.created_at) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </OwnerLayout>
</template>
