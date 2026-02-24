<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const logout = () => {
    router.post(route('logout'));
};

const navItems = [
    { name: 'Dashboard', route: 'admin.dashboard', icon: 'dashboard' },
    { name: 'Students', route: 'admin.students', icon: 'school' },
    { name: 'Teachers', route: 'admin.teachers', icon: 'person_apron' },
    { name: 'Pending Deletion', route: 'admin.deletions', icon: 'delete_forever' },
];

const pageTitle = computed(() => {
    if (route().current('admin.dashboard')) return 'Dashboard Overview';
    if (route().current('admin.students')) return 'Student Management';
    if (route().current('admin.teachers')) return 'Teacher Management';
    if (route().current('admin.deletions')) return 'Pending Deletion Requests';
    if (route().current('admin.settings')) return 'Account Settings';
    return 'Admin Panel';
});

// --- TAMBAHAN LOGIKA UNTUK RESPONSIVE (MOBILE MENU) ---
const isMobileMenuOpen = ref(false);

router.on('navigate', () => {
    isMobileMenuOpen.value = false;
});
</script>

<template>
    <div class="bg-background-light font-display text-text-main antialiased selection:bg-primary selection:text-black flex h-screen overflow-hidden">
        
        <Transition 
            enter-active-class="transition-opacity ease-linear duration-300"
            enter-from-class="opacity-0" 
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-linear duration-300" 
            leave-from-class="opacity-100" 
            leave-to-class="opacity-0"
        >
            <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"></div>
        </Transition>

        <aside :class="[
            'fixed inset-y-0 left-0 z-50 w-64 bg-sidebar-bg text-white flex flex-col h-full flex-shrink-0 transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0',
            isMobileMenuOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full'
        ]">

            <div class="h-20 flex items-center justify-between px-6 border-b border-white/10 shrink-0">
                <div class="flex items-center gap-3 cursor-pointer" @click="router.visit(route('admin.dashboard'))">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center p-1.5 shadow-sm">
                        <img src="/images/icon/aspas-logo.svg" alt="Aspas Logo" class="w-full h-full object-contain" />
                    </div>
                    <span class="text-xl font-bold tracking-tight">Aspas.</span>
                </div>
                <button @click="isMobileMenuOpen = false" class="lg:hidden text-gray-400 hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-2xl">close</span>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-3 custom-scrollbar">
                <Link v-for="item in navItems" :key="item.name" :href="route(item.route)" :class="[
                    'flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-200 group relative overflow-hidden',
                    route().current(item.route)
                        ? 'bg-gradient-to-r from-primary/20 to-transparent text-primary border-l-4 border-primary'
                        : 'text-gray-400 hover:text-white hover:bg-white/5'
                ]">
                    <span class="material-symbols-outlined" :class="{ 'text-primary': route().current(item.route) }">
                        {{ item.icon }}
                    </span>
                    <span class="font-medium text-sm tracking-wide">{{ item.name }}</span>
                </Link>

                <div class="pt-6 pb-2 px-3 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                    System
                </div>

                <Link :href="route('admin.settings')" :class="[
                    'flex items-center gap-3 px-3 py-3 rounded-lg transition-all duration-200 group',
                    route().current('admin.settings')
                        ? 'bg-gradient-to-r from-primary/20 to-transparent text-primary border-l-4 border-primary'
                        : 'text-gray-400 hover:text-white hover:bg-white/5'
                ]">
                    <span class="material-symbols-outlined">settings</span>
                    <span class="font-medium text-sm tracking-wide">Settings</span>
                </Link>
            </nav>

            <div class="p-4 border-t border-white/10 shrink-0">
                <button @click="logout"
                    class="flex items-center gap-3 w-full px-3 py-2 text-gray-400 hover:text-red-400 transition-colors group">
                    <span class="material-symbols-outlined group-hover:rotate-180 transition-transform duration-300">logout</span>
                    <span class="font-medium text-sm">Logout</span>
                </button>
            </div>
        </aside>

        <main class="flex-1 flex flex-col h-full relative overflow-hidden bg-gray-50 min-w-0">
            
            <header class="h-16 sm:h-20 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-8 flex-shrink-0 z-10">
                
                <div class="flex-1 flex items-center gap-3 min-w-0">
                    <button @click="isMobileMenuOpen = true" class="lg:hidden text-gray-500 hover:text-gray-900 focus:outline-none p-1 -ml-1 rounded-md hover:bg-gray-100 transition-colors">
                        <span class="material-symbols-outlined text-2xl block">menu</span>
                    </button>
                    
                    <h1 class="text-lg sm:text-2xl font-bold text-gray-800 truncate">{{ pageTitle }}</h1>
                </div>

                <div class="flex items-center gap-4 flex-shrink-0 ml-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold text-gray-800">{{ user.name }}</p>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">{{ user.role }}</p>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto h-full relative custom-scrollbar">
                <slot />
            </div>
        </main>
    </div>
</template>

<style scoped>
/* Agar scrollbar sidebar dan konten tidak kaku di Windows */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.3);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.5);
}
</style>