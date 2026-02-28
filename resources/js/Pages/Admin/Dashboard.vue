<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    topStudents: Array,
    recentTeachers: Array,
});
</script>

<template>
    <Head title="Admin Dashboard" />
    
    <AdminLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 space-y-10 font-display pb-24">
            
            <section class="border-b border-gray-200 pb-6">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">Admin Overview</h2>
                <p class="text-sm text-gray-500 font-medium mt-1">Monitor platform statistics and recent activities.</p>
            </section>

            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 flex items-center justify-between group">
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Students</p>
                        <h2 class="text-4xl font-black text-gray-900 leading-none">{{ stats.students.toLocaleString() }}</h2>
                    </div>
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-3xl">groups</span>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 flex items-center justify-between group">
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Teachers</p>
                        <h2 class="text-4xl font-black text-gray-900 leading-none">{{ stats.teachers }}</h2>
                    </div>
                    <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-3xl">cast_for_education</span>
                    </div>
                </div>

                <div class="bg-[#111] p-6 rounded-[2rem] border border-gray-800 shadow-xl flex items-center justify-between group relative overflow-hidden sm:col-span-2 lg:col-span-1">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-red-500/20 rounded-full blur-[40px] -mr-10 -mt-10 pointer-events-none transition-all duration-500 group-hover:bg-red-500/40"></div>
                    <div class="relative z-10">
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">Pending Deletions</p>
                        <h2 class="text-4xl font-black text-white leading-none">{{ stats.deletions }}</h2>
                    </div>
                    <div class="relative z-10 w-14 h-14 bg-red-500/20 rounded-2xl flex items-center justify-center text-red-500 border border-red-500/30">
                        <span class="material-symbols-outlined text-3xl">delete_forever</span>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 bg-white rounded-[2rem] shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                    <div class="p-6 sm:px-8 sm:py-6 border-b border-gray-100 flex justify-between items-center bg-white z-10">
                        <h3 class="font-black text-gray-900 text-lg tracking-tight">Top Performing Students</h3>
                        <a class="text-xs font-bold text-gray-500 hover:text-gray-900 transition-colors cursor-pointer border-b border-transparent hover:border-gray-900 pb-0.5">View All</a>
                    </div>
                    
                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-50/80 text-gray-400 text-[10px] uppercase tracking-widest font-black">
                                <tr>
                                    <th class="px-6 sm:px-8 py-4 whitespace-nowrap">Student Name</th>
                                    <th class="px-6 py-4 whitespace-nowrap">Rank / XP</th>
                                    <th class="px-6 py-4 whitespace-nowrap">Class</th>
                                    <th class="px-6 sm:px-8 py-4 text-right whitespace-nowrap">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <tr v-for="(student, index) in topStudents" :key="student.id" class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="px-6 sm:px-8 py-4">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full bg-gray-100 overflow-hidden border border-gray-200 shrink-0">
                                                <img :src="student.user.avatar || `https://ui-avatars.com/api/?name=${student.user.name}&background=ffde24&color=000`" alt="Student" class="w-full h-full object-cover"/>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900 leading-tight">{{ student.user.name }}</p>
                                                <p class="text-xs text-gray-500 font-medium mt-0.5">{{ student.user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0"
                                                :class="index === 0 ? 'bg-yellow-100 text-yellow-600' : index === 1 ? 'bg-gray-200 text-gray-600' : index === 2 ? 'bg-orange-100 text-orange-600' : 'bg-gray-50 text-gray-400'">
                                                <span class="material-symbols-outlined text-[14px]">military_tech</span>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900 leading-tight text-xs">Rank {{ index + 1 }}</p>
                                                <p class="text-[10px] text-gray-500 font-medium mt-0.5">{{ student.xp.toLocaleString() }} XP</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-3 py-1 rounded-lg bg-gray-100 text-gray-600 text-[11px] font-bold border border-gray-200 whitespace-nowrap">{{ student.class_name }}</span>
                                    </td>
                                    <td class="px-6 sm:px-8 py-4 text-right">
                                        <button class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors shrink-0" title="Update">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                    </td>
                                </tr>
                                
                                <tr v-if="topStudents.length === 0">
                                    <td colspan="4" class="px-6 sm:px-8 py-12 text-center text-gray-500 font-medium">
                                        <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3 border border-gray-100">
                                            <span class="material-symbols-outlined text-2xl text-gray-400">group_off</span>
                                        </div>
                                        No top students found yet.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="lg:col-span-1 bg-white rounded-[2rem] shadow-sm border border-gray-100 flex flex-col h-full overflow-hidden">
                    <div class="p-6 sm:px-8 sm:py-6 border-b border-gray-100 flex justify-between items-center bg-white z-10">
                        <h3 class="font-black text-gray-900 text-lg tracking-tight">Recent Teachers</h3>
                        <a class="text-xs font-bold text-gray-500 hover:text-gray-900 transition-colors cursor-pointer border-b border-transparent hover:border-gray-900 pb-0.5">View All</a>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto max-h-[500px] custom-scrollbar">
                        <div class="divide-y divide-gray-100">
                            
                            <div v-for="teacher in recentTeachers" :key="teacher.id" class="p-4 sm:px-6 hover:bg-gray-50 transition-colors flex items-center gap-4 group">
                                <div class="w-12 h-12 rounded-full bg-gray-100 overflow-hidden shrink-0 border border-gray-200">
                                    <img :src="teacher.avatar || `https://ui-avatars.com/api/?name=${teacher.name}&background=random`" alt="Teacher" class="w-full h-full object-cover"/>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-900 truncate group-hover:text-blue-600 transition-colors">{{ teacher.name }}</p>
                                    <p class="text-xs text-gray-500 font-medium truncate mt-0.5">{{ teacher.email }}</p>
                                </div>
                                <span class="text-[10px] font-bold px-2.5 py-1 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg shrink-0 uppercase tracking-widest">
                                    Teacher
                                </span>
                            </div>

                            <div v-if="recentTeachers.length === 0" class="p-8 text-center text-gray-500 font-medium flex flex-col items-center justify-center h-full">
                                <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mb-3 border border-gray-100">
                                    <span class="material-symbols-outlined text-2xl text-gray-400">person_off</span>
                                </div>
                                No teachers registered.
                            </div>
                            
                        </div>
                    </div>
                </div>

            </section>
        </div>
    </AdminLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.font-display {
    font-family: 'Inter', sans-serif;
}


.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(229, 231, 235, 1); 
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(209, 213, 219, 1); 
}
</style>