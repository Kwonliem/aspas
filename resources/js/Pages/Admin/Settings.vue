<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;

const profileForm = useForm({
    name: user.name,
    email: user.email,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updateProfile = () => {
    profileForm.patch(route('admin.settings.update'), {
        preserveScroll: true,
        onSuccess: () => alert('Profile updated successfully!'),
    });
};

const updatePassword = () => {
    // FIX: Jangan auto-fill konfirmasi. Biarkan user yang isi.
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            alert('Password changed successfully!');
        },
        onError: () => {
            if (passwordForm.errors.password || passwordForm.errors.current_password) {
                passwordForm.reset('password', 'password_confirmation');
            }
        },
    });
};
</script>

<template>
    <Head title="Account Settings" />

    <AdminLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12 space-y-10 font-display pb-24">
            
            <section class="border-b border-gray-200 pb-6">
                <h2 class="text-2xl md:text-3xl font-black text-gray-900 tracking-tight">Account Settings</h2>
                <p class="text-sm text-gray-500 font-medium mt-1">Manage your administrator profile and security preferences.</p>
            </section>

            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 sm:px-8 py-6 border-b border-gray-100 bg-white">
                    <h3 class="font-black text-gray-900 text-lg">Public Profile</h3>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">This information will be displayed across the platform.</p>
                </div>
                
                <form @submit.prevent="updateProfile">
                    <div class="p-6 sm:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2" for="full_name">Full Name</label>
                                <input 
                                    v-model="profileForm.name"
                                    id="full_name" 
                                    type="text" 
                                    class="block w-full rounded-xl border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 shadow-sm py-3 px-4 transition-all text-sm font-medium"
                                    placeholder="Enter your full name" 
                                />
                                <div v-if="profileForm.errors.name" class="text-red-500 text-xs mt-1.5 font-bold">{{ profileForm.errors.name }}</div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest" for="email">Email Address</label>
                                    <span v-if="user.email_verified_at" class="flex items-center gap-1 text-[9px] bg-green-50 text-green-600 border border-green-200 px-2 py-0.5 rounded-md font-bold uppercase tracking-widest">
                                        <span class="material-symbols-outlined text-[12px]">verified</span> Verified
                                    </span>
                                    <span v-else class="text-[9px] bg-yellow-50 text-yellow-600 border border-yellow-200 px-2 py-0.5 rounded-md font-bold uppercase tracking-widest">
                                        Unverified
                                    </span>
                                </div>
                                <input 
                                    v-model="profileForm.email"
                                    id="email" 
                                    type="email" 
                                    class="block w-full rounded-xl border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 shadow-sm py-3 px-4 transition-all text-sm font-medium"
                                    placeholder="Enter your email address" 
                                />
                                <div v-if="profileForm.errors.email" class="text-red-500 text-xs mt-1.5 font-bold">{{ profileForm.errors.email }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-6 py-5 sm:px-8 flex justify-end border-t border-gray-100">
                        <button 
                            type="submit" 
                            :disabled="profileForm.processing"
                            class="flex items-center justify-center gap-2 py-2.5 px-6 bg-[#111] hover:bg-black text-white font-bold rounded-xl transition-all shadow-sm active:scale-95 disabled:opacity-50 text-sm tracking-wide"
                        >
                            <span v-if="profileForm.processing" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                            Save Profile
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 sm:px-8 py-6 border-b border-gray-100 bg-white">
                    <h3 class="font-black text-gray-900 text-lg">Change Password</h3>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Ensure your account is using a long, random password to stay secure.</p>
                </div>

                <form @submit.prevent="updatePassword">
                    <div class="p-6 sm:p-8 space-y-6">
                        
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Current Password</label>
                            <input 
                                v-model="passwordForm.current_password"
                                type="password" 
                                class="block w-full md:w-1/2 rounded-xl border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 shadow-sm py-3 px-4 transition-all text-sm font-medium tracking-widest placeholder:tracking-normal placeholder:font-normal"
                                placeholder="Enter current password" 
                            />
                            <div v-if="passwordForm.errors.current_password" class="text-red-500 text-xs mt-1.5 font-bold">{{ passwordForm.errors.current_password }}</div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2 border-t border-gray-100">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">New Password</label>
                                <input 
                                    v-model="passwordForm.password"
                                    type="password" 
                                    class="block w-full rounded-xl border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 shadow-sm py-3 px-4 transition-all text-sm font-medium tracking-widest placeholder:tracking-normal placeholder:font-normal"
                                    placeholder="Min. 8 characters" 
                                />
                                <div v-if="passwordForm.errors.password" class="text-red-500 text-xs mt-1.5 font-bold">{{ passwordForm.errors.password }}</div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2">Confirm New Password</label>
                                <input 
                                    v-model="passwordForm.password_confirmation"
                                    type="password" 
                                    class="block w-full rounded-xl border-gray-200 bg-gray-50 text-gray-900 focus:bg-white focus:border-gray-900 focus:ring-1 focus:ring-gray-900 shadow-sm py-3 px-4 transition-all text-sm font-medium tracking-widest placeholder:tracking-normal placeholder:font-normal"
                                    placeholder="Re-enter new password" 
                                />
                            </div>
                        </div>

                    </div>

                    <div class="bg-gray-50 px-6 py-5 sm:px-8 flex items-center justify-end gap-3 border-t border-gray-100">
                        <button 
                            type="button"
                            @click="passwordForm.reset()"
                            class="py-2.5 px-5 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition-colors text-sm shadow-sm"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            :disabled="passwordForm.processing"
                            class="flex items-center justify-center gap-2 py-2.5 px-6 bg-[#ffde24] hover:bg-[#eacb1e] text-black font-bold rounded-xl transition-all shadow-md hover:shadow-lg disabled:opacity-50 text-sm tracking-wide active:scale-95"
                        >
                            <span v-if="passwordForm.processing" class="material-symbols-outlined animate-spin text-[18px]">sync</span>
                            <span v-else class="material-symbols-outlined text-[18px]">key</span>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </AdminLayout>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

.font-display {
    font-family: 'Inter', sans-serif;
}
</style>