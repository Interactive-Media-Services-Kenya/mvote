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
    '/assets/carousel_1.png',
    '/assets/carousel_2.png',
    '/assets/carousel_3.png',
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

// Logic for featured/live artists
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
        <Head title="Lineup - MVote" />

        <nav class="sticky top-0 z-50 glass-nav px-6 py-4 flex items-center justify-between">
            <h1 class="text-2xl font-black italic tracking-tighter">
                M<span class="text-brand-yellow">VOTE</span>
            </h1>
            <UserMenu />
        </nav>

        <div class="px-6 pt-8 animate-fade-up">
            
            <div class="relative mb-12 group p-[1px] rounded-[2.5rem] bg-linear-to-br from-white/20 via-transparent to-white/5 overflow-hidden shadow-2xl">
                <div class="relative overflow-hidden rounded-[2.4rem] aspect-[16/9] sm:aspect-[21/9] bg-brand-gray">
                    <div 
                        v-for="(img, index) in carouselImages" 
                        :key="index"
                        class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                        :class="currentSlide === index ? 'opacity-100 z-10' : 'opacity-0 z-0'"
                    >
                        <img :src="img" class="w-full h-full object-cover transform transition-transform duration-[6000ms] ease-out" :class="currentSlide === index ? 'scale-110' : 'scale-100'" />
                        <div class="absolute inset-0 bg-linear-to-t from-brand-black/90 via-transparent to-black/20"></div>
                    </div>

                    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex gap-2.5 px-4 py-2 bg-black/20 backdrop-blur-md rounded-full border border-white/5">
                        <button 
                            v-for="(_, i) in carouselImages" 
                            :key="i"
                            @click="currentSlide = i"
                            class="h-1.5 rounded-full transition-all duration-500"
                            :class="currentSlide === i ? 'w-8 bg-brand-yellow' : 'w-2 bg-white/20 hover:bg-white/40'"
                        ></button>
                    </div>
                </div>
            </div>

            <header class="mb-8 relative">
                <div class="absolute -left-6 top-0 w-1 h-12 bg-brand-yellow rounded-r-full"></div>
                <h2 class="text-4xl font-extrabold tracking-tight">Artist Lineup</h2>
                <p class="text-gray-400 font-medium">Tonight's performing artists</p>
            </header>

            <div class="relative mb-10 group">
                <input v-model="searchQuery" type="text" placeholder="Search artist or genre..." class="w-full bg-brand-gray/30 border border-white/5 px-12 py-4 rounded-2xl outline-none focus:border-brand-yellow/40 focus:bg-brand-gray/60 transition-all font-medium backdrop-blur-sm" />
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 group-focus-within:text-brand-yellow transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center gap-6 mb-8 border-b border-white/5 px-2">
                <button @click="activeTab = 'upcoming'" class="pb-4 text-xs font-black uppercase tracking-widest transition-all relative" :class="activeTab === 'upcoming' ? 'text-brand-yellow' : 'text-gray-500 hover:text-white'">
                    Lined Up
                    <div v-if="activeTab === 'upcoming'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-brand-yellow shadow-[0_0_15px_#ffcd00]"></div>
                </button>
                <button @click="activeTab = 'closed'" class="pb-4 text-xs font-black uppercase tracking-widest transition-all relative" :class="activeTab === 'closed' ? 'text-brand-yellow' : 'text-gray-500 hover:text-white'">
                    Performed
                    <div v-if="activeTab === 'closed'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-brand-yellow shadow-[0_0_15px_#ffcd00]"></div>
                </button>
            </div>

            <div class="grid grid-cols-2 gap-5 sm:gap-8">
                <div v-if="featuredArtist && !searchQuery" class="col-span-2 mb-4 animate-fade-up">
                    <div class="relative group rounded-[2rem] overflow-hidden border border-white/10 glass-card h-[200px] sm:h-[240px] shadow-2xl transition-all duration-500 hover:border-brand-yellow/30" :class="{ 'artist-performing': featuredArtist.status === 'live' }">
                        <div class="absolute inset-0 z-0 flex items-center justify-center opacity-10 pointer-events-none">
                            <div class="flex gap-1.5 items-end">
                                <div v-for="i in 18" :key="i" class="w-2 bg-brand-yellow rounded-full" :style="{ height: Math.random() * 80 + 20 + '%', animation: `hype-pulse ${Math.random() * 1 + 0.5}s infinite alternate` }"></div>
                            </div>
                        </div>

                        <div class="relative z-10 flex h-full items-center">
                            <div class="flex-1 p-6 sm:p-8 flex flex-col justify-center">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="w-8 h-[1px] bg-brand-yellow"></span>
                                    <p class="text-brand-yellow text-[10px] font-black uppercase tracking-[0.3em]">{{ featuredArtist.status === "live" ? "Live" : "Next" }}</p>
                                </div>
                                <h3 class="text-3xl sm:text-5xl font-black italic tracking-tighter uppercase leading-none mb-4 truncate">{{ featuredArtist.name }}</h3>
                                <div class="flex gap-3">
                                    <Link :href="`/artist/${featuredArtist.id}`" class="bg-white/5 hover:bg-white/10 text-white font-black py-3 px-5 rounded-xl uppercase text-[9px] tracking-widest border border-white/10 transition-colors backdrop-blur-sm">View Profile</Link>
                                    <button v-if="featuredArtist.status === 'live' && !featuredArtist.hasVoted" @click="openVoting(featuredArtist)" class="bg-brand-yellow text-black font-black py-3 px-6 rounded-xl uppercase text-[9px] tracking-widest shadow-[0_10px_30px_rgba(255,205,0,0.3)] hover:scale-105 transition-transform">Vote Now {{ timeRemaining }}</button>
                                </div>
                            </div>
                            <div class="w-1/3 sm:w-2/5 h-full relative overflow-hidden">
                                <img :src="featuredArtist.image" class="w-full h-full object-cover" />
                                <div class="absolute inset-0 bg-linear-to-r from-brand-gray/90 via-transparent to-transparent"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-for="artist in displayArtists" :key="artist.id" class="group relative flex flex-col transition-all duration-500" :class="[ artist.status === 'live' ? 'scale-[1.03]' : 'hover:scale-[1.02]' ]">
                    <div class="relative mb-4 p-[1px] rounded-[1.8rem] bg-linear-to-b from-white/10 to-transparent">
                        <Link :href="`/artist/${artist.id}`" class="block aspect-square rounded-[1.75rem] overflow-hidden shadow-xl group-active:scale-95 transition-transform bg-brand-gray">
                            <img :src="artist.image" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                            <div class="absolute inset-x-0 bottom-0 p-4 bg-linear-to-t from-black/90 via-black/20 to-transparent">
                                <div v-if="artist.hasVoted" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-green-500/20 backdrop-blur-xl border border-green-500/30 text-[9px] uppercase font-black text-green-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                    Voted
                                </div>
                            </div>
                        </Link>
                    </div>
                    <div class="px-2">
                        <h3 class="font-bold text-lg leading-tight truncate">{{ artist.name }}</h3>
                        <div class="flex justify-between items-center mt-1.5">
                            <p class="text-brand-yellow text-[10px] font-black uppercase tracking-tighter">{{ artist.genre }}</p>
                            <span class="text-gray-600 text-[10px] font-bold">•</span>
                            <p class="text-gray-500 text-[10px] font-black italic tracking-widest">{{ artist.scheduled_time }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="fixed bottom-8 left-1/2 -translate-x-1/2 w-[85%] max-w-xs z-40">
            <button v-if="liveArtist" @click="liveArtist.voting_started_at && !liveArtist.hasVoted ? openVoting(liveArtist) : null" :class="['group w-full py-4.5 rounded-2xl font-black uppercase text-sm flex items-center justify-center gap-3 transition-all shadow-[0_20px_50px_rgba(0,0,0,0.5)] border border-white/10 backdrop-blur-xl', liveArtist.hasVoted ? 'bg-green-600 text-white' : 'bg-brand-yellow text-black hover:scale-105 active:scale-95']">
                <span v-if="!liveArtist.hasVoted" class="w-2.5 h-2.5 rounded-full bg-black animate-pulse"></span>
                <span>{{ liveArtist.hasVoted ? 'Voted Successful' : 'Vote: ' + liveArtist.name }}</span>
            </button>
        </div>

        <VotingOverlay v-if="activeArtist" :show="showVoting" :artist="activeArtist" :questions="event?.questions || []" :isJudge="userRole === 'judge'" @close="showVoting = false" @submit="null" />
    </div>
</template>

<style scoped>
/* Animated border shimmer effect */
.artist-performing {
    border-color: rgba(255, 205, 0, 0.5) !important;
    box-shadow: 0 0 30px rgba(255, 205, 0, 0.15);
}

@keyframes hype-pulse {
    from { opacity: 0.1; transform: scaleY(0.8); }
    to { opacity: 0.4; transform: scaleY(1.2); }
}

/* Hide scrollbar but allow functionality if needed */
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>