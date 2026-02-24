<script setup>
import TeacherLayout from '@/Layouts/TeacherLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const deletionRequest = computed(() => page.props.deletion_request);

// --- AVATAR & PROFILE LOGIC ---
const photoInput = ref(null);
const photoPreview = ref(null);

const profileForm = useForm({
    _method: 'POST',
    name: user.value.name,
    email: user.value.email,
    specialization: user.value.subject || 'development',
    avatar: null,
});

const selectNewPhoto = () => photoInput.value.click();

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];
    if (!photo) return;
    profileForm.avatar = photo;
    const reader = new FileReader();
    reader.onload = (e) => { photoPreview.value = e.target.result; };
    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    if (confirm('Are you sure you want to remove your profile photo?')) {
        router.delete(route('teacher.settings.avatar.delete'), {
            preserveScroll: true,
            onSuccess: () => {
                photoPreview.value = null;
                profileForm.avatar = null;
            },
        });
    }
};

const updateProfile = () => {
    profileForm.post(route('teacher.settings.update'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            if (photoInput.value) photoInput.value.value = null;
            photoPreview.value = null;
        },
    });
};

// --- PASSWORD LOGIC ---
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            alert('Password changed successfully!');
        },
        onError: () => {
            if (passwordForm.errors.password) {
                passwordForm.reset('password', 'password_confirmation');
            }
            if (passwordForm.errors.current_password) {
                passwordForm.reset('current_password');
            }
        },
    });
};

// --- DELETION REQUEST LOGIC ---
const showDeleteModal = ref(false);
const deleteForm = useForm({
    password: '',
    reason: '',
});

const showCurrent = ref(false);
const showNew = ref(false);

const openDeleteModal = () => {
    deleteForm.reset();
    showDeleteModal.value = true;
};

const submitDeletionRequest = () => {
    deleteForm.post(route('teacher.settings.deletion.request'), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            deleteForm.reset();
        }
    });
};
</script>

