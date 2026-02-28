<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});


const showPassword = ref(false);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Reset Password - Aspas" />

    <div class="bg-[#f8f8f5] dark:bg-[#23200f] font-display text-gray-800 antialiased selection:bg-[#ffde24] selection:text-black min-h-screen flex flex-col">
        
       

        <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
            
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
                <div class="absolute -top-[10%] -right-[5%] w-96 h-96 bg-[#ffde24]/20 rounded-full blur-3xl opacity-60 mix-blend-multiply dark:mix-blend-lighten"></div>
                <div class="absolute bottom-[10%] -left-[5%] w-96 h-96 bg-blue-400/10 rounded-full blur-3xl opacity-60 mix-blend-multiply dark:mix-blend-lighten"></div>
            </div>
            
            <div class="max-w-md w-full space-y-8 relative z-10">
                <div class="bg-white dark:bg-[#2c2918] rounded-[2rem] shadow-xl border border-gray-100 dark:border-white/5 p-8 sm:p-10">
                    
                    <div class="text-center mb-10">
                        
                        <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight">Reset Password</h2>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 font-medium">
                            Create a strong password for your Aspas account.
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <input type="hidden" v-model="form.email">
                        
                        <div class="space-y-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2" for="password">New Password</label>
                                <div class="relative rounded-xl shadow-sm">
                                    
                                    <input 
                                        :type="showPassword ? 'text' : 'password'" 
                                        id="password" 
                                        v-model="form.password"
                                        required
                                        autofocus
                                        class="block w-full pl-4 pr-12 py-3.5 border border-gray-200 dark:border-white/10 rounded-xl focus:ring-4 focus:ring-[#ffde24]/20 focus:border-[#ffde24] bg-gray-50 dark:bg-black/20 text-gray-900 dark:text-white sm:text-[15px] placeholder-gray-400 transition-all font-medium tracking-widest" 
                                        placeholder="••••••••" 
                                    />
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center cursor-pointer" @click="showPassword = !showPassword">
                                        <span class="material-symbols-outlined text-gray-400 hover:text-gray-600 transition-colors text-[20px]">
                                            {{ showPassword ? 'visibility_off' : 'visibility' }}
                                        </span>
                                    </div>
                                </div>
                                <div v-if="form.errors.password" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.password }}</div>

                                <div v-if="form.password.length > 0" class="mt-3 flex gap-1 h-1.5">
                                    <div class="flex-1 rounded-full transition-colors" :class="form.password.length > 0 ? 'bg-red-500' : 'bg-gray-200 dark:bg-white/10'"></div>
                                    <div class="flex-1 rounded-full transition-colors" :class="form.password.length > 5 ? 'bg-yellow-500' : 'bg-gray-200 dark:bg-white/10'"></div>
                                    <div class="flex-1 rounded-full transition-colors" :class="form.password.length >= 8 ? 'bg-green-500' : 'bg-gray-200 dark:bg-white/10'"></div>
                                    <div class="flex-1 rounded-full transition-colors" :class="form.password.length > 10 && /[A-Z]/.test(form.password) ? 'bg-green-500' : 'bg-gray-200 dark:bg-white/10'"></div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2" for="password_confirmation">Confirm New Password</label>
                                <div class="relative rounded-xl shadow-sm">
                                    
                                    <input 
                                        :type="showPassword ? 'text' : 'password'"
                                        id="password_confirmation" 
                                        v-model="form.password_confirmation"
                                        required
                                        class="block w-full pl-4 pr-4 py-3.5 border border-gray-200 dark:border-white/10 rounded-xl focus:ring-4 focus:ring-[#ffde24]/20 focus:border-[#ffde24] bg-gray-50 dark:bg-black/20 text-gray-900 dark:text-white sm:text-[15px] placeholder-gray-400 transition-all font-medium tracking-widest" 
                                        placeholder="••••••••" 
                                    />
                                </div>
                                <div v-if="form.errors.password_confirmation" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.password_confirmation }}</div>
                            </div>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 flex gap-3 items-start border border-blue-100 dark:border-blue-800/50">
                            <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-[18px] mt-0.5">info</span>
                            <div class="text-xs text-blue-800 dark:text-blue-300 font-medium">
                                <p class="font-bold mb-1">Password requirements:</p>
                                <ul class="list-disc pl-4 space-y-0.5">
                                    <li>Minimum 8 characters long</li>
                                    <li>At least one uppercase letter</li>
                                    <li>At least one number or special character</li>
                                </ul>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                                class="w-full flex justify-center items-center gap-2 py-4 px-4 border border-transparent rounded-xl shadow-md text-sm font-black text-black bg-[#ffde24] hover:bg-[#eacb1e] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ffde24] transition-all transform hover:-translate-y-0.5 uppercase tracking-wide"
                            >
                                <span v-if="form.processing" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                                Update Password
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 text-center">
                        <Link :href="route('login')" class="inline-flex items-center text-sm font-bold text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors group">
                            
                            Back to Login
                        </Link>
                    </div>
                </div>

                <p class="text-center text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mt-8">
                    &copy; 2026 Aspas.
                </p>
            </div>
        </main>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200');

.font-display {
    font-family: 'Lexend', sans-serif;
}
</style>