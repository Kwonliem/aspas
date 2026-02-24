<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Props dari Controller
const props = defineProps({
    courses: Array
});

// --- STATE: MODAL & FORM ---
const showModal = ref(false);
const photoInput = ref(null);
const photoPreview = ref(null);
const isEditing = ref(false); 
const editingCourseId = ref(null); 

const form = useForm({
    title: '',
    description: '',
    credits: 0,
    xp: 100,
    duration_days: 0, // DITAMBAHKAN: Field baru untuk durasi hari
    status: 'draft',
    cover_image: null,
});

// --- STATE: VIEW MODAL ---
const showViewModal = ref(false);
const selectedCourse = ref(null);

// --- FUNCTIONS: VIEW ---
const openViewModal = (course) => {
    selectedCourse.value = course;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    setTimeout(() => selectedCourse.value = null, 200);
};

// --- FUNCTIONS: CREATE & EDIT ---
const openCreateModal = () => {
    isEditing.value = false;
    editingCourseId.value = null;
    form.reset();
    form.clearErrors();
    photoPreview.value = null;
    showModal.value = true;
};

const openEditModal = (course) => {
    isEditing.value = true;
    editingCourseId.value = course.id;

    form.title = course.title;
    form.description = course.description;
    form.credits = course.credits;
    form.xp = course.xp;
    form.duration_days = course.duration_days; // DITAMBAHKAN: Set form value dari data course
    // Pastikan status lowercase untuk radio button
    form.status = course.status.toLowerCase(); 
    form.cover_image = null; 

    photoPreview.value = course.image;
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
    isEditing.value = false;
};

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.cover_image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const triggerFileInput = () => {
    photoInput.value.click();
};

const submit = () => {
    if (isEditing.value) {
        form.post(route('teacher.courses.update', editingCourseId.value), {
            onSuccess: () => closeModal(),
            preserveScroll: true
        });
    } else {
        form.post(route('teacher.courses.store'), {
            onSuccess: () => closeModal(),
            preserveScroll: true
        });
    }
};

const deleteCourse = (id) => {
    if (confirm('Are you sure you want to delete this course? All data will be lost.')) {
        router.delete(route('teacher.courses.destroy', id), {
            preserveScroll: true,
            onSuccess: () => {
                if(showViewModal.value) closeViewModal();
            }
        });
    }
}
</script>

