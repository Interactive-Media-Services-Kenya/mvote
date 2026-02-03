<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import UserMenu from "../Components/UserMenu.vue";
import VotingOverlay from "../Components/VotingOverlay.vue";

const props = defineProps({
    artists: Array,
    event: Object,
    userRole: String,
});

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
    <div class="min-h-screen bg-black text-white font-sans pb-32">
        <Head title="Lineup - StarYako" />

        <nav class="sticky top-0 z-50 glass-nav px-6 py-4 flex items-center justify-between border-b border-white/10">
            <Link href="/lineup" class="flex items-center">
                <img :src="'/assets/star-yako-logo.png'" alt="Star Yako Logo" class="h-8 w-auto object-contain" />
            </Link>
            <UserMenu />
        </nav>

        <div class="px-5 pt-6">
            <div class="relative mb-6 overflow-hidden rounded-xl aspect-[14/13] sm:aspect-[21/9] bg-zinc-900 border border-white/5">
                <div v-for="(img, index) in carouselImages" :key="index"
                    class="absolute inset-0 transition-opacity duration-1000"
                    :class="currentSlide === index ? 'opacity-100 z-10' : 'opacity-0'">
                    <img :src="img" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                </div>
            </div>

            <div class="relative mb-8">
                <input v-model="searchQuery" type="text" placeholder="Search artist or genre..." 
                    class="w-full bg-white/5 backdrop-blur-md border border-white/10 px-12 py-4 rounded-xl outline-none focus:border-[#ffcd00]/40 transition-all text-sm" />
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
            </div>

            <div class="flex items-center justify-between mb-4 px-1">
                <h2 class="text-3xl font-black italic uppercase tracking-tighter">Lineup</h2>
                <div class="flex gap-4">
                    <button @click="activeTab = 'upcoming'" class="text-[11px] font-black uppercase tracking-widest" :class="activeTab === 'upcoming' ? 'text-[#ffcd00]' : 'text-zinc-500'">Lined Up</button>
                    <button @click="activeTab = 'closed'" class="text-[11px] font-black uppercase tracking-widest" :class="activeTab === 'closed' ? 'text-[#ffcd00]' : 'text-zinc-500'">Past</button>
                </div>
            </div>

            <div class="p-4 rounded-xl border border-[#ffcd00]/30 bg-gradient-to-b from-[#ffcd00]/5 to-transparent shadow-[0_0_30px_rgba(255,205,0,0.02)]">
                
                <div class="grid grid-cols-2 gap-4">
                    <div v-if="featuredArtist && !searchQuery" class="col-span-2 mb-2">
                        <div class="relative rounded-xl overflow-hidden glass-card-border bg-white/[0.03] backdrop-blur-xl h-[190px]">
                            <div class="relative z-10 flex h-full">
                                <div class="flex-1 p-6 flex flex-col justify-center">
                                    <span class="text-[#ffcd00] text-[9px] font-black uppercase tracking-[0.2em] mb-1">Performing Now</span>
                                    <h3 class="text-2xl font-black italic uppercase leading-none mb-4 truncate">{{ featuredArtist.name }}</h3>
                                    <div class="flex gap-2">
                                        <Link :href="`/artist/${featuredArtist.id}`" class="bg-white/10 hover:bg-white/20 border border-white/10 text-[9px] font-black uppercase px-5 py-2.5 rounded-lg transition-colors">Profile</Link>
                                        <button v-if="featuredArtist.status === 'live'" @click="openVoting(featuredArtist)" class="bg-[#ffcd00] hover:bg-[#ffdb4d] text-black text-[9px] font-black uppercase px-5 py-2.5 rounded-lg transition-colors">Vote Now</button>
                                    </div>
                                </div>
                                <div class="w-2/5 h-full relative overflow-hidden">
                                    <img :src="featuredArtist.image" class="w-full h-full object-cover img-fade-bottom" />
                                    <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-for="artist in displayArtists" :key="artist.id" 
                        class="relative p-1 rounded-xl transition-all duration-300"
                        :class="artist.status === 'live' ? 'glass-card-live shadow-[0_0_20px_rgba(255,205,0,0.15)]' : 'glass-card-border bg-white/[0.02] backdrop-blur-lg'">
                        
                        <div class="relative aspect-square rounded-lg overflow-hidden mb-3">
                            <img :src="artist.image" class="w-full h-full object-cover img-fade-bottom" />
                            
                            <div v-if="artist.status === 'upcoming'" class="absolute bottom-2 left-2 bg-black/60 backdrop-blur-md px-2 py-1 rounded-md border border-white/10">
                                <span class="text-[8px] font-black text-white uppercase tracking-tighter">Upcoming</span>
                            </div>
                        </div>

                        <div class="px-2 pb-3">
                            <h3 class="font-bold text-sm uppercase italic tracking-tighter truncate">{{ artist.name }}</h3>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-[#ffcd00] text-[8px] font-black uppercase tracking-widest">{{ artist.genre }}</span>
                                <span class="text-zinc-500 text-[9px] italic font-bold">{{ artist.scheduled_time }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="fixed bottom-8 left-1/2 -translate-x-1/2 w-[90%] max-w-sm z-40">
            <button v-if="liveArtist" @click="openVoting(liveArtist)" 
                class="w-full py-4 rounded-full font-black uppercase text-[11px] tracking-widest flex items-center justify-center gap-2 transition-all shadow-[0_15px_40px_rgba(0,0,0,0.5)]"
                :class="liveArtist.hasVoted ? 'bg-green-600 text-white' : 'bg-[#ffcd00] text-black shadow-[#ffcd00]/20'">
                <div v-if="!liveArtist.hasVoted" class="w-2 h-2 rounded-full bg-black animate-pulse"></div>
                <span>{{ liveArtist.hasVoted ? 'Vote Recorded' : 'Vote: ' + liveArtist.name + timeRemaining }}</span>
            </button>
        </div>

        <VotingOverlay v-if="activeArtist" :show="showVoting" :artist="activeArtist" :questions="event?.questions || []" :isJudge="userRole === 'judge'" @close="showVoting = false" @submit="null" />
    </div>
</template>

<style scoped>
.glass-nav {
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(20px);
}

/* Glass border for normal cards */
.glass-card-border {
    border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Glass border for live cards */
.glass-card-live {
    border: 1px solid rgba(255, 205, 0, 0.6);
    background: rgba(255, 205, 0, 0.03);
    backdrop-filter: blur(15px);
}

/* Image mask to create that smooth fade into the card background at the bottom */
.img-fade-bottom {
    mask-image: linear-gradient(to bottom, black 70%, transparent 100%);
    -webkit-mask-image: linear-gradient(to bottom, black 70%, transparent 100%);
}
</style>