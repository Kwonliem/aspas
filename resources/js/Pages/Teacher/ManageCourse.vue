<script setup>
import { Head, router, Link, useForm } from '@inertiajs/vue3';
import { ref, nextTick, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    course: Object,
    chapters: Array,
    project: Object
});

// STATE
const curriculum = ref(props.chapters && props.chapters.length ? props.chapters : []);
const hasProject = ref(!!props.project);
const projectData = ref(props.project || { title: 'Final Project Submission', description: '' });

const activeType = ref('lesson');
const activeChapterIndex = ref(0);
const activeItemIndex = ref(0);
const isSaving = ref(false);

// INIT DEFAULT IF EMPTY
if (curriculum.value.length === 0) {
    curriculum.value.push({ id: null, title: 'Introduction', lessons: [], quizzes: [] });
}

// HELPER
const getActiveItem = () => {
    if (activeType.value === 'project') return projectData.value;
    const chapter = curriculum.value[activeChapterIndex.value];
    if (!chapter) return null;
    if (activeType.value === 'lesson') {
        if (!chapter.lessons) chapter.lessons = [];
        return chapter.lessons[activeItemIndex.value];
    }
    if (activeType.value === 'quiz') {
        if (!chapter.quizzes) chapter.quizzes = [];
        return chapter.quizzes[activeItemIndex.value];
    }
    return null;
};

// NAVIGATION
const selectLesson = (cIndex, lIndex) => {
    activeType.value = 'lesson';
    activeChapterIndex.value = cIndex;
    activeItemIndex.value = lIndex;
};
const selectQuiz = (cIndex, qIndex) => {
    activeType.value = 'quiz';
    activeChapterIndex.value = cIndex;
    activeItemIndex.value = qIndex;
};
const selectProject = () => {
    activeType.value = 'project';
    activeChapterIndex.value = -1;
    activeItemIndex.value = -1;
};

// ACTIONS: PROJECT
const enableProject = () => {
    hasProject.value = true;
    if (!projectData.value.title) {
        projectData.value = { title: 'Final Project Submission', description: '' };
    }
    selectProject();
};
const removeProject = () => {
    if (confirm('Remove project requirement?')) {
        hasProject.value = false;
        activeType.value = 'lesson';
        activeChapterIndex.value = 0;
        activeItemIndex.value = 0;
    }
};

// ACTIONS: STRUCTURE
const addChapter = () => {
    curriculum.value.push({ id: null, title: 'New Chapter', lessons: [], quizzes: [] });
};
const deleteChapter = (index) => {
    if (!confirm('Delete this chapter and all its contents?')) return;
    const chapter = curriculum.value[index];
    if (chapter.id) {
        router.delete(route('teacher.courses.chapters.destroy', chapter.id), {
            preserveScroll: true,
            onSuccess: () => {
                curriculum.value = curriculum.value.filter((_, i) => i !== index);
                if (activeChapterIndex.value === index) { activeChapterIndex.value = 0; activeItemIndex.value = 0; }
            }
        });
    } else {
        curriculum.value.splice(index, 1);
    }
};

const addLesson = (cIndex) => {
    if (!curriculum.value[cIndex].lessons) curriculum.value[cIndex].lessons = [];
    curriculum.value[cIndex].lessons.push({ id: null, title: 'New Lesson', content: [], is_published: false });
    selectLesson(cIndex, curriculum.value[cIndex].lessons.length - 1);
};

const addQuiz = (cIndex) => {
    if (!curriculum.value[cIndex].quizzes) curriculum.value[cIndex].quizzes = [];
    curriculum.value[cIndex].quizzes.push({ id: null, title: 'Chapter Quiz', min_score: 80, questions: [] });
    selectQuiz(cIndex, curriculum.value[cIndex].quizzes.length - 1);
};

const deleteItem = (type, cIndex, index) => {
    if (!confirm('Delete this item?')) return;
    const chapter = curriculum.value[cIndex];
    const list = type === 'lesson' ? chapter.lessons : chapter.quizzes;
    const item = list[index];

    if (item.id) {
        const routeName = type === 'lesson' ? 'teacher.courses.lessons.destroy' : 'teacher.courses.quizzes.destroy';
        router.delete(route(routeName, [props.course.id, item.id]), {
            preserveScroll: true,
            onSuccess: () => list.splice(index, 1)
        });
    } else {
        list.splice(index, 1);
    }
};

