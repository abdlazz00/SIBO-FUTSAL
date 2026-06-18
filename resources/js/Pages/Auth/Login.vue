<script setup lang="ts">
import { ref } from 'vue';
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import gsap from 'gsap';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};

// Animated navigation to register page
const goToRegister = (e: Event) => {
    e.preventDefault();
    const card = document.querySelector('.auth-form-card');
    if (card) {
        gsap.to(card, {
            x: -60,
            opacity: 0,
            scale: 0.96,
            duration: 0.3,
            ease: 'power2.in',
            onComplete: () => {
                router.visit(route('register'));
            }
        });
    } else {
        router.visit(route('register'));
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk Akun" />

        <div class="space-y-6">
            <div class="space-y-2 text-center border-b border-verge-text-primary/10 pb-4">
                <h2 class="font-display text-2xl font-black uppercase tracking-tight">Masuk Akun</h2>
                <p class="text-xs text-verge-text-muted">Masukkan email dan password terdaftar Anda.</p>
            </div>

            <div v-if="status" class="mb-4 text-xs font-mono font-bold text-green-600 bg-green-50 border border-green-200 p-3 rounded-sm">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-4 pt-2">
                <!-- Email -->
                <div class="space-y-1">
                    <label for="email" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted block">Alamat Email</label>
                    <input 
                        id="email" 
                        type="email" 
                        v-model="form.email" 
                        required 
                        autofocus 
                        autocomplete="username" 
                        class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet text-xs bg-verge-canvas-white transition-colors" 
                        placeholder="nama@email.com"
                    />
                    <InputError class="mt-1" :message="form.errors.email" />
                </div>

                <!-- Password -->
                <div class="space-y-1">
                    <div class="flex justify-between items-center">
                        <label for="password" class="font-mono text-[10px] uppercase font-bold text-verge-text-muted">Password</label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="font-mono text-[9px] text-verge-ultraviolet hover:underline uppercase font-bold"
                        >
                            Lupa Password?
                        </Link>
                    </div>
                    <input 
                        id="password" 
                        type="password" 
                        v-model="form.password" 
                        required 
                        autocomplete="current-password" 
                        class="w-full border-2 border-verge-text-primary p-2.5 rounded-sm font-sans focus:outline-none focus:border-verge-ultraviolet text-xs bg-verge-canvas-white transition-colors" 
                        placeholder="••••••••"
                    />
                    <InputError class="mt-1" :message="form.errors.password" />
                </div>

                <!-- Remember Me -->
                <div class="block">
                    <label class="flex items-center cursor-pointer select-none">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ms-2 font-mono text-[10px] uppercase font-bold text-verge-text-muted">Ingat Saya</span>
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="space-y-3 pt-2">
                    <button
                        type="submit"
                        class="w-full px-5 py-3 bg-verge-canvas-black text-verge-canvas-white hover:bg-verge-text-muted border-2 border-verge-text-primary font-mono text-xs uppercase font-bold rounded-sm shadow-[4px_4px_0px_0px_rgba(19,19,19,1)] hover:shadow-[2px_2px_0px_0px_rgba(19,19,19,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all flex items-center justify-center gap-2"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Memproses...</span>
                        <span v-else>Masuk ke Sistem</span>
                    </button>

                    <div class="text-center pt-2">
                        <span class="text-[10px] text-verge-text-muted font-sans">Belum memiliki akun? </span>
                        <a
                            href="#"
                            @click="goToRegister"
                            class="font-mono text-[10px] text-verge-ultraviolet hover:underline uppercase font-bold cursor-pointer"
                        >
                            Daftar Sekarang
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
