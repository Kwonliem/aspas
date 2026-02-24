<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
    students: {
        type: Object,
        default: () => ({ data: [] })
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    availableClasses: {
        type: Array,
        default: () => []
    }
});

const search = ref(props.filters.search || '');
const classFilter = ref(props.filters.class_filter || '');

watch([search, classFilter], debounce(() => {
    router.get(route('admin.students'), { 
        search: search.value, 
        class_filter: classFilter.value 
    }, { 
        preserveState: true, 
        replace: true 
    });
}, 300));

const calculateRank = (index) => {
    return (props.students.current_page - 1) * props.students.per_page + index + 1;
};

const showEditModal = ref(false);
const editingStudent = ref(null);

const editForm = useForm({
    nis: '',
});

const openEditModal = (student) => {
    editingStudent.value = student;
    editForm.nis = student.nis || '';
    editForm.clearErrors();
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingStudent.value = null;
    editForm.reset();
};

const submitEdit = () => {
    editForm.put(route('admin.students.update', editingStudent.value.id), {
        preserveScroll: true,
        onSuccess: () => closeEditModal(),
    });
};

const deleteStudent = (id) => {
    if (confirm('Are you sure you want to permanently delete this student? All their data, progress, portfolios, and avatar will be wiped out.')) {
        router.delete(route('admin.students.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Student Management" />

    <AdminLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 space-y-8 font-display pb-24">
            
            <section class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div>
                    <h2 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">Student Management</h2>
                    <p class="text-sm text-gray-500 font-medium mt-1">Manage all registered students, their NIS, and track their progress.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 md:gap-4 w-full md:w-auto">
                    <div class="relative w-full sm:w-64">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-[20px] pointer-events-none">search</span>
                        <input 
                            v-model="search"
                            class="block w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-colors shadow-sm placeholder:text-gray-400 font-medium" 
                            placeholder="Search name, email, or NIS..." 
                            type="text"
                        />
                    </div>

                    <div class="relative w-full sm:w-48">
                        <select 
                            v-model="classFilter"
                            class="block w-full pl-4 pr-10 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:border-gray-900 focus:ring-1 focus:ring-gray-900 transition-colors shadow-sm appearance-none cursor-pointer font-medium text-gray-700"
                        >
                            <option value="">All Classes</option>
                            <option v-for="cls in availableClasses" :key="cls" :value="cls">
                                Class {{ cls }}
                            </option>
                        </select>
                        
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-[2rem] shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead class="bg-gray-50/80 text-gray-400 text-[10px] uppercase tracking-widest font-black sticky top-0 z-10">
                            <tr>
                                <th class="px-6 sm:px-8 py-5 border-b border-gray-100 whitespace-nowrap">Student Name</th>
                                <th class="px-6 py-5 border-b border-gray-100 whitespace-nowrap">Email Address</th>
                                <th class="px-6 py-5 border-b border-gray-100 whitespace-nowrap">Rank & XP</th>
                                <th class="px-6 py-5 border-b border-gray-100 whitespace-nowrap">Class</th>
                                <th class="px-6 sm:px-8 py-5 border-b border-gray-100 text-right whitespace-nowrap">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <tr v-for="(student, index) in students.data" :key="student.id" class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-6 sm:px-8 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-gray-100 overflow-hidden border border-gray-200 shrink-0">
                                            <img :src="student.avatar || `https://ui-avatars.com/api/?name=${student.name}&background=ffde24&color=000`" alt="Student Avatar" class="w-full h-full object-cover" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 group-hover:text-blue-600 transition-colors leading-tight">{{ student.name }}</p>
                                            <p class="text-xs text-gray-500 font-medium mt-0.5">NIS: <span class="text-gray-700 font-semibold">{{ student.nis || 'Not Set' }}</span></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-medium">
                                    {{ student.email }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[16px]" 
                                            :class="calculateRank(index) <= 3 && students.current_page === 1 ? 'text-yellow-500' : 'text-gray-300'">
                                            military_tech
                                        </span>
                                        <div>
                                            <p class="font-bold text-gray-900 leading-tight text-xs">Rank #{{ calculateRank(index) }}</p>
                                            <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wide mt-0.5">{{ student.xp.toLocaleString() }} XP</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="student.class" class="px-3 py-1 rounded-lg bg-gray-100 text-gray-700 text-[11px] font-bold border border-gray-200 whitespace-nowrap">
                                        {{ student.class }}
                                    </span>
                                    <span v-else class="text-xs text-gray-400 font-medium italic">Unassigned</span>
                                </td>
                                <td class="px-6 sm:px-8 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button @click="openEditModal(student)" class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors shrink-0" title="Edit NIS">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                        </button>
                                        <button @click="deleteStudent(student.id)" class="w-8 h-8 inline-flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors shrink-0" title="Delete Student">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            
                            <tr v-if="students.data.length === 0">
                                <td colspan="5" class="px-6 py-16 text-center text-gray-500 bg-gray-50/50">
                                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-3 border border-gray-200 shadow-sm">
                                        <span class="material-symbols-outlined text-2xl text-gray-400">search_off</span>
                                    </div>
                                    <p class="font-bold text-gray-700">No students found</p>
                                    <p class="text-xs mt-1">Try adjusting your search or class filter.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="students.data.length > 0" class="p-4 sm:px-8 sm:py-5 border-t border-gray-100 bg-white flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="text-[11px] font-bold text-gray-500 uppercase tracking-widest text-center sm:text-left">
                        Showing <span class="text-gray-900">{{ students.from }}</span> to <span class="text-gray-900">{{ students.to }}</span> of <span class="text-gray-900">{{ students.total }}</span> results
                    </div>
                    
                    <div class="flex items-center gap-1.5 flex-wrap justify-center">
                        <Link 
                            v-for="(link, k) in students.links" 
                            :key="k"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-lg transition-colors border',
                                link.url ? 'hover:bg-gray-50 hover:text-gray-900 cursor-pointer' : 'cursor-not-allowed opacity-50 bg-gray-50 text-gray-400 border-transparent',
                                link.active ? 'bg-gray-900 text-white border-gray-900 hover:bg-black hover:text-white' : 'border-gray-200 text-gray-600 bg-white'
                            ]"
                            preserve-scroll
                        />
                    </div>
                </div>
            </section>
        </div>

        <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showEditModal" class="fixed inset-0 z-[60] overflow-y-auto font-display">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="closeEditModal"></div>

                    <div class="relative inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                        <form @submit.prevent="submitEdit">
                            <div class="bg-white px-6 py-8 sm:p-8">
                                <div class="flex items-start justify-between mb-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-600 shrink-0">
                                            <span class="material-symbols-outlined text-2xl">badge</span>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-black text-gray-900 leading-tight">Edit Student NIS</h3>
                                            <p class="text-sm font-medium text-gray-500 mt-0.5 truncate max-w-[200px]">{{ editingStudent?.name }}</p>
                                        </div>
                                    </div>
                                    <button type="button" @click="closeEditModal" class="text-gray-400 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center transition-colors shrink-0">
                                        <span class="material-symbols-outlined text-[18px]">close</span>
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-widest mb-2">NIS (Nomor Induk Siswa)</label>
                                        <input v-model="editForm.nis" type="text" class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-gray-900/20 focus:border-gray-900 focus:bg-white text-gray-900 text-sm font-medium transition-all outline-none" placeholder="Enter new NIS...">
                                        <div v-if="editForm.errors.nis" class="text-red-500 text-xs mt-1.5 font-bold">{{ editForm.errors.nis }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 px-6 py-5 sm:px-8 flex items-center justify-end gap-3 border-t border-gray-100">
                                <button type="button" @click="closeEditModal" class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors text-sm shadow-sm">Cancel</button>
                                <button type="submit" :disabled="editForm.processing" class="flex items-center gap-2 px-6 py-2.5 bg-[#111] text-white font-bold rounded-xl shadow-md hover:bg-black transition-all disabled:opacity-50 text-sm active:scale-95">
                                    <span v-if="editForm.processing" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                                    Save Changes
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