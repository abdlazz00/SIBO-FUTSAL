<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import gsap from 'gsap';
import { 
    Plus, Search, Calendar, DollarSign, Edit, Trash2, X, AlertTriangle, Lightbulb, Users, Tag
} from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
}

interface Expense {
    id: number;
    category: 'utilities' | 'maintenance' | 'salaries' | 'other';
    description: string | null;
    amount: number;
    expense_date: string;
    recorded_by: number;
    recorded_by_user?: User | null;
}

interface Summary {
    total_expense: number;
    by_category: {
        utilities: number;
        maintenance: number;
        salaries: number;
        other: number;
    };
}

const props = defineProps<{
    expenses: {
        data: Expense[];
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
        per_page: number;
        links: { url: string | null; label: string; active: boolean }[];
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    summary: Summary;
    filters: {
        search?: string;
        category?: string;
        start_date?: string;
        end_date?: string;
    };
}>();

// Filter states
const searchInput = ref(props.filters.search ?? '');
const categoryInput = ref(props.filters.category ?? '');
const startDateInput = ref(props.filters.start_date ?? '');
const endDateInput = ref(props.filters.end_date ?? '');

const applyFilters = () => {
    router.get(route('admin.expenses.index'), {
        search: searchInput.value,
        category: categoryInput.value,
        start_date: startDateInput.value,
        end_date: endDateInput.value
    }, {
        preserveState: true,
        replace: true
    });
};

const clearFilters = () => {
    searchInput.value = '';
    categoryInput.value = '';
    startDateInput.value = '';
    endDateInput.value = '';
    router.get(route('admin.expenses.index'), {}, {
        preserveState: true,
        replace: true
    });
};

// Add / Edit Modal States
const isModalOpen = ref(false);
const editingExpense = ref<Expense | null>(null);

const form = useForm({
    category: 'other',
    description: '',
    amount: 0,
    expense_date: new Date().toISOString().slice(0, 10)
});

const openCreateModal = () => {
    editingExpense.value = null;
    form.reset();
    form.category = 'other';
    form.description = '';
    form.amount = 0;
    form.expense_date = new Date().toISOString().slice(0, 10);
    isModalOpen.value = true;
};

const openEditModal = (expense: Expense) => {
    editingExpense.value = expense;
    form.category = expense.category;
    form.description = expense.description ?? '';
    form.amount = Number(expense.amount);
    form.expense_date = expense.expense_date;
    isModalOpen.value = true;
};

const closeModal = () => {
    editingExpense.value = null;
    form.reset();
    isModalOpen.value = false;
};

const submitForm = () => {
    if (editingExpense.value) {
        form.put(route('admin.expenses.update', editingExpense.value.id), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('admin.expenses.store'), {
            onSuccess: () => closeModal()
        });
    }
};

// Delete Expense
const deleteExpense = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus catatan pengeluaran ini?')) {
        router.delete(route('admin.expenses.destroy', id));
    }
};

// Helpers
const formatPrice = (price: number | string) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(Number(price));
};

const formatCategory = (cat: string) => {
    switch (cat) {
        case 'utilities': return 'Listrik/Air/Wifi';
        case 'maintenance': return 'Perbaikan & Servis';
        case 'salaries': return 'Gaji Karyawan';
        case 'other': return 'Lain-lain';
        default: return cat;
    }
};