// ACTIONS: CONTENT BLOCKS
const addBlock = (type) => {
    const item = getActiveItem();
    if (!item) return;
    if (!item.content) item.content = [];

    let val = '';
    if (type === 'code') val = { language: 'html', code: '' };

    item.content.push({ type, value: val, isUploading: false });

    nextTick(() => {
        const container = document.getElementById('editor-canvas');
        if (container) container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
    });
};

const removeBlock = (index) => {
    if (confirm('Remove this block?')) getActiveItem().content.splice(index, 1);
};

// ACTIONS: QUIZ QUESTIONS
const addQuestion = () => {
    const quiz = getActiveItem();
    if (!quiz) return;
    if (!quiz.questions) quiz.questions = [];
    quiz.questions.push({ text: '', options: ['', '', '', ''], correct_index: 0 });
};
const removeQuestion = (index) => getActiveItem().questions.splice(index, 1);

// MEDIA HANDLING
const triggerUpload = (id) => document.getElementById(id).click();

const handleUpload = async (event, block) => {
    const file = event.target.files[0];
    if (!file) return;

    block.isUploading = true;
    const formData = new FormData();
    formData.append('file', file);

    try {
        const response = await axios.post(route('teacher.courses.media.upload'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        block.value = response.data.url;
    } catch (e) {
        alert('Upload failed!');
        console.error(e);
    } finally {
        block.isUploading = false;
    }
};

const removeImage = async (block) => {
    if (!block.value) return;
    if (confirm('Delete image?')) {
        try {
            await axios.post(route('teacher.courses.media.delete'), { url: block.value });
            block.value = '';
        } catch (e) {
            alert('Failed to delete');
        }
    }
};

const getYoutubeId = (url) => {
    if (!url) return null;
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
};

// SAVE FUNCTION
const saveAll = () => {
    isSaving.value = true;

    router.post(route('teacher.courses.update_chapters', props.course.id), {
        chapters: curriculum.value,
        project: hasProject.value ? projectData.value : null
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isSaving.value = false;
        },
        onError: (errors) => {
            isSaving.value = false;
            console.error(errors);
            alert('Failed to save. Check console.');
        }
    });
};
</script>

<template>

    <Head title="Course Builder" />

    <div
        class="lg:hidden fixed inset-0 z-[999] bg-white flex flex-col items-center justify-center p-10 text-center font-display">
        <div
            class="w-24 h-24 bg-red-50 text-red-500 rounded-full flex items-center justify-center mb-6 border border-red-100 shadow-sm">
            <span class="material-symbols-outlined text-5xl">laptop_mac</span>
        </div>
        <h2 class="text-2xl font-black text-gray-900 leading-tight mb-3">Akses Ditolak</h2>
        <p class="text-gray-500 text-sm leading-relaxed max-w-xs">
            Halaman <span class="font-bold">Course Builder</span> memerlukan ruang kerja yang luas. Gunakan <span
                class="text-gray-900 font-bold">Laptop atau PC</span> anda untuk mengelola kurikulum.
        </p>
        <Link :href="route('teacher.dashboard')"
            class="mt-8 px-6 py-2.5 bg-gray-900 text-white text-xs font-bold uppercase tracking-widest rounded-xl transition-all active:scale-95">
            Back to Dashboard
        </Link>
    </div>

    <div class="hidden lg:flex bg-[#FDFDFD] font-display text-gray-900 h-screen overflow-hidden">

        <aside
            class="w-80 bg-white border-r border-gray-100 flex flex-col h-full shrink-0 z-20 shadow-[1px_0_10px_rgba(0,0,0,0.02)]">
            <div class="h-16 flex items-center px-6 border-b border-gray-50 cursor-pointer hover:bg-gray-50 transition-colors group shrink-0"
                @click="router.visit(route('teacher.courses'))">
                <span
                    class="material-symbols-outlined text-gray-400 group-hover:text-black mr-3 text-xl transition-colors">arrow_back</span>
                <span
                    class="font-black text-gray-500 group-hover:text-black text-[10px] tracking-[0.2em] uppercase transition-colors">Course
                    List</span>
            </div>

            <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-8">
                <div v-for="(chapter, cIndex) in curriculum" :key="cIndex" class="space-y-4">
                    <div class="flex items-center justify-between px-2 group/chap">
                        <div class="flex items-center gap-2 flex-1">
                            <input v-model="chapter.title"
                                class="bg-transparent border-none p-0 text-xs font-black text-gray-900 w-full focus:ring-0 placeholder-gray-300 uppercase tracking-widest"
                                placeholder="Chapter Title">
                        </div>
                        <button @click="deleteChapter(cIndex)"
                            class="text-gray-300 hover:text-red-500 opacity-0 group-hover/chap:opacity-100 transition-all">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                        </button>
                    </div>

                    <div
                        class="space-y-2 ml-2 pl-3 border-l border-gray-100 transition-colors group-hover:border-gray-200">
                        <div v-for="(lesson, lIndex) in chapter.lessons" :key="'l' + lIndex"
                            @click="selectLesson(cIndex, lIndex)"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs cursor-pointer transition-all duration-200 group/item relative"
                            :class="(activeType === 'lesson' && activeChapterIndex === cIndex && activeItemIndex === lIndex)
                                ? 'bg-gray-900 text-white shadow-lg ring-1 ring-gray-900'
                                : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'">
                            <div class="flex items-center gap-3 truncate">
                                <span class="material-symbols-outlined text-[16px]"
                                    :class="(activeType === 'lesson' && activeChapterIndex === cIndex && activeItemIndex === lIndex) ? 'text-[#FFDE21]' : 'text-gray-300'">
                                    {{ (activeType === 'lesson' && activeChapterIndex === cIndex && activeItemIndex ===
                                        lIndex) ? 'play_circle' : 'description' }}
                                </span>
                                <span class="truncate font-bold">{{ lesson.title || 'Untitled' }}</span>
                            </div>
                            <button @click.stop="deleteItem('lesson', cIndex, lIndex)"
                                class="opacity-0 group-hover/item:opacity-100 text-gray-400 hover:text-red-400 transition-opacity flex-shrink-0 ml-2">
                                <span class="material-symbols-outlined text-[14px]">close</span>
                            </button>
                        </div>

                        <div v-for="(quiz, qIndex) in chapter.quizzes" :key="'q' + qIndex"
                            @click="selectQuiz(cIndex, qIndex)"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs cursor-pointer transition-all duration-200 group/item"
                            :class="(activeType === 'quiz' && activeChapterIndex === cIndex && activeItemIndex === qIndex)
                                ? 'bg-purple-600 text-white shadow-lg'
                                : 'text-gray-500 hover:bg-purple-50 hover:text-purple-700'">
                            <div class="flex items-center gap-3 truncate">
                                <span class="material-symbols-outlined text-[16px]"
                                    :class="(activeType === 'quiz' && activeChapterIndex === cIndex && activeItemIndex === qIndex) ? 'text-white' : 'text-purple-400'">quiz</span>
                                <span class="truncate font-bold">{{ quiz.title || 'Quiz' }}</span>
                            </div>
                            <button @click.stop="deleteItem('quiz', cIndex, qIndex)"
                                class="opacity-0 group-hover/item:opacity-100 text-gray-400 hover:text-red-400 transition-opacity flex-shrink-0 ml-2">
                                <span class="material-symbols-outlined text-[14px]">close</span>
                            </button>
                        </div>

                        <div class="flex gap-2 mt-4 px-1">
                            <button @click="addLesson(cIndex)"
                                class="flex-1 py-2 flex items-center justify-center gap-1 text-[9px] font-black uppercase tracking-widest text-gray-400 border border-dashed border-gray-200 rounded-lg hover:border-gray-900 hover:text-gray-900 transition-all bg-white">
                                + Lesson
                            </button>
                            <button @click="addQuiz(cIndex)"
                                class="flex-1 py-2 flex items-center justify-center gap-1 text-[9px] font-black uppercase tracking-widest text-gray-400 border border-dashed border-gray-200 rounded-lg hover:border-purple-500 hover:text-purple-600 transition-all bg-white">
                                + Quiz
                            </button>
                        </div>
                    </div>
                </div>

                <button @click="addChapter"
                    class="w-full py-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 border-2 border-dashed border-gray-50 rounded-2xl hover:bg-gray-50 hover:border-gray-200 hover:text-gray-600 transition-all mt-4">
                    + New Chapter
                </button>
            </div>

            <div class="p-4 bg-gray-50/50 border-t border-gray-100 shrink-0">
                <div @click="selectProject"
                    class="flex items-center gap-3 p-4 rounded-2xl cursor-pointer border-2 transition-all duration-300 relative overflow-hidden"
                    :class="activeType === 'project' ? 'bg-white border-blue-600 shadow-xl' : 'bg-transparent border-transparent hover:bg-white'">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors shrink-0"
                        :class="activeType === 'project' ? 'bg-blue-600 text-white' : 'bg-white text-gray-400 border border-gray-200'">
                        <span class="material-symbols-outlined text-xl">rocket_launch</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-black text-gray-900 truncate uppercase tracking-widest">Final Project
                        </h4>
                        <p class="text-[9px] uppercase tracking-widest font-black mt-0.5"
                            :class="hasProject ? 'text-green-500' : 'text-gray-400'">
                            {{ hasProject ? 'Required' : 'Disabled' }}
                        </p>
                    </div>
                    <span v-if="hasProject"
                        class="w-2 h-2 rounded-full bg-green-500 animate-pulse flex-shrink-0"></span>
                </div>
            </div>
        </aside>

        <main class="flex-1 flex flex-col h-full overflow-hidden relative bg-white">
            <header class="h-16 border-b border-gray-50 flex items-center justify-between px-8 shrink-0 z-10 bg-white">
                <div class="min-w-0">
                    <p class="text-[9px] font-black text-gray-300 uppercase tracking-[0.3em] mb-0.5">Editing Curriculum
                        Content</p>
                    <h1 class="text-xs font-bold text-gray-500 truncate max-w-md">{{ course.title }}</h1>
                </div>

                <div class="flex items-center gap-3">
                    <a :href="route('teacher.courses.preview', course.id)" target="_blank"
                        class="flex items-center gap-2 px-5 py-2.5 text-gray-600 font-black rounded-xl hover:bg-gray-50 transition-all text-[10px] uppercase tracking-widest border border-gray-100">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                        Preview
                    </a>

                    <button @click="saveAll" :disabled="isSaving"
                        class="flex items-center gap-2 bg-[#111] text-white font-black px-6 py-2.5 rounded-xl shadow-lg shadow-gray-200 hover:bg-black disabled:opacity-50 transition-all text-[10px] uppercase tracking-widest active:scale-95">
                        <span v-if="isSaving"
                            class="material-symbols-outlined animate-spin text-sm">progress_activity</span>
                        <span v-else class="material-symbols-outlined text-sm text-[#FFDE21]">check_circle</span>
                        {{ isSaving ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </header>

            <div id="editor-canvas"
                class="flex-1 overflow-y-auto p-12 lg:p-20 scroll-smooth pb-40 bg-[#FCFCFC] custom-scrollbar relative">

                <div v-if="activeType === 'lesson' && getActiveItem()"
                    class="max-w-3xl mx-auto space-y-12 animate-fade-in-up">
                    <div class="space-y-4">
                        <label class="text-[10px] font-black text-gray-300 uppercase tracking-[0.3em]">Lesson
                            Heading</label>
                        <input v-model="getActiveItem().title"
                            class="w-full text-5xl lg:text-6xl font-black text-gray-900 border-none p-0 focus:ring-0 placeholder-gray-100 leading-tight bg-transparent"
                            placeholder="My Lesson Title">
                    </div>

                    <div class="space-y-8 pb-32">
                        <div v-if="getActiveItem().content.length === 0"
                            class="flex flex-col items-center justify-center py-32 text-gray-200 border-2 border-dashed border-gray-100 rounded-[3rem] bg-white shadow-inner">
                            <span class="material-symbols-outlined text-6xl mb-6 opacity-10">edit_note</span>
                            <p class="font-black text-[10px] uppercase tracking-[0.2em]">Craft your lesson content below
                            </p>
                        </div>

                        <div v-for="(block, i) in getActiveItem().content" :key="i"
                            class="relative group animate-fade-in-up">
                            <div
                                class="absolute -left-14 top-2 flex flex-col gap-1 opacity-0 group-hover:opacity-100 transition-all z-20">
                                <button @click="removeBlock(i)"
                                    class="p-2.5 bg-white border border-gray-100 text-gray-300 hover:text-red-500 rounded-xl shadow-sm transition-all">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </div>

                            <div v-if="block.type === 'text'">
                                <textarea v-model="block.value"
                                    class="w-full rounded-3xl border-transparent bg-white p-8 text-gray-700 leading-relaxed shadow-[0_4px_20px_rgba(0,0,0,0.03)] focus:shadow-xl focus:ring-0 resize-none transition-all hover:shadow-md text-lg"
                                    rows="5" placeholder="Write here..."></textarea>
                            </div>

                            <div v-if="block.type === 'code'"
                                class="bg-[#0D1117] rounded-3xl shadow-2xl overflow-hidden border border-gray-800">
                                <div
                                    class="bg-[#161B22] px-6 py-4 flex justify-between items-center border-b border-gray-800">
                                    <div class="flex gap-2">
                                        <div class="w-3 h-3 rounded-full bg-[#FF5F56]"></div>
                                        <div class="w-3 h-3 rounded-full bg-[#FFBD2E]"></div>
                                        <div class="w-3 h-3 rounded-full bg-[#27C93F]"></div>
                                    </div>
                                    <select v-model="block.value.language"
                                        class="bg-transparent text-[10px] font-black text-gray-500 border-none p-0 focus:ring-0 cursor-pointer uppercase tracking-widest">
                                        <option value="html">HTML</option>
                                        <option value="css">CSS</option>
                                        <option value="js">JS</option>
                                        <option value="php">PHP</option>
                                    </select>
                                </div>
                                <textarea v-model="block.value.code"
                                    class="w-full bg-transparent border-none font-mono text-sm text-[#C9D1D9] p-8 focus:ring-0 leading-relaxed"
                                    rows="8" placeholder="// Code here..."></textarea>
                            </div>

                        </div>
                    </div>
                </div>

                <div
                    class="fixed bottom-8 left-1/2 md:left-[calc(50%+160px)] -translate-x-1/2 z-[999] pointer-events-none">
                    <div
                        class="flex items-center gap-1 bg-gray-900/95 backdrop-blur-2xl p-2 rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.4)] border border-white/10 pointer-events-auto transform transition-all hover:scale-105 active:scale-95">
                        <button @click="addBlock('text')"
                            class="flex flex-col items-center justify-center w-16 h-14 rounded-[1.5rem] text-white hover:bg-white/10 transition-all gap-1 group/btn">
                            <span
                                class="material-symbols-outlined text-xl group-hover/btn:scale-110 transition-transform">notes</span>
                            <span class="text-[7px] font-black uppercase tracking-widest opacity-40">Text</span>
                        </button>
                        <button @click="addBlock('code')"
                            class="flex flex-col items-center justify-center w-16 h-14 rounded-[1.5rem] text-white hover:bg-white/10 transition-all gap-1 group/btn">
                            <span
                                class="material-symbols-outlined text-xl group-hover/btn:scale-110 transition-transform">terminal</span>
                            <span class="text-[7px] font-black uppercase tracking-widest opacity-40">Code</span>
                        </button>
                        <div class="w-[1px] h-8 bg-white/10 mx-1"></div>
                        <button @click="addBlock('video')"
                            class="flex flex-col items-center justify-center w-16 h-14 rounded-[1.5rem] text-white hover:bg-white/10 transition-all gap-1 group/btn">
                            <span
                                class="material-symbols-outlined text-xl group-hover/btn:scale-110 transition-transform">play_circle</span>
                            <span class="text-[7px] font-black uppercase tracking-widest opacity-40">Video</span>
                        </button>
                        <button @click="addBlock('image')"
                            class="flex flex-col items-center justify-center w-16 h-14 rounded-[1.5rem] text-white hover:bg-white/10 transition-all gap-1 group/btn">
                            <span
                                class="material-symbols-outlined text-xl text-[#FFDE21] group-hover/btn:scale-110 transition-transform">add_a_photo</span>
                            <span class="text-[7px] font-black uppercase tracking-widest opacity-40">Image</span>
                        </button>
                    </div>
                </div>

                <div v-if="activeType === 'quiz' && getActiveItem()"
                    class="max-w-4xl mx-auto space-y-10 animate-fade-in-up pb-40">
                    <div
                        class="bg-white p-12 rounded-[3rem] shadow-sm border border-gray-100 border-t-[12px] border-t-purple-600">
                        <div class="flex flex-col md:flex-row justify-between items-start gap-12">
                            <div class="flex-1 w-full">
                                <label
                                    class="text-[10px] font-black text-purple-600 uppercase tracking-[0.3em] mb-3 block">Module
                                    Assessment</label>
                                <input v-model="getActiveItem().title"
                                    class="w-full text-4xl lg:text-5xl font-black text-gray-900 border-none p-0 focus:ring-0 placeholder-gray-100 leading-tight bg-transparent"
                                    placeholder="Quiz Title">
                            </div>
                            <div
                                class="bg-purple-50 p-8 rounded-[2rem] text-center min-w-[160px] border border-purple-100 shadow-inner shrink-0">
                                <label
                                    class="text-[9px] font-black text-purple-400 uppercase tracking-widest block mb-3">Min.
                                    Score</label>
                                <div class="flex items-center justify-center gap-1">
                                    <input v-model="getActiveItem().min_score" type="number"
                                        class="w-16 text-center font-black text-4xl bg-transparent border-none p-0 focus:ring-0 text-purple-700">
                                    <span class="text-purple-300 font-black text-2xl">%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div v-for="(q, index) in getActiveItem().questions" :key="index"
                            class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-gray-100 relative group hover:shadow-xl transition-all border-l-[6px] border-l-purple-50">
                            <button @click="removeQuestion(index)"
                                class="absolute top-8 right-8 text-gray-200 hover:text-red-500 transition-colors">
                                <span class="material-symbols-outlined">delete</span>
                            </button>

                            <div class="flex flex-col gap-10">
                                <div class="space-y-4">
                                    <span
                                        class="text-[10px] font-black text-purple-400 uppercase tracking-[0.4em]">Question
                                        #{{ index + 1 }}</span>
                                    <input v-model="q.text"
                                        class="w-full font-bold text-2xl border-none focus:ring-0 p-0 placeholder-gray-100 text-gray-800"
                                        placeholder="Ask something meaningful...">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div v-for="(opt, oIndex) in q.options" :key="oIndex"
                                        class="flex items-center gap-4 p-5 rounded-[1.5rem] border-2 transition-all cursor-pointer group/opt"
                                        :class="q.correct_index === oIndex ? 'bg-green-50 border-green-500 ring-4 ring-green-500/5' : 'bg-gray-50/50 border-gray-50 hover:border-purple-200 hover:bg-white'">
                                        <div @click="q.correct_index = oIndex"
                                            class="w-7 h-7 rounded-full border-2 flex items-center justify-center shrink-0 transition-all"
                                            :class="q.correct_index === oIndex ? 'bg-green-500 border-green-500 scale-110 shadow-lg' : 'bg-white border-gray-200 group-hover/opt:border-purple-400'">
                                            <div v-if="q.correct_index === oIndex"
                                                class="w-2.5 h-2.5 rounded-full bg-white"></div>
                                        </div>
                                        <input v-model="q.options[oIndex]"
                                            class="flex-1 bg-transparent border-none text-[15px] font-bold p-0 focus:ring-0 text-gray-700"
                                            placeholder="Enter option...">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button @click="addQuestion"
                            class="w-full py-8 border-2 border-dashed border-gray-100 text-purple-400 font-black text-[11px] uppercase tracking-[0.3em] rounded-[3rem] hover:bg-purple-50 hover:border-purple-200 hover:text-purple-600 transition-all shadow-sm bg-white">
                            + Add Question
                        </button>
                    </div>
                </div>

                <div v-if="activeType === 'project'" class="max-w-4xl mx-auto animate-fade-in-up pb-40">
                    <div
                        class="bg-white rounded-[3rem] shadow-2xl border border-gray-100 overflow-hidden min-h-[600px] flex flex-col">
                        <div class="p-12 bg-[#0F172A] text-white relative overflow-hidden shrink-0">
                            <div
                                class="absolute -right-20 -top-20 w-80 h-80 bg-blue-600 rounded-full blur-[100px] opacity-20">
                            </div>
                            <div class="flex items-center gap-8 relative z-10">
                                <div
                                    class="w-20 h-20 bg-blue-600 rounded-[2rem] flex items-center justify-center shadow-2xl shadow-blue-900/50 border border-blue-400/20">
                                    <span class="material-symbols-outlined text-4xl">rocket_launch</span>
                                </div>
                                <div class="flex-1">
                                    <h2 class="text-4xl font-black tracking-tight leading-tight">Capstone Project</h2>
                                    <p class="text-blue-300 text-sm font-medium mt-2 max-w-sm">The final challenge for
                                        students to prove their skills and earn the certificate.</p>
                                </div>
                                <div class="shrink-0">
                                    <div class="px-5 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-widest backdrop-blur-2xl border transition-all"
                                        :class="hasProject ? 'bg-green-500/20 text-green-400 border-green-500/30' : 'bg-white/5 text-white/30 border-white/10'">
                                        {{ hasProject ? 'Project Active' : 'No Project' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="hasProject" class="p-12 flex-1 space-y-10 bg-white">
                            <div class="space-y-3">
                                <label
                                    class="block text-[10px] font-black text-gray-300 uppercase tracking-[0.3em] px-1">Project
                                    Assignment Name</label>
                                <input v-model="projectData.title"
                                    class="w-full text-3xl font-black text-gray-900 border-none p-0 focus:ring-0 placeholder:text-gray-50 leading-tight bg-transparent"
                                    placeholder="e.g. Building a Full-Stack CMS">
                            </div>

                            <div class="space-y-4">
                                <label
                                    class="block text-[10px] font-black text-gray-300 uppercase tracking-[0.3em] px-1">Mission
                                    Brief & Submission Guidelines</label>
                                <div class="relative group/text">
                                    <textarea v-model="projectData.description" rows="14"
                                        class="w-full rounded-[2.5rem] border-transparent bg-gray-50/50 p-10 text-gray-700 leading-relaxed focus:bg-white focus:shadow-xl focus:ring-0 transition-all text-lg shadow-inner"
                                        placeholder="Tell students what they need to build..."></textarea>
                                    <div
                                        class="absolute bottom-6 right-10 flex items-center gap-2 opacity-20 group-hover/text:opacity-50 transition-all pointer-events-none">
                                        <span class="material-symbols-outlined text-[16px]">markdown</span>
                                        <span class="text-[9px] font-black uppercase tracking-widest">Markdown
                                            Enabled</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4 border-t border-gray-50">
                                <button @click="removeProject"
                                    class="flex items-center gap-2 text-red-500 hover:text-red-700 font-black text-[9px] uppercase tracking-[0.2em] transition-all">
                                    <span class="material-symbols-outlined text-[16px]">cancel</span> Remove Project
                                    Requirement
                                </button>
                            </div>
                        </div>

                        <div v-else class="flex-1 flex flex-col items-center justify-center p-20 text-center bg-white">
                            <div
                                class="w-24 h-24 bg-gray-50 rounded-[2rem] flex items-center justify-center mb-8 text-gray-200 border border-gray-100">
                                <span class="material-symbols-outlined text-4xl">folder_off</span>
                            </div>
                            <h3 class="text-2xl font-black text-gray-900 mb-3 tracking-tight">No Submission Required
                            </h3>
                            <p class="text-gray-400 text-sm max-w-sm mb-12 leading-relaxed font-medium italic">Course
                                will conclude immediately after the last quiz.</p>
                            <button @click="enableProject"
                                class="px-10 py-4 bg-blue-600 text-white font-black text-xs uppercase tracking-[0.3em] rounded-[1.5rem] shadow-2xl shadow-blue-200 hover:bg-blue-700 hover:-translate-y-1 active:translate-y-0 transition-all">
                                + Activate Final Project
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
@import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap');

.font-display {
    font-family: 'Inter', sans-serif;
}

.font-mono {
    font-family: 'JetBrains Mono', monospace;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
    height: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #F1F1F1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #E5E5E5;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(15px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>