<template>
    <Head title="My Courses" />

    <TeacherLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 space-y-8 font-display pb-24">
            
            <section class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 border-b border-gray-200 pb-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">My Courses</h1>
                    <p class="text-sm text-gray-500 mt-1 font-medium">Manage your curriculum and content.</p>
                </div>
                
                <button @click="openCreateModal"
                    class="flex items-center justify-center gap-2 bg-[#111] hover:bg-black text-white font-bold px-6 py-2.5 rounded-xl transition-all shadow-sm active:scale-95 text-sm">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Create Course
                </button>
            </section>

            <div v-if="courses.length === 0" class="flex flex-col items-center justify-center py-24 bg-white rounded-[2rem] border border-dashed border-gray-200 shadow-sm">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-5 border border-gray-100">
                    <span class="material-symbols-outlined text-4xl text-gray-400">library_add</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900">No Courses Yet</h3>
                <p class="text-gray-500 mt-1 mb-6 text-sm font-medium">Start your teaching journey by creating your first curriculum.</p>
                <button @click="openCreateModal" class="px-6 py-2.5 bg-[#ffde24] hover:bg-[#eacb1e] text-black font-bold rounded-xl shadow-sm transition-all active:scale-95 text-sm">
                    Create New Course
                </button>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8">
                
                <button @click="openCreateModal"
                    class="group relative flex flex-col items-center justify-center h-full min-h-[350px] rounded-[1.5rem] border-2 border-dashed border-gray-200 hover:border-gray-300 hover:bg-white transition-all cursor-pointer bg-gray-50/50">
                    <div class="w-14 h-14 rounded-full bg-white shadow-sm flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300 border border-gray-100 text-gray-400 group-hover:text-gray-900">
                        <span class="material-symbols-outlined text-3xl">add</span>
                    </div>
                    <h3 class="text-sm font-bold text-gray-600 group-hover:text-gray-900">Create New Course</h3>
                </button>

                <div v-for="course in courses" :key="course.id"
                    class="group bg-white rounded-[1.5rem] border border-gray-200 shadow-sm hover:shadow-xl hover:border-gray-300 transition-all duration-300 flex flex-col overflow-hidden h-full relative cursor-pointer"
                    @click="openViewModal(course)">
                    
                    <div class="relative h-44 overflow-hidden bg-gray-100 shrink-0">
                        <img v-if="course.image" :src="course.image" :alt="course.title" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                            <span class="material-symbols-outlined text-5xl">image</span>
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-60"></div>

                        <div class="absolute top-3 left-3 flex flex-col items-start gap-2">
                            <span :class="['px-3 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg shadow-sm backdrop-blur-sm', 
                                course.status.toLowerCase() === 'published' ? 'bg-green-500 text-white' : 'bg-white/90 text-gray-900']">
                                {{ course.status }}
                            </span>
                        </div>

                        <span v-if="course.duration_days > 0" class="absolute bottom-3 left-3 px-2.5 py-1 text-[9px] font-bold bg-white/90 text-gray-800 rounded-lg shadow-sm backdrop-blur flex items-center gap-1 uppercase tracking-widest">
                            <span class="material-symbols-outlined text-[12px] text-red-500">timer</span>
                            {{ course.duration_days }} Days
                        </span>

                        <div class="absolute top-3 right-3 flex gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 transform translate-y-2 group-hover:translate-y-0">
                            <button @click.stop="openEditModal(course)" class="w-8 h-8 flex items-center justify-center bg-white/90 backdrop-blur text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors shadow-sm" title="Edit Info">
                                <span class="material-symbols-outlined text-[16px]">edit</span>
                            </button>
                            <button @click.stop="deleteCourse(course.id)" class="w-8 h-8 flex items-center justify-center bg-white/90 backdrop-blur text-gray-700 rounded-lg hover:bg-red-50 hover:text-red-600 transition-colors shadow-sm" title="Delete">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                            </button>
                        </div>
                    </div>

                    <div class="p-5 flex flex-col flex-1">
                        
                        <div class="flex gap-2 mb-3">
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-yellow-50 text-yellow-700 px-2 py-0.5 rounded-md border border-yellow-100 uppercase tracking-widest">
                                <span class="material-symbols-outlined text-[12px]">monetization_on</span>
                                {{ course.credits > 0 ? course.credits : 'Free' }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-gray-50 text-gray-600 px-2 py-0.5 rounded-md border border-gray-200 uppercase tracking-widest">
                                <span class="material-symbols-outlined text-[12px]">bolt</span>
                                {{ course.xp }} XP
                            </span>
                        </div>

                        <h3 class="text-base font-black text-gray-900 leading-tight mb-1.5 line-clamp-2 group-hover:text-blue-600 transition-colors">
                            {{ course.title }}
                        </h3>
                        <p class="text-xs text-gray-500 line-clamp-2 mb-4 font-medium leading-relaxed">
                            {{ course.description }}
                        </p>

                        <div class="grid grid-cols-3 gap-2 pt-4 border-t border-gray-100 mt-auto">
                            <div class="text-center">
                                <p class="text-[9px] uppercase text-gray-400 font-bold tracking-widest">Lessons</p>
                                <p class="font-bold text-gray-900 text-sm mt-0.5">{{ course.lessons_count }}</p>
                            </div>
                            <div class="text-center border-l border-gray-100">
                                <p class="text-[9px] uppercase text-gray-400 font-bold tracking-widest">Quizzes</p>
                                <p class="font-bold text-gray-900 text-sm mt-0.5">{{ course.quizzes_count }}</p>
                            </div>
                            <div class="text-center border-l border-gray-100">
                                <p class="text-[9px] uppercase text-gray-400 font-bold tracking-widest">Students</p>
                                <p class="font-bold text-gray-900 text-sm mt-0.5">{{ course.students_count }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto font-display">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

                    <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl w-full">
                        
                        <div class="bg-white px-6 py-6 sm:px-8 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-black text-gray-900 tracking-tight">
                                    {{ isEditing ? 'Edit Course Details' : 'Create New Course' }}
                                </h3>
                                <p class="text-xs text-gray-500 mt-1 font-medium">Fill in the information below to set up your course.</p>
                            </div>
                            <button @click="closeModal" class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-500 hover:bg-gray-200 hover:text-gray-900 transition-colors">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                            </button>
                        </div>

                        <div class="px-6 py-6 sm:p-8">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
                                
                                <div class="md:col-span-5 space-y-2">
                                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">Course Cover</label>
                                    <div @click="triggerFileInput"
                                        class="aspect-[4/3] border-2 border-dashed border-gray-200 rounded-2xl flex flex-col items-center justify-center cursor-pointer hover:border-gray-400 hover:bg-gray-50 transition-all relative overflow-hidden group bg-gray-50/50">
                                        
                                        <input type="file" ref="photoInput" class="hidden" @change="handleImageUpload" accept="image/*" />
                                        
                                        <div v-if="photoPreview" class="absolute inset-0">
                                            <img :src="photoPreview" class="w-full h-full object-cover" />
                                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <span class="text-white font-bold text-xs flex items-center gap-1.5 bg-black/50 px-3 py-1.5 rounded-lg backdrop-blur-sm">
                                                    <span class="material-symbols-outlined text-[16px]">edit</span> Change Image
                                                </span>
                                            </div>
                                        </div>
                                        <div v-else class="text-center p-4">
                                            <div class="w-10 h-10 bg-white border border-gray-200 rounded-full shadow-sm flex items-center justify-center mx-auto mb-3 text-gray-400 group-hover:text-gray-600 transition-colors">
                                                <span class="material-symbols-outlined text-xl">add_photo_alternate</span>
                                            </div>
                                            <p class="text-xs font-bold text-gray-700">Upload Image</p>
                                            <p class="text-[10px] text-gray-400 mt-1 font-medium">PNG, JPG up to 2MB</p>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.cover_image" class="text-red-500 text-xs font-bold mt-1">{{ form.errors.cover_image }}</div>
                                </div>

                                <div class="md:col-span-7 space-y-5">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Course Title</label>
                                        <input v-model="form.title" type="text"
                                            class="block w-full rounded-xl border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 shadow-sm py-2.5 px-4 transition-all text-sm font-medium placeholder:text-gray-400"
                                            placeholder="e.g. Advanced Vue.js Development">
                                        <div v-if="form.errors.title" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.title }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Description</label>
                                        <textarea v-model="form.description" rows="3"
                                            class="block w-full rounded-xl border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 shadow-sm py-2.5 px-4 transition-all text-sm font-medium placeholder:text-gray-400 resize-none"
                                            placeholder="Briefly describe what students will learn..."></textarea>
                                        <div v-if="form.errors.description" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.description }}</div>
                                    </div>

                                    <div class="grid grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Credits</label>
                                            <input v-model="form.credits" type="number" min="0"
                                                class="block w-full rounded-xl border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 shadow-sm py-2.5 px-4 transition-all text-sm font-bold">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Reward (XP)</label>
                                            <input v-model="form.xp" type="number" min="0"
                                                class="block w-full rounded-xl border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 shadow-sm py-2.5 px-4 transition-all text-sm font-bold">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2" title="0 = Never expire">Days Limit</label>
                                            <input v-model="form.duration_days" type="number" min="0"
                                                class="block w-full rounded-xl border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 shadow-sm py-2.5 px-4 transition-all text-sm font-bold placeholder-gray-400"
                                                placeholder="0 for none">
                                        </div>
                                    </div>

                                    <div class="pt-1">
                                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-3">Publication Status</label>
                                        <div class="grid grid-cols-2 gap-4">
                                            <label class="cursor-pointer group">
                                                <input type="radio" v-model="form.status" value="draft" class="hidden peer">
                                                <div class="border border-gray-200 rounded-xl p-3 flex items-center gap-3 peer-checked:border-gray-900 peer-checked:bg-gray-50 transition-all hover:bg-gray-50 bg-white">
                                                    <div class="w-4 h-4 rounded-full border-2 border-gray-300 peer-checked:border-gray-900 peer-checked:bg-gray-900 flex items-center justify-center transition-colors"></div>
                                                    <div>
                                                        <span class="block text-xs font-bold text-gray-900">Draft</span>
                                                        <span class="block text-[10px] text-gray-500 font-medium">Still working on it</span>
                                                    </div>
                                                </div>
                                            </label>

                                            <label class="cursor-pointer group">
                                                <input type="radio" v-model="form.status" value="published" class="hidden peer">
                                                <div class="border border-gray-200 rounded-xl p-3 flex items-center gap-3 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all hover:bg-gray-50 bg-white">
                                                    <div class="w-4 h-4 rounded-full border-2 border-gray-300 peer-checked:border-green-500 peer-checked:bg-green-500 flex items-center justify-center transition-colors"></div>
                                                    <div>
                                                        <span class="block text-xs font-bold text-gray-900">Published</span>
                                                        <span class="block text-[10px] text-gray-500 font-medium">Visible to students</span>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-5 sm:px-8 flex justify-end gap-3 border-t border-gray-100">
                            <button @click="closeModal" type="button"
                                class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors text-sm shadow-sm">
                                Cancel
                            </button>
                            <button @click="submit" :disabled="form.processing"
                                class="flex items-center gap-2 px-6 py-2.5 bg-[#111] text-white font-bold rounded-xl hover:bg-black transition-all shadow-md active:scale-95 disabled:opacity-50 text-sm tracking-wide">
                                <span v-if="form.processing" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                                <span>{{ isEditing ? 'Save Changes' : 'Create Course' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showViewModal && selectedCourse" class="fixed inset-0 z-50 overflow-y-auto font-display">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeViewModal"></div>

                    <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full">

                        <div class="relative h-56 bg-gray-100">
                            <img v-if="selectedCourse.image" :src="selectedCourse.image" class="w-full h-full object-cover">
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                                <span class="material-symbols-outlined text-5xl">image</span>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>

                            <button @click="closeViewModal" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center bg-black/40 hover:bg-black/60 backdrop-blur text-white rounded-full transition-all z-10">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                            </button>

                            <div class="absolute top-4 left-4 flex gap-2">
                                <span :class="['px-3 py-1.5 text-[9px] font-black uppercase tracking-widest rounded-lg shadow-sm backdrop-blur', selectedCourse.status.toLowerCase() === 'published' ? 'bg-green-500 text-white' : 'bg-white text-gray-900']">
                                    {{ selectedCourse.status }}
                                </span>
                            </div>
                        </div>

                        <div class="p-6 sm:p-8">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-yellow-50 text-yellow-700 px-2 py-1 rounded border border-yellow-100 uppercase tracking-widest">
                                    <span class="material-symbols-outlined text-[14px]">monetization_on</span>
                                    {{ selectedCourse.credits > 0 ? selectedCourse.credits + ' Cr' : 'FREE' }}
                                </span>
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold bg-blue-50 text-blue-700 px-2 py-1 rounded border border-blue-100 uppercase tracking-widest">
                                    <span class="material-symbols-outlined text-[14px]">bolt</span>
                                    {{ selectedCourse.xp }} XP
                                </span>
                                <span v-if="selectedCourse.duration_days > 0" class="inline-flex items-center gap-1 text-[10px] font-bold bg-red-50 text-red-600 px-2 py-1 rounded border border-red-100 uppercase tracking-widest">
                                    <span class="material-symbols-outlined text-[14px]">timer</span>
                                    {{ selectedCourse.duration_days }} Days Limit
                                </span>
                            </div>

                            <h2 class="text-2xl font-black text-gray-900 mb-3 leading-tight">{{ selectedCourse.title }}</h2>
                            <p class="text-gray-600 text-sm leading-relaxed mb-6 font-medium">{{ selectedCourse.description }}</p>

                            <div class="grid grid-cols-3 gap-3 py-4 border-y border-gray-100 mb-6">
                                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center">
                                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mb-1">Lessons</p>
                                    <p class="text-lg font-black text-gray-800">{{ selectedCourse.lessons_count || 0 }}</p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center">
                                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mb-1">Quizzes</p>
                                    <p class="text-lg font-black text-gray-800">{{ selectedCourse.quizzes_count || 0 }}</p>
                                </div>
                                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 text-center">
                                    <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mb-1">Students</p>
                                    <p class="text-lg font-black text-gray-800">{{ selectedCourse.students_count || 0 }}</p>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3">
                                <button @click="() => { closeViewModal(); openEditModal(selectedCourse); }"
                                    class="flex-1 px-5 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-all shadow-sm text-sm flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">edit</span> Edit Details
                                </button>
                                <Link :href="route('teacher.courses.manage', selectedCourse.id)"
                                    class="flex-1 px-5 py-3 bg-[#111] text-white font-bold rounded-xl hover:bg-black transition-all shadow-md active:scale-95 text-sm flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">settings_suggest</span> Manage Content
                                </Link>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </Transition>
    </TeacherLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.font-display { font-family: 'Inter', sans-serif; }

.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>