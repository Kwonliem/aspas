<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    challenges: Array
});

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    title: '',
    description: '',
    xp_reward: 100,
    credit_reward: 10,
    end_date: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (challenge) => {
    isEditing.value = true;
    editingId.value = challenge.id;
    form.title = challenge.title;
    form.description = challenge.description;
    form.xp_reward = challenge.xp_reward;
    form.credit_reward = challenge.credit_reward;
    form.end_date = new Date(challenge.end_date).toISOString().slice(0, 16);
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('teacher.challenges.update', editingId.value), {
            onSuccess: () => closeModal(),
            preserveScroll: true
        });
    } else {
        form.post(route('teacher.challenges.store'), {
            onSuccess: () => closeModal(),
            preserveScroll: true
        });
    }
};

const deleteChallenge = (id) => {
    if (confirm('Are you sure you want to delete this challenge?')) {
        router.delete(route('teacher.challenges.destroy', id), {
            preserveScroll: true
        });
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

const isExpired = (dateString) => {
    return new Date(dateString) < new Date();
};
</script>

<template>
    <Head title="Weekly Challenges" />

    <TeacherLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 space-y-10 font-display pb-24">
            
            <section class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 border-b border-gray-200 pb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">Weekly Challenges</h1>
                    <p class="text-sm text-gray-500 mt-1 font-medium">Manage tasks and rewards for your students.</p>
                </div>
                <button @click="openCreateModal"
                    class="flex items-center justify-center gap-2 bg-[#111] hover:bg-black text-white font-bold px-6 py-2.5 rounded-xl transition-all shadow-md active:scale-95 text-sm">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Create Challenge
                </button>
            </section>

            <div v-if="challenges.length === 0" class="flex flex-col items-center justify-center py-24 bg-white rounded-[2rem] border border-dashed border-gray-200 shadow-sm">
                <div class="w-20 h-20 bg-yellow-50 rounded-full flex items-center justify-center mb-5 border border-yellow-100">
                    <span class="material-symbols-outlined text-4xl text-[#FFDE21]">emoji_events</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-1">No Challenges Yet</h3>
                <p class="text-gray-500 text-sm font-medium mb-6">Create a challenge to engage your students and give them extra rewards.</p>
                <button @click="openCreateModal" class="px-6 py-2.5 bg-[#FFDE21] hover:bg-[#eacb1e] text-black font-bold rounded-xl transition-all shadow-sm active:scale-95 text-sm">
                    Create New Challenge
                </button>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 md:gap-8">
                
                <div v-for="challenge in challenges" :key="challenge.id"
                    class="group bg-white rounded-[2rem] border border-gray-200/80 shadow-sm hover:shadow-xl hover:border-gray-300 transition-all duration-300 flex flex-col overflow-hidden relative h-full"
                    :class="isExpired(challenge.end_date) ? 'opacity-80 grayscale-[30%] hover:grayscale-0' : ''">

                    <div class="p-6 pb-0 flex items-start justify-between relative z-10">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 shadow-inner"
                                :class="isExpired(challenge.end_date) ? 'bg-red-50 text-red-500 border border-red-100' : 'bg-yellow-50 text-yellow-600 border border-yellow-100'">
                                <span class="material-symbols-outlined text-[28px]">campaign</span>
                            </div>
                            
                            <div class="flex flex-col items-start gap-1.5">
                                <span v-if="isExpired(challenge.end_date)" class="inline-flex px-2.5 py-0.5 rounded-md text-[9px] font-black bg-red-100 text-red-700 uppercase tracking-widest border border-red-200">
                                    Expired
                                </span>
                                <span v-else class="inline-flex px-2.5 py-0.5 rounded-md text-[9px] font-black bg-green-50 text-green-600 uppercase tracking-widest border border-green-200">
                                    Published
                                </span>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                                    Ends: {{ formatDate(challenge.end_date) }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <button @click="openEditModal(challenge)" class="w-8 h-8 rounded-lg bg-gray-50 border border-gray-200 text-gray-500 flex items-center justify-center hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-colors" title="Edit">
                                <span class="material-symbols-outlined text-[16px]">edit</span>
                            </button>
                            <button @click="deleteChallenge(challenge.id)" class="w-8 h-8 rounded-lg bg-gray-50 border border-gray-200 text-gray-500 flex items-center justify-center hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-colors" title="Delete">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                            </button>
                        </div>
                    </div>

                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="text-xl font-black text-gray-900 mb-2 leading-tight group-hover:text-blue-600 transition-colors line-clamp-2">
                            {{ challenge.title }}
                        </h3>
                        <p class="text-sm text-gray-500 line-clamp-3 mb-6 font-medium leading-relaxed">
                            {{ challenge.description }}
                        </p>

                        <div class="mt-auto flex items-center justify-start gap-5 py-4 border-t border-gray-100">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100 shrink-0">
                                    <span class="material-symbols-outlined text-[16px]">bolt</span>
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">XP Reward</p>
                                    <p class="text-sm font-black text-gray-800 leading-none">{{ challenge.xp_reward }}</p>
                                </div>
                            </div>
                            
                            <div class="w-px h-8 bg-gray-200"></div>

                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-yellow-50 flex items-center justify-center text-yellow-600 border border-yellow-100 shrink-0">
                                    <span class="material-symbols-outlined text-[16px]">monetization_on</span>
                                </div>
                                <div>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Credits</p>
                                    <p class="text-sm font-black text-gray-800 leading-none">{{ challenge.credit_reward }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <Link :href="route('teacher.challenges.submissions', challenge.id)"
                        class="px-6 py-4 border-t border-gray-100 flex items-center justify-between transition-all group/link"
                        :class="challenge.pending_submissions > 0 ? 'bg-orange-50 hover:bg-orange-100' : 'bg-gray-50 hover:bg-gray-100'">
                        <div class="flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-[18px]" :class="challenge.pending_submissions > 0 ? 'text-orange-500' : 'text-gray-400'">inbox</span>
                            <span class="text-[11px] font-bold uppercase tracking-widest" :class="challenge.pending_submissions > 0 ? 'text-orange-700' : 'text-gray-600'">Pending Review</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-black" :class="challenge.pending_submissions > 0 ? 'text-orange-600' : 'text-gray-900'">{{ challenge.pending_submissions }}</span>
                            <span class="material-symbols-outlined text-[16px] text-gray-400 group-hover/link:translate-x-1 transition-transform">arrow_forward</span>
                        </div>
                    </Link>

                </div>
            </div>
        </div>

        <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showModal" class="fixed inset-0 z-[60] overflow-y-auto font-display">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

                    <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
                        
                        <div class="bg-white px-6 sm:px-8 py-6 border-b border-gray-100 flex justify-between items-center">
                            <div>
                                <h3 class="text-xl font-black text-gray-900 tracking-tight">
                                    {{ isEditing ? 'Edit Challenge' : 'Create New Challenge' }}
                                </h3>
                                <p class="text-xs text-gray-500 font-medium mt-1">Set up tasks and rewards for your students.</p>
                            </div>
                            <button @click="closeModal" class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-500 hover:bg-gray-200 hover:text-gray-900 transition-colors shrink-0">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                            </button>
                        </div>

                        <form @submit.prevent="submit">
                            <div class="px-6 sm:px-8 py-6 space-y-6">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Challenge Title</label>
                                    <input v-model="form.title" type="text"
                                        class="block w-full rounded-xl border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 shadow-sm py-3 px-4 transition-all text-sm font-medium placeholder:text-gray-400"
                                        placeholder="e.g. Build a Calculator App">
                                    <div v-if="form.errors.title" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.title }}</div>
                                </div>
                                
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Description / Instructions</label>
                                    <textarea v-model="form.description" rows="4"
                                        class="block w-full rounded-xl border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 shadow-sm py-3 px-4 transition-all text-sm font-medium placeholder:text-gray-400 resize-none"
                                        placeholder="Explain what the students need to do to complete this challenge..."></textarea>
                                    <div v-if="form.errors.description" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.description }}</div>
                                </div>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">XP Reward</label>
                                        <div class="relative">
                                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[18px]">bolt</span>
                                            <input v-model="form.xp_reward" type="number" min="0"
                                                class="block w-full pl-10 pr-4 rounded-xl border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 shadow-sm py-3 transition-all text-sm font-bold">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Credit Reward</label>
                                        <div class="relative">
                                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[18px]">monetization_on</span>
                                            <input v-model="form.credit_reward" type="number" min="0"
                                                class="block w-full pl-10 pr-4 rounded-xl border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 shadow-sm py-3 transition-all text-sm font-bold">
                                        </div>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">End Date & Time</label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[18px]">event</span>
                                        <input v-model="form.end_date" type="datetime-local"
                                            class="block w-full pl-10 pr-4 rounded-xl border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 shadow-sm py-3 transition-all text-sm font-medium">
                                    </div>
                                    <div v-if="form.errors.end_date" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.end_date }}</div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 px-6 sm:px-8 py-5 flex justify-end gap-3 border-t border-gray-100">
                                <button @click="closeModal" type="button"
                                    class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors text-sm shadow-sm">
                                    Cancel
                                </button>
                                <button type="submit" :disabled="form.processing"
                                    class="flex items-center gap-2 px-6 py-2.5 bg-[#111] text-white font-bold rounded-xl shadow-md hover:bg-black active:scale-95 disabled:opacity-50 text-sm tracking-wide transition-all">
                                    <span v-if="form.processing" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                                    {{ isEditing ? 'Save Changes' : 'Publish Challenge' }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </Transition>

    </TeacherLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.font-display {
    font-family: 'Inter', sans-serif;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}
</style>