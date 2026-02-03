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
    }, 5000); // 5-second interval
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

        <nav class="sticky top-0 z-50 glass-nav px-6 py-4 flex items-center justify-between border-b border-white/5 bg-brand-black/50 backdrop-blur-2xl">
            <Link href="/lineup" class="flex items-center">
                <img :src="'/assets/star-yako-logo.png'" alt="Star Yako Logo" class="h-8 w-auto object-contain" />
            </Link>
            <UserMenu />
        </nav>

        <div class="px-6 pt-8 animate-fade-up">
            
            <div class="relative mb-10 group overflow-hidden rounded-[2.5rem] border border-white/10 shadow-2xl aspect-[16/9] sm:aspect-[21/9]">
                <div 
                    v-for="(img, index) in carouselImages" 
                    :key="index"
                    class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                    :class="currentSlide === index ? 'opacity-100 z-10' : 'opacity-0 z-0'"
                >
                    <img :src="img" class="w-full h-full object-cover transform transition-transform duration-[8000ms]" :class="currentSlide === index ? 'scale-110' : 'scale-100'" />
                    <div class="absolute inset-0 bg-gradient-to-t from-brand-black/90 via-transparent to-transparent"></div>
                </div>

                <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                    <button 
                        v-for="(_, i) in carouselImages" 
                        :key="i"
                        @click="currentSlide = i"
                        class="h-1.5 rounded-full transition-all duration-500"
                        :class="currentSlide === i ? 'w-8 bg-brand-yellow shadow-[0_0_10px_#ffcd00]' : 'w-2 bg-white/30'"
                    ></button>
                </div>
            </div>

            <header class="mb-8">
                <h2 class="text-4xl font-black italic tracking-tighter uppercase leading-none">Artist Lineup</h2>
                <p class="text-gray-400 font-bold uppercase text-[10px] tracking-[0.2em] mt-2">Your voice, the stage energy.</p>
            </header>

            <div class="relative mb-10">
                <input v-model="searchQuery" type="text" placeholder="Search artist or genre..." class="w-full bg-brand-gray/40 border border-white/10 px-12 py-5 rounded-2xl outline-none focus:border-brand-yellow/50 transition-all font-medium text-white placeholder:text-gray-600 shadow-inner" />
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center gap-8 mb-8 border-b border-white/5 px-2">
                <button @click="activeTab = 'upcoming'" class="pb-4 text-xs font-black uppercase tracking-[0.2em] transition-all relative" :class="activeTab === 'upcoming' ? 'text-brand-yellow' : 'text-gray-500 hover:text-white'">
                    Lined Up
                    <div v-if="activeTab === 'upcoming'" class="absolute bottom-0 left-0 right-0 h-1 bg-brand-yellow shadow-[0_0_15px_#ffcd00]"></div>
                </button>
                <button @click="activeTab = 'closed'" class="pb-4 text-xs font-black uppercase tracking-[0.2em] transition-all relative" :class="activeTab === 'closed' ? 'text-brand-yellow' : 'text-gray-500 hover:text-white'">
                    Performed
                    <div v-if="activeTab === 'closed'" class="absolute bottom-0 left-0 right-0 h-1 bg-brand-yellow shadow-[0_0_15px_#ffcd00]"></div>
                </button>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:gap-6">
                <div v-if="featuredArtist && !searchQuery" class="col-span-2 mb-4 animate-fade-up">
                    <div class="relative group rounded-[2rem] overflow-hidden border border-white/10 glass-card h-[200px] sm:h-[240px]" :class="{ 'artist-performing ring-2 ring-brand-yellow/50': featuredArtist.status === 'live' }">
                        <div v-if="featuredArtist.status === 'live'" class="absolute inset-0 z-0 flex items-center justify-center opacity-30 pointer-events-none">
                            <div class="flex gap-1.5 items-end">
                                <div v-for="i in 15" :key="i" class="w-1.5 bg-brand-yellow rounded-full" :style="{ height: Math.random() * 70 + 30 + '%', animation: `hype-pulse ${Math.random() * 0.5 + 0.4}s infinite alternate` }"></div>
                            </div>
                        </div>

                        <div class="relative z-10 flex h-full items-center">
                            <div class="flex-1 p-6 sm:p-10 flex flex-col justify-center">
                                <p class="text-brand-yellow text-[10px] font-black uppercase tracking-[0.3em] mb-2">{{ featuredArtist.status === "live" ? "🔥 Performing Now" : "Coming Soon" }}</p>
                                <h3 class="text-3xl sm:text-5xl font-black italic tracking-tighter uppercase leading-none mb-6 truncate drop-shadow-lg">{{ featuredArtist.name }}</h3>
                                <div class="flex gap-3">
                                    <Link :href="`/artist/${featuredArtist.id}`" class="bg-white/10 hover:bg-white/20 text-white font-black py-3 px-6 rounded-xl uppercase text-[10px] tracking-widest border border-white/10 transition-all active:scale-95">Profile</Link>
                                    <button v-if="featuredArtist.status === 'live' && !featuredArtist.hasVoted" @click="openVoting(featuredArtist)" class="bg-brand-yellow text-black font-black py-3 px-6 rounded-xl uppercase text-[10px] tracking-widest shadow-[0_10px_30px_rgba(255,205,0,0.3)] hover:bg-white transition-all active:scale-95">Vote Now {{ timeRemaining }}</button>
                                </div>
                            </div>
                            <div class="w-1/3 sm:w-2/5 h-full relative overflow-hidden hidden xs:block">
                                <img :src="featuredArtist.image" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" />
                                <div class="absolute inset-0 bg-gradient-to-r from-brand-black via-transparent to-transparent opacity-80"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-for="artist in displayArtists" :key="artist.id" class="group relative p-2 sm:p-3 rounded-2xl transition-all duration-500" :class="[ artist.status === 'live' ? 'artist-performing scale-[1.02]' : 'artist-card-border hover:scale-[1.02]' ]">
                    <div class="relative mb-4">
                        <Link :href="`/artist/${artist.id}`" class="block aspect-square rounded-2xl overflow-hidden shadow-2xl group-active:scale-95 transition-all">
                            <img :src="artist.image" class="w-full h-full object-cover grayscale-[20%] group-hover:grayscale-0 transition-all duration-500" />
                            <div class="absolute inset-x-0 bottom-0 p-3 bg-gradient-to-t from-black/90 via-transparent to-transparent">
                                <div v-if="artist.hasVoted" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-green-500/20 backdrop-blur-md border border-green-500/40 text-[9px] uppercase font-black text-green-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                    <span>Voted</span>
                                </div>
                            </div>
                        </Link>
                    </div>
                    <h3 class="font-black text-lg leading-tight truncate px-1 uppercase italic tracking-tighter">{{ artist.name }}</h3>
                    <div class="flex justify-between mt-1 px-1">
                        <p class="text-brand-yellow text-[10px] font-black uppercase tracking-wider">{{ artist.genre }}</p>
                        <p class="text-gray-500 text-[10px] font-bold italic">{{ artist.scheduled_time }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="fixed bottom-10 left-1/2 -translate-x-1/2 w-[85%] max-w-sm z-40">
            <button v-if="liveArtist" @click="liveArtist.voting_started_at && !liveArtist.hasVoted ? openVoting(liveArtist) : null" :class="['w-full py-5 rounded-full font-black uppercase text-xs tracking-widest flex items-center justify-center gap-3 transition-all text-white shadow-[0_20px_50px_rgba(0,0,0,0.5)] border-2 border-transparent transform active:scale-95', liveArtist.hasVoted ? 'bg-green-600 border-green-400/30' : 'bg-brand-yellow text-black border-yellow-400/20']">
                <span v-if="!liveArtist.hasVoted" class="w-2.5 h-2.5 rounded-full bg-white animate-pulse"></span>
                <span class="truncate">{{ liveArtist.hasVoted ? 'Verified Vote' : 'Vote Now: ' + liveArtist.name + timeRemaining }}</span>
            </button>
        </div>

        <VotingOverlay v-if="activeArtist" :show="showVoting" :artist="activeArtist" :questions="event?.questions || []" :isJudge="userRole === 'judge'" @close="showVoting = false" @submit="null" />
    </div>
</template>

<style scoped>
@keyframes hype-pulse {
    from { opacity: 0.2; transform: translateY(0); }
    to { opacity: 0.8; transform: translateY(-8px); }
}

.glass-nav {
    background: rgba(0, 0, 0, 0.7);
    -webkit-backdrop-filter: blur(20px);
    backdrop-filter: blur(20px);
}

.artist-performing {
    background: radial-gradient(circle at top right, rgba(255, 205, 0, 0.1), transparent);
}
</style>