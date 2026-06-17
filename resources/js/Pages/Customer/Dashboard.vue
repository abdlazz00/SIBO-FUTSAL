<script setup lang="ts">
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Customer Dashboard" />

    <CustomerLayout>
        <div class="space-y-6">
            <!-- Header Bento Card -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-verge-ultraviolet">Panel Pelanggan</span>
                    <h1 class="text-3xl font-display font-bold uppercase mt-1">Dashboard Saya</h1>
                    <p class="text-xs text-verge-text-muted mt-1">Lihat histori pemesanan lapangan Anda dan edit profil di sini.</p>
                </div>
                <div>
                    <span class="bg-verge-jelly-mint text-verge-text-primary px-4 py-2 border-2 border-verge-text-primary font-mono text-xs uppercase tracking-wider font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] text-[10px]">
                        Customer Account
                    </span>
                </div>
            </div>

            <!-- Bento Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-32">
                    <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider">Total Booking Saya</span>
                    <span class="text-4xl font-display font-bold mt-2">0</span>
                    <span class="text-[9px] font-mono text-verge-text-muted mt-1">Semua pesanan terdaftar</span>
                </div>

                <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-32">
                    <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider">Status Sewa Aktif</span>
                    <span class="text-md font-mono mt-2 text-verge-text-muted">Tidak ada sewa aktif dalam waktu dekat.</span>
                    <span class="text-[9px] font-mono text-verge-ultraviolet mt-1 uppercase font-bold hover:underline cursor-pointer">Cari lapangan &rarr;</span>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
