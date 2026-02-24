<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    student: Object,
    portfolios: Array,
    certificates: Array, // <-- PASTIKAN MENGGUNAKAN 'certificates', BUKAN 'completedCourses'
    enrolledCourses: Array,
    rank: Number
});

// Fungsi untuk membuat inisial jika tidak ada avatar
const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
};
</script>

<template>
    <Head :title="`${student.name}'s Profile`" />

    <div class="min-h-screen bg-[#f8f9fa] font-display text-gray-900 pb-24 selection:bg-[#ffde24] selection:text-black">
        
        <div class="bg-[#111111] pt-24 pb-40 px-4 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-[#ffde24]/20 rounded-full blur-[120px] pointer-events-none"></div>
            
            <div class="max-w-5xl mx-auto relative z-10 flex flex-col items-center text-center">
                <div class="relative mb-6 group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-[#ffde24] to-yellow-600 rounded-full blur opacity-70 group-hover:opacity-100 transition duration-500"></div>
                    <div class="relative w-36 h-36 rounded-full border-4 border-[#111111] overflow-hidden bg-white shadow-2xl flex items-center justify-center">
                        <img v-if="student.avatar" :src="student.avatar" class="w-full h-full object-cover" />
                        <span v-else class="text-4xl font-black text-gray-800">{{ getInitials(student.name) }}</span>
                    </div>
                </div>
                
                <h1 class="text-5xl md:text-6xl font-black text-white tracking-tight mb-3">{{ student.name }}</h1>
                
                <div class="flex items-center gap-3 mb-6">
                    <span class="px-4 py-1.5 rounded-full bg-white/10 border border-white/10 text-[#ffde24] text-xs font-bold tracking-widest uppercase backdrop-blur-sm">
                        {{ student.class || 'Student' }}
                    </span>
                    <span v-if="student.nis" class="px-4 py-1.5 rounded-full bg-white/5 border border-white/10 text-white/70 text-xs font-bold tracking-widest uppercase backdrop-blur-sm">
                        NIS: {{ student.nis }}
                    </span>
                </div>

                <p v-if="student.bio" class="text-gray-300 max-w-2xl mx-auto mb-10 text-base md:text-lg leading-relaxed font-medium">
                    "{{ student.bio }}"
                </p>
                <div v-else class="mb-10"></div>
                
                <div class="flex flex-wrap justify-center gap-5">
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl px-8 py-5 flex items-center gap-5 hover:bg-white/10 hover:-translate-y-1 transition-all duration-300">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#ffde24] to-yellow-600 flex items-center justify-center text-black shadow-lg shadow-yellow-500/20">
                            <span class="material-symbols-outlined text-2xl">military_tech</span>
                        </div>
                        <div class="text-left">
                            <p class="text-[10px] text-white/60 uppercase tracking-widest font-black mb-0.5">Global Rank</p>
                            <p class="text-3xl font-black text-white leading-none">#{{ rank }}</p>
                        </div>
                    </div>

                    <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl px-8 py-5 flex items-center gap-5 hover:bg-white/10 hover:-translate-y-1 transition-all duration-300">
                        <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center text-[#ffde24]">
                            <span class="material-symbols-outlined text-2xl">bolt</span>
                        </div>
                        <div class="text-left">
                            <p class="text-[10px] text-white/60 uppercase tracking-widest font-black mb-0.5">Total XP</p>
                            <p class="text-3xl font-black text-white leading-none">{{ student.xp }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto px-4 -mt-20 relative z-20 space-y-10">
            
            <div class="bg-white rounded-[2rem] p-8 md:p-12 shadow-xl shadow-gray-200/50 border border-gray-100">
                <div class="flex items-center justify-between mb-8 pb-6 border-b border-gray-100">
                    <div>
                        <h2 class="text-2xl font-black flex items-center gap-3 text-gray-900">
                            <div class="w-10 h-10 rounded-lg bg-yellow-50 text-yellow-600 flex items-center justify-center">
                                <span class="material-symbols-outlined text-xl">folder_special</span>
                            </div>
                            Latest Projects
                        </h2>
                    </div>
                    <span class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full">{{ portfolios.length }} Projects</span>
                </div>
                
                <div v-if="portfolios.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="project in portfolios" :key="project.id" class="group flex flex-col bg-gray-50 rounded-2xl border border-gray-100 overflow-hidden hover:shadow-2xl hover:shadow-gray-200 transition-all duration-500 hover:-translate-y-1">
                        <div class="h-56 relative overflow-hidden">
                            <img :src="project.image || 'https://images.unsplash.com/photo-1555099962-4199c345e5dd?w=800&q=80'" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            
                            <div class="absolute top-4 left-4">
                                <span class="bg-white/90 backdrop-blur-sm text-black text-[10px] font-black uppercase tracking-wider px-3 py-1.5 rounded-lg shadow-sm">
                                    {{ project.category }}
                                </span>
                            </div>

                            <a v-if="project.link" :href="project.link" target="_blank" class="absolute bottom-4 right-4 translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 bg-[#ffde24] text-black w-10 h-10 rounded-full flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110">
                                <span class="material-symbols-outlined text-xl">arrow_outward</span>
                            </a>
                        </div>
                        
                        <div class="p-6 flex-1 flex flex-col bg-white">
                            <h3 class="font-black text-xl mb-3 text-gray-900 group-hover:text-yellow-600 transition-colors line-clamp-1">{{ project.title }}</h3>
                            <p class="text-gray-500 text-sm leading-relaxed mb-6 line-clamp-3 font-medium">{{ project.description }}</p>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-16 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                    <span class="material-symbols-outlined text-5xl text-gray-300 mb-3 block">folder_off</span>
                    <p class="text-gray-500 font-bold">No projects published yet.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                
                <div class="bg-white rounded-[2rem] p-8 md:p-10 shadow-xl shadow-gray-200/50 border border-gray-100 flex flex-col h-full">
                    <h2 class="text-xl font-black mb-8 flex items-center gap-3 text-gray-900 border-b border-gray-100 pb-5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">menu_book</span>
                        </div>
                        Learning Journey
                    </h2>
                    
                    <div v-if="enrolledCourses.length > 0" class="space-y-5 flex-1">
                        <div v-for="course in enrolledCourses" :key="course.id" class="flex gap-4 p-4 rounded-2xl border border-gray-100 hover:border-gray-300 hover:shadow-md transition-all bg-gray-50/50 group">
                            
                            <div class="w-24 h-24 rounded-xl overflow-hidden relative flex-shrink-0">
                                <img :src="course.cover_image || 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=800&q=80'" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                            </div>
                            
                            <div class="flex-1 flex flex-col justify-center">
                                <h3 class="font-bold text-gray-900 text-sm md:text-base mb-1 line-clamp-2 leading-tight">{{ course.title }}</h3>
                                <p class="text-gray-500 text-[11px] font-medium mb-3 uppercase tracking-wider">By {{ course.teacher?.name || 'Unknown' }}</p>
                                
                                <div class="mt-auto">
                                    <div class="flex items-center justify-between text-[10px] font-black mb-1.5">
                                        <span :class="(course.pivot?.progress || 0) === 100 ? 'text-green-600' : 'text-gray-500'">
                                            {{ (course.pivot?.progress || 0) === 100 ? 'COMPLETED' : 'IN PROGRESS' }}
                                        </span>
                                        <span class="text-gray-900">{{ course.pivot?.progress || 0 }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                                        <div :class="['h-full rounded-full transition-all duration-1000', (course.pivot?.progress || 0) === 100 ? 'bg-green-500' : 'bg-[#ffde24]']" :style="{ width: `${course.pivot?.progress || 0}%` }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="flex-1 flex flex-col items-center justify-center py-10 text-gray-400">
                        <span class="material-symbols-outlined text-4xl mb-2 opacity-50">inbox</span>
                        <p class="font-medium text-sm">No courses enrolled yet.</p>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-8 md:p-10 shadow-xl shadow-gray-200/50 border border-gray-100 flex flex-col h-full">
                    <h2 class="text-xl font-black mb-8 flex items-center gap-3 text-gray-900 border-b border-gray-100 pb-5">
                        <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-lg">workspace_premium</span>
                        </div>
                        Earned Certificates
                    </h2>
                    
                    <div v-if="certificates.length > 0" class="space-y-4 flex-1">
                        <div v-for="cert in certificates" :key="cert.id" class="flex items-center gap-5 p-5 border border-gray-100 rounded-2xl hover:border-[#ffde24] hover:shadow-md transition-all cursor-default group relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-yellow-50/0 via-yellow-50/50 to-yellow-50/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
                            
                            <div class="w-14 h-14 bg-gradient-to-br from-[#ffde24] to-yellow-500 rounded-xl flex items-center justify-center text-white shadow-sm flex-shrink-0 relative z-10">
                                <span class="material-symbols-outlined text-2xl">verified</span>
                            </div>
                            <div class="relative z-10 flex-1">
                                <h3 class="font-black text-gray-900 text-sm mb-0.5 line-clamp-1 group-hover:text-yellow-700 transition-colors">{{ cert.title }}</h3>
                                <p class="text-xs text-gray-500 font-medium">{{ cert.subtitle }}</p>
                            </div>
                            <div class="relative z-10">
                                <a :href="cert.download_url" target="_blank" class="w-8 h-8 rounded-full bg-gray-50 hover:bg-[#ffde24] hover:text-black text-gray-400 flex items-center justify-center transition-colors shadow-sm">
                                    <span class="material-symbols-outlined text-[18px]">download</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="flex-1 flex flex-col items-center justify-center py-10 text-gray-400">
                        <span class="material-symbols-outlined text-4xl mb-2 opacity-50">workspace_premium</span>
                        <p class="font-medium text-sm text-center">Complete a course or challenge<br>to earn a certificate.</p>
                    </div>
                </div>

            </div>
        </div>

        <div class="text-center mt-20 text-sm font-bold text-gray-400 tracking-wider uppercase">
            &copy; 2026 Aspas
        </div>
        
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&display=swap');
.font-display { font-family: 'Lexend', sans-serif; }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
</style>