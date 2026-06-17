<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CourtForm from '@/Features/courts/components/CourtForm.vue';
import CourtPhotoGallery from '@/Features/courts/components/CourtPhotoGallery.vue';
import PriceOverrideForm from '@/Features/courts/components/PriceOverrideForm.vue';
import AuditLogTable from '@/Features/courts/components/AuditLogTable.vue';
import { ArrowLeft } from 'lucide-vue-next';

interface Photo {
    id: number;
    path: string;
}

interface PriceOverride {
    id: number;
    date: string;
    price: number;
    note: string;
}

interface User {
    name: string;
}

interface AuditLog {
    id: number;
    action: 'create' | 'update' | 'delete';
    field_name: string | null;
    old_value: string | null;
    new_value: string | null;
    created_at: string;
    user: User;
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
    photos: Photo[];
    price_overrides: PriceOverride[];
    audit_logs: AuditLog[];
}

const props = defineProps<{
    court: Court;
}>();
</script>

<template>
    <Head :title="'Edit ' + court.name" />

    <AdminLayout>
        <div class="space-y-8 pb-12">
            <!-- Header Card -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-verge-ultraviolet">Pengaturan Lapangan</span>
                    <h1 class="text-3xl font-display font-bold uppercase mt-1">Edit Lapangan</h1>
                    <p class="text-xs text-verge-text-muted mt-1">Ubah rincian lapangan {{ court.name }}, atur foto, harga override khusus, dan lihat audit log.</p>
                </div>
                <div>
                    <Link :href="route('admin.courts.index')" class="flex items-center justify-center gap-1.5 px-4 py-2 border-2 border-verge-text-primary font-mono text-xs uppercase font-bold hover:bg-verge-surface-light rounded-sm transition-all shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] bg-verge-canvas-white">
                        <ArrowLeft class="w-4 h-4" />
                        <span>Kembali</span>
                    </Link>
                </div>
            </div>

            <!-- 1. Edit Details & Gallery -->
            <div class="space-y-6">
                <CourtForm :court="court" :is-edit="true" />
                <CourtPhotoGallery :photos="court.photos" :court-id="court.id" />
            </div>

            <!-- 2. Price Overrides Section -->
            <div class="space-y-4">
                <h2 class="font-display text-2xl font-bold uppercase tracking-tight text-verge-text-primary">Kebijakan Harga Khusus</h2>
                <PriceOverrideForm :court-id="court.id" :overrides="court.price_overrides" />
            </div>

            <!-- 3. Audit Logs Section -->
            <div class="space-y-4">
                <h2 class="font-display text-2xl font-bold uppercase tracking-tight text-verge-text-primary">Integritas Data</h2>
                <AuditLogTable :logs="court.audit_logs" />
            </div>
        </div>
    </AdminLayout>
</template>
