<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const showDropdown = ref(false);

const props = defineProps({
    courses: Array,
    latestChallenge: Object,
});

const publishedCourses = computed(() => {
    return props.courses ? props.courses.filter(course => course.status === 'published') : [];
});

const getDashboardRoute = () => {
    if (!user.value) return 'login';
    switch (user.value.role) {
        case 'admin': return 'admin.dashboard';
        case 'teacher': return 'teacher.dashboard';
        default: return 'dashboard';
    }
};

const getSettingsRoute = () => {
    if (!user.value) return 'login';
    switch (user.value.role) {
        case 'admin': return 'admin.settings';
        case 'teacher': return 'teacher.settings';
        default: return 'profile.edit';
    }
};

const closeDropdown = (e) => {
    if (showDropdown.value && !e.target.closest('.user-menu-container')) {
        showDropdown.value = false;
    }
};

const scrollToSection = (id) => {
    if (id === 'home') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        const element = document.getElementById(id);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    }
};

const enrollCourse = (courseId) => {
    if (!user.value) {
        router.visit(route('login'));
        return;
    }
    router.post(route('student.courses.enroll', courseId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            alert('Successfully enrolled! Check your My Learning dashboard.');
        }
    });
};

onMounted(() => document.addEventListener('click', closeDropdown));
onUnmounted(() => document.removeEventListener('click', closeDropdown));
</script>

