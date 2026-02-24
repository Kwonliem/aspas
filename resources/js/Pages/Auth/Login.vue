<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

// 1. Tangkap props 'status' dari Controller (PENTING untuk pesan verifikasi)
defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Log in" />

    <div
        class="h-screen w-full bg-[#f8f8f5] dark:bg-[#23200f] font-display text-gray-800 antialiased selection:bg-[#ffde24] selection:text-black overflow-hidden flex items-center justify-center">

        <div class="w-full h-full grid lg:grid-cols-2">

            <div class="relative hidden lg:block h-full w-full overflow-hidden bg-gray-900">
                <div class="absolute inset-0 bg-black/40 z-10"></div>
                <img alt="Professional learning environment" class="absolute inset-0 w-full h-full object-cover"
                    src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=1471&auto=format&fit=crop" />

                <div class="absolute bottom-16 left-16 z-20 max-w-lg text-white">
                    <Link href="/" class="mb-8 block group cursor-pointer w-max">
                        <img src="/images/icon/aspas-logo.svg" alt="Aspas Logo"
                            class="h-14 w-auto object-contain group-hover:scale-105 transition-transform duration-300">
                    </Link>

                    <h2 class="text-4xl lg:text-5xl font-bold mb-5 leading-tight tracking-tight">Master your craft with
                        industry experts.</h2>
                    <p class="text-lg text-gray-200 font-medium leading-relaxed">Join a community of lifelong learners
                        and take your professional skills to the next level.</p>
                </div>
            </div>

            <div
                class="flex flex-col justify-center items-center p-8 sm:p-12 lg:p-24 bg-white dark:bg-[#2c2918] overflow-y-auto w-full h-full">
                <div class="w-full max-w-md space-y-8">

                    <div class="lg:hidden flex justify-center mb-8">
                        <Link href="/" class="flex items-center gap-3 group">
                            <div
                                class="w-12 h-12 bg-[#ffde24] rounded-xl flex items-center justify-center text-black shadow-lg shadow-yellow-400/20 group-hover:rotate-6 transition-transform">
                                <span class="material-symbols-outlined text-2xl font-bold">school</span>
                            </div>
                            <span class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Aspas.</span>
                        </Link>
                    </div>

                    <div class="text-center lg:text-left">
                        <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Login to Aspas</h1>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 font-medium">Welcome back! Please enter
                            your details.</p>
                    </div>

                    <div v-if="status"
                        class="font-medium text-sm text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 p-4 rounded-xl border border-green-100 dark:border-green-800/50">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="mt-8 space-y-6">
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300"
                                    for="email">Email</label>
                                <div class="mt-2">
                                    <input v-model="form.email" autocomplete="email"
                                        class="appearance-none block w-full px-4 py-3.5 border border-gray-300 dark:border-white/10 dark:bg-white/5 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-[#ffde24]/20 focus:border-[#ffde24] sm:text-[15px] transition-all text-gray-900 dark:text-white font-medium"
                                        id="email" type="email" placeholder="student@school.com" required autofocus />
                                    <div v-if="form.errors.email" class="text-red-500 text-xs mt-1.5 font-bold">{{
                                        form.errors.email }}</div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300"
                                    for="password">Password</label>
                                <div class="mt-2">
                                    <input v-model="form.password" autocomplete="current-password"
                                        class="appearance-none block w-full px-4 py-3.5 border border-gray-300 dark:border-white/10 dark:bg-white/5 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-[#ffde24]/20 focus:border-[#ffde24] sm:text-[15px] transition-all text-gray-900 dark:text-white font-medium tracking-widest"
                                        id="password" type="password" placeholder="••••••••" required />
                                    <div v-if="form.errors.password" class="text-red-500 text-xs mt-1.5 font-bold">{{
                                        form.errors.password }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input v-model="form.remember"
                                    class="h-4 w-4 text-[#eacb1e] focus:ring-[#ffde24] border-gray-300 rounded bg-white dark:bg-white/5 dark:border-white/10 cursor-pointer"
                                    id="remember-me" type="checkbox" />
                                <label
                                    class="ml-2 block text-sm text-gray-600 dark:text-gray-400 font-medium cursor-pointer"
                                    for="remember-me">Remember me</label>
                            </div>
                            <div class="text-sm">
                                <Link v-if="canResetPassword" :href="route('password.request')"
                                    class="font-bold text-gray-900 dark:text-white hover:text-[#e0c218] dark:hover:text-[#ffde24] transition-colors">
                                    Forgot Password?
                                </Link>
                            </div>
                        </div>

                        <div>
                            <button :disabled="form.processing"
                                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                                class="w-full flex justify-center items-center gap-2 py-4 px-4 border border-transparent rounded-xl shadow-sm text-sm font-black text-black bg-[#ffde24] hover:bg-[#eacb1e] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ffde24] transition-all transform hover:-translate-y-0.5 uppercase tracking-wide"
                                type="submit">
                                <span v-if="form.processing"
                                    class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                                Log in
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                            Don't have an account?
                            <Link :href="route('register')"
                                class="font-bold text-gray-900 dark:text-white hover:text-[#e0c218] dark:hover:text-[#ffde24] transition-colors ml-1">
                                Register
                            </Link>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200');

.font-display {
    font-family: 'Lexend', sans-serif;
}
</style>