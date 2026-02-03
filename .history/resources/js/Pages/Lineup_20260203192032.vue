<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import UserMenu from "../Components/UserMenu.vue";
import VotingOverlay from "../Components/VotingOverlay.vue";

const props = defineProps({
    artists: Array,
    event: Object,
    userRole: String,
});

// Carousel Logic
const carouselImages = [
    '/assets/tusker_1.png',
    '/assets/tusker_2.png',
    '/assets/tusker_3.png',
];
const currentSlide = ref(0);
let autoPlayInterval = null;

const startAutoPlay = () => {
    autoPlayInterval = setInterval(() => {
        currentSlide.value = (currentSlide.value + 1) % carouselImages.length;
    }, 5000);
};

const searchQuery = ref("");
const activeTab = ref("upcoming");
const showVoting = ref(false);
const activeArtist = ref(null);
const timeRemaining = ref("");
let timerInterval = null;

const featuredArtist = computed(() => {
    if (liveArtist.value) return liveArtist.value;
    return (props.artists || []).find((a) => a.status === "upcoming") || null;
});

const liveArtist = computed(() => (props.artists || []).find((a) => a.status === "live"));

const filteredArtistsBySearch = computed(() => {
    if (!searchQuery.value) return props.artists || [];
    return (props.artists || []).filter(
        (a) => a.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
               a.genre.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const displayArtists = computed(() => {
    const list = filteredArtistsBySearch.value.filter((a) => a.id !== featuredArtist.value?.id);
    if (searchQuery.value) return list;
    return activeTab.value === "upcoming" 
        ? list.filter((a) => a.status === "upcoming" || a.status === "live")
        : list.filter((a) => a.status === "closed");
});

const calculateTimeLeft = () => {
    if (!liveArtist.value?.voting_ends_at) {
        timeRemaining.value = "";
        return;
    }
    const end = new Date(liveArtist.value.voting_ends_at).getTime();
    const diff = end - new Date().getTime();
    if (diff <= 0) {
        timeRemaining.value = " (EXPIRED)";
        return;
    }
    const mins = Math.floor(diff / 60000);
    const secs = Math.floor((diff % 60000) / 1000);
    timeRemaining.value = ` (${mins}:${secs.toString().padStart(2, "0")})`;
};

onMounted(() => {
    timerInterval = setInterval(calculateTimeLeft, 1000);
    startAutoPlay();
    
    if (window.Echo) {
        window.Echo.channel("performances")
            .listen(".performance.updated", () => router.reload({ preserveScroll: true }))
            .listen(".lineup.updated", () => router.reload({ preserveScroll: true }));
    }
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
    if (autoPlayInterval) clearInterval(autoPlayInterval);
});

const openVoting = (artist) => {
    activeArtist.value = artist;
    showVoting.value = true;
};
</script>

<template>
    <div class="min-h-screen bg-brand-black text-white font-sans pb-32">
        <Head title="Lineup - StarYako" />

        <nav class="sticky top-0 z-50 glass-nav px-6 py-4 flex items-center justify-between border-b border-white/10">
            <Link href="/lineup" class="flex items-center active:scale-95 transition-transform">
                <img :src="'/assets/star-yako-logo.png'" alt="Star Yako Logo" class="h-8 w-auto object-contain" />
            </Link>
            <UserMenu />
        </nav>

        <div class="px-6 pt-6">
            <div class="relative mb-6 group overflow-hidden rounded-2xl border border-white/10 shadow-2xl aspect-[14/11] sm:aspect-[21/9] bg-brand-gray/20">
                <div 
                    v-for="(img, index) in carouselImages" 
                    :key="index"
                    class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                    :class="currentSlide === index ? 'opacity-100 z-10' : 'opacity-0 z-0'"
                >
                    <img :src="img" class="w-full h-full object-cover transform transition-transform duration-[8000ms]" :class="currentSlide === index ? 'scale-110' : 'scale-100'" />
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-black/90 via-transparent to-transparent"></div>
                </div>

                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                    <button 
                        v-for="(_, i) in carouselImages" 
                        :key="i"
                        @click="currentSlide = i"
                        class="h-1.5 rounded-full transition-all duration-300"
                        :class="currentSlide === i ? 'w-8 bg-brand-yellow' : 'w-2 bg-white/30'"
                    ></button>
                </div>
            </div>

            <div class="relative mb-8">
                <input v-model="searchQuery" type="text" placeholder="Search artist or genre..." class="w-full bg-brand-gray/20 border border-white/10 px-12 py-4 rounded-2xl outline-none focus:border-brand-yellow/50 transition-all font-medium" />
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="p-4 sm:p-6 rounded-[2rem] border-2 border-brand-yellow/40 bg-gradient-to-b from-brand-yellow/5 to-transparent shadow-[0_0_20px_rgba(255,205,0,0.05)] mb-10">
                
                <div class="flex items-center justify-between mb-8 px-2">
                    <header>
                        <h2 class="text-3xl font-extrabold tracking-tight italic uppercase">Lineup</h2>
                        <p class="text-brand-yellow/70 font-bold uppercase text-[9px] tracking-[0.2em]">Tonight's Schedule</p>
                    </header>
                    
                    <div class="flex gap-4">
                        <button @click="activeTab = 'upcoming'" class="text-[10px] font-black uppercase tracking-widest transition-all" :class="activeTab === 'upcoming' ? 'text-brand-yellow' : 'text-gray-500'">Lined Up</button>
                        <button @click="activeTab = 'closed'" class="text-[10px] font-black uppercase tracking-widest transition-all" :class="activeTab === 'closed' ? 'text-brand-yellow' : 'text-gray-500'">Past</button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 sm:gap-6">
                    <div v-if="featuredArtist && !searchQuery" class="col-span-2 mb-2">
                        <div class="relative group rounded-[1.8rem] overflow-hidden border border-brand-yellow/50 glass-card h-[180px] sm:h-[220px]">
                            <div class="relative z-10 flex h-full items-center">
                                <div class="flex-1 p-5 sm:p-8">
                                    <p class="text-brand-yellow text-[9px] font-black uppercase tracking-[0.2em] mb-1">
                                        {{ featuredArtist.status === "live" ? "Performing Now" : "Next Up" }}
                                    </p>
                                    <h3 class="text-2xl sm:text-4xl font-black italic uppercase leading-none mb-4 truncate">{{ featuredArtist.name }}</h3>
                                    <div class="flex gap-2">
                                        <Link :href="`/artist/${featuredArtist.id}`" class="bg-white/10 text-white font-black py-2 px-4 rounded-xl uppercase text-[8px] tracking-widest border border-white/10">Profile</Link>
                                        <button v-if="featuredArtist.status === 'live' && !featuredArtist.hasVoted" @click="openVoting(featuredArtist)" class="bg-brand-yellow text-black font-black py-2 px-4 rounded-xl uppercase text-[8px] tracking-widest">Vote Now</button>
                                    </div>
                                </div>
                                <div class="w-1/3 h-full relative overflow-hidden">
                                    <img :src="featuredArtist.image" class="w-full h-full object-cover" />
                                    <div class="absolute inset-0 bg-gradient-to-r from-brand-black via-brand-black/20 to-transparent"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-for="artist in displayArtists" :key="artist.id" 
                        class="group relative p-1 rounded-[1.5rem] border-2 transition-all duration-300"
                        :class="artist.status === 'live' ? 'border-brand-yellow shadow-[0_0_15px_rgba(255,205,0,0.3)] bg-brand-yellow/5' : 'border-white/10 bg-brand-gray/10'"
                    >
                        <div class="relative mb-3">
                            <Link :href="`/artist/${artist.id}`" class="block aspect-square rounded-[1.2rem] overflow-hidden">
                                <img :src="artist.image" class="w-full h-full object-cover" />
                                <div v-if="artist.status === 'upcoming'" class="absolute bottom-2 left-2 px-2 py-1 bg-black/70 backdrop-blur-md rounded-lg text-[8px] font-black uppercase tracking-tighter border border-white/10">Upcoming</div>
                            </Link>
                        </div>
                        <div class="px-2 pb-2">
                            <h3 class="font-bold text-sm leading-tight truncate uppercase italic tracking-tighter">{{ artist.name }}</h3>
                            <div class="flex justify-between mt-1 items-center">
                                <p class="text-brand-yellow text-[8px] font-black uppercase tracking-widest">{{ artist.genre }}</p>
                                <p class="text-gray-500 text-[9px] font-bold italic">{{ artist.scheduled_time }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="fixed bottom-8 left-1/2 -translate-x-1/2 w-[90%] max-w-sm z-40">
            <button v-if="liveArtist" @click="liveArtist.voting_started_at && !liveArtist.hasVoted ? openVoting(liveArtist) : null" 
                class="w-full py-4 rounded-full font-black uppercase text-xs tracking-[0.2em] flex items-center justify-center gap-2 transition-all shadow-2xl"
                :class="liveArtist.hasVoted ? 'bg-green-600 text-white' : 'bg-brand-yellow text-black shadow-[0_10px_40px_rgba(255,205,0,0.4)]'"
            >
                <span v-if="!liveArtist.hasVoted" class="w-2 h-2 rounded-full bg-black animate-pulse"></span>
                <span>{{ liveArtist.hasVoted ? 'Vote Recorded' : 'Vote: ' + liveArtist.name }}</span>
            </button>
        </div>

        <VotingOverlay v-if="activeArtist" :show="showVoting" :artist="activeArtist" :questions="event?.questions || []" :isJudge="userRole === 'judge'" @close="showVoting = false" @submit="null" />
    </div>
</template>

<style scoped>
.glass-nav {
    background: rgba(0, 0, 0, 0.9);
    backdrop-filter: blur(20px);
}
.glass-card {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(10px);
}
</style>