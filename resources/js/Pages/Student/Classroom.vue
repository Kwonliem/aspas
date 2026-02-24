<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';


import hljs from 'highlight.js';
import 'highlight.js/styles/vs2015.css'; 

const props = defineProps({
    course: Object,
    progress: Number,
    expiredAt: String,
    completedData: {
        type: [Object, String],
        default: () => ({ lessons: [], quizzes: [], project_status: null, project_link: null })
    }
});

const isDark = ref(false);
const toggleDarkMode = () => isDark.value = !isDark.value;
const showSidebar = ref(false);

const chapters = ref([]);
const activeType = ref('lesson');
const activeItem = ref(null);
const openChapters = ref([]);

const quizAnswers = ref({});

const toast = ref({ show: false, message: '', type: 'info' });
let toastTimeout = null;

const showCustomAlert = (message, type = 'info') => {
    toast.value = { show: true, message, type };
    if (toastTimeout) clearTimeout(toastTimeout);
    
    
    toastTimeout = setTimeout(() => {
        toast.value.show = false;
    }, 3500); 
};

const localCompleted = ref({ lessons: [], quizzes: [], project_status: null, project_link: null });

watch(() => props.completedData, (newVal) => {
    let data = newVal;
    if (typeof data === 'string') {
        try { data = JSON.parse(data); } catch (e) { data = {}; }
    }
    localCompleted.value = {
        lessons: Array.isArray(data?.lessons) ? data.lessons.map(Number) : [],
        quizzes: Array.isArray(data?.quizzes) ? data.quizzes.map(Number) : [],
        project_status: data?.project_status || null,
        project_link: data?.project_link || null
    };
}, { immediate: true, deep: true });

const projectLinkInput = ref(localCompleted.value.project_link || '');

const checkIsCompleted = (item, type) => {
    if (!item) return false;
    const itemId = Number(item.id);
    if (type === 'lesson') return localCompleted.value.lessons.includes(itemId);
    if (type === 'quiz') return localCompleted.value.quizzes.includes(itemId);
    if (type === 'project') return localCompleted.value.project_status === 'passed';
    return false;
};

const flatCurriculum = computed(() => {
    let items = [];
    chapters.value.forEach(c => {
        c.lessons?.forEach(l => items.push({ ...l, _type: 'lesson', chapter_id: c.id }));
        c.quizzes?.forEach(q => items.push({ ...q, _type: 'quiz', chapter_id: c.id }));
    });
    if (props.course.project) items.push({ ...props.course.project, _type: 'project' });
    return items;
});

const isLocked = (targetItem, targetType) => {
    if (!targetItem) return false;
    const targetIdx = flatCurriculum.value.findIndex(i => i.id === targetItem.id && i._type === targetType);
    if (targetIdx <= 0) return false;
    const prevItem = flatCurriculum.value[targetIdx - 1];
    return !checkIsCompleted(prevItem, prevItem._type);
};

onMounted(() => {
    if (props.course && props.course.chapters) {
        chapters.value = props.course.chapters;
        if (chapters.value.length > 0) {
            const firstUncompleted = flatCurriculum.value.find(item => !checkIsCompleted(item, item._type));
            if (firstUncompleted) {
                if (firstUncompleted._type === 'lesson') selectLesson(firstUncompleted);
                else if (firstUncompleted._type === 'quiz') selectQuiz(firstUncompleted);
                else if (firstUncompleted._type === 'project') selectProject();
                openChapters.value = [firstUncompleted.chapter_id || chapters.value[0].id];
            } else if (flatCurriculum.value.length > 0) {
                const last = flatCurriculum.value[flatCurriculum.value.length - 1];
                if (last._type === 'lesson') selectLesson(last);
                else if (last._type === 'quiz') selectQuiz(last);
                else if (last._type === 'project') selectProject();
            }
        }
    }
});

const totalLessons = computed(() => flatCurriculum.value.length);

const currentProgress = computed(() => {
    if (totalLessons.value === 0) return 0;
    let completedCount = localCompleted.value.lessons.length + localCompleted.value.quizzes.length;
    if (localCompleted.value.project_status === 'passed') completedCount += 1;
    completedCount = Math.min(completedCount, totalLessons.value);
    return Math.round((completedCount / totalLessons.value) * 100);
});

