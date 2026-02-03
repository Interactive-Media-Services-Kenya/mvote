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

        <div class="px-6 pt-8 animate-fade-up">
            
            <div class="relative mb-8 group overflow-hidden rounded-[2rem] border border-white/10 shadow-2xl aspect-[16/9] sm:aspect-[21/9] bg-brand-gray/20">
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

            <header class="mb-8">
                <h2 class="text-4xl font-extrabold tracking-tight italic uppercase">Artist Lineup</h2>
                <p class="text-gray-400 font-medium uppercase text-[10px] tracking-widest">Tonight's performing artists</p>
            </header>

            <div class="relative mb-10">
                <input v-model="searchQuery" type="text" placeholder="Search artist or genre..." class="w-full bg-brand-gray/30 border border-white/10 px-12 py-4 rounded-2xl outline-none focus:border-brand-yellow/50 transition-all font-medium" />
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center gap-6 mb-8 border-b border-white/10 px-2">
                <button @click="activeTab = 'upcoming'" class="pb-4 text-xs font-black uppercase tracking-widest transition-all relative" :class="activeTab === 'upcoming' ? 'text-brand-yellow' : 'text-gray-500 hover:text-white'">
                    Lined Up
                    <div v-if="activeTab === 'upcoming'" class="absolute bottom-0 left-0 right-0 h-1 bg-brand-yellow shadow-[0_0_10px_#ffcd00]"></div>
                </button>
                <button @click="activeTab = 'closed'" class="pb-4 text-xs font-black uppercase tracking-widest transition-all relative" :class="activeTab === 'closed' ? 'text-brand-yellow' : 'text-gray-500 hover:text-white'">
                    Performed
                    <div v-if="activeTab === 'closed'" class="absolute bottom-0 left-0 right-0 h-1 bg-brand-yellow shadow-[0_0_10px_#ffcd00]"></div>
                </button>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:gap-6">
                <div v-if="featuredArtist && !searchQuery" class="col-span-2 mb-4 animate-fade-up">
                    <div class="relative group rounded-[2rem] overflow-hidden border border-white/10 glass-card h-[180px] sm:h-[220px]" :class="{ 'ring-1 ring-brand-yellow/30': featuredArtist.status === 'live' }">
                        <div v-if="featuredArtist.status === 'live'" class="absolute inset-0 z-0 flex items-center justify-center opacity-20 pointer-events-none">
                            <div class="flex gap-1 items-end">
                                <div v-for="i in 12" :key="i" class="w-2 bg-brand-yellow/40 rounded-full" :style="{ height: Math.random() * 80 + 20 + '%', animation: `hype-pulse ${Math.random() * 1 + 0.5}s infinite alternate` }"></div>
                            </div>
                        </div>

                        <div class="relative z-10 flex h-full items-center">
                            <div class="flex-1 p-5 sm:p-7 flex flex-col justify-center">
                                <p class="text-brand-yellow text-[10px] font-black uppercase tracking-[0.2em] mb-1">{{ featuredArtist.status === "live" ? "Performing Now" : "Next Up" }}</p>
                                <h3 class="text-2xl sm:text-4xl font-black italic tracking-tighter uppercase leading-none mb-4 truncate">{{ featuredArtist.name }}</h3>
                                <div class="flex gap-2">
                                    <Link :href="`/artist/${featuredArtist.id}`" class="bg-white/10 hover:bg-white/20 text-white font-black py-2.5 px-4 rounded-xl uppercase text-[9px] tracking-widest border border-white/10">Profile</Link>
                                    <button v-if="featuredArtist.status === 'live' && !featuredArtist.hasVoted" @click="openVoting(featuredArtist)" class="bg-brand-yellow text-black font-black py-2.5 px-4 rounded-xl uppercase text-[9px] tracking-widest shadow-lg">Vote Now {{ timeRemaining }}</button>
                                </div>
                            </div>
                            <div class="w-1/3 sm:w-2/5 h-full relative overflow-hidden">
                                <img :src="featuredArtist.image" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                                <div class="absolute inset-0 bg-gradient-to-r from-brand-black via-transparent to-transparent opacity-80"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-for="artist in displayArtists" :key="artist.id" class="group relative p-2 sm:p-3 rounded-[2rem] border border-white/5 bg-brand-gray/10 transition-all duration-500" :class="[ artist.status === 'live' ? 'ring-1 ring-brand-yellow/40 scale-[1.02]' : 'hover:scale-[1.01] hover:border-white/20' ]">
                    <div class="relative mb-3">
                        <Link :href="`/artist/${artist.id}`" class="block aspect-square rounded-[1.5rem] overflow-hidden shadow-2xl group-active:scale-95 transition-transform">
                            <img :src="artist.image" class="w-full h-full object-cover" />
                            <div class="absolute inset-x-0 bottom-0 p-3 bg-gradient-to-t from-black/80 via-transparent to-transparent">
                                <div v-if="artist.hasVoted" class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-green-500/20 backdrop-blur-md border border-green-500/30 text-[9px] uppercase font-black">
                                    <span class="w-1 h-1 rounded-full bg-green-500"></span>
                                    <span class="text-green-500">Voted</span>
                                </div>
                            </div>
                        </Link>
                    </div>
                    <h3 class="font-bold text-base leading-tight truncate px-1 uppercase italic tracking-tighter">{{ artist.name }}</h3>
                    <div class="flex justify-between mt-1 px-1">
                        <p class="text-brand-yellow text-[9px] font-black uppercase tracking-wider">{{ artist.genre }}</p>
                        <p class="text-gray-500 text-[9px] font-black italic">{{ artist.scheduled_time }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="fixed bottom-8 left-1/2 -translate-x-1/2 w-[85%] max-w-sm z-40">
            <button v-if="liveArtist" @click="liveArtist.voting_started_at && !liveArtist.hasVoted ? openVoting(liveArtist) : null" :class="['w-full py-4 rounded-full font-black uppercase text-sm flex items-center justify-center gap-2 transition-all text-white shadow-2xl', liveArtist.hasVoted ? 'bg-green-600' : 'bg-brand-yellow text-black shadow-[0_10px_30px_rgba(255,205,0,0.3)]']">
                <span v-if="!liveArtist.hasVoted" class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                <span>{{ liveArtist.hasVoted ? 'Vote Recorded' : 'Vote Now: ' + liveArtist.name + timeRemaining }}</span>
            </button>
        </div>

        <VotingOverlay v-if="activeArtist" :show="showVoting" :artist="activeArtist" :questions="event?.questions || []" :isJudge="userRole === 'judge'" @close="showVoting = false" @submit="null" />
    </div>
</template>

<style scoped>
@keyframes hype-pulse {
    from { opacity: 0.2; transform: translateY(0); }
    to { opacity: 0.6; transform: translateY(-5px); }
}
.glass-nav {
    background: rgba(10, 10, 10, 0.8);
    backdrop-filter: blur(20px);
}
.glass-card {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(10px);
}
</style>