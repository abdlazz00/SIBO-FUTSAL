<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Calendar, Save, Trash2, ArrowUpRight } from 'lucide-vue-next';

interface PriceOverride {
    id: number;
    date: string;
    price: number;
    note: string;
}

const props = defineProps<{
    courtId: number;
    overrides: PriceOverride[];
}>();

const form = useForm({
    date: new Date().toISOString().slice(0, 10),
    price: 150000,
    note: ''
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(price);
};

const formatDate = (dateStr: string) => {
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const submitOverride = () => {
    form.post(route('admin.courts.price-override', props.courtId), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('note');
        }
    });
};
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add Override Form -->
        <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
            <h3 class="font-display text-lg font-bold uppercase border-b border-verge-text-primary/10 pb-2 mb-4">Set Override Harga khusus</h3>

            <form @submit.prevent="submitOverride" class="space-y-4">
                <div class="space-y-1">
                    <label for="override_date" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Pilih Tanggal</label>
                    <input id="override_date" v-model="form.date" type="date" required class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-mono focus:outline-none focus:border-verge-ultraviolet" />
                    <span v-if="form.errors.date" class="text-[10px] font-mono text-red-600 block">{{ form.errors.date }}</span>
                </div>

                <div class="space-y-1">
                    <label for="override_price" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Harga Khusus per Slot (Rp)</label>
                    <input id="override_price" v-model="form.price" type="number" required min="0" step="5000" class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet" />
                    <span v-if="form.errors.price" class="text-[10px] font-mono text-red-600 block">{{ form.errors.price }}</span>
                </div>

                <div class="space-y-1">
                    <label for="override_note" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Catatan / Keterangan</label>
                    <input id="override_note" v-model="form.note" type="text" class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet" placeholder="Contoh: Libur Nasional / Akhir Pekan" />
                    <span v-if="form.errors.note" class="text-[10px] font-mono text-red-600 block">{{ form.errors.note }}</span>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-verge-ultraviolet text-verge-canvas-white hover:bg-verge-deep-link-blue font-mono text-xs uppercase font-bold rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] transition-colors">
                    <Save class="w-4 h-4" />
                    <span>Terapkan Harga</span>
                </button>
            </form>
        </div>

        <!-- Existing Overrides List -->
        <div class="lg:col-span-2 bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between">
            <div>
                <h3 class="font-display text-lg font-bold uppercase border-b border-verge-text-primary/10 pb-2 mb-4">Daftar Harga Khusus Aktif</h3>

                <div v-if="overrides.length === 0" class="flex flex-col items-center justify-center p-8 border border-dashed border-verge-text-primary/20 rounded bg-verge-surface-light text-verge-text-primary/30 font-mono text-[10px]">
                    <Calendar class="w-8 h-8 mb-2" />
                    <span>Belum ada override harga yang diatur</span>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left font-mono text-[11px] border-collapse">
                        <thead>
                            <tr class="border-b-2 border-verge-text-primary bg-verge-surface-light text-verge-text-muted uppercase text-[9px]">
                                <th class="p-3">Tanggal</th>
                                <th class="p-3 text-right">Harga Baru</th>
                                <th class="p-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="override in overrides" :key="override.id" class="border-b border-verge-text-primary/10 hover:bg-verge-surface-light transition-colors">
                                <td class="p-3 font-bold">{{ formatDate(override.date) }}</td>
                                <td class="p-3 text-right text-verge-ultraviolet font-bold">{{ formatPrice(override.price) }}</td>
                                <td class="p-3 font-sans">{{ override.note || '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
