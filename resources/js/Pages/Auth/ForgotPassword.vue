<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Forgot Password - Aspas" />

    <div class="bg-[#f8f8f5] dark:bg-[#23200f] font-display text-gray-800 antialiased selection:bg-[#ffde24] selection:text-black flex flex-col min-h-screen">
        
        

        <main class="flex-grow flex items-center justify-center p-4">
            <div class="w-full max-w-md">
                <div class="bg-white dark:bg-[#2c2918] shadow-xl shadow-gray-200/50 dark:shadow-none rounded-3xl border border-gray-100 dark:border-white/5 overflow-hidden">
                    <div class="p-8 sm:p-10">
                        
                        <div class="text-center mb-8">
                            
                            <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-3 tracking-tight">Forgot Password?</h1>
                            <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed font-medium">
                                Enter your email address and we will send you a link to reset your password.
                            </p>
                        </div>

                        <div v-if="status" class="mb-6 font-medium text-sm text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 p-4 rounded-xl border border-green-100 dark:border-green-800 text-center">
                            {{ status }}
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2" for="email">
                                    Email Address
                                </label>
                                <div class="relative">
                                    
                                    <input 
                                        id="email" 
                                        v-model="form.email"
                                        type="email" 
                                        required
                                        autofocus
                                        class="block w-full pl-4 pr-4 py-3.5 border border-gray-200 dark:border-white/10 rounded-xl leading-5 bg-gray-50 dark:bg-black/20 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-[#ffde24]/20 focus:border-[#ffde24] sm:text-[15px] transition-all font-medium" 
                                        placeholder="you@school.com" 
                                    />
                                </div>
                                <div v-if="form.errors.email" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.email }}</div>
                            </div>

                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                                class="w-full flex justify-center items-center gap-2 py-4 px-4 border border-transparent rounded-xl shadow-md text-sm font-black text-black bg-[#ffde24] hover:bg-[#eacb1e] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ffde24] transition-all transform hover:-translate-y-0.5 uppercase tracking-wide"
                            >
                                <span v-if="form.processing" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                                Send Reset Link
                            </button>
                        </form>

                        <div class="mt-8 text-center">
                            <Link :href="route('login')" class="inline-flex items-center text-sm font-bold text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors group">
                                
                                Back to Login
                            </Link>
                        </div>

                    </div>
                    
                    
                </div>
            </div>
        </main>

        <footer class="py-8 text-center text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">
            <p>&copy; 2026 Aspas</p>
        </footer>

    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200');

.font-display {
    font-family: 'Lexend', sans-serif;
}
</style>