const parsedContent = computed(() => {
    if (activeType.value !== 'lesson' || !activeItem.value?.content) return [];
    let data = activeItem.value.content;
    if (typeof data === 'string') { try { data = JSON.parse(data); } catch (e) { return []; } }
    return Array.isArray(data) ? data : [];
});

const parsedQuestions = computed(() => {
    if (activeType.value !== 'quiz' || !activeItem.value?.questions) return [];
    let data = activeItem.value.questions;
    if (typeof data === 'string') { try { data = JSON.parse(data); } catch (e) { return []; } }
    return Array.isArray(data) ? data : [];
});

watch(parsedQuestions, (newQs) => {
    if (activeType.value === 'quiz') {
        const initial = {};
        newQs.forEach((_, i) => initial[i] = null);
        quizAnswers.value = initial;
    }
}, { immediate: true });

const currentIndex = computed(() => flatCurriculum.value.findIndex(i => i.id === activeItem.value?.id && i._type === activeType.value));
const hasNext = computed(() => currentIndex.value < flatCurriculum.value.length - 1);
const hasPrev = computed(() => currentIndex.value > 0);

const toggleChapter = (chapterId) => {
    const index = openChapters.value.indexOf(chapterId);
    if (index === -1) openChapters.value.push(chapterId);
    else openChapters.value.splice(index, 1);
};

const selectLesson = (lesson) => {
    if (isLocked(lesson, 'lesson')) return showCustomAlert("Selesaikan materi sebelumnya terlebih dahulu.");
    activeType.value = 'lesson'; activeItem.value = lesson; showSidebar.value = false;
};

const selectQuiz = (quiz) => {
    if (isLocked(quiz, 'quiz')) return showCustomAlert("Selesaikan pelajaran sebelumnya terlebih dahulu.");
    activeType.value = 'quiz'; activeItem.value = quiz; showSidebar.value = false;
};

const selectProject = () => {
    if (isLocked(props.course.project, 'project')) return showCustomAlert("Lulus semua kuis untuk membuka Project.");
    activeType.value = 'project'; activeItem.value = props.course.project; showSidebar.value = false;
};

