<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    challenge: Object,
    submissions: Array
});

const reviewSubmission = (studentId, status) => {
    if (confirm(`Tandai submission ini sebagai ${status.toUpperCase()}?`)) {
        router.post(route('teacher.challenges.review', { challenge: props.challenge.id, student: studentId }), {
            status: status
        }, { preserveScroll: true });
    }
};
</script>

<template>
    <Head :title="`Submissions - ${challenge.title}`" />

    <TeacherLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 space-y-8 font-display pb-24">
            
            <section class="flex flex-col md:flex-row md:items-center gap-4 border-b border-gray-200 pb-6">
                <Link :href="route('teacher.challenges')" class="w-10 h-10 bg-white rounded-full flex items-center justify-center border border-gray-200 text-gray-500 hover:text-black hover:bg-gray-50 transition-all shadow-sm shrink-0">
                    <span class="material-symbols-outlined text-xl">arrow_back</span>
                </Link>
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">Challenge Submissions</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-[10px] font-bold uppercase tracking-widest rounded">Target</span>
                        <p class="text-sm text-gray-600 font-medium line-clamp-1">{{ challenge.title }}</p>
                    </div>
                </div>
            </section>

            <section>
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                    
                    <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-center justify-between bg-white z-10">
                        <h3 class="font-black text-gray-900 text-lg">Student Entries</h3>
                        <span class="px-3 py-1 bg-gray-50 border border-gray-200 text-gray-600 text-[10px] font-bold uppercase tracking-widest rounded-lg">
                            {{ submissions.length }} Total
                        </span>
                    </div>

                    <div v-if="submissions.length === 0" class="flex flex-col items-center justify-center py-24 bg-gray-50/50">
                        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mb-5 border border-gray-200 shadow-sm">
                            <span class="material-symbols-outlined text-4xl text-gray-300">inbox</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Belum Ada Submission</h3>
                        <p class="text-gray-500 text-sm font-medium">Belum ada murid yang mengumpulkan karya untuk challenge ini.</p>
                    </div>

                    <div v-else class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse min-w-[800px]">
                            <thead class="bg-gray-50/80 border-b border-gray-100 text-gray-400 text-[10px] uppercase font-black tracking-widest sticky top-0">
                                <tr>
                                    <th class="px-6 sm:px-8 py-5 whitespace-nowrap">Student Info</th>
                                    <th class="px-6 py-5 whitespace-nowrap">Submitted At</th>
                                    <th class="px-6 py-5 whitespace-nowrap">Project Link</th>
                                    <th class="px-6 py-5 whitespace-nowrap">Status</th>
                                    <th class="px-6 sm:px-8 py-5 text-right whitespace-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <tr v-for="sub in submissions" :key="sub.id" class="hover:bg-gray-50/50 transition-colors group">
                                    
                                    <td class="px-6 sm:px-8 py-4 align-middle">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-200 shrink-0 bg-gray-100">
                                                <img 
                                                    :src="sub.avatar || `https://ui-avatars.com/api/?name=${sub.name}&background=random&color=fff`" 
                                                    class="h-full w-full object-cover"
                                                />
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900 leading-tight group-hover:text-blue-600 transition-colors">{{ sub.name }}</p>
                                                <p class="text-[11px] text-gray-500 font-medium mt-0.5">{{ sub.email }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 align-middle">
                                        <div class="flex items-center gap-1.5 text-gray-500">
                                            <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                                            <span class="text-xs font-medium">{{ sub.submitted_at }}</span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 align-middle">
                                        <a :href="sub.link" target="_blank" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-[11px] font-bold border border-blue-100 transition-colors shadow-sm"
                                            title="Buka karya di tab baru">
                                            <span class="material-symbols-outlined text-[14px]">open_in_new</span> Lihat Karya
                                        </a>
                                    </td>

                                    <td class="px-6 py-4 align-middle">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest shadow-sm"
                                            :class="{
                                                'bg-orange-50 text-orange-700 border border-orange-100': sub.status === 'pending',
                                                'bg-green-50 text-green-700 border border-green-200': sub.status === 'passed',
                                                'bg-red-50 text-red-700 border border-red-200': sub.status === 'failed'
                                            }">
                                            <span v-if="sub.status === 'pending'" class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                                            <span v-if="sub.status === 'passed'" class="material-symbols-outlined text-[12px]">check_circle</span>
                                            <span v-if="sub.status === 'failed'" class="material-symbols-outlined text-[12px]">cancel</span>
                                            {{ sub.status }}
                                        </span>
                                    </td>

                                    <td class="px-6 sm:px-8 py-4 align-middle text-right">
                                        <div class="flex items-center justify-end gap-2" v-if="sub.status === 'pending'">
                                            <button @click="reviewSubmission(sub.id, 'failed')" 
                                                class="px-4 py-2 bg-white border border-red-200 text-red-600 rounded-xl text-[11px] font-bold hover:bg-red-50 transition-colors shadow-sm active:scale-95 uppercase tracking-wider" title="Tolak / Minta Revisi">
                                                Reject
                                            </button>
                                            <button @click="reviewSubmission(sub.id, 'passed')" 
                                                class="px-4 py-2 bg-[#111] text-white rounded-xl text-[11px] font-bold hover:bg-black transition-colors shadow-md flex items-center gap-1.5 active:scale-95 uppercase tracking-wider" title="Terima / Luluskan">
                                                <span class="material-symbols-outlined text-[14px] text-[#ffde24]">check_circle</span> Approve
                                            </button>
                                        </div>
                                        <span v-else class="inline-flex items-center gap-1 text-[11px] font-bold text-gray-400 uppercase tracking-widest">
                                            <span class="material-symbols-outlined text-[14px]">done_all</span> Reviewed
                                        </span>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </section>

        </div>
    </TeacherLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.font-display {
    font-family: 'Inter', sans-serif;
}

.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}

/* Custom scrollbar untuk tabel overflow horizontal di HP */
.custom-scrollbar::-webkit-scrollbar { height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(229, 231, 235, 1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: rgba(209, 213, 219, 1); }
</style>