const formatDate = (dateStr: string) => {
    return new Date(dateStr).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

// ─── ANIMATION ────────────────────────────────────────────────────────────────

const animatedTotal = ref(0);
const animatedUtilities = ref(0);
const animatedMaintenance = ref(0);
const animatedSalaries = ref(0);
const animatedOther = ref(0);

const animateCounter = (refVal: { value: number }, target: number, delay = 0) => {
    const obj = { val: 0 };
    gsap.to(obj, {
        val: target,
        duration: 1.4,
        delay,
        ease: 'power2.out',
        onUpdate: () => { refVal.value = Math.round(obj.val); }
    });
};

const formatCounter = (val: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(val);
};

onMounted(() => {
    // 1. Entrance stagger
    const sections = document.querySelectorAll('.anim-section');
    gsap.fromTo(sections,
        { y: 28, opacity: 0 },
        { y: 0, opacity: 1, duration: 0.5, stagger: 0.09, ease: 'power3.out', delay: 0.05 }
    );

    // 2. Counter animations for 5 stat cards
    animateCounter(animatedTotal, Number(props.summary.total_expense), 0.2);
    animateCounter(animatedUtilities, Number(props.summary.by_category.utilities), 0.33);
    animateCounter(animatedMaintenance, Number(props.summary.by_category.maintenance), 0.46);
    animateCounter(animatedSalaries, Number(props.summary.by_category.salaries), 0.59);
    animateCounter(animatedOther, Number(props.summary.by_category.other), 0.72);

    // 3. Hover lift effects
    const statCards = document.querySelectorAll('.stat-card-hover');
    statCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            gsap.to(card, { y: -4, boxShadow: '6px 10px 0px 0px rgba(19,19,19,1)', duration: 0.2, ease: 'power2.out' });
        });
        card.addEventListener('mouseleave', () => {
            gsap.to(card, { y: 0, boxShadow: '4px 4px 0px 0px rgba(19,19,19,1)', duration: 0.2, ease: 'power2.out' });
        });
    });
});
</script>

