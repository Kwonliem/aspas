<script setup>
import { ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import StudentLayout from '@/Layouts/StudentLayout.vue';

// Menerima data asli dari backend (Controller)
defineProps({
    podium: {
        type: Array,
        default: () => []
    },
    rankings: {
        type: Array,
        default: () => []
    },
    currentUserInfo: {
        type: Object,
        default: null
    }
});

const filterTab = ref('all_time');

// Fungsi bantuan untuk men-generate warna random avatar jika user tidak punya foto
const getInitialsBg = (name) => {
    const colors = ['bg-blue-100 text-blue-600', 'bg-green-100 text-green-600', 'bg-red-100 text-red-600', 'bg-purple-100 text-purple-600', 'bg-yellow-100 text-yellow-700'];
    const charCode = name.charCodeAt(0);
    return colors[charCode % colors.length];
};
</script>

<template>
    <Head title="Leaderboard" />

    <StudentLayout>
        <div class="p-8 max-w-7xl mx-auto space-y-8 font-display">
            
            <section class="text-center mb-10">
                <h2 class="text-3xl font-black text-gray-900 tracking-tight mb-2">Leaderboard</h2>
                <p class="text-gray-500 font-medium max-w-2xl mx-auto text-sm">Top performers of all time. Earn XP by completing lessons, assignments, and quizzes to climb the ranks!</p>
            </section>

            <section v-if="podium.length > 0" class="flex justify-center items-end gap-4 md:gap-8 mb-16 px-4">
                <div v-for="person in podium" :key="person.rank" :class="['flex flex-col items-center relative', person.rank === 1 ? 'z-10 -mb-4' : '']">
                    
                    <div class="mb-3 relative flex flex-col items-center">
                        <span v-if="person.rank === 1" class="absolute -top-8 text-4xl animate-bounce">👑</span>
                        
                        <div :class="[
                            'rounded-full overflow-hidden border-4 shadow-sm flex items-center justify-center font-bold',
                            person.rank === 1 ? 'w-24 h-24 border-yellow-300 bg-yellow-50 shadow-lg text-2xl text-yellow-700' : 'w-20 h-20 border-gray-200 bg-gray-50 text-xl text-gray-500'
                        ]">
                            <img v-if="person.image" :alt="person.name" class="w-full h-full object-cover" :src="person.image" />
                            <span v-else>{{ person.initials }}</span>
                        </div>
                        
                        <div :class="[
                            'absolute -bottom-2 text-white text-xs font-black flex items-center justify-center rounded-full border-2 border-white shadow-sm',
                            person.rank === 1 ? 'w-7 h-7 bg-yellow-400' : 'w-6 h-6 bg-gray-400',
                            person.rank === 3 ? 'bg-orange-400' : ''
                        ]">
                            {{ person.rank }}
                        </div>
                    </div>

                    <div class="text-center mb-2">
                        <p class="font-bold text-gray-900 text-sm md:text-base">{{ person.name }}</p>
                        <p :class="[
                            'text-xs font-bold mt-1',
                            person.rank === 1 ? 'text-yellow-700 bg-yellow-100 px-3 py-1 rounded-full inline-block' : 'text-gray-500'
                        ]">
                            {{ person.xp }} XP
                        </p>
                    </div>

                    <div :class="[
                        'rounded-t-xl shadow-sm flex items-end justify-center relative group w-20 md:w-32',
                        person.rank === 1 ? 'h-40 md:h-56 bg-gradient-to-t from-yellow-400 to-[#ffde24] md:w-40 shadow-lg pb-6' : '',
                        person.rank === 2 ? 'h-32 md:h-40 bg-gradient-to-t from-gray-300 to-gray-200 pb-4' : '',
                        person.rank === 3 ? 'h-24 md:h-32 bg-gradient-to-t from-orange-300 to-orange-200 pb-4' : ''
                    ]">
                        <span :class="[
                            'font-black text-white/50 group-hover:text-white/80 transition-colors',
                            person.rank === 1 ? 'text-6xl' : 'text-4xl'
                        ]">
                            {{ person.rank }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b border-gray-100">
                    <h3 class="text-xl font-black text-gray-900">Rankings</h3>
                    <div class="flex gap-2 bg-gray-50 p-1 rounded-xl border border-gray-100">
                        <button @click="filterTab = 'all_time'" :class="['px-4 py-2 text-xs font-bold rounded-lg transition-all', filterTab === 'all_time' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']">All Time</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50/50 text-xs uppercase tracking-wider text-gray-400 font-bold border-b border-gray-100">
                                <th class="px-6 py-4 w-24 text-center">Rank</th>
                                <th class="px-6 py-4">Student</th>
                                <th class="px-6 py-4">Level</th>
                                <th class="px-6 py-4 text-right">Total XP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-sm">
                            <tr v-for="student in rankings" :key="student.rank" class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-6 py-4 text-center font-black text-gray-400">{{ student.rank }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div v-if="student.image" class="w-8 h-8 rounded-full overflow-hidden border border-gray-200">
                                            <img :alt="student.name" class="w-full h-full object-cover" :src="student.image" />
                                        </div>
                                        <div v-else :class="['w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs uppercase', getInitialsBg(student.name)]">
                                            {{ student.initials }}
                                        </div>
                                        <span class="font-bold text-gray-900">{{ student.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1.5 bg-purple-50 text-purple-700 rounded-lg text-xs font-bold border border-purple-100">{{ student.level }}</span>
                                </td>
                                <td class="px-6 py-4 text-right font-black text-gray-900">{{ student.xp }} XP</td>
                            </tr>
                            
                            <tr v-if="rankings.length === 0">
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500 font-medium">No other students found in the ranking.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="currentUserInfo" class="bg-[#1a180b] border-t border-gray-100 relative overflow-hidden mt-2">
                    <div class="absolute left-0 top-0 h-full w-1.5 bg-[#ffde24]"></div>
                    <div class="absolute inset-0 bg-[#ffde24]/5 pointer-events-none"></div>
                    
                    <div class="flex items-center gap-6 px-6 py-5 z-10 relative">
                        <div class="w-12 text-center font-black text-[#ffde24] text-xl">{{ currentUserInfo.rank }}</div>
                        
                        <div class="flex items-center gap-4 flex-1">
                            <div class="w-12 h-12 rounded-full border-2 border-[#ffde24] p-0.5 bg-white overflow-hidden flex items-center justify-center font-bold uppercase text-gray-600">
                                <img v-if="currentUserInfo.image" :alt="currentUserInfo.name" class="w-full h-full rounded-full object-cover" :src="currentUserInfo.image" />
                                <span v-else>{{ currentUserInfo.initials }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-white block text-base">{{ currentUserInfo.name }}</span>
                                <span class="text-xs text-[#ffde24] font-medium">
                                    {{ currentUserInfo.rank <= 3 ? 'Amazing work! You are in the top 3!' : 'Keep going! Climb the ranks.' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-8">
                            <div class="hidden sm:block">
                                <span class="px-3 py-1.5 bg-white/10 text-white rounded-lg text-xs font-bold border border-white/20">{{ currentUserInfo.level }}</span>
                            </div>
                            <div class="text-right">
                                <span class="block font-black text-[#ffde24] text-xl">{{ currentUserInfo.xp }} XP</span>
                                <span class="text-[10px] font-bold text-white/50 uppercase tracking-widest">Total Points</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </StudentLayout>
</template>