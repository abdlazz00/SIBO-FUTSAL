<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CourtForm from '@/Features/courts/components/CourtForm.vue';
import { ArrowLeft } from 'lucide-vue-next';
</script>

<template>
    <Head title="Tambah Lapangan" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header Card -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-verge-ultraviolet">Pengaturan Lapangan</span>
                    <h1 class="text-3xl font-display font-bold uppercase mt-1">Tambah Lapangan Baru</h1>
                    <p class="text-xs text-verge-text-muted mt-1">Masukkan spesifikasi lapangan futsal baru untuk didaftarkan pada kalender booking.</p>
                </div>
                <div>
                    <Link :href="route('admin.courts.index')" class="flex items-center justify-center gap-1.5 px-4 py-2 border-2 border-verge-text-primary font-mono text-xs uppercase font-bold hover:bg-verge-surface-light rounded-sm transition-all shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] bg-verge-canvas-white">
                        <ArrowLeft class="w-4 h-4" />
                        <span>Kembali</span>
                    </Link>
                </div>
            </div>

            <!-- Court Form -->
            <CourtForm />
        </div>
    </AdminLayout>
</template>
