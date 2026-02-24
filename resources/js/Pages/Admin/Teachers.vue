<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    teachers: Object,
    filters: Object,
});

const search = ref(props.filters.search || '');
const showModal = ref(false);
const isEditing = ref(false); 
const editingTeacherId = ref(null); 

const form = useForm({
    name: '',
    email: '',
    subject: '',
    password: '',
    password_confirmation: '',
});

const modalTitle = computed(() => isEditing.value ? 'Edit Teacher' : 'Add New Mentor');
const modalButton = computed(() => isEditing.value ? 'Update Account' : 'Create Account');

watch(search, debounce((value) => {
    router.get(route('admin.teachers'), { search: value }, { preserveState: true, replace: true });
}, 300));

const openCreateModal = () => {
    isEditing.value = false;
    editingTeacherId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (teacher) => {
    isEditing.value = true;
    editingTeacherId.value = teacher.id;
    
    form.name = teacher.name;
    form.email = teacher.email;
    form.subject = teacher.specialization; 
    form.password = ''; 
    form.password_confirmation = '';
    
    form.clearErrors();
    showModal.value = true;
};

const deleteTeacher = (teacher) => {
    if (confirm(`Are you sure you want to delete ${teacher.name}?`)) {
        router.delete(route('admin.teachers.destroy', teacher.id), {
            preserveScroll: true,
        });
    }
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.teachers.update', editingTeacherId.value), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.teachers.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};
</script>

<template>
    <Head title="Teacher Management" />

    <AdminLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 space-y-8 font-display pb-24">
            
            <section class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h2 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">Teacher Management</h2>
                    <p class="text-sm text-gray-500 font-medium mt-1">Manage instructor accounts, subjects, and credentials.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-4 w-full md:w-auto">
                    <div class="relative w-full sm:w-72">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-[20px] pointer-events-none">search</span>
                        <input 
                            v-model="search" 
                            class="block w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-colors shadow-sm placeholder:text-gray-400 font-medium" 
                            placeholder="Search by name or email..." 
                            type="text" 
                        />
                    </div>

                    <button @click="openCreateModal" class="w-full sm:w-auto flex justify-center items-center gap-2 bg-[#111] hover:bg-black text-white font-bold px-6 py-2.5 rounded-xl transition-all shadow-md transform active:scale-95 text-sm">
                        <span class="material-symbols-outlined text-[18px]">person_add</span>
                        Add New Teacher
                    </button>
                </div>
            </section>

            <section class="bg-white rounded-[2rem] shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead class="bg-gray-50/80 text-gray-400 text-[10px] uppercase tracking-widest font-black sticky top-0 z-10">
                            <tr>
                                <th class="px-6 sm:px-8 py-5 border-b border-gray-100 whitespace-nowrap">Teacher Details</th>
                                <th class="px-6 py-5 border-b border-gray-100 whitespace-nowrap">Email Address</th>
                                <th class="px-6 py-5 border-b border-gray-100 whitespace-nowrap">Specialization</th>
                                <th class="px-6 py-5 border-b border-gray-100 whitespace-nowrap">Joining Date</th>
                                <th class="px-6 sm:px-8 py-5 border-b border-gray-100 text-right whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <tr v-for="teacher in teachers.data" :key="teacher.id" class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-6 sm:px-8 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-gray-100 overflow-hidden border border-gray-200 shrink-0">
                                            <img :src="teacher.avatar || `https://ui-avatars.com/api/?name=${teacher.name}&background=random`" class="w-full h-full object-cover"/>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 group-hover:text-blue-600 transition-colors leading-tight">{{ teacher.name }}</p>
                                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">ID: T-{{ 1000 + teacher.id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-medium">
                                    {{ teacher.email }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-lg bg-blue-50 text-blue-700 text-[11px] font-bold border border-blue-100 whitespace-nowrap">
                                        {{ teacher.specialization }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500 text-xs font-medium">
                                    {{ teacher.joined_at }}
                                </td>
                                <td class="px-6 sm:px-8 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="openEditModal(teacher)" class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors shrink-0" title="Edit Teacher">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <button @click="deleteTeacher(teacher)" class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors shrink-0" title="Delete Teacher">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr v-if="teachers.data.length === 0">
                                <td colspan="5" class="px-6 sm:px-8 py-16 text-center text-gray-500 bg-gray-50/50">
                                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-3 border border-gray-200 shadow-sm">
                                        <span class="material-symbols-outlined text-2xl text-gray-400">person_off</span>
                                    </div>
                                    <p class="font-bold text-gray-700">No teachers found</p>
                                    <p class="text-xs mt-1">Try adjusting your search query or add a new teacher.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div v-if="teachers.data.length > 0" class="p-4 sm:px-8 sm:py-5 border-t border-gray-100 bg-white flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-[11px] font-bold text-gray-500 uppercase tracking-widest text-center sm:text-left">
                        Showing <span class="text-gray-900">{{ teachers.from }}</span> to <span class="text-gray-900">{{ teachers.to }}</span> of <span class="text-gray-900">{{ teachers.total }}</span> teachers
                    </div>
                    <div class="flex items-center gap-1.5 flex-wrap justify-center">
                        <Link 
                            v-for="(link, k) in teachers.links" 
                            :key="k" 
                            :href="link.url || '#'" 
                            v-html="link.label" 
                            :class="[
                                'px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-lg transition-colors border', 
                                link.url ? 'hover:bg-gray-50 hover:text-gray-900 cursor-pointer' : 'cursor-not-allowed opacity-50 bg-gray-50 text-gray-400 border-transparent', 
                                link.active ? 'bg-gray-900 text-white border-gray-900 hover:bg-black hover:text-white' : 'border-gray-200 text-gray-600 bg-white'
                            ]" 
                        />
                    </div>
                </div>
            </section>
        </div>

        <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showModal" class="fixed inset-0 z-[60] overflow-y-auto font-display">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="closeModal"></div>

                    <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                        <form @submit.prevent="submit">
                            <div class="bg-white px-6 py-8 sm:p-8">
                                <div class="flex items-start justify-between mb-8">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0 border border-blue-100">
                                            <span class="material-symbols-outlined text-2xl">
                                                {{ isEditing ? 'edit_square' : 'person_add' }}
                                            </span>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-black text-gray-900 leading-tight">{{ modalTitle }}</h3>
                                            <p class="text-sm font-medium text-gray-500 mt-0.5">
                                                {{ isEditing ? 'Update teacher details.' : 'Create a new account for a teacher.' }}
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" @click="closeModal" class="text-gray-400 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center transition-colors shrink-0">
                                        <span class="material-symbols-outlined text-[18px]">close</span>
                                    </button>
                                </div>

                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-2">Full Name</label>
                                        <input v-model="form.name" type="text" class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-gray-900 focus:ring-1 focus:ring-gray-900 focus:bg-white text-gray-900 text-sm font-medium transition-all outline-none" placeholder="e.g. Jane Doe">
                                        <div v-if="form.errors.name" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.name }}</div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-2">Email Address</label>
                                            <input v-model="form.email" type="email" class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-gray-900 focus:ring-1 focus:ring-gray-900 focus:bg-white text-gray-900 text-sm font-medium transition-all outline-none" placeholder="teacher@school.edu">
                                            <div v-if="form.errors.email" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.email }}</div>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-2">Subject</label>
                                            <input v-model="form.subject" type="text" class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-gray-900 focus:ring-1 focus:ring-gray-900 focus:bg-white text-gray-900 text-sm font-medium transition-all outline-none" placeholder="e.g. Mathematics">
                                            <div v-if="form.errors.subject" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.subject }}</div>
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-gray-100">
                                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-4">Security</p>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-2">Password</label>
                                                <input v-model="form.password" type="password" class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-gray-900 focus:ring-1 focus:ring-gray-900 focus:bg-white text-gray-900 text-sm font-medium transition-all outline-none" :placeholder="isEditing ? 'Leave blank to keep' : 'Min. 8 characters'">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-2">Confirm Password</label>
                                                <input v-model="form.password_confirmation" type="password" class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-gray-900 focus:ring-1 focus:ring-gray-900 focus:bg-white text-gray-900 text-sm font-medium transition-all outline-none" placeholder="Re-enter password">
                                            </div>
                                        </div>
                                        <div v-if="form.errors.password" class="text-red-500 text-xs mt-1.5 font-bold">{{ form.errors.password }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 px-6 py-5 sm:px-8 flex items-center justify-end gap-3 border-t border-gray-100">
                                <button type="button" @click="closeModal" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors text-sm shadow-sm">Cancel</button>
                                <button type="submit" :disabled="form.processing" class="flex items-center gap-2 px-6 py-2.5 bg-[#111] text-white font-bold rounded-xl shadow-md hover:bg-black transition-all disabled:opacity-50 text-sm active:scale-95 tracking-wide">
                                    <span v-if="form.processing" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                                    {{ modalButton }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Transition>
    </AdminLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.font-display {
    font-family: 'Inter', sans-serif;
}

/* Custom scrollbar untuk tabel overflow horizontal */
.custom-scrollbar::-webkit-scrollbar { height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(229, 231, 235, 1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: rgba(209, 213, 219, 1); }
</style>