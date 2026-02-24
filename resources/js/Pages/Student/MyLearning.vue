<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';

// 1. Terima data real dari backend
const props = defineProps({
    courses: {
        type: Array,
        default: () => []
    }
});

// Filter Tab State
const activeTab = ref('all');

// 2. Computed property untuk memfilter course dari database
const filteredCourses = computed(() => {
    if (activeTab.value === 'all') return props.courses;

    if (activeTab.value === 'in-progress') {
        return props.courses.filter(course => (course.pivot?.progress || 0) < 100);
    }

    if (activeTab.value === 'completed') {
        return props.courses.filter(course => (course.pivot?.progress || 0) === 100);
    }

    return props.courses;
});
</script>

<template>
    <Head title="My Learning" />

    <StudentLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 space-y-10 font-display pb-24">

            <section class="flex flex-col md:flex-row md:items-end justify-between gap-6 border-b border-gray-200 pb-6">
                <div>
                    <h1 class="text-3xl md:text-4xl font-black text-gray-900 tracking-tight">My Courses</h1>
                    <p class="text-sm md:text-base text-gray-500 font-medium mt-2">Manage and continue your learning journey.</p>
                </div>

                <div class="flex items-center gap-1 bg-gray-100/80 p-1 rounded-xl border border-gray-200/50 overflow-x-auto custom-scrollbar">
                    <button @click="activeTab = 'all'"
                        class="px-5 py-2 text-sm font-bold transition-all rounded-lg whitespace-nowrap"
                        :class="activeTab === 'all' ? 'bg-white text-gray-900 shadow-sm border border-gray-200/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/50'">
                        All Courses
                    </button>
                    <button @click="activeTab = 'in-progress'"
                        class="px-5 py-2 text-sm font-bold transition-all rounded-lg whitespace-nowrap"
                        :class="activeTab === 'in-progress' ? 'bg-white text-gray-900 shadow-sm border border-gray-200/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/50'">
                        In Progress
                    </button>
                    <button @click="activeTab = 'completed'"
                        class="px-5 py-2 text-sm font-bold transition-all rounded-lg whitespace-nowrap"
                        :class="activeTab === 'completed' ? 'bg-white text-gray-900 shadow-sm border border-gray-200/50' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/50'">
                        Completed
                    </button>
                </div>
            </section>

            <div v-if="filteredCourses.length === 0" class="text-center py-24 bg-white rounded-[2rem] border border-dashed border-gray-200 shadow-sm">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-5 border border-gray-100">
                    <span class="material-symbols-outlined text-4xl text-gray-300">inbox</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-1">No courses found</h3>
                <p class="text-gray-500 text-sm font-medium">You don't have any courses in this category yet.</p>
                <button v-if="activeTab !== 'all'" @click="activeTab = 'all'" class="mt-6 text-sm font-bold text-blue-600 hover:text-blue-800 transition-colors">
                    View All Courses
                </button>
            </div>

            <section v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8">
                
                <div v-for="course in filteredCourses" :key="course.id" class="bg-white rounded-[1.5rem] border border-gray-100 overflow-hidden group hover:shadow-xl hover:border-gray-200 hover:-translate-y-1 transition-all duration-300 flex flex-col h-full">

                    <div class="h-44 md:h-48 bg-cover bg-center relative bg-gray-100 overflow-hidden">
                        <img v-if="course.cover_image" :src="course.cover_image" :alt="course.title" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700" />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                            <span class="material-symbols-outlined text-4xl md:text-5xl">image</span>
                        </div>
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-60"></div>
                        
                        <span class="absolute top-3 left-3 md:top-4 md:left-4 backdrop-blur-sm px-3 py-1 md:py-1.5 rounded-lg text-[9px] md:text-[10px] font-black uppercase tracking-widest shadow-sm"
                            :class="(course.pivot?.progress || 0) === 100 ? 'bg-green-500 text-white' : 'bg-white/90 text-gray-900'">
                            {{ (course.pivot?.progress || 0) === 100 ? 'Completed' : 'Enrolled' }}
                        </span>
                    </div>

                    <div class="p-5 md:p-6 flex-1 flex flex-col">
                        
                        <div class="flex items-center gap-2 mb-3 md:mb-4">
                            <div class="w-5 h-5 md:w-6 md:h-6 rounded-full overflow-hidden bg-gray-200 border border-white shrink-0">
                                <img v-if="course.teacher?.avatar" :src="course.teacher?.avatar" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center text-[9px] md:text-[10px] font-bold text-gray-500 bg-gray-100">
                                    {{ course.teacher?.name?.charAt(0) || '?' }}
                                </div>
                            </div>
                            <span class="text-[10px] md:text-xs text-gray-500 font-medium truncate">By <span class="font-bold text-gray-800">{{ course.teacher?.name || 'Unknown' }}</span></span>
                        </div>

                        <h3 class="text-base md:text-lg font-black text-gray-900 mb-6 leading-tight group-hover:text-[#e0c218] transition-colors line-clamp-2">
                            {{ course.title }}
                        </h3>

                        <div class="mt-auto space-y-4">
                            <div>
                                <div class="flex items-center justify-between text-[10px] font-bold mb-2 uppercase tracking-widest">
                                    <span :class="(course.pivot?.progress || 0) === 100 ? 'text-green-600' : 'text-gray-500'">
                                        Progress
                                    </span>
                                    <span class="text-gray-900">{{ course.pivot?.progress || 0 }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden border border-gray-200/50">
                                    <div :class="['h-full rounded-full transition-all duration-1000 ease-out', (course.pivot?.progress || 0) === 100 ? 'bg-green-500' : 'bg-gradient-to-r from-[#ffde24] to-yellow-500']"
                                         :style="{ width: `${course.pivot?.progress || 0}%` }"></div>
                                </div>
                            </div>

                            <Link v-if="(course.pivot?.progress || 0) === 100" :href="route('classroom.show', course.id)"
                                class="w-full py-2.5 md:py-3 bg-white text-gray-700 text-xs md:text-sm font-bold rounded-xl border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[16px] md:text-[18px]">replay</span> Review Course
                            </Link>

                            <Link v-else :href="route('classroom.show', course.id)"
                                class="w-full py-2.5 md:py-3 bg-[#111] text-white text-xs md:text-sm font-bold rounded-xl shadow-md hover:shadow-lg hover:bg-black transform active:scale-95 transition-all flex items-center justify-center gap-2 uppercase tracking-wide">
                                <span class="material-symbols-outlined text-[16px] md:text-[18px] text-[#ffde24]">play_circle</span> Continue
                            </Link>
                        </div>
                        
                    </div>
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

/* Menyembunyikan scrollbar di tab filter mobile tapi tetap bisa di-scroll */
.custom-scrollbar::-webkit-scrollbar {
    display: none;
}
.custom-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>