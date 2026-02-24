<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    course: Object,
    curriculum: Array
});

// --- STATE ---
const activeType = ref('lesson'); // 'lesson', 'quiz', 'project'
const activeChapterIndex = ref(0);
const activeItemIndex = ref(0);
const sidebarOpen = ref(true);

// --- COMPUTED ---
const currentItem = computed(() => {
    if (activeType.value === 'project') return props.course.project;
    
    const chapter = props.curriculum[activeChapterIndex.value];
    if (!chapter) return null;

    if (activeType.value === 'lesson') {
        return chapter.lessons ? chapter.lessons[activeItemIndex.value] : null;
    }
    
    if (activeType.value === 'quiz') {
        return chapter.quizzes ? chapter.quizzes[activeItemIndex.value] : null;
    }
    
    return null;
});

const getYoutubeId = (url) => {
    if(!url) return null;
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
};

// --- NAVIGATION FUNCTIONS ---
const selectLesson = (cIndex, lIndex) => {
    activeType.value = 'lesson';
    activeChapterIndex.value = cIndex;
    activeItemIndex.value = lIndex;
    scrollToTop();
};

const selectQuiz = (cIndex, qIndex) => {
    activeType.value = 'quiz';
    activeChapterIndex.value = cIndex;
    activeItemIndex.value = qIndex;
    scrollToTop();
};

const selectProject = () => {
    activeType.value = 'project';
    activeChapterIndex.value = -1;
    activeItemIndex.value = -1;
    scrollToTop();
};

const scrollToTop = () => {
    const main = document.getElementById('main-content');
    if(main) main.scrollTo({ top: 0, behavior: 'smooth' });
};

// Cek apakah ada item pertama kali load
const init = () => {
    if(props.curriculum.length > 0) {
        if(props.curriculum[0].lessons.length > 0) selectLesson(0, 0);
        else if(props.curriculum[0].quizzes.length > 0) selectQuiz(0, 0);
    } else if (props.course.project) {
        selectProject();
    }
};

init();
</script>

