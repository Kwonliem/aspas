<script setup>
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const props = defineProps({
    challenges: Array
});

const form = useForm({
    link: ''
});

const activeChallenge = ref(null);
const showSubmitModal = ref(false);

// Pisahkan challenge yang masih aktif dan yang sudah lewat
const activeChallengesList = computed(() => props.challenges.filter(c => !c.is_expired));
const pastChallengesList = computed(() => props.challenges.filter(c => c.is_expired));

const openSubmitModal = (challenge) => {
    activeChallenge.value = challenge;
    form.link = challenge.submission_link || '';
    form.clearErrors();
    showSubmitModal.value = true;
};

const closeSubmitModal = () => {
    showSubmitModal.value = false;
    setTimeout(() => activeChallenge.value = null, 200);
};

const submitChallenge = () => {
    form.post(route('student.challenges.submit', activeChallenge.value.id), {
        preserveScroll: true,
        onSuccess: () => closeSubmitModal()
    });
};
</script>

<template>
    <Head title="Weekly Challenges - Competition" />

    <div class="min-h-screen bg-gray-50 font-display selection:bg-[#ffde24] selection:text-black pb-20">
        
        <nav class="fixed top-0 left-0 w-full z-40 bg-[#1e1e1e]/90 backdrop-blur-md border-b border-white/10 h-20 flex items-center justify-between px-6 lg:px-12 transition-all">
            <Link :href="route('dashboard')" class="flex items-center gap-3 text-gray-400 hover:text-white transition-colors group">
                <div class="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center group-hover:bg-[#ffde24] group-hover:text-black transition-colors border border-white/10 group-hover:border-transparent">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </div>
                <span class="font-bold text-sm hidden sm:block tracking-wide">Back to Dashboard</span>
            </Link>

            <div class="hidden lg:flex items-center gap-2 text-white font-black text-lg tracking-tight">
                <span class="material-symbols-outlined text-[#ffde24]">emoji_events</span>
                Aspas <span class="text-[#ffde24]">Challenges</span>
            </div>

            <div class="flex items-center gap-4">
                <div class="hidden md:flex items-center gap-4 bg-black/50 border border-white/10 rounded-full px-5 py-2 shadow-inner">
                    <div class="flex items-center gap-1.5 text-[#ffde24]">
                        <span class="material-symbols-outlined text-[18px]">bolt</span>
                        <span class="text-sm font-black">{{ user.xp }} XP</span>
                    </div>
                    <div class="w-px h-4 bg-white/20"></div>
                    <div class="flex items-center gap-1.5 text-blue-400">
                        <span class="material-symbols-outlined text-[18px]">monetization_on</span>
                        <span class="text-sm font-black">{{ user.credits || 0 }} Cr</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#ffde24] to-yellow-600 flex items-center justify-center text-black font-black uppercase shadow-lg shadow-yellow-500/20 border-2 border-[#1e1e1e] ring-2 ring-[#ffde24]/30">
                    {{ user.name.charAt(0) }}
                </div>
            </div>
        </nav>

        <div class="bg-[#1e1e1e] relative overflow-hidden pt-32 pb-24 border-b border-white/10 shadow-2xl">
            <div class="absolute inset-0 opacity-20"
                style="background-image: radial-gradient(#ffde24 1.5px, transparent 1.5px); background-size: 32px 32px;">
            </div>
            <div class="absolute top-0 right-1/4 w-[500px] h-[500px] bg-[#ffde24] rounded-full mix-blend-multiply filter blur-[150px] opacity-10 animate-pulse"></div>
            <div class="absolute bottom-0 left-1/4 w-[400px] h-[400px] bg-blue-500 rounded-full mix-blend-multiply filter blur-[150px] opacity-10"></div>

            <div class="relative z-10 max-w-4xl mx-auto text-center px-4">
                <span class="inline-flex items-center gap-2 py-1.5 px-5 rounded-full bg-white/5 text-[#ffde24] border border-[#ffde24]/30 text-[10px] font-black uppercase tracking-[0.2em] mb-8 backdrop-blur-md shadow-[0_0_15px_rgba(255,222,36,0.15)]">
                    <span class="w-2 h-2 rounded-full bg-[#ffde24] animate-ping"></span>
                    Live Competition
                </span>
                <h1 class="text-5xl md:text-7xl font-black text-white mb-6 tracking-tight leading-[1.1]">
                    Code. Compete. <br class="hidden md:block"/> <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#ffde24] to-yellow-500">Conquer.</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-400 mb-0 leading-relaxed max-w-2xl mx-auto font-medium">
                    Buktikan kemampuanmu! Selesaikan tantangan mingguan dari instruktur, kumpulkan XP, raih Credits, dan dapatkan sertifikat eksklusif.
                </p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-20 relative z-10">
            
            <section>
                <div class="flex items-center gap-4 mb-10">
                    <div class="w-3 h-10 bg-[#ffde24] rounded-full shadow-[0_0_10px_rgba(255,222,36,0.5)]"></div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Active Challenges</h2>
                </div>

                <div v-if="activeChallengesList.length === 0" class="bg-white rounded-[2rem] border border-dashed border-gray-200 p-20 text-center shadow-sm">
                    <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="material-symbols-outlined text-5xl text-gray-300">hourglass_empty</span>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">Tidak ada tantangan aktif</h3>
                    <p class="text-gray-500 font-medium">Beristirahatlah sejenak. Instruktur belum merilis tantangan baru minggu ini.</p>
                </div>

                <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div v-for="challenge in activeChallengesList" :key="challenge.id" 
                        class="bg-white rounded-[2rem] border border-gray-100 shadow-sm flex flex-col overflow-hidden relative transition-all duration-300 transform hover:-translate-y-1 hover:shadow-xl group"
                        :class="[
                            challenge.status === 'passed' ? 'ring-2 ring-green-400 border-transparent' : 
                            challenge.status === 'pending' ? 'ring-2 ring-yellow-400 border-transparent' : ''
                        ]">
                        
                        <div class="p-8 border-b border-gray-100 flex items-start justify-between bg-white relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-full h-1" 
                                :class="[
                                    challenge.status === 'passed' ? 'bg-green-400' : 
                                    challenge.status === 'pending' ? 'bg-yellow-400' : 'bg-gray-200 group-hover:bg-[#ffde24] transition-colors'
                                ]"></div>
                            
                            <div class="pr-4 z-10">
                                <h3 class="text-2xl font-black text-gray-900 mb-3 leading-tight">{{ challenge.title }}</h3>
                                <div class="flex items-center gap-2 text-[11px] font-black uppercase tracking-widest text-red-600 bg-red-50 w-max px-3 py-1.5 rounded-lg border border-red-100">
                                    <span class="material-symbols-outlined text-[16px]">timer</span>
                                    Ends: {{ challenge.end_date }}
                                </div>
                            </div>
                            
                            <div class="px-4 py-2 rounded-xl text-[11px] font-black shadow-sm uppercase tracking-wider shrink-0 z-10"
                                :class="{
                                    'bg-green-500 text-white': challenge.status === 'passed',
                                    'bg-[#ffde24] text-black': challenge.status === 'pending',
                                    'bg-red-500 text-white': challenge.status === 'failed',
                                    'bg-gray-100 text-gray-500 border border-gray-200': challenge.status === 'not_started'
                                }">
                                {{ challenge.status.replace('_', ' ') }}
                            </div>
                        </div>

                        <div class="p-8 flex-1 flex flex-col bg-gray-50/50">
                            <p class="text-gray-600 leading-relaxed mb-8 whitespace-pre-line flex-1 text-[15px] font-medium">{{ challenge.description }}</p>
                            
                            <div class="flex flex-wrap gap-3 mb-8">
                                <div class="flex items-center gap-2 bg-white text-blue-700 px-4 py-2.5 rounded-xl text-sm font-bold border border-gray-200 shadow-sm">
                                    <span class="material-symbols-outlined text-[18px]">bolt</span>
                                    {{ challenge.xp_reward }} XP
                                </div>
                                <div class="flex items-center gap-2 bg-white text-yellow-700 px-4 py-2.5 rounded-xl text-sm font-bold border border-gray-200 shadow-sm">
                                    <span class="material-symbols-outlined text-[18px]">monetization_on</span>
                                    {{ challenge.credit_reward }} Credits
                                </div>
                                <div class="flex items-center gap-2 bg-white text-green-700 px-4 py-2.5 rounded-xl text-sm font-bold border border-gray-200 shadow-sm">
                                    <span class="material-symbols-outlined text-[18px]">workspace_premium</span>
                                    Certificate
                                </div>
                            </div>

                            <button v-if="challenge.status === 'not_started'" @click="openSubmitModal(challenge)"
                                class="w-full py-4 bg-[#1e1e1e] hover:bg-black text-white font-black text-sm rounded-xl transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2 transform active:scale-[0.98]">
                                Submit Karya Anda <span class="material-symbols-outlined">rocket_launch</span>
                            </button>

                            <button v-else-if="challenge.status === 'failed'" @click="openSubmitModal(challenge)"
                                class="w-full py-4 bg-red-600 hover:bg-red-700 text-white font-black text-sm rounded-xl transition-all shadow-lg flex items-center justify-center gap-2 transform active:scale-[0.98]">
                                <span class="material-symbols-outlined">replay</span> Coba Lagi (Revisi)
                            </button>

                            <a v-else-if="challenge.status === 'passed'" :href="route('student.challenges.certificate', challenge.id)" target="_blank"
                                class="w-full py-4 bg-green-500 hover:bg-green-600 text-white font-black text-sm rounded-xl transition-all shadow-lg shadow-green-500/30 flex items-center justify-center gap-2 transform hover:-translate-y-0.5">
                                <span class="material-symbols-outlined">download</span> Download Certificate
                            </a>

                            <div v-else-if="challenge.status === 'pending'" class="w-full py-4 bg-white text-yellow-600 font-black text-sm rounded-xl text-center border-2 border-yellow-200 flex items-center justify-center gap-2 shadow-sm">
                                <span class="material-symbols-outlined animate-spin">sync</span> Sedang Dinilai Instruktur
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section v-if="pastChallengesList.length > 0">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-3 h-10 bg-gray-300 rounded-full"></div>
                    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Past Challenges</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="challenge in pastChallengesList" :key="challenge.id" 
                        class="bg-white rounded-3xl border border-gray-200 p-8 flex flex-col relative opacity-75 hover:opacity-100 transition-opacity shadow-sm hover:shadow-md">
                        
                        <div class="absolute top-6 right-6">
                            <span v-if="challenge.status === 'passed'" class="material-symbols-outlined text-green-500 text-3xl">verified</span>
                            <span v-else class="material-symbols-outlined text-gray-300 text-3xl">cancel</span>
                        </div>

                        <h3 class="text-xl font-black text-gray-800 mb-2 pr-10 line-clamp-1">{{ challenge.title }}</h3>
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px]">event_busy</span>
                            Ended: {{ challenge.end_date }}
                        </p>
                        
                        <p class="text-sm text-gray-500 line-clamp-2 mb-8 font-medium leading-relaxed">{{ challenge.description }}</p>
                        
                        <a v-if="challenge.status === 'passed'" :href="route('student.challenges.certificate', challenge.id)" target="_blank"
                            class="mt-auto w-full py-3 bg-gray-50 border border-gray-200 hover:bg-green-50 hover:border-green-200 hover:text-green-700 text-gray-600 font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">workspace_premium</span> Lihat Sertifikat
                        </a>
                        <div v-else class="mt-auto w-full py-3 bg-gray-50 text-gray-400 font-bold text-xs rounded-xl text-center uppercase tracking-wider">
                            Missed Opportunity
                        </div>
                    </div>
                </div>
            </section>

        </div>

        <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showSubmitModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div class="fixed inset-0 bg-[#1e1e1e]/80 backdrop-blur-md" @click="closeSubmitModal"></div>
                    <div class="relative bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-xl w-full border border-gray-100">
                        <div class="bg-white px-8 py-6 flex justify-between items-center border-b border-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-yellow-50 text-yellow-600 rounded-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-[20px]">rocket_launch</span>
                                </div>
                                <h3 class="text-xl font-black text-gray-900">Kumpulkan Karya</h3>
                            </div>
                            <button @click="closeSubmitModal" class="w-8 h-8 rounded-full bg-gray-100 text-gray-500 hover:bg-red-100 hover:text-red-500 transition-colors flex items-center justify-center">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        <div class="p-8">
                            <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-5 mb-8 flex gap-4 text-blue-800">
                                <span class="material-symbols-outlined shrink-0 text-blue-500">info</span>
                                <p class="text-sm font-medium leading-relaxed">Pastikan link repositori atau website Anda bersifat <strong class="font-black">publik</strong> agar instruktur dapat mereview karya tersebut dengan lancar.</p>
                            </div>
                            
                            <label class="block text-sm font-black text-gray-900 mb-3">Project URL</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                    <span class="material-symbols-outlined text-[22px]">link</span>
                                </div>
                                <input v-model="form.link" type="url" required
                                    class="w-full rounded-2xl border-2 border-gray-200 focus:border-[#ffde24] focus:ring-4 focus:ring-[#ffde24]/20 shadow-sm pl-12 pr-4 py-4 text-[15px] font-medium transition-all outline-none"
                                    placeholder="https://github.com/username/project">
                            </div>
                            <div v-if="form.errors.link" class="text-red-500 text-xs mt-2 font-bold">{{ form.errors.link }}</div>
                        </div>
                        <div class="bg-gray-50 px-8 py-6 border-t border-gray-100 flex justify-end gap-3">
                            <button @click="closeSubmitModal" type="button" class="px-6 py-3.5 bg-white border border-gray-200 text-gray-600 font-bold rounded-xl hover:bg-gray-50 text-sm transition-colors shadow-sm">Batal</button>
                            <button @click="submitChallenge" :disabled="form.processing" class="flex items-center gap-2 px-8 py-3.5 bg-[#1e1e1e] text-white font-black rounded-xl hover:bg-black shadow-lg shadow-black/20 text-sm disabled:opacity-50 transition-all transform active:scale-95">
                                <span v-if="form.processing" class="material-symbols-outlined animate-spin text-lg">sync</span>
                                Kumpulkan Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>