const goToNext = () => {
    if (hasNext.value) {
        const next = flatCurriculum.value[currentIndex.value + 1];
        if (isLocked(next, next._type)) return showCustomAlert("Selesaikan materi ini terlebih dahulu untuk lanjut.");
        if (next._type === 'lesson') selectLesson(next);
        else if (next._type === 'quiz') selectQuiz(next);
        else if (next._type === 'project') selectProject();
        if (next.chapter_id && !openChapters.value.includes(next.chapter_id)) {
            openChapters.value.push(next.chapter_id);
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const goToPrev = () => {
    if (hasPrev.value) {
        const prev = flatCurriculum.value[currentIndex.value - 1];
        if (prev._type === 'lesson') selectLesson(prev);
        else if (prev._type === 'quiz') selectQuiz(prev);
        else if (prev._type === 'project') selectProject();
        if (prev.chapter_id && !openChapters.value.includes(prev.chapter_id)) {
            openChapters.value.push(prev.chapter_id);
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const isProcessing = ref(false);

const markLessonComplete = () => {
    if (checkIsCompleted(activeItem.value, 'lesson')) { goToNext(); return; }
    const currentId = Number(activeItem.value.id);
    localCompleted.value.lessons.push(currentId);
    router.post(route('classroom.complete', props.course.id), {
        type: 'lesson', item_id: currentId
    }, { preserveScroll: true, preserveState: true });
    goToNext();
};

const submitQuiz = () => {
    if (checkIsCompleted(activeItem.value, 'quiz')) { goToNext(); return; }
    let answeredCount = 0;
    for (let key in quizAnswers.value) { if (quizAnswers.value[key] !== null) answeredCount++; }
    if (answeredCount < parsedQuestions.value.length) return showCustomAlert('Tolong jawab semua pertanyaan sebelum dikumpulkan.');

    let correct = 0;
    parsedQuestions.value.forEach((q, i) => {
        const userAnswer = String(quizAnswers.value[i]).trim();
        const correctAnswer = String(q.correct_index).trim();
        if (userAnswer === correctAnswer) correct++;
    });

    const score = Math.round((correct / parsedQuestions.value.length) * 100);

    if (score >= activeItem.value.min_score) {
        showCustomAlert(`Selamat! Anda lulus kuis dengan nilai ${score}%!`);
        const currentId = Number(activeItem.value.id);
        localCompleted.value.quizzes.push(currentId);
        router.post(route('classroom.complete', props.course.id), {
            type: 'quiz', item_id: currentId
        }, { preserveScroll: true, preserveState: true });
        goToNext();
    } else {
        showCustomAlert(`Nilai Anda ${score}%. Dibutuhkan minimal ${activeItem.value.min_score}% untuk lulus.`);
    }
};

const submitProject = () => {
    if (!projectLinkInput.value) return showCustomAlert("Tolong masukkan link project Anda.");
    isProcessing.value = true;
    router.post(route('classroom.complete', props.course.id), {
        type: 'project', link: projectLinkInput.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isProcessing.value = false;
            showCustomAlert('Project berhasil dikumpulkan! Menunggu review guru.');
        }
    });
};

const getSidebarIcon = (item, type) => {
    if (isLocked(item, type)) return 'lock';
    if (checkIsCompleted(item, type)) return 'check_circle';
    return 'radio_button_unchecked';
};

const getYoutubeId = (url) => {
    if (!url) return null;
    const match = url.match(/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/);
    return (match && match[2].length === 11) ? match[2] : null;
};



const highlightCode = (code, lang) => {
    if (!code) return '';
    try {
        if (lang && hljs.getLanguage(lang)) {
            return hljs.highlight(code, { language: lang }).value;
        }
        return hljs.highlightAuto(code).value;
    } catch (e) {
        return code;
    }
};
</script>

<template>

    <Head :title="`${activeItem?.title || 'Classroom'} - ${course.title}`" />

    <div :class="{ 'dark': isDark }" class="h-screen w-full font-sans flex flex-col bg-white dark:bg-[#111111]">

        <header
            class="h-16 bg-white dark:bg-[#111111] border-b border-gray-200 dark:border-white/10 flex items-center justify-between px-4 lg:px-6 z-30 shrink-0 relative">
            <div class="flex items-center gap-4">
                <button @click="showSidebar = !showSidebar"
                    class="md:hidden p-2 text-gray-600 hover:bg-gray-100 rounded">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <Link :href="route('classroom.my-courses')"
                    class="flex items-center gap-3 text-gray-800 dark:text-white hover:text-black transition-colors group">
                    <div
                        class="w-8 h-8 rounded bg-gray-100 dark:bg-white/10 flex items-center justify-center group-hover:bg-gray-200 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">home</span>
                    </div>
                    <span class="font-bold text-[15px] hidden sm:block tracking-tight">Aspas</span>
                </Link>
                <div class="h-5 w-px bg-gray-300 dark:bg-white/20 mx-2 hidden sm:block"></div>
                <h1
                    class="text-[14px] font-medium text-gray-600 dark:text-gray-300 hidden md:block line-clamp-1 max-w-sm">
                    {{ course.title }}
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <div v-if="expiredAt"
                    class="hidden sm:flex items-center gap-1.5 px-3 py-1 bg-red-50 text-red-600 rounded text-xs font-bold border border-red-100">
                    <span class="material-symbols-outlined text-[14px]">timer</span>
                    {{ expiredAt }}
                </div>
                <button @click="toggleDarkMode"
                    class="w-9 h-9 rounded flex items-center justify-center text-gray-500 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                    <span class="material-symbols-outlined dark:hidden text-[20px]">dark_mode</span>
                    <span class="material-symbols-outlined hidden dark:block text-[20px]">light_mode</span>
                </button>
            </div>
        </header>

        <div class="flex flex-1 h-full overflow-hidden relative">

            <aside :class="showSidebar ? 'translate-x-0 shadow-2xl' : '-translate-x-full md:translate-x-0'"
                class="w-[320px] bg-white dark:bg-[#111111] border-r border-gray-200 dark:border-white/10 flex flex-col shrink-0 transition-transform duration-300 absolute md:relative z-20 h-full">

                <div class="p-5 border-b border-gray-100 dark:border-white/5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-gray-500">Course Progress</span>
                        <span class="text-xs font-bold text-green-600">{{ currentProgress }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-white/5 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-green-500 h-full rounded-full transition-all duration-1000 ease-out"
                            :style="`width: ${currentProgress}%`"></div>
                    </div>
                    <p class="text-[10px] text-gray-400 mt-2">{{ localCompleted.lessons.length +
                        localCompleted.quizzes.length + (localCompleted.project_status === 'passed' ? 1 : 0) }} dari {{
                            totalLessons }} pelajaran selesai</p>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar py-2">
                    <div v-for="chapter in chapters" :key="chapter.id"
                        class="border-b border-gray-50 dark:border-white/5 last:border-0">
                        <button @click="toggleChapter(chapter.id)"
                            class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                            <div class="flex flex-col gap-1 pr-4">
                                <span
                                    class="text-[14px] font-bold text-gray-800 dark:text-gray-200 group-hover:text-black dark:group-hover:text-white transition-colors">
                                    {{ chapter.title }}
                                </span>
                                <span class="text-[10px] font-medium text-gray-400">
                                    {{ chapter.lessons?.length || 0 }} Lessons • {{ chapter.quizzes?.length || 0 }} Quiz
                                </span>
                            </div>
                            <span
                                class="material-symbols-outlined text-gray-400 text-[18px] transition-transform duration-300"
                                :class="{ 'rotate-180': !openChapters.includes(chapter.id) }">expand_more</span>
                        </button>

                        <div v-show="openChapters.includes(chapter.id)" class="animate-fade-in pb-2">
                            <button v-for="lesson in chapter.lessons" :key="'l' + lesson.id"
                                @click="selectLesson(lesson)"
                                class="w-full flex items-center py-2.5 px-4 text-left transition-all duration-200 relative group"
                                :class="[
                                    (activeType === 'lesson' && activeItem?.id === lesson.id) ? 'bg-[#f8f9fa] dark:bg-white/5' : 'hover:bg-gray-50 dark:hover:bg-white/5',
                                    isLocked(lesson, 'lesson') ? 'opacity-50 cursor-not-allowed grayscale' : ''
                                ]">

                                <div v-if="activeType === 'lesson' && activeItem?.id === lesson.id"
                                    class="absolute left-0 top-0 bottom-0 w-1 bg-gray-800 dark:bg-gray-200"></div>

                                <span class="material-symbols-outlined text-[16px] mr-3 transition-colors" :class="[
                                    isLocked(lesson, 'lesson') ? 'text-gray-300' :
                                        checkIsCompleted(lesson, 'lesson') ? 'text-green-500' : 'text-gray-400 group-hover:text-gray-600'
                                ]">
                                    {{ getSidebarIcon(lesson, 'lesson') }}
                                </span>
                                <p class="text-[13px] line-clamp-2"
                                    :class="(activeType === 'lesson' && activeItem?.id === lesson.id) ? 'font-bold text-gray-900 dark:text-white' : 'font-normal text-gray-600 dark:text-gray-400'">
                                    {{ lesson.title }}
                                </p>
                            </button>

                            <button v-for="quiz in chapter.quizzes" :key="'q' + quiz.id" @click="selectQuiz(quiz)"
                                class="w-full flex items-center py-2.5 px-4 text-left transition-all duration-200 relative group"
                                :class="[
                                    (activeType === 'quiz' && activeItem?.id === quiz.id) ? 'bg-[#f8f9fa] dark:bg-white/5' : 'hover:bg-gray-50 dark:hover:bg-white/5',
                                    isLocked(quiz, 'quiz') ? 'opacity-50 cursor-not-allowed grayscale' : ''
                                ]">

                                <div v-if="activeType === 'quiz' && activeItem?.id === quiz.id"
                                    class="absolute left-0 top-0 bottom-0 w-1 bg-gray-800 dark:bg-gray-200"></div>

                                <span class="material-symbols-outlined text-[16px] mr-3 transition-colors" :class="[
                                    isLocked(quiz, 'quiz') ? 'text-gray-300' :
                                        checkIsCompleted(quiz, 'quiz') ? 'text-green-500' : 'text-gray-400 group-hover:text-gray-600'
                                ]">
                                    {{ getSidebarIcon(quiz, 'quiz') === 'radio_button_unchecked' ? 'help_outline' :
                                        getSidebarIcon(quiz, 'quiz') }}
                                </span>
                                <p class="text-[13px] line-clamp-2"
                                    :class="(activeType === 'quiz' && activeItem?.id === quiz.id) ? 'font-bold text-gray-900 dark:text-white' : 'font-normal text-gray-600 dark:text-gray-400'">
                                    Quiz: {{ quiz.title }}
                                </p>
                            </button>
                        </div>
                    </div>

                    <div v-if="course.project" class="border-t border-gray-100 dark:border-white/5 mt-4">
                        <button @click="selectProject()"
                            class="w-full flex items-center p-4 text-left transition-all duration-200 relative group"
                            :class="[
                                activeType === 'project' ? 'bg-[#f8f9fa] dark:bg-white/5' : 'hover:bg-gray-50 dark:hover:bg-white/5',
                                isLocked(course.project, 'project') ? 'opacity-50 cursor-not-allowed grayscale' : ''
                            ]">

                            <div v-if="activeType === 'project'" class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500">
                            </div>

                            <span class="material-symbols-outlined text-[18px] mr-3 transition-colors" :class="[
                                isLocked(course.project, 'project') ? 'text-gray-300' :
                                    checkIsCompleted(course.project, 'project') ? 'text-green-500' : 'text-blue-500'
                            ]">
                                {{ getSidebarIcon(course.project, 'project') === 'radio_button_unchecked' ?
                                    'rocket_launch' : getSidebarIcon(course.project, 'project') }}
                            </span>
                            <div class="flex-1">
                                <p class="text-[13px] line-clamp-1"
                                    :class="activeType === 'project' ? 'font-bold text-gray-900 dark:text-white' : 'font-normal text-gray-600 dark:text-gray-400'">
                                    {{ course.project.title }}
                                </p>
                                <p class="text-[10px] font-bold mt-0.5"
                                    :class="localCompleted.project_status === 'passed' ? 'text-green-600' : (localCompleted.project_status === 'pending' ? 'text-yellow-600' : 'text-gray-400')">
                                    {{ localCompleted.project_status === 'pending' ? 'Pending Review' :
                                        (localCompleted.project_status === 'passed' ? 'Passed' : 'Final Project') }}
                                </p>
                            </div>
                        </button>
                    </div>
                </div>
            </aside>

            <div v-if="showSidebar" @click="showSidebar = false"
                class="md:hidden fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-10"></div>

            <main
                class="flex-1 h-full overflow-y-auto scroll-smooth relative custom-scrollbar bg-white dark:bg-[#0a0a0a] text-gray-800 dark:text-gray-200">
                <div v-if="activeItem"
                    class="w-full mx-auto px-4 sm:px-6 md:px-8 py-10 md:py-14 min-h-full flex flex-col">

                    <div class="mb-10">
                        <h1
                            class="text-3xl md:text-[40px] font-bold text-black dark:text-white leading-[1.3] tracking-tight">
                            {{ activeItem.title }}
                        </h1>
                        <div class="w-16 h-1 bg-[#FFDE21] mt-6"></div>
                    </div>

                    <div v-if="activeType === 'lesson'" class="flex-1">
                        <div v-if="parsedContent.length > 0" class="space-y-8">
                            <div v-for="(block, index) in parsedContent" :key="index" class="animate-fade-in-up"
                                :style="`animation-delay: ${index * 0.05}s`">

                                <div v-if="block.type === 'text'"
                                    class="prose prose-slate dark:prose-invert max-w-none text-[16px] md:text-[17px] leading-[1.8] text-gray-700 dark:text-gray-300"
                                    v-text="block.value"></div>

                                <div v-else-if="block.type === 'code'"
                                    class="bg-[#1e1e1e] rounded-xl shadow-lg overflow-hidden my-8 group">
                                    <div class="bg-[#2d2d2d] px-4 py-2 flex items-center justify-between">
                                        <span class="text-[11px] font-mono text-gray-400 uppercase tracking-widest">{{
                                            block.value.language }}</span>
                                    </div>

                                    <div
                                        class="p-5 overflow-x-auto text-[14px] leading-relaxed font-mono text-gray-300 custom-scrollbar custom-code-area">
                                        <pre
                                            style="margin:0; padding:0;"><code class="hljs" style="background: transparent; padding: 0;" v-html="highlightCode(block.value.code, block.value.language)"></code></pre>
                                    </div>
                                </div>

                                <div v-else-if="block.type === 'video' && block.value"
                                    class="aspect-video bg-black rounded-xl overflow-hidden shadow-md my-8">
                                    <iframe v-if="getYoutubeId(block.value)"
                                        :src="`https://www.youtube.com/embed/${getYoutubeId(block.value)}`"
                                        class="w-full h-full border-0" allowfullscreen></iframe>
                                    <video v-else controls class="w-full h-full object-cover">
                                        <source :src="block.value">
                                    </video>
                                </div>

                                <div v-else-if="block.type === 'image' && block.value"
                                    class="my-8 rounded-xl overflow-hidden bg-gray-50 border border-gray-100">
                                    <img :src="block.value" class="w-full max-h-[600px] object-contain">
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-gray-500 italic py-10">Materi belum ditambahkan.</div>
                    </div>

                    <div v-else-if="activeType === 'quiz'" class="flex-1 space-y-10">

                        <div
                            class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-bold text-black dark:text-white">Knowledge Check</h3>
                                <p class="text-sm text-gray-600 mt-1">Uji pemahamanmu sebelum melanjutkan.</p>
                            </div>
                            <div class="text-right">
                                <span
                                    class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest">Passing
                                    Score</span>
                                <span class="text-2xl font-black text-black dark:text-white">{{ activeItem.min_score
                                    }}%</span>
                            </div>
                        </div>

                        <div v-if="checkIsCompleted(activeItem, 'quiz')"
                            class="bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800 rounded-3xl p-10 text-center animate-fade-in-up">
                            <div
                                class="w-20 h-20 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center mx-auto mb-6">
                                <span
                                    class="material-symbols-outlined text-4xl text-green-600 dark:text-green-400">task_alt</span>
                            </div>
                            <h3 class="text-2xl font-black text-green-800 dark:text-green-400 mb-2">Kamu Telah Lulus
                                Kuis Ini!</h3>
                            <p class="text-green-700 dark:text-green-500 font-medium">Bagus sekali, pemahamanmu tentang
                                materi ini sudah teruji.</p>
                            <div class="mt-8 flex justify-center">
                                <button @click="goToNext"
                                    class="px-8 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-colors shadow-lg shadow-green-600/30 flex items-center gap-2">
                                    Lanjut ke Materi Selanjutnya <span
                                        class="material-symbols-outlined text-[20px]">arrow_forward</span>
                                </button>
                            </div>
                        </div>

                        <div v-else-if="parsedQuestions.length > 0" class="space-y-10">
                            <div v-for="(q, index) in parsedQuestions" :key="index">
                                <h4
                                    class="text-[16px] font-bold text-black dark:text-white mb-4 leading-relaxed flex items-start gap-3">
                                    <span class="text-gray-400">{{ index + 1 }}.</span>
                                    <span>{{ q.text }}</span>
                                </h4>
                                <div class="space-y-3 pl-7">
                                    <label v-for="(opt, oIndex) in q.options" :key="oIndex"
                                        class="flex items-center gap-4 p-4 rounded-xl border cursor-pointer transition-all duration-200"
                                        :class="quizAnswers[index] === oIndex ? 'border-gray-800 bg-gray-50 dark:bg-white/10 dark:border-gray-200' : 'border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5'">
                                        <div class="relative flex items-center justify-center w-5 h-5 shrink-0">
                                            <input type="radio" :name="`quiz_${activeItem.id}_q_${index}`"
                                                :value="oIndex" v-model="quizAnswers[index]"
                                                class="peer absolute opacity-0 w-full h-full cursor-pointer">
                                            <div class="w-full h-full rounded-full border-2 transition-colors flex items-center justify-center"
                                                :class="quizAnswers[index] === oIndex ? 'border-gray-800 dark:border-white' : 'border-gray-300'">
                                                <div class="w-2.5 h-2.5 rounded-full bg-gray-800 dark:bg-white transform scale-0 transition-transform"
                                                    :class="quizAnswers[index] === oIndex ? 'scale-100' : ''"></div>
                                            </div>
                                        </div>
                                        <span class="text-[15px] font-medium transition-colors"
                                            :class="quizAnswers[index] === oIndex ? 'text-black dark:text-white' : 'text-gray-700 dark:text-gray-300'">{{
                                            opt }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else-if="activeType === 'project'" class="flex-1 space-y-10">
                        <div class="prose prose-slate dark:prose-invert max-w-none text-[16px] leading-[1.8] text-gray-700 mb-10 whitespace-pre-wrap"
                            v-text="activeItem.description"></div>

                        <div v-if="localCompleted.project_status"
                            class="p-6 rounded-2xl border bg-gray-50 dark:bg-white/5"
                            :class="localCompleted.project_status === 'passed' ? 'border-green-200' : 'border-yellow-200'">
                            <h3 class="text-lg font-bold mb-2"
                                :class="localCompleted.project_status === 'passed' ? 'text-green-700' : 'text-yellow-700'">
                                {{ localCompleted.project_status === 'passed' ? 'Project Diterima!' : 'Submission Sedang Direview' }}
                            </h3>
                            <p class="text-sm mb-4"
                                :class="localCompleted.project_status === 'passed' ? 'text-green-600' : 'text-yellow-600'">
                                {{ localCompleted.project_status === 'passed' ? 'Instruktur Anda telah menyatakan Anda lulus project ini.' : "Instruktur sedang memeriksa project yang Anda kumpulkan." }}
                            </p>
                            <div class="flex gap-3">
                                <a :href="localCompleted.project_link" target="_blank"
                                    class="text-sm font-bold bg-white px-4 py-2 rounded-lg border shadow-sm hover:bg-gray-50">Lihat
                                    Submission</a>
                                <a v-if="localCompleted.project_status === 'passed'"
                                    :href="route('classroom.certificate', course.id)" target="_blank"
                                    class="text-sm font-bold text-white bg-green-600 px-4 py-2 rounded-lg shadow-sm hover:bg-green-700">Download
                                    Sertifikat</a>
                            </div>
                        </div>

                        <div v-else
                            class="border border-gray-200 dark:border-white/10 rounded-2xl p-6 md:p-8 bg-gray-50 dark:bg-white/5">
                            <h3 class="text-lg font-bold text-black dark:text-white mb-2">Submit Project</h3>
                            <p class="text-[14px] text-gray-600 mb-6">Tempelkan link ke repositori atau live project
                                Anda.</p>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Project
                                URL</label>
                            <input v-model="projectLinkInput" type="url"
                                class="w-full bg-white border border-gray-300 rounded-lg py-3 px-4 text-[15px] focus:border-gray-800 focus:ring-1 focus:ring-gray-800 outline-none transition-all"
                                placeholder="https://github.com/username/repo">
                        </div>
                    </div>

                    <div
                        class="mt-16 pt-6 border-t border-gray-200 dark:border-white/10 flex items-center justify-between">
                        <button @click="goToPrev" :disabled="!hasPrev"
                            class="px-5 py-2.5 rounded-lg text-gray-500 font-bold hover:bg-gray-100 dark:hover:bg-white/10 disabled:opacity-30 disabled:hover:bg-transparent transition-colors flex items-center gap-2 text-sm">
                            <span class="material-symbols-outlined text-[18px]">arrow_back</span> Sebelumnya
                        </button>

                        <button v-if="activeType === 'lesson'" @click="markLessonComplete" :disabled="isProcessing"
                            class="px-6 py-2.5 rounded-lg font-bold transition-all flex items-center gap-2 text-sm shadow-sm"
                            :class="checkIsCompleted(activeItem, 'lesson') ? 'bg-gray-100 text-gray-600 border border-gray-200' : 'bg-[#FFDE21] text-black hover:bg-[#ebd01f]'">
                            {{ checkIsCompleted(activeItem, 'lesson') ? 'Selanjutnya' : 'Tandai Selesai' }}
                            <span class="material-symbols-outlined text-[18px]">{{ checkIsCompleted(activeItem,
                                'lesson') ? 'arrow_forward' : 'check' }}</span>
                        </button>

                        <button v-else-if="activeType === 'quiz'" @click="submitQuiz" :disabled="isProcessing"
                            class="px-6 py-2.5 rounded-lg font-bold transition-all flex items-center gap-2 text-sm shadow-sm"
                            :class="checkIsCompleted(activeItem, 'quiz') ? 'bg-gray-100 text-gray-600 border border-gray-200' : 'bg-gray-900 text-white hover:bg-black'">
                            {{ checkIsCompleted(activeItem, 'quiz') ? 'Selanjutnya' : 'Kirim Jawaban' }}
                            <span class="material-symbols-outlined text-[18px]">{{ checkIsCompleted(activeItem, 'quiz')
                                ? 'arrow_forward' : 'send' }}</span>
                        </button>

                        <button v-else-if="activeType === 'project' && !localCompleted?.project_status"
                            @click="submitProject" :disabled="isProcessing"
                            class="px-6 py-2.5 rounded-lg bg-gray-900 text-white font-bold hover:bg-black transition-all flex items-center gap-2 text-sm shadow-sm">
                            Submit Project
                        </button>
                    </div>

                </div>
            </main>
        </div>
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform -translate-y-10 opacity-0 scale-95"
            enter-to-class="transform translate-y-0 opacity-100 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="transform translate-y-0 opacity-100 scale-100"
            leave-to-class="transform -translate-y-10 opacity-0 scale-95"
        >
            <div v-if="toast.show" class="fixed top-8 left-1/2 -translate-x-1/2 z-[100] flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-xl border backdrop-blur-md"
                :class="{
                    'bg-white/90 border-gray-200 text-gray-800 dark:bg-gray-800/90 dark:border-gray-700 dark:text-white shadow-gray-200/50 dark:shadow-black/50': toast.type === 'info',
                    'bg-green-50/95 border-green-200 text-green-800 dark:bg-green-900/90 dark:border-green-800 dark:text-green-100 shadow-green-500/20': toast.type === 'success',
                    'bg-red-50/95 border-red-200 text-red-800 dark:bg-red-900/90 dark:border-red-800 dark:text-red-100 shadow-red-500/20': toast.type === 'error',
                    'bg-yellow-50/95 border-yellow-200 text-yellow-800 dark:bg-yellow-900/90 dark:border-yellow-800 dark:text-yellow-100 shadow-yellow-500/20': toast.type === 'warning',
                }">
                <span class="material-symbols-outlined text-[20px]"
                    :class="{
                        'text-blue-500': toast.type === 'info',
                        'text-green-500': toast.type === 'success',
                        'text-red-500': toast.type === 'error',
                        'text-yellow-600': toast.type === 'warning'
                    }">
                    {{ toast.type === 'success' ? 'check_circle' : toast.type === 'error' ? 'error' : toast.type === 'warning' ? 'lock' : 'info' }}
                </span>
                <span class="text-sm font-bold tracking-wide">{{ toast.message }}</span>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.font-sans {
    font-family: 'Inter', sans-serif;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.3);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.6);
}


.custom-code-area,
.custom-code-area pre,
.custom-code-area code {
    user-select: none !important;
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
}

.prose p {
    margin-bottom: 1.5em;
}

.prose h1,
.prose h2,
.prose h3 {
    margin-top: 1.8em;
    margin-bottom: 0.8em;
    letter-spacing: -0.01em;
    font-weight: 700;
    color: #111;
}

.prose ul {
    margin-top: 1em;
    margin-bottom: 1em;
    padding-left: 1.5em;
    list-style-type: disc;
}

.prose li {
    margin-bottom: 0.5em;
}

.dark .prose h1,
.dark .prose h2,
.dark .prose h3 {
    color: #f3f4f6;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.4s ease-out forwards;
    opacity: 0;
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}

input[type="radio"]:checked {
    background-image: none;
}
</style>