<template>
    <Head :title="`Preview: ${course.title}`" />
    
    <div class="flex h-screen bg-white font-sans overflow-hidden">
        
        <aside class="flex-shrink-0 bg-gray-50 border-r border-gray-200 flex flex-col transition-all duration-300" :class="sidebarOpen ? 'w-80' : 'w-0 overflow-hidden'">
            <div class="h-16 flex items-center px-6 border-b border-gray-200 bg-white">
                <Link :href="route('teacher.courses.manage', course.id)" class="flex items-center gap-2 text-gray-500 hover:text-black transition-colors group">
                    <span class="material-symbols-outlined text-xl group-hover:-translate-x-1 transition-transform">arrow_back</span>
                    <span class="font-bold text-sm">Back to Editor</span>
                </Link>
            </div>

            <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                <div v-for="(chapter, cIndex) in curriculum" :key="cIndex" class="mb-8">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-3 px-2 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
                        {{ chapter.title }}
                    </h3>
                    
                    <div class="space-y-1">
                        <button 
                            v-for="(lesson, lIndex) in chapter.lessons" 
                            :key="`l-${lIndex}`"
                            @click="selectLesson(cIndex, lIndex)"
                            class="w-full text-left flex items-start gap-3 px-3 py-3 rounded-xl transition-all"
                            :class="(activeType === 'lesson' && activeChapterIndex === cIndex && activeItemIndex === lIndex) 
                                ? 'bg-white shadow-sm border border-gray-200 text-blue-600' 
                                : 'text-gray-600 hover:bg-gray-200/50'"
                        >
                            <span class="material-symbols-outlined text-lg mt-0.5" 
                                :class="(activeType === 'lesson' && activeChapterIndex === cIndex && activeItemIndex === lIndex) ? 'filled' : ''">
                                play_circle
                            </span>
                            <div class="flex-1">
                                <p class="text-sm font-bold leading-tight">{{ lesson.title }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Lesson</p>
                            </div>
                        </button>

                        <button 
                            v-for="(quiz, qIndex) in chapter.quizzes" 
                            :key="`q-${qIndex}`"
                            @click="selectQuiz(cIndex, qIndex)"
                            class="w-full text-left flex items-start gap-3 px-3 py-3 rounded-xl transition-all"
                            :class="(activeType === 'quiz' && activeChapterIndex === cIndex && activeItemIndex === qIndex) 
                                ? 'bg-white shadow-sm border border-purple-200 text-purple-600' 
                                : 'text-gray-600 hover:bg-gray-200/50'"
                        >
                            <span class="material-symbols-outlined text-lg mt-0.5"
                                :class="(activeType === 'quiz' && activeChapterIndex === cIndex && activeItemIndex === qIndex) ? 'filled' : ''">
                                quiz
                            </span>
                            <div class="flex-1">
                                <p class="text-sm font-bold leading-tight">{{ quiz.title }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Quiz • {{ quiz.questions?.length || 0 }} Questions</p>
                            </div>
                        </button>
                    </div>
                </div>

                <div v-if="course.project" class="mt-4 pt-4 border-t border-gray-200">
                    <button 
                        @click="selectProject"
                        class="w-full text-left flex items-center gap-3 px-3 py-3 rounded-xl transition-all"
                        :class="activeType === 'project' 
                            ? 'bg-orange-50 border border-orange-200 text-orange-700' 
                            : 'text-gray-600 hover:bg-gray-100'"
                    >
                        <span class="material-symbols-outlined text-lg" :class="activeType === 'project' ? 'filled' : ''">emoji_events</span>
                        <div>
                            <p class="text-sm font-bold">Final Project</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Submission Required</p>
                        </div>
                    </button>
                </div>
            </div>
        </aside>

        <main class="flex-1 flex flex-col h-full relative w-full bg-white">
            
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 flex-shrink-0 z-10">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="p-2 hover:bg-gray-100 rounded-lg text-gray-600 transition-colors">
                        <span class="material-symbols-outlined">menu_open</span>
                    </button>
                    <h1 class="text-lg font-bold text-gray-800 line-clamp-1">{{ course.title }}</h1>
                </div>
                
                <div class="bg-orange-100 text-orange-800 px-4 py-1.5 rounded-full text-xs font-bold border border-orange-200 flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-sm">visibility</span>
                    PREVIEW MODE (Student View)
                </div>
            </header>

            <div id="main-content" class="flex-1 overflow-y-auto p-6 md:p-10 scroll-smooth">
                <div class="max-w-4xl mx-auto pb-20">
                    
                    <div v-if="currentItem">
                        
                        <div class="mb-8 border-b border-gray-100 pb-6">
                            <div class="flex items-center gap-2 mb-2">
                                <span v-if="activeType === 'lesson'" class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase">Lesson</span>
                                <span v-if="activeType === 'quiz'" class="bg-purple-100 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase">Quiz</span>
                                <span v-if="activeType === 'project'" class="bg-orange-100 text-orange-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase">Final Project</span>
                            </div>
                            <h2 class="text-3xl font-black text-gray-900 leading-tight">{{ currentItem.title }}</h2>
                            <p v-if="activeType === 'quiz'" class="text-gray-500 text-sm mt-2">
                                Passing Score: <span class="font-bold text-gray-900">{{ currentItem.min_score }}%</span>
                            </p>
                        </div>

                        <div v-if="activeType === 'lesson'" class="space-y-8 animate-fade-in">
                            <div v-for="(block, index) in currentItem.content" :key="index">
                                <div v-if="block.type === 'text'" class="prose prose-lg max-w-none text-gray-600 leading-relaxed whitespace-pre-line">
                                    {{ block.value }}
                                </div>
                                <div v-if="block.type === 'video' && getYoutubeId(block.value)" class="aspect-video rounded-2xl overflow-hidden shadow-lg bg-black">
                                    <iframe class="w-full h-full" :src="`https://www.youtube.com/embed/${getYoutubeId(block.value)}`" frameborder="0" allowfullscreen></iframe>
                                </div>
                                <div v-if="block.type === 'image'" class="rounded-2xl overflow-hidden border border-gray-100 shadow-sm">
                                    <img :src="block.value" class="w-full object-cover">
                                </div>
                                <div v-if="block.type === 'code'" class="bg-[#1e1e1e] rounded-xl overflow-hidden shadow-md text-sm font-mono border border-gray-700">
                                    <div class="bg-[#2d2d2d] px-4 py-2 flex justify-between items-center border-b border-gray-700">
                                        <span class="text-gray-400 text-xs uppercase">{{ block.value.language }}</span>
                                        <button class="text-gray-500 hover:text-white transition-colors"><span class="material-symbols-outlined text-sm">content_copy</span></button>
                                    </div>
                                    <pre class="p-4 text-gray-300 overflow-x-auto"><code>{{ block.value.code }}</code></pre>
                                </div>
                            </div>
                            <div v-if="!currentItem.content || currentItem.content.length === 0" class="py-12 text-center text-gray-400 border-2 border-dashed border-gray-100 rounded-2xl">
                                <span class="material-symbols-outlined text-4xl mb-2">subtitles_off</span>
                                <p>No content added to this lesson.</p>
                            </div>
                        </div>

                        <div v-if="activeType === 'quiz'" class="space-y-6 animate-fade-in">
                            <div v-if="currentItem.questions && currentItem.questions.length > 0" class="space-y-6">
                                <div v-for="(q, index) in currentItem.questions" :key="index" class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                                    <div class="flex gap-4">
                                        <div class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center font-bold text-gray-500 text-sm flex-shrink-0 shadow-sm">
                                            {{ index + 1 }}
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold text-gray-800 text-lg mb-4">{{ q.text }}</p>
                                            <div class="space-y-2">
                                                <div v-for="(opt, oIndex) in q.options" :key="oIndex" 
                                                    class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-white hover:border-purple-300 transition-colors cursor-pointer group">
                                                    <div class="w-5 h-5 rounded-full border-2 border-gray-300 group-hover:border-purple-500 flex items-center justify-center">
                                                        <div class="w-2.5 h-2.5 rounded-full bg-purple-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                                    </div>
                                                    <span class="text-gray-600 text-sm">{{ opt }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-end mt-6">
                                    <button class="bg-purple-600 text-white font-bold px-8 py-3 rounded-xl shadow-lg hover:bg-purple-700 transition-all">
                                        Submit Quiz (Preview)
                                    </button>
                                </div>
                            </div>
                            <div v-else class="py-12 text-center text-gray-400 border-2 border-dashed border-gray-100 rounded-2xl">
                                <span class="material-symbols-outlined text-4xl mb-2">quiz</span>
                                <p>No questions added to this quiz.</p>
                            </div>
                        </div>

                        <div v-if="activeType === 'project'" class="animate-fade-in">
                            <div class="bg-orange-50 p-6 rounded-2xl border border-orange-100 mb-8">
                                <h3 class="font-bold text-orange-900 mb-2 flex items-center gap-2">
                                    <span class="material-symbols-outlined">info</span> Instructions
                                </h3>
                                <div class="prose prose-sm text-orange-800 whitespace-pre-line">
                                    {{ currentItem.description || 'No description provided.' }}
                                </div>
                            </div>

                            <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
                                    <span class="material-symbols-outlined text-3xl">cloud_upload</span>
                                </div>
                                <h4 class="font-bold text-gray-900">Upload your work</h4>
                                <p class="text-sm text-gray-500 mb-6">Supported files: ZIP, PDF, PNG. Max 10MB.</p>
                                
                                <button class="bg-black text-white font-bold px-6 py-2.5 rounded-xl shadow-md hover:bg-gray-800 transition-all cursor-not-allowed opacity-80" disabled>
                                    Select File (Disabled in Preview)
                                </button>
                            </div>
                        </div>

                    </div>

                    <div v-else class="flex flex-col items-center justify-center py-20 text-center">
                        <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-4xl text-blue-500">school</span>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Ready to Preview?</h2>
                        <p class="text-gray-500 mt-2">Select an item from the sidebar to start reviewing.</p>
                    </div>

                </div>
            </div>

        </main>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 5px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
.material-symbols-outlined.filled { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
.animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>