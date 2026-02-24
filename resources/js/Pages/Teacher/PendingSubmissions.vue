<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    submissions: Object
});

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleDateString('en-US', options);
};

// FUNGSI UNTUK MENGIRIM KEPUTUSAN GURU
const reviewSubmission = (courseId, studentId, status) => {
    let msg = status === 'passed' 
        ? "Apakah Anda yakin ingin LULUSKAN project ini?" 
        : "Apakah Anda yakin ingin meminta REVISI? Link project siswa akan dihapus dan mereka harus mengumpulkan ulang.";
        
    if (confirm(msg)) {
        router.post(route('teacher.submissions.review', { course: courseId, student: studentId }), {
            status: status
        }, {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Pending Submissions" />

    <TeacherLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 space-y-10 font-display pb-24">
            
            <section class="border-b border-gray-200 pb-6">
                <div class="flex items-center justify-between w-full">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">Pending Submissions</h1>
                        <p class="text-sm text-gray-500 mt-1 font-medium">Review and grade your students' final projects.</p>
                    </div>
                </div>
            </section>

            <section>
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                    
                    <div class="px-6 sm:px-8 py-6 border-b border-gray-100 flex items-center justify-between bg-white z-10">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-orange-50 text-orange-500 flex items-center justify-center shrink-0 border border-orange-100">
                                <span class="material-symbols-outlined text-2xl">pending_actions</span>
                            </div>
                            <div>
                                <h3 class="font-black text-gray-900 text-lg">Needs Review</h3>
                                <p class="text-xs text-gray-500 font-medium mt-0.5">Projects awaiting your evaluation</p>
                            </div>
                        </div>
                        <div class="hidden sm:block">
                            <span class="px-3 py-1.5 bg-gray-50 text-gray-600 text-[10px] font-bold uppercase tracking-widest rounded-lg border border-gray-200 shadow-sm">
                                {{ submissions.total || 0 }} Pending
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse min-w-[800px]">
                            <thead class="bg-gray-50/80 border-b border-gray-100 text-gray-400 text-[10px] uppercase font-black tracking-widest sticky top-0">
                                <tr>
                                    <th class="px-6 sm:px-8 py-5 whitespace-nowrap">Student Info</th>
                                    <th class="px-6 py-5 whitespace-nowrap">Course & Project</th>
                                    <th class="px-6 py-5 whitespace-nowrap">Submitted On</th>
                                    <th class="px-6 py-5 whitespace-nowrap">Status</th>
                                    <th class="px-6 sm:px-8 py-5 text-right whitespace-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <tr v-for="sub in submissions.data" :key="sub.id" class="hover:bg-gray-50/50 transition-colors group">
                                    
                                    <td class="px-6 sm:px-8 py-4 align-top">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-200 shrink-0 bg-gray-100">
                                                <img 
                                                    :src="sub.student.avatar || `https://ui-avatars.com/api/?name=${sub.student.name}&background=random&color=fff`" 
                                                    class="h-full w-full object-cover"
                                                />
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900 leading-tight">{{ sub.student.name }}</p>
                                                <p class="text-[11px] text-gray-500 font-medium mt-0.5 truncate max-w-[150px]">{{ sub.student.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 align-top">
                                        <div class="flex flex-col gap-1">
                                            <span class="text-sm font-bold text-gray-800 line-clamp-1 max-w-[200px] group-hover:text-blue-600 transition-colors" :title="sub.course.title">
                                                {{ sub.course.title }}
                                            </span>
                                            <span class="text-[11px] font-medium text-gray-500 line-clamp-1">
                                                Assignment: {{ sub.title }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 align-top">
                                        <div class="flex items-center gap-1.5 text-gray-500">
                                            <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                                            <span class="text-[11px] font-bold">{{ formatDate(sub.created_at) }}</span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 align-top">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-orange-50 text-orange-700 border border-orange-100 text-[9px] font-black uppercase tracking-widest shadow-sm">
                                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                                            Pending
                                        </span>
                                    </td>

                                    <td class="px-6 sm:px-8 py-4 align-top text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a v-if="sub.link" :href="sub.link" target="_blank" 
                                                class="inline-flex items-center justify-center w-8 h-8 bg-gray-50 hover:bg-gray-200 text-gray-600 border border-gray-200 rounded-lg transition-all shadow-sm" 
                                                title="View Project Link">
                                                <span class="material-symbols-outlined text-[16px]">link</span>
                                            </a>
                                            
                                            <button @click="reviewSubmission(sub.course.id, sub.student_id, 'revision')" 
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-red-200 hover:bg-red-50 text-red-600 text-[10px] font-bold uppercase tracking-widest rounded-lg transition-all shadow-sm active:scale-95" 
                                                title="Request Revision">
                                                <span class="material-symbols-outlined text-[14px]">refresh</span>
                                                Revisi
                                            </button>

                                            <button @click="reviewSubmission(sub.course.id, sub.student_id, 'passed')" 
                                                class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-50 border border-green-200 hover:bg-green-100 text-green-700 text-[10px] font-bold uppercase tracking-widest rounded-lg transition-all shadow-sm active:scale-95" 
                                                title="Approve Project">
                                                <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                                Lulus
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="submissions.data.length === 0">
                                    <td colspan="5" class="px-6 py-16 text-center text-gray-500 bg-gray-50/50">
                                        <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-200 shadow-sm">
                                            <span class="material-symbols-outlined text-3xl text-green-500">task_alt</span>
                                        </div>
                                        <h3 class="text-lg font-black text-gray-900">All Caught Up!</h3>
                                        <p class="text-sm font-medium mt-1">There are no pending submissions to review right now.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="submissions.links && submissions.links.length > 3" class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white">
                        <div class="text-[11px] font-bold text-gray-500 uppercase tracking-widest text-center sm:text-left">
                            Page {{ submissions.current_page }} of {{ submissions.last_page }}
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap justify-center">
                            <Link v-for="(link, k) in submissions.links" :key="k" :href="link.url || '#'" v-html="link.label"
                                class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-lg transition-colors border text-center min-w-[32px]" 
                                :class="[link.active ? 'bg-gray-900 text-white border-gray-900 hover:bg-black' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50', !link.url ? 'opacity-50 cursor-not-allowed pointer-events-none bg-gray-50 border-transparent' : 'cursor-pointer']" 
                                preserve-scroll 
                            />
                        </div>
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

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}

/* Custom scrollbar untuk tabel overflow horizontal */
.custom-scrollbar::-webkit-scrollbar { height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(229, 231, 235, 1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: rgba(209, 213, 219, 1); }
</style>