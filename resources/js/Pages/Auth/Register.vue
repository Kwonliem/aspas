<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    nis: '',
    class: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register Student" />

    <div class="h-screen w-full bg-[#f8f8f5] dark:bg-[#23200f] font-display text-gray-800 antialiased selection:bg-[#ffde24] selection:text-black overflow-hidden flex items-center justify-center">
        
        <div class="w-full h-full grid lg:grid-cols-2">
            
            <div class="relative hidden lg:block h-full w-full overflow-hidden bg-gray-900">
                <div class="absolute inset-0 bg-black/40 z-10"></div>
                <img alt="Professional learning environment" class="absolute inset-0 w-full h-full object-cover" src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=1471&auto=format&fit=crop"/>
                
                <div class="absolute bottom-16 left-16 z-20 max-w-lg text-white">
                    <Link href="/" class="mb-8 block group cursor-pointer w-max">
                        <img 
                            src="/images/icon/aspas-logo.svg" 
                            alt="Aspas Logo" 
                            class="h-14 w-auto object-contain group-hover:scale-105 transition-transform duration-300"
                        >
                    </Link>
                    <h2 class="text-4xl lg:text-5xl font-bold mb-5 leading-tight tracking-tight">Master your craft with industry experts.</h2>
                    <p class="text-lg text-gray-200 font-medium leading-relaxed">Join a community of lifelong learners and take your professional skills to the next level.</p>
                </div>
            </div>

            <div class="flex flex-col justify-center items-center p-8 sm:p-12 lg:p-16 xl:p-24 bg-white dark:bg-[#2c2918] overflow-y-auto w-full h-full custom-scrollbar">
                <div class="w-full max-w-md space-y-8 my-auto">
                    
                    <div class="lg:hidden flex justify-center mb-6">
                        <Link href="/" class="flex items-center gap-3 group">
                            <div class="w-12 h-12 bg-[#ffde24] rounded-xl flex items-center justify-center text-black shadow-lg shadow-yellow-400/20 group-hover:rotate-6 transition-transform">
                                <span class="material-symbols-outlined text-2xl font-bold">school</span>
                            </div>
                            <span class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Aspas.</span>
                        </Link>
                    </div>

                    <div class="text-center lg:text-left">
                        <h1 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Start Learning</h1>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 font-medium">Create your student account to get started.</p>
                    </div>

                    <form @submit.prevent="submit" class="mt-8 space-y-5">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Full Name</label>
                            <input 
                                v-model="form.name"
                                type="text" 
                                class="appearance-none block w-full px-4 py-3.5 border border-gray-300 dark:border-white/10 dark:bg-white/5 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-[#ffde24]/20 focus:border-[#ffde24] sm:text-[15px] transition-all text-gray-900 dark:text-white font-medium"
                                placeholder="John Doe"
                                required
                                autofocus
                            />
                            <div v-if="form.errors.name" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Email Address</label>
                            <input 
                                v-model="form.email"
                                type="email" 
                                class="appearance-none block w-full px-4 py-3.5 border border-gray-300 dark:border-white/10 dark:bg-white/5 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-[#ffde24]/20 focus:border-[#ffde24] sm:text-[15px] transition-all text-gray-900 dark:text-white font-medium"
                                placeholder="student@school.com"
                                required
                            />
                            <div v-if="form.errors.email" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.email }}</div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">NIS</label>
                                <input 
                                    v-model="form.nis"
                                    type="text" 
                                    class="appearance-none block w-full px-4 py-3.5 border border-gray-300 dark:border-white/10 dark:bg-white/5 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-[#ffde24]/20 focus:border-[#ffde24] sm:text-[15px] transition-all text-gray-900 dark:text-white font-medium"
                                    placeholder="12345"
                                    required
                                />
                                <div v-if="form.errors.nis" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.nis }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Class</label>
                                <input 
                                    v-model="form.class"
                                    type="text" 
                                    class="appearance-none block w-full px-4 py-3.5 border border-gray-300 dark:border-white/10 dark:bg-white/5 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-[#ffde24]/20 focus:border-[#ffde24] sm:text-[15px] transition-all text-gray-900 dark:text-white font-medium"
                                    placeholder="9B"
                                    required
                                />
                                <div v-if="form.errors.class" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.class }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Password</label>
                            <input 
                                v-model="form.password"
                                type="password" 
                                class="appearance-none block w-full px-4 py-3.5 border border-gray-300 dark:border-white/10 dark:bg-white/5 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-[#ffde24]/20 focus:border-[#ffde24] sm:text-[15px] transition-all text-gray-900 dark:text-white font-medium tracking-widest"
                                placeholder="••••••••"
                                required
                            />
                            <div v-if="form.errors.password" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.password }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">Confirm Password</label>
                            <input 
                                v-model="form.password_confirmation"
                                type="password" 
                                class="appearance-none block w-full px-4 py-3.5 border border-gray-300 dark:border-white/10 dark:bg-white/5 rounded-xl shadow-sm placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-[#ffde24]/20 focus:border-[#ffde24] sm:text-[15px] transition-all text-gray-900 dark:text-white font-medium tracking-widest"
                                placeholder="••••••••"
                                required
                            />
                            <div v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.password_confirmation }}</div>
                        </div>

                        <div class="pt-2">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                :class="{ 'opacity-50 cursor-not-allowed': form.processing }" 
                                class="w-full flex justify-center items-center gap-2 py-4 px-4 border border-transparent rounded-xl shadow-sm text-sm font-black text-black bg-[#ffde24] hover:bg-[#eacb1e] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ffde24] transition-all transform hover:-translate-y-0.5 uppercase tracking-wide"
                            >
                                <span v-if="form.processing" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                                Create Account
                            </button>
                        </div>

                        <div class="text-center mt-8 pb-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                                Already have an account? 
                                <Link :href="route('login')" class="font-bold text-gray-900 dark:text-white hover:text-[#e0c218] dark:hover:text-[#ffde24] transition-colors ml-1">
                                    Log In
                                </Link>
                            </p>
                        </div>
                    </form>

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

/* Custom scrollbar khusus untuk form register yang panjang */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(156, 163, 175, 0.3); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: rgba(156, 163, 175, 0.6); }
</style>