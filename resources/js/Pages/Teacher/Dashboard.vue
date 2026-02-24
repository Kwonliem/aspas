<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

// Menerima data dari DashboardController
const props = defineProps({
    stats: Object,
    recent_courses: Array
});

// Helper untuk warna status (Logic tetap sama, styling class disesuaikan di template)
const getStatusColor = (status) => {
    return status === 'published' 
        ? 'bg-green-100 text-green-700 border-green-200' 
        : 'bg-gray-100 text-gray-600 border-gray-200';
};
</script>

<template>
    <Head title="Teacher Dashboard" />

    <TeacherLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 space-y-10 font-display pb-24">
            
            <section class="border-b border-gray-200 pb-6">
                <div class="flex items-center justify-between w-full">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">Dashboard Overview</h1>
                        <p class="text-sm text-gray-500 mt-1 font-medium">Welcome back, here's what's happening today.</p>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md transition-all duration-300 group">
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Courses</p>
                        <h2 class="text-4xl font-black text-gray-900 leading-none mb-1">{{ stats.total_courses }}</h2>
                        <p class="text-xs text-gray-500 font-medium mt-1">Active Curriculum</p>
                    </div>
                    <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-3xl">library_books</span>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100 flex items-center justify-between hover:shadow-md transition-all duration-300 group">
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Students</p>
                        <h2 class="text-4xl font-black text-gray-900 leading-none mb-1">{{ stats.total_students }}</h2>
                        <p class="text-xs text-gray-500 font-medium mt-1">Enrolled Across Courses</p>
                    </div>
                    <div class="w-16 h-16 bg-yellow-50 rounded-2xl flex items-center justify-center text-yellow-600 group-hover:bg-yellow-500 group-hover:text-white transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-3xl">groups</span>
                    </div>
                </div>
            </section>

            <section class="flex flex-col gap-6">
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                    
                    <div class="px-6 sm:px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-white z-10">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center border border-gray-200 shrink-0">
                                <span class="material-symbols-outlined text-gray-600 text-2xl">monitoring</span>
                            </div>
                            <div>
                                <h3 class="font-black text-gray-900 text-lg">Course Performance</h3>
                                <p class="text-xs text-gray-500 font-medium mt-0.5">Recent updates from your courses</p>
                            </div>
                        </div>
                        <Link :href="route('teacher.courses')" class="hidden sm:inline-flex items-center gap-1 text-xs font-bold text-gray-500 hover:text-gray-900 transition-colors border-b border-transparent hover:border-gray-900 pb-0.5">
                            View All Courses
                        </Link>
                    </div>
                    
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse min-w-[800px]">
                            <thead class="bg-gray-50/80 border-b border-gray-100 text-gray-400 text-[10px] uppercase font-black tracking-widest sticky top-0">
                                <tr>
                                    <th class="px-6 sm:px-8 py-5 whitespace-nowrap">Course Details</th>
                                    <th class="px-6 py-5 text-center whitespace-nowrap">Students</th>
                                    <th class="px-6 py-5 whitespace-nowrap">Content</th>
                                    <th class="px-6 py-5 whitespace-nowrap">Avg. Grade</th>
                                    <th class="px-6 py-5 whitespace-nowrap">Status</th>
                                    <th class="px-6 sm:px-8 py-5 text-right whitespace-nowrap">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <tr v-for="course in recent_courses" :key="course.id" class="hover:bg-gray-50/50 transition-colors group">
                                    
                                    <td class="px-6 sm:px-8 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-xl flex-shrink-0 bg-gray-100 overflow-hidden border border-gray-200 shadow-sm transition-transform duration-300 group-hover:scale-105">
                                                <img v-if="course.cover_image" :src="course.cover_image" class="w-full h-full object-cover">
                                                <div v-else class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-50">
                                                    <span class="material-symbols-outlined text-xl">image</span>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900 text-[13px] sm:text-sm line-clamp-2 max-w-[250px] leading-snug group-hover:text-blue-600 transition-colors" :title="course.title">
                                                    {{ course.title }}
                                                </p>
                                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">ID: {{ course.id }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-gray-100 border border-gray-200">
                                            <span class="material-symbols-outlined text-gray-500 text-[14px]">group</span>
                                            <span class="text-xs font-bold text-gray-700">{{ course.students_count }}</span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                         <div class="flex items-center gap-2 text-gray-600">
                                            <span class="material-symbols-outlined text-[16px]">menu_book</span>
                                            <span class="text-xs font-semibold">{{ course.chapters_count }} Chapters</span>
                                         </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="text-sm font-black text-gray-900">{{ course.avg_grade }}</span>
                                    </td>

                                    <td class="px-6 py-4">
                                        <span :class="['px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-widest border', getStatusColor(course.status)]">
                                            {{ course.status }}
                                        </span>
                                    </td>

                                    <td class="px-6 sm:px-8 py-4 text-right">
                                        <div class="flex items-center justify-end">
                                            <Link :href="route('teacher.courses.manage', course.id)" 
                                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border-2 border-gray-200 rounded-xl text-xs font-bold text-gray-700 hover:bg-[#111] hover:text-white hover:border-[#111] transition-all shadow-sm active:scale-95">
                                                <span class="material-symbols-outlined text-[16px]">settings</span>
                                                Manage
                                            </Link>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="recent_courses.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center">
                                        <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-200 shadow-sm">
                                            <span class="material-symbols-outlined text-3xl text-gray-400">library_add</span>
                                        </div>
                                        <h3 class="text-lg font-black text-gray-900">No Courses Found</h3>
                                        <p class="text-gray-500 text-sm mt-1 mb-6 font-medium">Get started by creating your first curriculum.</p>
                                        <Link :href="route('teacher.courses')" class="inline-flex items-center justify-center px-6 py-2.5 bg-[#111] hover:bg-black text-white font-bold rounded-xl text-sm transition-colors shadow-md active:scale-95">
                                            Create Course
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-2 text-center sm:hidden">
                    <Link :href="route('teacher.courses')" class="text-sm font-bold text-gray-500 hover:text-gray-900 transition-colors">
                        View All Courses
                    </Link>
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

/* Memastikan tulisan panjang (seperti judul) bisa dipotong rapi dengan elipsis (...) */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;  
    overflow: hidden;
}

/* Custom scrollbar untuk tabel overflow horizontal di mode HP */
.custom-scrollbar::-webkit-scrollbar { height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(229, 231, 235, 1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: rgba(209, 213, 219, 1); }
</style>