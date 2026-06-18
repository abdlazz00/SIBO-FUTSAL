<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import gsap from 'gsap';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};

// Animated navigation back to login page
const goToLogin = (e: Event) => {
    e.preventDefault();
    const card = document.querySelector('.auth-form-card');
    if (card) {
        gsap.to(card, {
            x: 60,
            opacity: 0,
            scale: 0.96,
            duration: 0.3,
            ease: 'power2.in',
            onComplete: () => {
                router.visit(route('login'));
            }
        });
    } else {
        router.visit(route('login'));
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar Akun" />

        <div class="space-y-6">
            <div class="space-y-2 text-center border-b border-verge-text-primary/10 pb-4">
                <h2 class="font-display text-2xl font-black uppercase tracking-tight">Daftar Akun</h2>
                <p class="text-xs text-verge-text-muted">Lengkapi formulir untuk membuat akun baru.</p>
            </div>

            <form @submit.prevent="submit" class="space-y-4 pt-2">
                <!-- Nama Lengkap -->
                <div class="space-y-1">
                    <label for="name" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted block">Nama Lengkap</label>
                    <input 
                        id="name" 
                        type="text" 
                        v-model="form.name" 
                        required 
                        autofocus 
                        autocomplete="name" 
                        class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet text-xs bg-verge-canvas-white transition-colors" 
                        placeholder="Contoh: Budi Santoso"
                    />
                    <InputError class="mt-1" :message="form.errors.name" />
                </div>

                <!-- Email -->
                <div class="space-y-1">
                    <label for="email" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted block">Alamat Email</label>
                    <input 
                        id="email" 
                        type="email" 
                        v-model="form.email" 
                        required 
                        autocomplete="username" 
                        class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet text-xs bg-verge-canvas-white transition-colors" 
                        placeholder="nama@email.com"
                    />
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>

                <!-- Password -->
                <div class="space-y-1">
                    <label for="password" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted block">Password</label>
                    <input 
                        id="password" 
                        type="password" 
                        v-model="form.password" 
                        required 
                        autocomplete="new-password" 
                        class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet text-xs bg-verge-canvas-white transition-colors" 
                        placeholder="Minimal 8 karakter"
                    />
                    <InputError class="mt-1" :message="form.errors.password" />
                </div>

                <!-- Confirm Password -->
                <div class="space-y-1">
                    <label for="password_confirmation" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted block">Konfirmasi Password</label>
                    <input 
                        id="password_confirmation" 
                        type="password" 
                        v-model="form.password_confirmation" 
                        required 
                        autocomplete="new-password" 
                        class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet text-xs bg-verge-canvas-white transition-colors" 
                        placeholder="Ulangi password"
                    />
                    <InputError class="mt-1" :message="form.errors.password_confirmation" />
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3 pt-2">
                    <button
                        type="submit"
                        class="w-full px-5 py-3 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted border-2 border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] hover:shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all flex items-center justify-center gap-2"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Memproses Pendaftaran...</span>
                        <span v-else>Daftar Akun Baru</span>
                    </button>

                    <div class="text-center pt-2">
                        <span class="text-[10px] text-verge-text-muted font-sans">Sudah memiliki akun? </span>
                        <a
                            href="#"
                            @click="goToLogin"
                            class="font-mono text-[10px] text-verge-ultraviolet hover:underline uppercase font-bold cursor-pointer"
                        >
                            Masuk Di Sini
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