<template>
    <Head title="Welcome to Aspas" />

    <div class="font-display antialiased text-gray-900 dark:text-gray-100 bg-[#fafafa] dark:bg-[#111111] selection:bg-[#ffde24] selection:text-black min-h-screen flex flex-col">

        <nav class="fixed top-0 z-50 w-full border-b border-gray-200/80 dark:border-white/5 bg-white/80 dark:bg-[#1a1a1a]/80 backdrop-blur-xl transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16 md:h-20">
                    
                    <div class="flex-shrink-0 flex items-center gap-2 md:gap-3 cursor-pointer group" @click="scrollToSection('home')">
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-white rounded-lg md:rounded-xl flex items-center justify-center shadow-sm border border-gray-100 p-1 md:p-1.5 group-hover:-rotate-6 transition-transform duration-300">
                            <img src="/images/icon/aspas-logo.svg" alt="Aspas Logo" class="w-full h-full object-contain" />
                        </div>
                        <span class="text-xl md:text-2xl font-black text-gray-900 dark:text-white tracking-tight">Aspas.</span>
                    </div>

                    <div class="hidden md:flex space-x-8 lg:space-x-10 items-center">
                        <button @click="scrollToSection('home')" class="text-sm font-bold text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">Home</button>
                        <button @click="scrollToSection('courses')" class="text-sm font-bold text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">Courses</button>
                        <button @click="scrollToSection('about')" class="text-sm font-bold text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">Challenges</button>
                    </div>

                    <div class="flex items-center gap-3">
                        <div v-if="user" class="relative user-menu-container">
                            <button @click.stop="showDropdown = !showDropdown" class="flex items-center gap-2 bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 py-1.5 pl-1.5 pr-2 md:pr-4 rounded-full transition-all border border-gray-200 dark:border-white/10">
                                <div class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-[#111] dark:bg-[#ffde24] flex items-center justify-center text-white dark:text-black font-bold text-xs md:text-sm overflow-hidden">
                                    <img v-if="user.avatar" :src="user.avatar" alt="Avatar" class="w-full h-full object-cover">
                                    <span v-else>{{ user.name.charAt(0) }}</span>
                                </div>
                                <span class="hidden sm:block text-sm font-bold text-gray-700 dark:text-gray-200 max-w-[100px] truncate">{{ user.name }}</span>
                                <span class="material-symbols-outlined text-gray-400 text-sm transition-transform duration-200" :class="{ 'rotate-180': showDropdown }">expand_more</span>
                            </button>

                            <Transition enter-active-class="transition ease-out duration-200" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                                <div v-if="showDropdown" class="absolute right-0 mt-2 w-56 bg-white dark:bg-[#1e1e1e] rounded-2xl shadow-xl border border-gray-100 dark:border-white/10 overflow-hidden z-50">
                                    <div class="px-5 py-4 border-b border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/5">
                                        <p class="text-[10px] text-gray-500 uppercase tracking-widest font-bold mb-1">Signed in as</p>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ user.email }}</p>
                                        <div class="mt-2">
                                            <span class="text-[9px] bg-[#ffde24] text-black px-2 py-0.5 rounded font-black uppercase tracking-widest">{{ user.role }}</span>
                                        </div>
                                    </div>
                                    <div class="p-2">
                                        <Link :href="route(getDashboardRoute())" class="flex items-center gap-3 px-3 py-2.5 text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 rounded-xl transition-colors">
                                            <span class="material-symbols-outlined text-gray-400 text-[18px]">space_dashboard</span> Dashboard
                                        </Link>
                                        <Link :href="route(getSettingsRoute())" class="flex items-center gap-3 px-3 py-2.5 text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 rounded-xl transition-colors">
                                            <span class="material-symbols-outlined text-gray-400 text-[18px]">settings</span> Settings
                                        </Link>
                                    </div>
                                    <div class="p-2 border-t border-gray-100 dark:border-white/5">
                                        <Link :href="route('logout')" method="post" as="button" class="flex w-full items-center gap-3 px-3 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">logout</span> Log Out
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <template v-else>
                            <Link :href="route('login')" class="hidden sm:block text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-bold px-3 py-2 transition-colors text-sm">
                                Log In
                            </Link>
                            <Link :href="route('register')" class="bg-[#111] hover:bg-black dark:bg-[#ffde24] dark:hover:bg-[#eacb1e] text-white dark:text-black font-bold py-2 px-4 md:py-2.5 md:px-6 rounded-xl transition-all transform hover:-translate-y-0.5 text-xs md:text-sm shadow-sm">
                                Get Started
                            </Link>
                        </template>
                    </div>

                </div>
            </div>
        </nav>

        <section class="relative pt-24 pb-16 md:pt-32 md:pb-20 lg:pt-48 lg:pb-32 overflow-hidden" id="home">
            <div class="absolute top-0 right-0 w-[400px] h-[400px] md:w-[800px] md:h-[800px] bg-gradient-to-b from-[#ffde24]/20 to-transparent rounded-full blur-[80px] md:blur-[120px] -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
            
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center lg:text-left">
                <div class="grid lg:grid-cols-2 gap-10 md:gap-16 items-center">
                    <div class="max-w-2xl animate-fade-in-up">
                        <h1 class="text-4xl sm:text-5xl lg:text-7xl font-black text-gray-900 dark:text-white leading-[1.15] md:leading-[1.1] mb-4 md:mb-6 tracking-tight">
                            Master Skills. <br class="hidden sm:block" />
                            <span class="text-[#ffde24] relative inline-block mt-1 md:mt-2">
                                Evolve Faster.
                                <svg class="absolute w-full h-2 md:h-4 -bottom-1 md:-bottom-2 left-0 text-yellow-300 opacity-60" viewBox="0 0 100 10" preserveAspectRatio="none">
                                    <path d="M0 5 Q 50 10 100 5" stroke="currentColor" stroke-width="4" fill="none" />
                                </svg>
                            </span>
                        </h1>
                        <p class="text-base md:text-lg text-gray-500 dark:text-gray-400 mb-8 md:mb-10 leading-relaxed font-medium max-w-lg mx-auto lg:mx-0">
                            Aspas provides a curated curriculum to bridge the gap between theory and industry. Start your journey with expert-led courses today.
                        </p>
                        
                        <div class="flex flex-col sm:flex-row justify-center lg:justify-start gap-3 md:gap-4">
                            <Link :href="route('register')" class="w-full sm:w-auto bg-[#111] hover:bg-black dark:bg-[#ffde24] dark:hover:bg-[#eacb1e] text-white dark:text-black font-bold py-3.5 md:py-4 px-6 md:px-8 rounded-xl transition-all transform hover:-translate-y-1 text-center shadow-lg hover:shadow-xl flex items-center justify-center gap-2 text-sm md:text-base">
                                Start Learning Now <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                            </Link>
                            <button @click="scrollToSection('courses')" class="px-6 md:px-8 py-3.5 md:py-4 rounded-xl border-2 border-gray-200 dark:border-white/10 text-gray-700 dark:text-white font-bold hover:bg-gray-50 dark:hover:bg-white/5 transition-colors text-center text-sm md:text-base">
                                Explore Courses
                            </button>
                        </div>
                    </div>

                    <div class="relative h-[350px] sm:h-[450px] lg:h-[500px] animate-fade-in-left delay-200">
                        <div class="relative w-full h-full rounded-[2rem] md:rounded-[2.5rem] overflow-hidden shadow-2xl border border-gray-100 dark:border-white/5">
                            <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=1471&auto=format&fit=crop" alt="Students learning" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                            <div class="absolute bottom-4 left-4 right-4 md:bottom-6 md:left-6 md:right-6 bg-white/95 dark:bg-[#1a1a1a]/95 backdrop-blur-md p-4 md:p-5 rounded-2xl shadow-lg flex items-center gap-4 border border-white/20">
                                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                                    <span class="material-symbols-outlined text-xl md:text-2xl">code</span>
                                </div>
                                <div>
                                    <p class="text-[9px] md:text-[10px] text-gray-400 font-bold uppercase tracking-widest">Web Development</p>
                                    <p class="text-sm md:text-base font-black text-gray-900 dark:text-white leading-tight mt-0.5">Interactive Curriculum</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16 md:py-24 bg-white dark:bg-[#1a1a1a] border-y border-gray-100 dark:border-white/5" id="courses">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-10 md:mb-16 gap-4">
                    <div>
                        <h2 class="text-3xl md:text-5xl font-black text-gray-900 dark:text-white tracking-tight mb-2 md:mb-4">Popular Courses</h2>
                        <p class="text-sm md:text-lg text-gray-500 font-medium">Explore our highest-rated published curriculums.</p>
                    </div>
                    <Link :href="route('register')" class="hidden sm:flex items-center gap-2 text-xs md:text-sm font-bold text-gray-900 dark:text-white hover:text-[#ffde24] transition-colors group">
                        View All Categories 
                    </Link>
                </div>

                <div v-if="publishedCourses.length > 0" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8 md:gap-10">
                    
                    <div v-for="course in publishedCourses" :key="course.id" class="group bg-[#fafafa] dark:bg-[#111] rounded-[2.5rem] border border-gray-200/80 dark:border-white/5 flex flex-col h-full hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 relative overflow-hidden">
                        
                        <div class="relative aspect-video overflow-hidden bg-gray-100">
                            <img v-if="course.cover_image" :src="course.cover_image" :alt="course.title" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-1000">
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300 bg-gray-100">
                                <span class="material-symbols-outlined text-6xl md:text-7xl">image</span>
                            </div>
                            
                            <div class="absolute top-5 left-5 z-10">
                                <span class="bg-black/80 backdrop-blur-md text-white text-[10px] md:text-xs font-black px-4 py-2 rounded-xl shadow-lg uppercase tracking-widest">
                                    {{ course.credits > 0 ? `${course.credits} Credits` : 'Free' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-8 md:p-10 flex-1 flex flex-col">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-gray-200 overflow-hidden border-2 border-white shadow-sm shrink-0">
                                    <img v-if="course.teacher?.avatar" :src="course.teacher.avatar" class="w-full h-full object-cover">
                                    <div v-else class="w-full h-full flex items-center justify-center text-sm font-black text-gray-500 bg-white">
                                        {{ course.teacher?.name?.charAt(0) }}
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-0.5">Instructor</p>
                                    <p class="text-sm md:text-base font-bold text-gray-900 dark:text-white truncate">{{ course.teacher?.name || 'Unknown' }}</p>
                                </div>
                            </div>

                            <h3 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white mb-4 leading-tight group-hover:text-[#ffde24] transition-colors line-clamp-2">
                                {{ course.title }}
                            </h3>
                            <p class="text-sm md:text-base text-gray-500 line-clamp-3 mb-8 font-medium leading-relaxed">
                                {{ course.description }}
                            </p>

                            <div class="grid grid-cols-3 gap-4 border-t border-gray-100 dark:border-white/5 pt-8 mt-auto mb-8">
                                <div class="flex flex-col items-center p-3 rounded-2xl bg-gray-50 dark:bg-white/5">
                                    <span class="text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Materi</span>
                                    <span class="text-base md:text-lg font-black text-gray-900 dark:text-white flex items-center gap-1.5">
                                        
                                        {{ course.lessons_count || 0 }}
                                    </span>
                                </div>
                                <div class="flex flex-col items-center p-3 rounded-2xl bg-gray-50 dark:bg-white/5">
                                    <span class="text-[9px] md:text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Quizzes</span>
                                    <span class="text-base md:text-lg font-black text-gray-900 dark:text-white flex items-center gap-1.5">
                                        
                                        {{ course.quizzes_count || 0 }}
                                    </span>
                                </div>
                                <div class="flex flex-col items-center p-3 rounded-2xl bg-yellow-50 dark:bg-yellow-900/20">
                                    <span class="text-[9px] md:text-[10px] font-black text-yellow-600/70 dark:text-yellow-400/70 uppercase tracking-widest mb-2">Rewards</span>
                                    <span class="text-base md:text-lg font-black text-yellow-600 dark:text-yellow-400 flex items-center gap-1.5">
                                       
                                        {{ course.xp }} XP
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <a href="#" class="bg-white dark:bg-[#1a1a1a] border-2 border-gray-200 dark:border-white/10 text-gray-900 dark:text-white font-bold py-4 rounded-2xl hover:bg-gray-50 dark:hover:bg-white/5 transition-all flex items-center justify-center text-xs uppercase tracking-widest shadow-sm hover:border-gray-300 dark:hover:border-white/30">
                                    View Details
                                </a>
                                
                                <template v-if="user">
                                    <Link v-if="user.role === 'admin' || (user.role === 'teacher' && course.teacher_id === user.id)"
                                        :href="route('teacher.courses.manage', course.id)"
                                        class="bg-[#111] text-white font-bold py-4 rounded-2xl hover:bg-black transition-all flex items-center justify-center text-xs uppercase tracking-widest shadow-lg shadow-gray-900/20 hover:shadow-xl transform active:scale-95">
                                        Manage Course
                                    </Link>
                                    
                                    <button v-else-if="user.role === 'student'" @click="enrollCourse(course.id)" 
                                        class="bg-[#ffde24] hover:bg-[#eacb1e] text-black font-bold py-4 rounded-2xl transition-all flex items-center justify-center text-xs uppercase tracking-widest shadow-lg shadow-yellow-500/30 hover:shadow-xl transform active:scale-95">
                                        Enroll Now
                                    </button>

                                    <button v-else disabled class="bg-gray-100 text-gray-400 font-bold py-4 rounded-2xl cursor-not-allowed flex items-center justify-center text-xs uppercase tracking-widest border-2 border-transparent">
                                        Private Course
                                    </button>
                                </template>

                                <Link v-else :href="route('login')" class="bg-[#ffde24] hover:bg-[#eacb1e] text-black font-bold py-4 rounded-2xl transition-all flex items-center justify-center text-xs uppercase tracking-widest shadow-lg shadow-yellow-500/30 hover:shadow-xl transform active:scale-95">
                                    Get Started
                                </Link>
                            </div>

                        </div>
                    </div>

                </div>

                <div v-else class="text-center py-24 bg-gray-50 dark:bg-[#111] rounded-[3rem] border-2 border-dashed border-gray-200 dark:border-white/5">
                    <span class="material-symbols-outlined text-6xl text-gray-200 mb-4">school</span>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Curriculums Coming Soon</h3>
                    <p class="text-gray-500 mt-2 text-sm font-medium">Our expert instructors are crafting the next generation of materials.</p>
                </div>

                <div class="mt-10 text-center sm:hidden">
                    <Link :href="route('register')" class="inline-flex items-center text-sm text-gray-900 font-bold hover:text-[#ffde24] transition-colors border-b-2 border-gray-900 pb-0.5">
                        View All Categories
                    </Link>
                </div>
            </div>
        </section>

        <section class="py-16 md:py-24 bg-[#fafafa] dark:bg-[#111]" id="about">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div v-if="latestChallenge" class="bg-[#111] rounded-[2rem] md:rounded-[3rem] p-8 sm:p-10 md:p-16 relative overflow-hidden text-center shadow-2xl">
                    <div class="absolute top-0 right-0 w-48 h-48 md:w-64 md:h-64 bg-[#ffde24]/20 rounded-full blur-[60px] md:blur-[80px]"></div>
                    
                    <div class="relative z-10">
                        <span class="inline-block py-1 px-3 md:py-1.5 md:px-4 rounded-full bg-white/10 border border-white/20 text-[#ffde24] text-[9px] md:text-[10px] font-black uppercase tracking-widest mb-4 md:mb-6 backdrop-blur-sm">
                            Weekly Challenge
                        </span>
                        <h2 class="text-2xl sm:text-3xl md:text-5xl font-black text-white mb-4 md:mb-6 tracking-tight leading-tight">
                            {{ latestChallenge.title }}
                        </h2>
                        <p class="text-sm sm:text-base md:text-lg text-gray-400 mb-8 md:mb-10 font-medium max-w-2xl mx-auto line-clamp-3 md:line-clamp-none">
                            {{ latestChallenge.description }}
                        </p>

                        <div class="flex flex-col sm:flex-row justify-center gap-3">
                            <Link v-if="user" :href="route('student.challenges')" class="inline-block bg-[#ffde24] hover:bg-[#eacb1e] text-black font-bold py-3.5 md:py-4 px-8 md:px-10 rounded-xl transition-all transform hover:-translate-y-1 text-xs md:text-sm uppercase tracking-wide shadow-lg shadow-yellow-500/20">
                                Join the Challenge
                            </Link>
                            <Link v-else :href="route('register')" class="inline-block bg-white hover:bg-gray-100 text-black font-bold py-3.5 md:py-4 px-8 md:px-10 rounded-xl transition-all transform hover:-translate-y-1 text-xs md:text-sm uppercase tracking-wide shadow-lg">
                                Register to Join
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white dark:bg-[#1a1a1a] rounded-[2rem] md:rounded-[3rem] p-8 md:p-16 border-2 border-dashed border-gray-200 dark:border-white/10 text-center shadow-sm">
                    <div class="w-16 h-16 md:w-20 md:h-20 bg-gray-50 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 md:mb-6">
                        <span class="material-symbols-outlined text-3xl md:text-4xl text-gray-400 dark:text-gray-500">emoji_events</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-black text-gray-900 dark:text-white mb-2 md:mb-3 tracking-tight">No Active Challenges</h2>
                    <p class="text-sm md:text-base text-gray-500 font-medium max-w-lg mx-auto leading-relaxed">
                        Our instructors are brewing up something exciting. Check back later for new weekly challenges to test your skills and earn extra XP!
                    </p>
                </div>

            </div>
        </section>

        <footer class="bg-white dark:bg-[#1a1a1a] border-t border-gray-200 dark:border-white/5 pt-12 md:pt-16 pb-6 md:pb-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-start gap-8 md:gap-10 mb-10 md:mb-12">
                    
                    <div class="max-w-xs w-full text-center md:text-left">
                        <div class="flex items-center justify-center md:justify-start gap-2 mb-3 md:mb-4">
                            <div class="w-8 h-8 md:w-10 md:h-10 bg-white border border-gray-200 rounded-lg md:rounded-xl flex items-center justify-center p-1 md:p-1.5 shadow-sm">
                                <img src="/images/icon/aspas-logo.svg" alt="Aspas Logo" class="w-full h-full object-contain" />
                            </div>
                            <span class="text-lg md:text-xl font-black text-gray-900 dark:text-white tracking-tight">Aspas.</span>
                        </div>
                        <p class="text-[11px] md:text-xs text-gray-500 leading-relaxed font-medium">
                            An adaptive learning platform built to help you master essential skills and gain a competitive edge.
                        </p>
                    </div>
                    
                    <div class="flex flex-row w-full md:w-auto justify-around md:justify-end gap-12 md:gap-16">
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white mb-3 md:mb-4 text-[11px] md:text-sm uppercase tracking-wider">Platform</h4>
                            <ul class="space-y-2 md:space-y-3 text-[11px] md:text-sm text-gray-500 font-medium">
                                <li><a href="#" class="hover:text-black dark:hover:text-white transition-colors">Courses</a></li>
                                <li><a href="#" class="hover:text-black dark:hover:text-white transition-colors">Challenges</a></li>
                                <li><a href="#" class="hover:text-black dark:hover:text-white transition-colors">Pricing</a></li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white mb-3 md:mb-4 text-[11px] md:text-sm uppercase tracking-wider">Legal</h4>
                            <ul class="space-y-2 md:space-y-3 text-[11px] md:text-sm text-gray-500 font-medium">
                                <li><a href="#" class="hover:text-black dark:hover:text-white transition-colors">Privacy</a></li>
                                <li><a href="#" class="hover:text-black dark:hover:text-white transition-colors">Terms</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-white/5 pt-6 md:pt-8 flex justify-center md:justify-between items-center text-[10px] md:text-xs font-bold text-gray-400 uppercase tracking-widest">
                    <p>&copy; 2026 Aspas.</p>
                </div>
            </div>
        </footer>

    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&display=swap');

.font-display {
    font-family: 'Lexend', sans-serif;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInLeft {
    from { opacity: 0; transform: translateX(20px); }
    to { opacity: 1; transform: translateX(0); }
}

.animate-fade-in-up { animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
.animate-fade-in-left { animation: fadeInLeft 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; opacity: 0; }

.delay-200 { animation-delay: 200ms; }
</style>