<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    status: String,
});

const form = useForm({});
const page = usePage();

const user = computed(() => page.props.auth.user);

const countdown = ref(0);
let timerInterval = null;
let pollingInterval = null;

const startTimer = (seconds = 60) => {
    countdown.value = seconds;
    const expiryTime = Date.now() + (seconds * 1000);
    
    // Simpan ke localStorage dengan format spesifik untuk user ini
    localStorage.setItem(`verification_cooldown_${user.value.id}`, expiryTime);

    timerInterval = setInterval(() => {
        const now = Date.now();
        const distance = expiryTime - now;
        countdown.value = Math.ceil(distance / 1000);

        if (distance < 0) {
            clearInterval(timerInterval);
            countdown.value = 0;
            localStorage.removeItem(`verification_cooldown_${user.value.id}`);
        }
    }, 1000);
};

const startPolling = () => {
    pollingInterval = setInterval(() => {
        if (form.processing) return;

        router.reload({
            only: ['auth'],
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                // JIKA BERHASIL VERIFIKASI:
                if (user.value.email_verified_at) {
                    clearAllIntervals();
                    localStorage.removeItem(`verification_cooldown_${user.value.id}`);
                    router.visit(route('dashboard'));
                }
            }
        });
    }, 3000);
};

const clearAllIntervals = () => {
    if (timerInterval) clearInterval(timerInterval);
    if (pollingInterval) clearInterval(pollingInterval);
};

onMounted(() => {
    const savedExpiry = localStorage.getItem(`verification_cooldown_${user.value.id}`);
    
    if (savedExpiry) {
        const remaining = Math.ceil((parseInt(savedExpiry) - Date.now()) / 1000);
        if (remaining > 0) {
            startTimer(remaining);
        } else {
            localStorage.removeItem(`verification_cooldown_${user.value.id}`);
        }
    }

    startPolling();
});

onUnmounted(() => {
    clearAllIntervals();
});

const submit = () => {
    form.post(route('verification.send'), {
        preserveScroll: true,
        onSuccess: () => {
            startTimer(60);
        },
    });
};

const handleLogout = () => {
    localStorage.removeItem(`verification_cooldown_${user.value.id}`);
    clearAllIntervals();
    router.post(route('logout'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head title="Verify Email - Aspas" />

    <div class="bg-[#f8f8f5] dark:bg-[#23200f] font-display text-gray-800 antialiased selection:bg-[#ffde24] selection:text-black min-h-screen flex flex-col">
        
        

        <main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
            
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
                <div class="absolute -top-[10%] -right-[5%] w-96 h-96 bg-[#ffde24]/20 rounded-full blur-3xl opacity-60 mix-blend-multiply dark:mix-blend-lighten"></div>
                <div class="absolute bottom-[10%] -left-[5%] w-96 h-96 bg-blue-400/10 rounded-full blur-3xl opacity-60 mix-blend-multiply dark:mix-blend-lighten"></div>
            </div>
            
            <div class="max-w-md w-full space-y-8 relative z-10">
                <div class="bg-white dark:bg-[#2c2918] rounded-[2rem] shadow-xl border border-gray-100 dark:border-white/5 p-8 sm:p-12">
                    
                    <div class="text-center mb-8">
                        
                        <h2 class="text-3xl font-black text-gray-900 dark:text-white tracking-tight mb-4">Verify your email</h2>
                        
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                            Welcome, <span class="font-bold text-gray-900 dark:text-white">{{ user.name }}</span>!
                            Please click the button below to receive the secure link in your email inbox to activate your account.
                        </p>
                    </div>

                    <div v-if="verificationLinkSent && countdown > 0" class="mb-8 font-bold text-sm text-green-700 bg-green-50 p-4 rounded-xl border border-green-200 flex items-center justify-center gap-3">
                        <span class="material-symbols-outlined animate-spin">sync</span>
                        Link sent! Waiting for verification...
                    </div>

                    <div v-else class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 flex gap-3 items-start border border-blue-100 dark:border-blue-800/50 mb-8">
                        <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-[18px] mt-0.5">info</span>
                        <p class="text-xs text-blue-800 dark:text-blue-300 font-medium">
                            <strong class="font-bold block mb-1 text-red-600 dark:text-red-400">Important:</strong>
                            The verification link will automatically expire in 1 minute for your security.
                        </p>
                    </div>

                    <form @submit.prevent="submit">
                        <button :disabled="form.processing || countdown > 0"
                            class="w-full py-4 px-4 border border-transparent rounded-xl shadow-md text-sm font-black text-black transition-all transform uppercase tracking-wide flex items-center justify-center gap-2"
                            :class="[
                                countdown > 0 
                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed border-gray-200 dark:bg-white/5 dark:text-gray-500 dark:border-white/10' 
                                    : 'bg-[#ffde24] hover:bg-[#eacb1e] hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#ffde24] shadow-yellow-400/20'
                            ]">
                            
                            <span v-if="form.processing" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                            <span v-else-if="countdown > 0" class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px] animate-spin">hourglass_empty</span>
                                Checking Status ({{ countdown }}s)
                            </span>
                            <span v-else class="flex items-center gap-2">
                                {{ verificationLinkSent ? 'Resend Verification Email' : 'Send Verification Link' }}
                                
                            </span>
                            
                        </button>

                        
                    </form>
                    

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