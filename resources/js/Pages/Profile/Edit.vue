<script setup lang="ts">
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();

const page = usePage();
const userRole = computed(() => page.props.auth.user.role);

// Dynamically select layout component based on role
const currentLayout = computed(() => {
    if (userRole.value === 'owner') return OwnerLayout;
    if (userRole.value === 'admin') return AdminLayout;
    return CustomerLayout;
});
</script>

<template>
    <Head title="Pengaturan Profil" />

    <component :is="currentLayout">
        <div class="space-y-6 max-w-4xl">
            <!-- Header Card -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
                <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-verge-ultraviolet">User Settings</span>
                <h1 class="text-3xl font-display font-bold uppercase mt-1">Pengaturan Profil</h1>
                <p class="text-xs text-verge-text-muted mt-1">Ubah data identitas akun, ganti kata sandi, dan atur akun Vitka Futsal Anda.</p>
            </div>

            <!-- Profile Info Form Card -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <!-- Update Password Card -->
            <div class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <!-- Delete User Account Card -->
            <div v-if="userRole === 'customer'" class="bg-verge-canvas-white border-2 border-verge-text-primary p-6 rounded-lg shadow-[4px_4px_0px_0px_rgba(19,19,19,1)]">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </component>
</template>