<template>
    <Head title="Manajemen Pengeluaran Operasional" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- Header Card -->
            <div class="anim-section bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-verge-ultraviolet">Operasional Lapangan</span>
                    <h1 class="text-3xl font-display font-bold uppercase mt-1">Pengeluaran Operasional</h1>
                    <p class="text-xs text-verge-text-muted mt-1">Catat biaya operasional rutin seperti tagihan utilitas, perawatan sarana, dan gaji staf lapangan.</p>
                </div>
                <div>
                    <button @click="openCreateModal" class="flex items-center justify-center gap-2 px-5 py-3 bg-verge-ultraviolet text-verge-canvas-white hover:bg-verge-deep-link-blue border-2 border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] transition-all">
                        <Plus class="w-4 h-4" />
                        <span>Catat Pengeluaran</span>
                    </button>
                </div>
            </div>

            <!-- Bento Category Summary Grid -->
            <div class="anim-section grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- Total -->
                <div class="stat-card-hover bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-32 lg:col-span-1 cursor-default">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider">Total Biaya (Filter)</span>
                        <DollarSign class="w-4 h-4 text-verge-ultraviolet" />
                    </div>
                    <span class="text-xl font-display font-bold mt-2 text-verge-ultraviolet">{{ formatCounter(animatedTotal) }}</span>
                    <span class="text-[9px] font-mono text-verge-text-muted mt-1">Jumlah seluruh pengeluaran</span>
                </div>

                <!-- Utilities -->
                <div class="stat-card-hover bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-32 cursor-default">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider">Listrik / Air</span>
                        <Lightbulb class="w-4 h-4 text-amber-500" />
                    </div>
                    <span class="text-lg font-display font-bold mt-2">{{ formatCounter(animatedUtilities) }}</span>
                    <span class="text-[9px] font-mono text-verge-text-muted mt-1">Utilitas rutin bulanan</span>
                </div>

                <!-- Maintenance -->
                <div class="stat-card-hover bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-32 cursor-default">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider">Perbaikan</span>
                        <Tag class="w-4 h-4 text-blue-500" />
                    </div>
                    <span class="text-lg font-display font-bold mt-2">{{ formatCounter(animatedMaintenance) }}</span>
                    <span class="text-[9px] font-mono text-verge-text-muted mt-1">Sarana prasarana</span>
                </div>

                <!-- Salaries -->
                <div class="stat-card-hover bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-32 cursor-default">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider">Gaji Staf</span>
                        <Users class="w-4 h-4 text-green-500" />
                    </div>
                    <span class="text-lg font-display font-bold mt-2">{{ formatCounter(animatedSalaries) }}</span>
                    <span class="text-[9px] font-mono text-verge-text-muted mt-1">Staf & Kebersihan</span>
                </div>

                <!-- Other -->
                <div class="stat-card-hover bg-verge-canvas-white border-2 border-verge-text-primary p-5 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col justify-between h-32 cursor-default">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono text-verge-text-muted uppercase tracking-wider">Lain-Lain</span>
                        <Tag class="w-4 h-4 text-purple-500" />
                    </div>
                    <span class="text-lg font-display font-bold mt-2">{{ formatCounter(animatedOther) }}</span>
                    <span class="text-[9px] font-mono text-verge-text-muted mt-1">Kebutuhan tak terduga</span>
                </div>
            </div>

            <!-- Filters -->
            <div class="anim-section bg-verge-canvas-white border-2 border-verge-text-primary p-4 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] flex flex-col md:flex-row md:items-center justify-between gap-4">
                <!-- Search input -->
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-verge-text-muted">
                        <Search class="w-4 h-4" />
                    </div>
                    <input v-model="searchInput" type="text" @keyup.enter="applyFilters" placeholder="Cari deskripsi pengeluaran..." class="w-full border-2 border-verge-text-primary pl-9 pr-4 py-2 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet" />
                </div>

                <!-- Filters & actions -->
                <div class="flex items-center gap-3">
                    <select v-model="categoryInput" @change="applyFilters" class="border-2 border-verge-text-primary px-3 py-2 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white">
                        <option value="">Semua Kategori</option>
                        <option value="utilities">Listrik/Air/Wifi</option>
                        <option value="maintenance">Perbaikan & Servis</option>
                        <option value="salaries">Gaji Karyawan</option>
                        <option value="other">Lain-lain</option>
                    </select>

                    <input v-model="startDateInput" type="date" @change="applyFilters" class="border-2 border-verge-text-primary px-3 py-1.5 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white" />
                    <input v-model="endDateInput" type="date" @change="applyFilters" class="border-2 border-verge-text-primary px-3 py-1.5 rounded-sm text-xs font-mono focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white" />

                    <button @click="applyFilters" class="px-4 py-2 bg-verge-canvas-black text-verge-canvas-white border border-verge-text-primary hover:bg-verge-text-muted font-mono text-xs uppercase font-bold rounded-sm transition-colors">
                        Filter
                    </button>

                    <button v-if="searchInput || categoryInput || startDateInput || endDateInput" @click="clearFilters" class="px-3 py-2 border border-verge-text-primary/20 hover:bg-verge-surface-light rounded-sm text-xs font-mono flex items-center gap-1 transition-colors">
                        <X class="w-3.5 h-3.5" />
                        <span>Reset</span>
                    </button>
                </div>
            </div>

            <!-- Expense List Table -->
            <div class="anim-section bg-verge-canvas-white border-2 border-verge-text-primary rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-verge-surface-light border-b-2 border-verge-text-primary font-mono text-[10px] uppercase font-bold text-verge-text-muted">
                                <th class="py-3 px-4">Tanggal Pengeluaran</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4">Deskripsi / Rincian</th>
                                <th class="py-3 px-4">Jumlah Biaya</th>
                                <th class="py-3 px-4">Dicatat Oleh</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y border-verge-text-primary/10 text-xs">
                            <tr v-if="expenses.data.length === 0">
                                <td colspan="6" class="py-8 px-4 text-center font-mono text-verge-text-muted">
                                    Belum ada data pengeluaran operasional yang dicatat.
                                </td>
                            </tr>
                            <tr v-for="expense in expenses.data" :key="expense.id" class="hover:bg-verge-surface-light/50 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold">
                                    {{ formatDate(expense.expense_date) }}
                                </td>
                                <td class="py-3.5 px-4 font-mono">
                                    <span class="text-[10px] font-bold uppercase bg-verge-surface-light border border-verge-text-primary/15 px-2 py-0.5 rounded-sm">
                                        {{ formatCategory(expense.category) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-sans text-verge-text-primary">
                                    {{ expense.description ?? 'Tanpa deskripsi' }}
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-verge-ultraviolet text-sm">
                                    {{ formatPrice(expense.amount) }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-[10px] text-verge-text-muted">
                                    {{ expense.recorded_by_user?.name ?? 'Admin' }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button 
                                            @click="openEditModal(expense)" 
                                            class="p-1.5 hover:bg-verge-surface-light border border-verge-text-primary/10 hover:border-verge-text-primary rounded-sm transition-colors text-verge-text-muted hover:text-verge-text-primary"
                                            title="Ubah Catatan"
                                        >
                                            <Edit class="w-3.5 h-3.5" />
                                        </button>
                                        <button 
                                            @click="deleteExpense(expense.id)" 
                                            class="p-1.5 hover:bg-red-50 border border-verge-text-primary/10 hover:border-red-200 rounded-sm transition-colors text-verge-text-muted hover:text-red-600"
                                            title="Hapus Catatan"
                                        >
                                            <Trash2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="px-4 pb-4">
                    <Pagination :paginator="expenses" />
                </div>
            </div>
        </div>

        <!-- MODAL: Add / Edit Expense -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-verge-canvas-black/50 backdrop-blur-xs">
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary max-w-md w-full rounded-lg shadow-[6px_6px_0px_0px_rgba(19,19,19,1)] overflow-hidden">
                <div class="bg-verge-surface-light border-b-2 border-verge-text-primary p-4 flex items-center justify-between">
                    <h3 class="font-display text-lg font-bold uppercase">
                        {{ editingExpense ? 'Ubah Catatan Pengeluaran' : 'Catat Pengeluaran Baru' }}
                    </h3>
                    <button @click="closeModal" class="p-1 border border-verge-text-primary/10 hover:bg-verge-canvas-black/5 rounded-sm">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-5 space-y-4">
                    <!-- Category Selection -->
                    <div class="space-y-1">
                        <label for="category" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Kategori Pengeluaran</label>
                        <select 
                            id="category" 
                            v-model="form.category" 
                            required 
                            class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white text-xs"
                        >
                            <option value="utilities">Listrik / Air / Wifi (Utilitas)</option>
                            <option value="maintenance">Perbaikan & Pemeliharaan Sarana</option>
                            <option value="salaries">Gaji Karyawan / Staf</option>
                            <option value="other">Lain-lain / Kebutuhan Operasional</option>
                        </select>
                        <span v-if="form.errors.category" class="text-[10px] font-mono text-red-600 block">{{ form.errors.category }}</span>
                    </div>

                    <!-- Amount Input -->
                    <div class="space-y-1">
                        <label for="amount" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Jumlah Pengeluaran (Rupiah)</label>
                        <input 
                            id="amount" 
                            type="number" 
                            v-model="form.amount" 
                            required 
                            min="1"
                            placeholder="Contoh: 150000"
                            class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet" 
                        />
                        <span v-if="form.errors.amount" class="text-[10px] font-mono text-red-600 block">{{ form.errors.amount }}</span>
                    </div>

                    <!-- Expense Date -->
                    <div class="space-y-1">
                        <label for="expense_date" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Tanggal Biaya Dikeluarkan</label>
                        <input 
                            id="expense_date" 
                            type="date" 
                            v-model="form.expense_date" 
                            required 
                            class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-mono focus:outline-none focus:border-verge-ultraviolet bg-verge-canvas-white text-xs" 
                        />
                        <span v-if="form.errors.expense_date" class="text-[10px] font-mono text-red-600 block">{{ form.errors.expense_date }}</span>
                    </div>

                    <!-- Description -->
                    <div class="space-y-1">
                        <label for="description" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Deskripsi & Rincian Pengeluaran</label>
                        <textarea 
                            id="description" 
                            v-model="form.description" 
                            rows="3"
                            placeholder="Contoh: Pembayaran tagihan PLN lapangan A bulan Juni 2026..."
                            class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet text-xs"
                        ></textarea>
                        <span v-if="form.errors.description" class="text-[10px] font-mono text-red-600 block">{{ form.errors.description }}</span>
                    </div>

                    <div class="pt-2 flex justify-end gap-3 font-mono text-xs uppercase font-bold">
                        <button type="button" @click="closeModal" class="px-4 py-2.5 border-2 border-verge-text-primary hover:bg-verge-surface-light rounded-sm">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted border border-verge-text-primary rounded-sm shadow-[2px_2px_0px_0px_rgba(19,19,19,0.2)] disabled:opacity-50">
                            <span>Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
