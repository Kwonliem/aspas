<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const props = defineProps({
    projects: {
        type: Array,
        default: () => []
    }
});

// State CRUD
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const imagePreview = ref(null);
const imageInput = ref(null);

// State Share QR
const showShareModal = ref(false);
const shareUrl = computed(() => route('public.profile', user.value.id));

const copyLink = () => {
    navigator.clipboard.writeText(shareUrl.value);
    alert('Link copied to clipboard!');
};

const form = useForm({
    title: '',
    category: '',
    description: '',
    link: '',
    image: null,
});

const openAddModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    imagePreview.value = null;
    if (imageInput.value) imageInput.value.value = null;
    showModal.value = true;
};

const openEditModal = (project) => {
    isEditing.value = true;
    editingId.value = project.id;
    form.title = project.title;
    form.category = project.category;
    form.description = project.description;
    form.link = project.link || '';
    form.image = null; 
    imagePreview.value = project.image || null;
    form.clearErrors();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
    imagePreview.value = null;
};

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    if (isEditing.value) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT'
        })).post(route('student.portfolio.update', editingId.value), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('student.portfolio.store'), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteProject = (id) => {
    if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
        router.delete(route('student.portfolio.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="My Portfolio" />

    <StudentLayout>
        <div class="p-6 md:p-10 max-w-7xl mx-auto space-y-10 font-display pb-24">
            
            <section class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-gray-200">
                <div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight">My Portfolio</h1>
                    <p class="text-sm text-gray-500 font-medium mt-2">Showcase your best projects and real-world creations.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <button @click="showShareModal = true" class="bg-white border-2 border-gray-200 hover:border-gray-300 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">qr_code_scanner</span>
                        Share Profile
                    </button>
                    
                    <button @click="openAddModal" class="bg-[#111] hover:bg-black text-white px-5 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center justify-center gap-2 shadow-sm hover:shadow-md transform active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Add Project
                    </button>
                </div>
            </section>

            <section>
                <div v-if="projects.length === 0" class="text-center py-24 bg-white rounded-3xl border-2 border-dashed border-gray-200">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                        <span class="material-symbols-outlined text-3xl text-gray-400">folder_open</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">No projects yet</h3>
                    <p class="text-gray-500 text-sm mt-1 mb-6 font-medium">Start building your portfolio to impress others.</p>
                    <button @click="openAddModal" class="text-sm font-bold text-gray-900 bg-[#ffde24] px-6 py-2.5 rounded-xl hover:bg-[#eacb1e] transition-colors shadow-sm">
                        Create Your First Project
                    </button>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="project in projects" :key="project.id" class="bg-white rounded-[1.5rem] border border-gray-200 overflow-hidden group hover:shadow-xl hover:shadow-gray-200/50 hover:border-gray-300 transition-all duration-300 flex flex-col relative">
                        
                        <div class="h-52 bg-cover bg-center relative bg-gray-100 overflow-hidden">
                            <img :src="project.image || 'https://images.unsplash.com/photo-1555099962-4199c345e5dd?q=80&w=800&auto=format&fit=crop'" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-60"></div>
                            
                            <span class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-lg text-[10px] font-bold text-gray-900 uppercase tracking-widest shadow-sm">
                                {{ project.category }}
                            </span>
                        </div>
                        
                        <div class="p-6 flex-1 flex flex-col">
                            <h4 class="text-lg font-black text-gray-900 mb-2 leading-tight line-clamp-1 group-hover:text-blue-600 transition-colors">{{ project.title }}</h4>
                            <p class="text-sm text-gray-500 mb-6 line-clamp-2 font-medium leading-relaxed">{{ project.description }}</p>
                            
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100 mt-auto">
                                <a v-if="project.link" :href="project.link" target="_blank" class="text-sm font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1.5 transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">link</span> View Live
                                </a>
                                <span v-else class="text-sm font-medium text-gray-400 italic">No link provided</span>

                                <div class="flex items-center gap-1">
                                    <button @click="openEditModal(project)" class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition-colors" title="Edit">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </button>
                                    <button @click="deleteProject(project.id)" class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Delete">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showShareModal" class="fixed inset-0 z-50 overflow-y-auto font-display">
                <div class="flex items-center justify-center min-h-screen px-4 text-center sm:p-0">
                    <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="showShareModal = false"></div>

                    <div class="relative inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm w-full p-8 md:p-10 text-center">
                        <h3 class="text-2xl font-black text-gray-900 mb-2 tracking-tight">Share Profile</h3>
                        <p class="text-sm text-gray-500 mb-8 font-medium">Let others scan this QR code to view your portfolio and achievements.</p>
                        
                        <div class="bg-white p-4 rounded-2xl border-2 border-gray-100 inline-block mb-8 shadow-sm">
                            <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(shareUrl)}&color=111111`" alt="QR Code" class="w-48 h-48" />
                        </div>

                        <div class="flex items-center gap-2 bg-gray-50 p-1.5 rounded-xl border border-gray-200 mb-6">
                            <input type="text" readonly :value="shareUrl" class="w-full bg-transparent border-none text-xs font-bold text-gray-600 focus:ring-0 outline-none px-3 truncate">
                            <button @click="copyLink" class="bg-white border border-gray-200 shadow-sm hover:bg-gray-50 text-gray-800 px-4 py-2 rounded-lg text-xs font-bold transition-colors">Copy</button>
                        </div>

                        <button @click="showShareModal = false" class="w-full bg-[#111] text-white font-bold py-3.5 rounded-xl hover:bg-black transition-colors">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showModal" class="fixed inset-0 z-[60] overflow-y-auto font-display">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

                    <div class="relative inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full">
                        <form @submit.prevent="submit">
                            <div class="bg-white px-6 py-8 sm:p-10">
                                
                                <div class="flex items-start justify-between mb-8">
                                    <div>
                                        <h3 class="text-2xl font-black text-gray-900 leading-tight tracking-tight">{{ isEditing ? 'Edit Project' : 'Add New Project' }}</h3>
                                        <p class="text-sm font-medium text-gray-500 mt-1">Showcase your skills to the world.</p>
                                    </div>
                                    <button type="button" @click="closeModal" class="text-gray-400 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">close</span>
                                    </button>
                                </div>

                                <div class="space-y-6">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Project Thumbnail</label>
                                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-200 border-dashed rounded-2xl hover:border-gray-300 transition-colors relative group overflow-hidden bg-gray-50">
                                            <div v-if="imagePreview" class="absolute inset-0 w-full h-full">
                                                <img :src="imagePreview" class="w-full h-full object-cover opacity-80 group-hover:opacity-40 transition-opacity" />
                                            </div>
                                            <div class="space-y-2 text-center relative z-10">
                                                <span class="material-symbols-outlined text-4xl text-gray-400 group-hover:text-gray-600 transition-colors">add_photo_alternate</span>
                                                <div class="flex text-sm text-gray-600 justify-center">
                                                    <label for="file-upload" class="relative cursor-pointer bg-white border border-gray-200 shadow-sm rounded-lg font-bold text-gray-800 hover:bg-gray-50 focus-within:outline-none px-3 py-1.5 transition-colors">
                                                        <span>Browse files</span>
                                                        <input id="file-upload" ref="imageInput" type="file" class="sr-only" accept="image/*" @change="handleImageChange">
                                                    </label>
                                                </div>
                                                <p class="text-xs text-gray-500 font-medium">PNG, JPG up to 2MB</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Project Title</label>
                                            <input v-model="form.title" type="text" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#ffde24]/50 focus:border-[#ffde24] focus:bg-white text-gray-900 text-sm font-medium transition-all outline-none" placeholder="e.g. Landing Page" required>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Category</label>
                                            <input v-model="form.category" type="text" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#ffde24]/50 focus:border-[#ffde24] focus:bg-white text-gray-900 text-sm font-medium transition-all outline-none" placeholder="e.g. Web Design" required>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Project URL (Optional)</label>
                                        <input v-model="form.link" type="url" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#ffde24]/50 focus:border-[#ffde24] focus:bg-white text-gray-900 text-sm font-medium transition-all outline-none" placeholder="https://github.com/username/project">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Description</label>
                                        <textarea v-model="form.description" rows="3" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#ffde24]/50 focus:border-[#ffde24] focus:bg-white text-gray-900 text-sm font-medium transition-all outline-none resize-none" placeholder="Briefly describe what you built..." required></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 px-6 py-5 sm:px-10 flex items-center justify-end gap-3 border-t border-gray-100">
                                <button type="button" @click="closeModal" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors text-sm shadow-sm">Cancel</button>
                                <button type="submit" :disabled="form.processing" class="flex items-center justify-center gap-2 px-6 py-2.5 bg-[#111] text-white font-bold rounded-xl shadow-md hover:bg-black transition-all disabled:opacity-50 text-sm active:scale-95">
                                    <span v-if="form.processing" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                                    {{ isEditing ? 'Save Changes' : 'Publish Project' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Transition>

    </StudentLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
.font-display { font-family: 'Inter', sans-serif; }

.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>