<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

defineProps({
    activeCoursesCount: {
        type: Number,
        default: 0
    },
    completedCoursesCount: {
        type: Number,
        default: 0
    },
    activeCourses: {
        type: Array,
        default: () => []
    },
    certificates: { 
        type: Array,
        default: () => []
    }
});
</script>

<template>
    <Head title="Student Dashboard" />

    <StudentLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 space-y-12 font-display pb-24">
            
            <section class="border-b border-gray-200 pb-8">
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 tracking-tight mb-2">Welcome back, {{ user.name.split(' ')[0] }}!</h2>
                <p class="text-gray-500 font-medium text-sm md:text-base">
                    Keep up the good work! You are ready to learn today.
                </p>
            </section>

            <section class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-5 hover:shadow-md hover:-translate-y-1 transition-all duration-300 group">
                    <div class="bg-blue-50 text-blue-600 p-4 rounded-2xl group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-3xl">menu_book</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Active Courses</p>
                        <p class="text-3xl font-black text-gray-900 leading-none">{{ activeCoursesCount }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-5 hover:shadow-md hover:-translate-y-1 transition-all duration-300 group">
                    <div class="bg-green-50 text-green-600 p-4 rounded-2xl group-hover:bg-green-600 group-hover:text-white transition-colors">
                        <span class="material-symbols-outlined text-3xl">task_alt</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Completed</p>
                        <p class="text-3xl font-black text-gray-900 leading-none">{{ completedCoursesCount }}</p>
                    </div>
                </div>

                <div class="bg-[#111] p-6 rounded-3xl border border-gray-800 shadow-xl shadow-gray-200/50 flex items-center gap-5 hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group sm:col-span-2 md:col-span-1">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-[#ffde24]/20 rounded-full blur-[40px] -mr-10 -mt-10 pointer-events-none"></div>
                    <div class="bg-gradient-to-br from-[#ffde24] to-yellow-500 text-black p-4 rounded-2xl shadow-lg shadow-yellow-500/20 relative z-10">
                        <span class="material-symbols-outlined text-3xl">workspace_premium</span>
                    </div>
                    <div class="relative z-10">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Total XP</p>
                        <p class="text-3xl font-black text-white leading-none">{{ user.xp }}</p>
                    </div>
                </div>
            </section>

            <section>
                <div class="flex items-end justify-between mb-8">
                    <div>
                        <h3 class="text-xl md:text-2xl font-black text-gray-900 tracking-tight">
                            Active Courses
                        </h3>
                        <p class="text-sm text-gray-500 font-medium mt-1">Pick up where you left off.</p>
                    </div>
                    <Link :href="route('classroom.my-courses')" class="hidden sm:inline-flex text-sm font-bold text-gray-500 hover:text-[#ffde24] transition-colors border-b-2 border-transparent hover:border-[#ffde24] pb-0.5">
                        View All
                    </Link>
                </div>
                
                <div v-if="activeCourses.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <Link v-for="course in activeCourses" :key="course.id" :href="route('classroom.my-courses')" 
                        class="bg-white rounded-[2rem] border border-gray-100 overflow-hidden group hover:shadow-xl hover:shadow-gray-200/50 hover:border-gray-200 hover:-translate-y-1 transition-all duration-300 flex flex-col h-full relative cursor-pointer">
                        
                        <div class="h-48 bg-cover bg-center relative bg-gray-100 overflow-hidden">
                            <img v-if="course.cover_image" :src="course.cover_image" :alt="course.title" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700" />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                <span class="material-symbols-outlined text-5xl">image</span>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent opacity-60"></div>
                            
                            <span class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1.5 rounded-lg text-[10px] font-black text-gray-900 uppercase tracking-widest shadow-sm">
                                Course
                            </span>
                        </div>
                        
                        <div class="p-6 flex-1 flex flex-col">
                            <h5 class="text-lg font-black mb-2 text-gray-900 leading-tight group-hover:text-blue-600 transition-colors line-clamp-2">{{ course.title }}</h5>
                            
                            <div class="flex items-center gap-2 mb-6 mt-1">
                                <div class="w-5 h-5 rounded-full overflow-hidden bg-gray-200 border border-gray-100">
                                    <img v-if="course.teacher?.avatar" :src="course.teacher?.avatar" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-[9px] font-bold text-gray-500 bg-white">
                                        {{ course.teacher?.name?.charAt(0) }}
                                    </div>
                                </div>
                                <span class="text-xs text-gray-500 font-medium">By <span class="text-gray-900 font-bold">{{ course.teacher?.name || 'Unknown' }}</span></span>
                            </div>
                            
                            <div class="mt-auto">
                                <div class="flex items-center justify-between text-[10px] font-bold mb-2 uppercase tracking-widest">
                                    <span :class="(course.pivot?.progress || 0) === 100 ? 'text-green-600' : 'text-gray-400'">
                                        {{ (course.pivot?.progress || 0) === 100 ? 'Completed' : 'In Progress' }}
                                    </span>
                                    <span class="text-gray-900">{{ course.pivot?.progress || 0 }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden border border-gray-200/50">
                                    <div :class="['h-full rounded-full transition-all duration-1000 ease-out', (course.pivot?.progress || 0) === 100 ? 'bg-green-500' : 'bg-gradient-to-r from-[#ffde24] to-yellow-500']" 
                                         :style="{ width: `${course.pivot?.progress || 0}%` }"></div>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
                
                <div v-else class="text-center py-24 bg-white rounded-[2rem] border border-dashed border-gray-200 shadow-sm">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                        <span class="material-symbols-outlined text-3xl text-gray-400">inbox</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">No Active Courses</h3>
                    <p class="text-gray-500 text-sm mb-6 font-medium">You haven't enrolled in any courses yet.</p>
                    <Link :href="route('welcome')" class="inline-flex items-center justify-center px-6 py-2.5 bg-gray-900 text-white text-sm font-bold rounded-xl hover:bg-black transition-all shadow-sm">
                        Browse Courses
                    </Link>
                </div>

                <div class="mt-6 text-center sm:hidden">
                    <Link :href="route('classroom.my-courses')" class="text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">
                        View All Active Courses
                    </Link>
                </div>
            </section>

            <section class="pt-8 border-t border-gray-200">
                <div class="flex items-end justify-between mb-8">
                    <div>
                        <h3 class="text-xl md:text-2xl font-black text-gray-900 tracking-tight">
                            Earned Certificates
                        </h3>
                        <p class="text-sm text-gray-500 font-medium mt-1">Your verified achievements.</p>
                    </div>
                    <Link :href="route('portfolio')" class="hidden sm:inline-flex text-sm font-bold text-gray-500 hover:text-[#ffde24] transition-colors border-b-2 border-transparent hover:border-[#ffde24] pb-0.5">
                        View Portfolio
                    </Link>
                </div>

                <div v-if="certificates && certificates.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="cert in certificates" :key="cert.id" class="bg-white p-6 md:p-8 rounded-[2rem] border border-gray-100 shadow-sm flex flex-col items-center text-center hover:shadow-xl hover:border-gray-200 hover:-translate-y-1 transition-all duration-300 relative group overflow-hidden">
                        
                        <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-[#ffde24] to-yellow-400"></div>
                        
                        <div class="w-16 h-16 rounded-full bg-yellow-50 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-500 shadow-inner border border-yellow-100">
                            <span class="material-symbols-outlined text-3xl text-yellow-600">workspace_premium</span>
                        </div>
                        
                        <h5 class="font-black text-gray-900 mb-2 leading-snug line-clamp-2 h-12 flex items-center justify-center">{{ cert.title }}</h5>
                        <p class="text-[11px] text-gray-400 font-bold uppercase tracking-widest mb-8">Issued: {{ cert.completed_at }}</p>
                        
                        <a :href="cert.download_url" target="_blank" class="w-full mt-auto py-3 border-2 border-gray-100 rounded-xl text-xs font-bold text-gray-600 bg-white hover:bg-gray-50 hover:text-gray-900 transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">download</span>
                            Download PDF
                        </a>
                    </div>
                </div>

                <div v-else class="text-center py-20 bg-gray-50 rounded-[2rem] border border-gray-200">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm border border-gray-100">
                        <span class="material-symbols-outlined text-3xl text-gray-300">verified</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">No Certificates Yet</h3>
                    <p class="text-gray-500 text-sm font-medium">Complete a course to 100% to earn your first certificate.</p>
                </div>

                <div class="mt-6 text-center sm:hidden">
                    <Link :href="route('portfolio')" class="text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">
                        View Portfolio
                    </Link>
                </div>
            </section>

        </div>
    </StudentLayout>
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
</style>