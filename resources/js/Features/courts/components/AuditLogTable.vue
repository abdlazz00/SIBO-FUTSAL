<script setup lang="ts">
import { ClipboardList } from 'lucide-vue-next';

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

const props = defineProps<{
    logs: AuditLog[];
}>();

const formatDateTime = (dateStr: string) => {
    return new Date(dateStr).toLocaleString('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short'
    });
};

const getActionClass = (action: string) => {
    switch (action) {
        case 'create':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'update':
            return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'delete':
            return 'bg-red-100 text-red-800 border-red-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const formatValue = (field: string | null, val: string | null) => {
    if (val === null) return '-';
    if (field === 'price') {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(parseFloat(val));
    }
    return val;
};
</script>

<template>
    <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
        <h3 class="font-display text-lg font-bold uppercase border-b border-verge-text-primary/10 pb-2 mb-4">Riwayat Perubahan (Audit Log)</h3>

        <div v-if="logs.length === 0" class="flex flex-col items-center justify-center p-8 border border-dashed border-verge-text-primary/20 rounded bg-verge-surface-light text-verge-text-primary/30 font-mono text-[10px]">
            <ClipboardList class="w-8 h-8 mb-2" />
            <span>Belum ada log aktivitas untuk lapangan ini</span>
        </div>

        <div v-else class="overflow-x-auto">
            <table class="w-full text-left font-mono text-[11px] border-collapse">
                <thead>
                    <tr class="border-b-2 border-verge-text-primary bg-verge-surface-light text-verge-text-muted uppercase text-[9px]">
                        <th class="p-3">Waktu</th>
                        <th class="p-3">Staf</th>
                        <th class="p-3">Aksi</th>
                        <th class="p-3">Kolom</th>
                        <th class="p-3">Nilai Lama</th>
                        <th class="p-3">Nilai Baru</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="log in logs" :key="log.id" class="border-b border-verge-text-primary/10 hover:bg-verge-surface-light transition-colors">
                        <td class="p-3 whitespace-nowrap text-verge-text-muted">{{ formatDateTime(log.created_at) }}</td>
                        <td class="p-3 whitespace-nowrap font-bold text-verge-text-primary">{{ log.user.name }}</td>
                        <td class="p-3 whitespace-nowrap">
                            <span class="text-[9px] font-mono uppercase tracking-wider px-2 py-0.5 border rounded-sm font-bold" :class="getActionClass(log.action)">
                                {{ log.action }}
                            </span>
                        </td>
                        <td class="p-3 font-bold text-verge-ultraviolet">{{ log.field_name || '-' }}</td>
                        <td class="p-3 max-w-xs truncate" :title="log.old_value || ''">{{ formatValue(log.field_name, log.old_value) }}</td>
                        <td class="p-3 max-w-xs truncate" :title="log.new_value || ''">{{ formatValue(log.field_name, log.new_value) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