<template>
    <Head title="Account Settings" />

    <TeacherLayout>
        
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center w-full">
                <div>
                    <h1 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">Account Settings</h1>
                    <p class="text-sm text-gray-500 font-medium mt-1">Manage your profile details and security preferences.</p>
                </div>
            </div>
        </template>

        <div class="flex-1 overflow-y-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 font-display pb-24">
            <div class="max-w-6xl mx-auto space-y-12">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1 space-y-2">
                        <h3 class="text-lg font-black text-gray-900">Your Profile</h3>
                        <p class="text-sm text-gray-500 font-medium leading-relaxed max-w-sm">
                            Keep your personal details up to date. This information will be displayed on your public profile and used for communication.
                        </p>
                    </div>

                    <div class="lg:col-span-2 bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                        <form @submit.prevent="updateProfile">
                            <div class="p-6 sm:p-8 space-y-8">
                                
                                <div class="flex items-center gap-6">
                                    <input ref="photoInput" type="file" class="hidden" @change="updatePhotoPreview">
                                    <div class="relative group cursor-pointer shrink-0" @click="selectNewPhoto">
                                        <div class="w-24 h-24 rounded-full bg-gray-100 border-4 border-white shadow-md overflow-hidden transition-transform transform group-hover:scale-105">
                                            <img :src="photoPreview || user.avatar || `https://ui-avatars.com/api/?name=${user.name}&background=ffde21&color=000`"
                                                alt="Profile" class="w-full h-full object-cover" />
                                        </div>
                                        <div class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <span class="material-symbols-outlined text-white text-xl">photo_camera</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-sm mb-1">Profile Photo</h3>
                                        <p class="text-[11px] text-gray-500 font-medium mb-3">Accepts PNG, JPG. Max 2MB.</p>
                                        <div class="flex gap-2">
                                            <button type="button" @click="selectNewPhoto"
                                                class="px-4 py-2 bg-gray-50 border border-gray-200 text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-100 transition-colors shadow-sm">
                                                Upload New
                                            </button>
                                            <button v-if="user.avatar" type="button" @click="deletePhoto"
                                                class="px-4 py-2 text-red-500 bg-red-50 hover:bg-red-100 text-xs font-bold rounded-lg transition-colors">
                                                Remove
                                            </button>
                                        </div>
                                        <div v-if="profileForm.errors.avatar" class="text-red-500 text-xs mt-2 font-bold">{{ profileForm.errors.avatar }}</div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-gray-100">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Full Name</label>
                                        <input v-model="profileForm.name" type="text"
                                            class="block w-full rounded-xl border-gray-200 bg-gray-50 focus:border-gray-900 focus:bg-white focus:ring-1 focus:ring-gray-900 shadow-sm py-3 px-4 transition-all text-sm text-gray-900 font-medium placeholder-gray-400"
                                            placeholder="e.g. Jane Doe" />
                                        <div v-if="profileForm.errors.name" class="text-red-500 text-xs mt-1.5 font-bold">{{ profileForm.errors.name }}</div>
                                    </div>

                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest">Email Address</label>
                                            <span v-if="user.email_verified_at" class="inline-flex items-center gap-1 text-[9px] bg-green-50 text-green-600 border border-green-200 px-2 py-0.5 rounded-md font-black uppercase tracking-widest">
                                                <span class="material-symbols-outlined text-[10px]">verified</span> Verified
                                            </span>
                                            <span v-else class="text-[9px] bg-yellow-50 text-yellow-600 border border-yellow-200 px-2 py-0.5 rounded-md font-black uppercase tracking-widest">
                                                Unverified
                                            </span>
                                        </div>
                                        <input v-model="profileForm.email" type="email"
                                            class="block w-full rounded-xl border-gray-200 bg-gray-50 focus:border-gray-900 focus:bg-white focus:ring-1 focus:ring-gray-900 shadow-sm py-3 px-4 transition-all text-sm text-gray-900 font-medium placeholder-gray-400"
                                            placeholder="e.g. teacher@school.edu" />
                                        <div v-if="profileForm.errors.email" class="text-red-500 text-xs mt-1.5 font-bold">{{ profileForm.errors.email }}</div>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Specialization / Subject</label>
                                        <div class="relative">
                                            <select v-model="profileForm.specialization"
                                                class="block w-full rounded-xl border-gray-200 bg-gray-50 focus:border-gray-900 focus:bg-white focus:ring-1 focus:ring-gray-900 shadow-sm py-3 px-4 transition-all text-sm appearance-none cursor-pointer font-medium text-gray-900">
                                                <option disabled value="">Select Subject Area</option>
                                                <option value="development">Software Development</option>
                                                <option value="design">UI/UX Design</option>
                                                <option value="data">Data Science</option>
                                                <option value="business">Business & Management</option>
                                                <option value="marketing">Digital Marketing</option>
                                            </select>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-5 sm:px-8 flex justify-end border-t border-gray-100">
                                <button type="submit" :disabled="profileForm.processing"
                                    class="flex items-center justify-center gap-2 py-2.5 px-6 bg-[#111] hover:bg-black text-white font-bold rounded-xl transition-all shadow-md active:scale-95 disabled:opacity-50 text-sm tracking-wide">
                                    <span v-if="profileForm.processing" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                                    <span v-else class="material-symbols-outlined text-[18px]">save</span>
                                    Save Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-8 border-t border-gray-200">
                    <div class="lg:col-span-1 space-y-2">
                        <h3 class="text-lg font-black text-gray-900">Security & Password</h3>
                        <p class="text-sm text-gray-500 font-medium leading-relaxed max-w-sm">
                            Ensure your account stays secure. We recommend using a strong password and changing it periodically.
                        </p>
                    </div>

                    <div class="lg:col-span-2 bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                        <form @submit.prevent="updatePassword">
                            <div class="p-6 sm:p-8 space-y-6">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Current Password</label>
                                    <div class="relative w-full md:w-2/3">
                                        <input v-model="passwordForm.current_password" :type="showCurrent ? 'text' : 'password'"
                                            class="block w-full rounded-xl border-gray-200 bg-gray-50 focus:border-gray-900 focus:bg-white focus:ring-1 focus:ring-gray-900 shadow-sm py-3 pl-4 pr-10 transition-all text-sm font-medium tracking-widest placeholder:tracking-normal placeholder:font-normal"
                                            placeholder="Enter current password" />
                                        <button type="button" @click="showCurrent = !showCurrent"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none p-1 rounded-md transition-colors">
                                            <span class="material-symbols-outlined text-[18px] align-middle">{{ showCurrent ? 'visibility' : 'visibility_off' }}</span>
                                        </button>
                                    </div>
                                    <div v-if="passwordForm.errors.current_password" class="text-red-500 text-xs mt-1.5 font-bold">{{ passwordForm.errors.current_password }}</div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-100">
                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">New Password</label>
                                        <div class="relative">
                                            <input v-model="passwordForm.password" :type="showNew ? 'text' : 'password'"
                                                class="block w-full rounded-xl border-gray-200 bg-gray-50 focus:border-gray-900 focus:bg-white focus:ring-1 focus:ring-gray-900 shadow-sm py-3 pl-4 pr-10 transition-all text-sm font-medium tracking-widest placeholder:tracking-normal placeholder:font-normal"
                                                placeholder="Min. 8 characters" />
                                            <button type="button" @click="showNew = !showNew"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none p-1 rounded-md transition-colors">
                                                <span class="material-symbols-outlined text-[18px] align-middle">{{ showNew ? 'visibility' : 'visibility_off' }}</span>
                                            </button>
                                        </div>
                                        <div v-if="passwordForm.errors.password" class="text-red-500 text-xs mt-1.5 font-bold">{{ passwordForm.errors.password }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Confirm New Password</label>
                                        <input v-model="passwordForm.password_confirmation" :type="showNew ? 'text' : 'password'"
                                            class="block w-full rounded-xl border-gray-200 bg-gray-50 focus:border-gray-900 focus:bg-white focus:ring-1 focus:ring-gray-900 shadow-sm py-3 px-4 transition-all text-sm font-medium tracking-widest placeholder:tracking-normal placeholder:font-normal"
                                            placeholder="Re-enter new password" />
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-5 sm:px-8 flex items-center justify-end gap-3 border-t border-gray-100">
                                <button type="button" @click="passwordForm.reset()"
                                    class="py-2.5 px-5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors text-sm shadow-sm">
                                    Cancel
                                </button>
                                <button type="submit" :disabled="passwordForm.processing"
                                    class="flex items-center justify-center gap-2 py-2.5 px-6 bg-[#ffde24] hover:bg-[#eacb1e] text-black font-bold rounded-xl transition-all shadow-md hover:shadow-lg disabled:opacity-50 text-sm tracking-wide active:scale-95">
                                    <span v-if="passwordForm.processing" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                                    <span v-else class="material-symbols-outlined text-[18px]">key</span>
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pt-8 border-t border-gray-200">
                    <div class="lg:col-span-1 space-y-2">
                        <h3 class="text-lg font-black text-red-600">Danger Zone</h3>
                        <p class="text-sm text-gray-500 font-medium leading-relaxed max-w-sm">
                            Permanently remove your account and all associated data. This action cannot be undone once approved by Admin.
                        </p>
                    </div>

                    <div class="lg:col-span-2 bg-red-50/50 rounded-[2rem] border border-red-100 overflow-hidden flex flex-col justify-center p-6 sm:p-8">
                        
                        <div v-if="deletionRequest" class="bg-white p-5 rounded-xl border border-red-200 flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-yellow-600 text-lg animate-pulse">hourglass_top</span>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900">Deletion Request Pending</h4>
                                    <p class="text-xs text-gray-500 font-medium mt-0.5">Submitted on: {{ new Date(deletionRequest.created_at).toLocaleDateString() }}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-yellow-50 text-yellow-700 text-[10px] font-black uppercase tracking-widest rounded-lg border border-yellow-200 hidden sm:block">Waiting Admin</span>
                        </div>

                        <div v-else class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div>
                                <h4 class="font-bold text-red-800">Request Account Deletion</h4>
                                <p class="text-xs text-red-600/80 font-medium mt-1">Submit a request to Admin to terminate this account.</p>
                            </div>
                            <button @click="openDeleteModal"
                                class="bg-white text-red-600 border border-red-200 hover:bg-red-600 hover:text-white hover:border-red-600 font-bold px-6 py-2.5 rounded-xl transition-all shadow-sm text-sm shrink-0 whitespace-nowrap">
                                Request Deletion
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showDeleteModal" class="fixed inset-0 z-[60] overflow-y-auto font-display">
                <div class="flex items-center justify-center min-h-screen px-4 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="relative inline-block align-bottom bg-white rounded-[2rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-gray-100">
                        <div class="bg-white px-6 py-8 sm:p-8">
                            
                            <div class="flex items-center gap-4 mb-6 border-b border-gray-100 pb-6">
                                <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center text-red-600 shrink-0 border border-red-100">
                                    <span class="material-symbols-outlined text-3xl">delete_forever</span>
                                </div>
                                <div>
                                    <h3 class="text-xl font-black text-gray-900 leading-tight">Delete Account?</h3>
                                    <p class="text-sm font-medium text-gray-500 mt-0.5">This action is permanent and cannot be undone.</p>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 mb-6">
                                <p class="text-sm text-gray-700 font-medium leading-relaxed">
                                    Please tell us why you want to delete your account. This helps us improve our service. <span class="font-bold text-red-500 block mt-1">This requires Admin approval.</span>
                                </p>
                            </div>

                            <div class="space-y-5">
                                <div>
                                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Reason for leaving</label>
                                    <textarea v-model="deleteForm.reason" rows="3"
                                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 shadow-sm px-4 py-3 text-sm font-medium resize-none transition-all placeholder:text-gray-400"
                                        placeholder="I'm not using this anymore..."></textarea>
                                    <div v-if="deleteForm.errors.reason" class="text-red-500 text-xs mt-1.5 font-bold">{{ deleteForm.errors.reason }}</div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Confirm Password</label>
                                    <input v-model="deleteForm.password" type="password"
                                        class="w-full rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-red-500 focus:ring-1 focus:ring-red-500 shadow-sm px-4 py-3 text-sm font-medium transition-all placeholder:text-gray-400 tracking-widest placeholder:tracking-normal"
                                        placeholder="Enter password to confirm">
                                    <div v-if="deleteForm.errors.password" class="text-red-500 text-xs mt-1.5 font-bold">{{ deleteForm.errors.password }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-5 sm:px-8 flex justify-end gap-3 border-t border-gray-100">
                            <button @click="showDeleteModal = false" type="button"
                                class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors text-sm shadow-sm">
                                Cancel
                            </button>
                            <button @click="submitDeletionRequest" :disabled="deleteForm.processing"
                                class="flex items-center gap-2 px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg disabled:opacity-50 text-sm active:scale-95">
                                <span v-if="deleteForm.processing" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                                Submit Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </TeacherLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.font-display {
    font-family: 'Inter', sans-serif;
}
</style>