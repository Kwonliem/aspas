<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';


defineProps({
    studentRequests: Object,
    teacherRequests: Object
});

const approve = (id) => {
    if (confirm('DANGER: This will permanently delete the user account and all associated data. Continue?')) {
        router.delete(route('admin.deletions.approve', id), { preserveScroll: true });
    }
};

const reject = (id) => {
    if (confirm('Reject this deletion request?')) {
        router.delete(route('admin.deletions.reject', id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Deletion Requests" />

    <AdminLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 space-y-10 font-display pb-24">
            
            <section class="border-b border-gray-200 pb-6">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">Pending Deletions</h2>
                <p class="text-sm text-gray-500 font-medium mt-1">Review and manage account termination requests from users.</p>
            </section>

            <div class="flex flex-col gap-10">
                
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                    <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-center justify-between bg-white z-10">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                                <span class="material-symbols-outlined text-2xl">school</span>
                            </div>
                            <div>
                                <h3 class="font-black text-gray-900 text-lg">Student Requests</h3>
                                <p class="text-xs text-gray-500 font-medium mt-0.5">Termination requests from student accounts</p>
                            </div>
                        </div>
                        <div class="hidden sm:block">
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-[10px] font-bold uppercase tracking-widest rounded-lg border border-gray-200">
                                {{ studentRequests.total || 0 }} Pending
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse min-w-[800px]">
                            <thead class="bg-gray-50/80 text-gray-400 text-[10px] uppercase font-black tracking-widest sticky top-0">
                                <tr>
                                    <th class="px-6 sm:px-8 py-5 border-b border-gray-100 whitespace-nowrap">User Details</th>
                                    <th class="px-6 py-5 border-b border-gray-100 whitespace-nowrap">Role & Class</th>
                                    <th class="px-6 py-5 border-b border-gray-100 whitespace-nowrap w-1/3">Reason & Date</th>
                                    <th class="px-6 sm:px-8 py-5 border-b border-gray-100 text-right whitespace-nowrap">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <tr v-for="req in studentRequests.data" :key="req.id" class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="px-6 sm:px-8 py-5 align-top">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-200 shrink-0 bg-gray-100">
                                                <img :src="req.user.avatar || `https://ui-avatars.com/api/?name=${req.user.name}&background=random&color=fff`" class="w-full h-full object-cover">
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900 leading-tight">{{ req.user.name }}</p>
                                                <p class="text-xs text-gray-500 font-medium mt-0.5">{{ req.user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 align-top">
                                        <div class="flex flex-col items-start gap-1.5">
                                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 uppercase tracking-widest border border-blue-100">
                                                {{ req.user.role }}
                                            </span>
                                            <span class="text-xs font-semibold text-gray-600">
                                                {{ req.user.class || 'No Class' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 align-top">
                                        <p class="text-xs text-gray-600 italic leading-relaxed line-clamp-2 mb-1.5" :title="req.reason">
                                            "{{ req.reason || 'No reason provided.' }}"
                                        </p>
                                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                                            Requested on: {{ new Date(req.created_at).toLocaleDateString() }}
                                        </span>
                                    </td>
                                    <td class="px-6 sm:px-8 py-5 align-top text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="reject(req.id)" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 text-xs font-bold hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
                                                Reject
                                            </button>
                                            <button @click="approve(req.id)" class="px-4 py-2 rounded-xl bg-red-50 text-red-600 border border-red-100 text-xs font-bold hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                                Approve Deletion
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr v-if="studentRequests.data.length === 0">
                                    <td colspan="4" class="px-6 py-16 text-center text-gray-500 bg-gray-50/50">
                                        <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-200 shadow-sm">
                                            <span class="material-symbols-outlined text-3xl text-green-500">task_alt</span>
                                        </div>
                                        <p class="font-black text-gray-800 text-lg">All Clear!</p>
                                        <p class="text-sm font-medium mt-1">No pending deletion requests from students.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="studentRequests.links.length > 3" class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white">
                        <div class="text-[11px] font-bold text-gray-500 uppercase tracking-widest text-center sm:text-left">
                            Showing {{ studentRequests.from }} to {{ studentRequests.to }} of {{ studentRequests.total }}
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap justify-center">
                            <Link v-for="(link, k) in studentRequests.links" :key="k" :href="link.url || '#'" v-html="link.label"
                                class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-lg transition-colors border text-center min-w-[32px]" 
                                :class="[link.active ? 'bg-gray-900 text-white border-gray-900 hover:bg-black' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50', !link.url ? 'opacity-50 cursor-not-allowed pointer-events-none bg-gray-50 border-transparent' : 'cursor-pointer']" 
                                preserve-scroll 
                            />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                    <div class="px-6 sm:px-8 py-5 border-b border-gray-100 flex items-center justify-between bg-white z-10">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100">
                                <span class="material-symbols-outlined text-2xl">local_library</span>
                            </div>
                            <div>
                                <h3 class="font-black text-gray-900 text-lg">Teacher Requests</h3>
                                <p class="text-xs text-gray-500 font-medium mt-0.5">Termination requests from instructor accounts</p>
                            </div>
                        </div>
                        <div class="hidden sm:block">
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-[10px] font-bold uppercase tracking-widest rounded-lg border border-gray-200">
                                {{ teacherRequests.total || 0 }} Pending
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse min-w-[800px]">
                            <thead class="bg-gray-50/80 text-gray-400 text-[10px] uppercase font-black tracking-widest sticky top-0">
                                <tr>
                                    <th class="px-6 sm:px-8 py-5 border-b border-gray-100 whitespace-nowrap">User Details</th>
                                    <th class="px-6 py-5 border-b border-gray-100 whitespace-nowrap">Role & Subject</th>
                                    <th class="px-6 py-5 border-b border-gray-100 whitespace-nowrap w-1/3">Reason & Date</th>
                                    <th class="px-6 sm:px-8 py-5 border-b border-gray-100 text-right whitespace-nowrap">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-sm">
                                <tr v-for="req in teacherRequests.data" :key="req.id" class="hover:bg-gray-50/50 transition-colors group">
                                    <td class="px-6 sm:px-8 py-5 align-top">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full overflow-hidden border border-gray-200 shrink-0 bg-gray-100">
                                                <img :src="req.user.avatar || `https://ui-avatars.com/api/?name=${req.user.name}&background=random&color=fff`" class="w-full h-full object-cover">
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900 leading-tight">{{ req.user.name }}</p>
                                                <p class="text-[11px] text-gray-500 font-medium mt-0.5">{{ req.user.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 align-top">
                                        <div class="flex flex-col items-start gap-1.5">
                                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 uppercase tracking-widest border border-purple-100">
                                                {{ req.user.role }}
                                            </span>
                                            <span class="text-xs font-semibold text-gray-600">
                                                {{ req.user.subject || 'No Subject' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 align-top">
                                        <p class="text-xs text-gray-600 italic leading-relaxed line-clamp-2 mb-1.5" :title="req.reason">
                                            "{{ req.reason || 'No reason provided.' }}"
                                        </p>
                                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                                            Requested on: {{ new Date(req.created_at).toLocaleDateString() }}
                                        </span>
                                    </td>
                                    <td class="px-6 sm:px-8 py-5 align-top text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="reject(req.id)" class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 text-xs font-bold hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
                                                Reject
                                            </button>
                                            <button @click="approve(req.id)" class="px-4 py-2 rounded-xl bg-red-50 text-red-600 border border-red-100 text-xs font-bold hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                                Approve Deletion
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr v-if="teacherRequests.data.length === 0">
                                    <td colspan="4" class="px-6 py-16 text-center text-gray-500 bg-gray-50/50">
                                        <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-200 shadow-sm">
                                            <span class="material-symbols-outlined text-3xl text-green-500">task_alt</span>
                                        </div>
                                        <p class="font-black text-gray-800 text-lg">All Clear!</p>
                                        <p class="text-sm font-medium mt-1">No pending deletion requests from teachers.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="teacherRequests.links.length > 3" class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white">
                        <div class="text-[11px] font-bold text-gray-500 uppercase tracking-widest text-center sm:text-left">
                            Showing {{ teacherRequests.from }} to {{ teacherRequests.to }} of {{ teacherRequests.total }}
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap justify-center">
                            <Link v-for="(link, k) in teacherRequests.links" :key="k" :href="link.url || '#'" v-html="link.label"
                                class="px-3 py-1.5 text-[11px] font-bold uppercase tracking-wider rounded-lg transition-colors border text-center min-w-[32px]" 
                                :class="[link.active ? 'bg-gray-900 text-white border-gray-900 hover:bg-black' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50', !link.url ? 'opacity-50 cursor-not-allowed pointer-events-none bg-gray-50 border-transparent' : 'cursor-pointer']" 
                                preserve-scroll 
                            />
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
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


.custom-scrollbar::-webkit-scrollbar { height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(229, 231, 235, 1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: rgba(209, 213, 219, 1); }
</style>