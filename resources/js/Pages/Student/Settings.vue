<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const deletionRequest = computed(() => page.props.deletion_request);

const photoInput = ref(null);
const photoPreview = ref(null);

const profileForm = useForm({
    _method: 'POST', 
    name: user.value.name,
    email: user.value.email,
    class: user.value.class || '', 
    bio: user.value.bio || '', 
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
        router.delete(route('profile.avatar.delete'), { 
            preserveScroll: true,
            onSuccess: () => {
                photoPreview.value = null;
                profileForm.avatar = null;
            },
        });
    }
};

const updateProfile = () => {
    profileForm.post(route('profile.update'), {
        preserveScroll: true,
        forceFormData: true, 
        onSuccess: () => {
            if (photoInput.value) photoInput.value.value = null;
            photoPreview.value = null;
            
            if (user.value.email_verified_at === null) {
                alert('Profile updated! A verification link has been sent to your new email.');
            } else {
                alert('Profile updated successfully!');
            }
        },
    });
};

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const showCurrent = ref(false);
const showNew = ref(false);

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

const showDeleteModal = ref(false);
const deleteForm = useForm({
    password: '',
    reason: '',
});

const openDeleteModal = () => {
    deleteForm.reset();
    showDeleteModal.value = true;
};

const submitDeletionRequest = () => {
    deleteForm.post(route('profile.deletion.request'), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            deleteForm.reset();
            alert('Deletion request sent to Administrator.');
        }
    });
};
</script>

<template>
    <Head title="Account Settings" />

    <StudentLayout>
        
        <div class="flex-1 overflow-y-auto bg-[#fafafa] h-full font-display">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14 space-y-12 pb-24">

                <div class="border-b border-gray-200 pb-6">
                    <h1 class="text-3xl font-black text-gray-900 tracking-tight">Settings</h1>
                    <p class="text-sm text-gray-500 mt-2 font-medium">Manage your account preferences, profile details, and security.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-1">
                        <h2 class="text-lg font-bold text-gray-900">Profile Information</h2>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed pr-4">
                            Update your account's profile information and email address. This information will be displayed publicly.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <form @submit.prevent="updateProfile" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="p-6 md:p-8 space-y-8">
                                
                                <div class="flex items-center gap-6">
                                    <input ref="photoInput" type="file" class="hidden" @change="updatePhotoPreview" accept="image/*">
                                    <div class="relative group cursor-pointer" @click="selectNewPhoto">
                                        <div class="w-24 h-24 rounded-full bg-gray-100 overflow-hidden ring-4 ring-gray-50">
                                            <img :src="photoPreview || user.avatar || `https://ui-avatars.com/api/?name=${user.name}&background=ffde21&color=000`"
                                                alt="Profile" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
                                        </div>
                                        <div class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <span class="text-white text-xs font-bold uppercase tracking-wider">Change</span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-3">
                                            <button type="button" @click="selectNewPhoto" class="text-sm font-bold text-gray-900 hover:text-yellow-600 transition-colors">
                                                Upload new photo
                                            </button>
                                            <span v-if="user.avatar" class="text-gray-300">•</span>
                                            <button v-if="user.avatar" type="button" @click="deletePhoto" class="text-sm font-bold text-red-500 hover:text-red-700 transition-colors">
                                                Remove
                                            </button>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Recommended size 256x256px. Max 2MB.</p>
                                        <div v-if="profileForm.errors.avatar" class="text-red-500 text-xs mt-2 font-medium">{{ profileForm.errors.avatar }}</div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Full Name</label>
                                        <input v-model="profileForm.name" type="text"
                                            class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:bg-white focus:border-[#ffde24] focus:ring-[#ffde24] transition-colors" />
                                        <div v-if="profileForm.errors.name" class="text-red-500 text-xs mt-1.5 font-medium">{{ profileForm.errors.name }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Email Address</label>
                                        <input v-model="profileForm.email" type="email"
                                            class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:bg-white focus:border-[#ffde24] focus:ring-[#ffde24] transition-colors" />
                                        <div v-if="profileForm.errors.email" class="text-red-500 text-xs mt-1.5 font-medium">{{ profileForm.errors.email }}</div>
                                        
                                        <div v-if="user.email_verified_at === null" class="mt-2 text-[11px] font-bold text-amber-600 flex items-center gap-1.5 bg-amber-50 px-3 py-2 rounded-lg border border-amber-100">
                                            Verification link sent to your new email.
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Class</label>
                                        <input v-model="profileForm.class" type="text"
                                            class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:bg-white focus:border-[#ffde24] focus:ring-[#ffde24] transition-colors" />
                                        <div v-if="profileForm.errors.class" class="text-red-500 text-xs mt-1.5 font-medium">{{ profileForm.errors.class }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Student ID (NIS)</label>
                                        <input :value="user.nis" type="text" disabled
                                            class="block w-full rounded-xl border-gray-200 bg-gray-100 text-gray-500 px-4 py-3 text-sm font-medium cursor-not-allowed" />
                                        <p class="text-[10px] text-gray-400 font-medium mt-1.5">Contact administrator to change this.</p>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Bio</label>
                                        <textarea v-model="profileForm.bio" rows="4"
                                            class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:bg-white focus:border-[#ffde24] focus:ring-[#ffde24] transition-colors resize-none"
                                            placeholder="Write a few sentences about yourself..."></textarea>
                                        <div v-if="profileForm.errors.bio" class="text-red-500 text-xs mt-1.5 font-medium">{{ profileForm.errors.bio }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end">
                                <button type="submit" :disabled="profileForm.processing"
                                    class="px-6 py-2.5 bg-[#111] hover:bg-black text-white text-sm font-bold rounded-xl transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                                    {{ profileForm.processing ? 'Saving...' : 'Save Profile' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <hr class="border-gray-200" />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-1">
                        <h2 class="text-lg font-bold text-gray-900">Security</h2>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed pr-4">
                            Ensure your account is using a long, random password to stay secure.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <form @submit.prevent="updatePassword" class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="p-6 md:p-8 space-y-6">
                                
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Current Password</label>
                                    <div class="relative">
                                        <input v-model="passwordForm.current_password" :type="showCurrent ? 'text' : 'password'"
                                            class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:bg-white focus:border-[#ffde24] focus:ring-[#ffde24] transition-colors pr-12" />
                                        <button type="button" @click="showCurrent = !showCurrent" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 font-bold text-[10px] uppercase tracking-wider">
                                            {{ showCurrent ? 'Hide' : 'Show' }}
                                        </button>
                                    </div>
                                    <div v-if="passwordForm.errors.current_password" class="text-red-500 text-xs mt-1.5 font-medium">{{ passwordForm.errors.current_password }}</div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">New Password</label>
                                        <div class="relative">
                                            <input v-model="passwordForm.password" :type="showNew ? 'text' : 'password'"
                                                class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:bg-white focus:border-[#ffde24] focus:ring-[#ffde24] transition-colors pr-12" />
                                            <button type="button" @click="showNew = !showNew" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 font-bold text-[10px] uppercase tracking-wider">
                                                {{ showNew ? 'Hide' : 'Show' }}
                                            </button>
                                        </div>
                                        <div v-if="passwordForm.errors.password" class="text-red-500 text-xs mt-1.5 font-medium">{{ passwordForm.errors.password }}</div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Confirm Password</label>
                                        <input v-model="passwordForm.password_confirmation" :type="showNew ? 'text' : 'password'"
                                            class="block w-full rounded-xl border-gray-200 bg-gray-50 px-4 py-3 text-sm focus:bg-white focus:border-[#ffde24] focus:ring-[#ffde24] transition-colors" />
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end">
                                <button type="submit" :disabled="passwordForm.processing"
                                    class="px-6 py-2.5 bg-[#111] hover:bg-black text-white text-sm font-bold rounded-xl transition-all shadow-sm disabled:opacity-50 disabled:cursor-not-allowed active:scale-95">
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <hr class="border-gray-200" />

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="md:col-span-1">
                        <h2 class="text-lg font-bold text-red-600">Danger Zone</h2>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed pr-4">
                            Permanently delete your account and all of its contents. This action is irreversible.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <div class="bg-white rounded-2xl shadow-sm border border-red-200 overflow-hidden">
                            <div class="p-6 md:p-8">
                                <p class="text-sm text-gray-700 font-medium mb-6">
                                    Once your account is deleted, all of your resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.
                                </p>

                                <div v-if="deletionRequest" class="inline-flex items-center gap-4 bg-amber-50 border border-amber-200 px-5 py-3.5 rounded-xl">
                                    <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                                    <div>
                                        <p class="text-sm font-bold text-amber-900">Deletion request is pending review.</p>
                                        <p class="text-xs text-amber-700 mt-0.5">Submitted on {{ new Date(deletionRequest.created_at).toLocaleDateString() }}</p>
                                    </div>
                                </div>
                                <button v-else @click="openDeleteModal" class="px-6 py-2.5 bg-white border-2 border-red-100 text-red-600 text-sm font-bold rounded-xl hover:bg-red-50 transition-colors">
                                    Request Account Deletion
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <Transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showDeleteModal" class="fixed inset-0 z-[60] overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false"></div>

                    <div class="relative inline-block bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                        <div class="bg-white px-6 pt-8 pb-6 sm:p-8">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Delete Account</h3>
                            <p class="text-sm text-gray-500 mb-6 font-medium leading-relaxed">
                                Are you sure you want to delete your account? Please confirm your identity by entering your password and a reason for deletion.
                            </p>

                            <div class="space-y-5">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Reason (Optional)</label>
                                    <textarea v-model="deleteForm.reason" rows="3"
                                        class="w-full border-gray-200 rounded-xl bg-gray-50 focus:border-red-500 focus:ring-red-500 text-sm py-3 px-4 resize-none transition-colors"
                                        placeholder="Tell us why you are leaving..."></textarea>
                                    <div v-if="deleteForm.errors.reason" class="text-red-500 text-xs mt-1.5 font-medium">{{ deleteForm.errors.reason }}</div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">Password Confirmation</label>
                                    <input v-model="deleteForm.password" type="password"
                                        class="w-full border-gray-200 rounded-xl bg-gray-50 focus:border-red-500 focus:ring-red-500 text-sm py-3 px-4 transition-colors"
                                        placeholder="••••••••" />
                                    <div v-if="deleteForm.errors.password" class="text-red-500 text-xs mt-1.5 font-medium">{{ deleteForm.errors.password }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-5 flex items-center justify-end gap-3 border-t border-gray-100">
                            <button @click="showDeleteModal = false" type="button"
                                class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-bold rounded-xl hover:bg-gray-50 text-sm transition-colors">
                                Cancel
                            </button>
                            <button @click="submitDeletionRequest" :disabled="deleteForm.processing"
                                class="px-5 py-2.5 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 disabled:opacity-50 text-sm transition-colors shadow-sm">
                                {{ deleteForm.processing ? 'Processing...' : 'Confirm Deletion' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </StudentLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
.font-display {
    font-family: 'Inter', sans-serif;
}
</style>