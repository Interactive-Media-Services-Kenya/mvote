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

// Carousel Data
const carouselImages = [
    { id: 1, src: '/carousel_1.png', alt: 'Event Promo 1' },
    { id: 2, src: '/carousel_2.png', alt: 'Event Promo 2' },
    { id: 3, src: '/carousel_3.png', alt: 'Event Promo 3' },
];

const searchQuery = ref("");
const activeTab = ref("upcoming"); // 'upcoming' or 'closed'
const showVoting = ref(false);
const activeArtist = ref(null);
const timeRemaining = ref("");
const isTimerActive = ref(false);
let timerInterval = null;

const featuredArtist = computed(() => {
    if (liveArtist.value) return liveArtist.value;
    const upcoming = (props.artists || []).find((a) => a.status === "upcoming");
    return upcoming || null;
});

const liveArtist = computed(() =>
    (props.artists || []).find((a) => a.status === "live"),
);

const filteredArtistsBySearch = computed(() => {
    if (!searchQuery.value) return props.artists || [];
    return (props.artists || []).filter(
        (a) =>
            a.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            a.genre.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );
});

const displayArtists = computed(() => {
    const list = filteredArtistsBySearch.value.filter(
        (a) => a.id !== featuredArtist.value?.id,
    );

    if (searchQuery.value) return list;

    if (activeTab.value === "upcoming") {
        return list.filter(
            (a) => a.status === "upcoming" || a.status === "live",
        );
    } else {
        return list.filter((a) => a.status === "closed");
    }
});

watch(
    () => liveArtist.value,
    () => {
        calculateTimeLeft();
    },
    { deep: true },
);

const openVoting = (artist) => {
    activeArtist.value = artist;
    showVoting.value = true;
};

const calculateTimeLeft = () => {
    if (!liveArtist.value || !liveArtist.value.voting_ends_at) {
        timeRemaining.value = "";
        return;
    }

    if (liveArtist.value.is_voting_paused) {
        timeRemaining.value = " (PAUSED)";
        return;
    }

    const end = new Date(liveArtist.value.voting_ends_at).getTime();
    const now = new Date().getTime();
    const diff = end - now;

    if (diff <= 0) {
        timeRemaining.value = " (EXPIRED)";
        return;
    }

    const minutes = Math.floor(diff / 1000 / 60);
    const seconds = Math.floor((diff / 1000) % 60);
    timeRemaining.value = ` (${minutes}:${seconds.toString().padStart(2, "0")})`;
};

onMounted(() => {
    timerInterval = setInterval(calculateTimeLeft, 1000);
    calculateTimeLeft();

    if (window.Echo) {
        window.Echo.channel("performances")
            .listen(".performance.updated", (e) => {
                router.reload({ preserveScroll: true });
            })
            .listen(".lineup.updated", (e) => {
                router.reload({ preserveScroll: true });
            });
    }
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
    if (window.Echo) {
        window.Echo.leaveChannel("performances");
    }
});
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

        <div class="px-6 pt-6 animate-fade-up">
            
            <section class="mb-10 -mx-6 px-6 overflow-hidden">
                <div class="flex gap-4 overflow-x-auto snap-x snap-mandatory no-scrollbar pb-4">
                    <div 
                        v-for="slide in carouselImages" 
                        :key="slide.id"
                        class="flex-none w-[85%] sm:w-[60%] lg:w-[40%] snap-center"
                    >
                        <div class="relative aspect-[16/9] rounded-3xl overflow-hidden border border-white/10 shadow-2xl">
                            <img 
                                :src="slide.src" 
                                :alt="slide.alt"
                                class="w-full h-full object-cover"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                        </div>
                    </div>
                </div>
            </section>

            <header class="mb-8">
                <h2 class="text-4xl font-extrabold tracking-tight">
                    Artist Lineup
                </h2>
                <p class="text-gray-400 font-medium">
                    Tonight's performing artists
                </p>
            </header>

            <div class="relative mb-10">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search artist or genre..."
                    class="w-full bg-brand-gray/50 border border-white/10 px-12 py-4 rounded-2xl outline-none focus:border-brand-yellow/50 transition-all font-medium"
                />
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="flex items-center gap-6 mb-8 border-b border-white/5 px-2">
                <button
                    @click="activeTab = 'upcoming'"
                    class="pb-4 text-xs font-black uppercase tracking-widest transition-all relative"
                    :class="activeTab === 'upcoming' ? 'text-brand-yellow' : 'text-gray-500 hover:text-white'"
                >
                    Lined Up
                    <div v-if="activeTab === 'upcoming'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-brand-yellow shadow-[0_0_10px_#ffcd00]"></div>
                </button>
                <button
                    @click="activeTab = 'closed'"
                    class="pb-4 text-xs font-black uppercase tracking-widest transition-all relative"
                    :class="activeTab === 'closed' ? 'text-brand-yellow' : 'text-gray-500 hover:text-white'"
                >
                    Performed
                    <div v-if="activeTab === 'closed'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-brand-yellow shadow-[0_0_10px_#ffcd00]"></div>
                </button>
            </div>

            <div class="grid grid-cols-2 gap-4 sm:gap-6">
                <div v-if="featuredArtist && !searchQuery" class="col-span-2 mb-2 animate-fade-up">
                    <div class="relative group rounded-3xl overflow-hidden border border-white/10 glass-card h-[180px] sm:h-[220px]"
                        :class="{ 'artist-performing': featuredArtist.status === 'live' }">
                        
                        <div class="absolute inset-0 z-0 flex items-center justify-center opacity-20 pointer-events-none overflow-hidden">
                            <div class="flex gap-1 items-end">
                                <div v-for="i in 12" :key="i" class="w-2 sm:w-3 bg-brand-yellow/40 rounded-full"
                                    :style="{ height: Math.random() * 80 + 20 + '%', animation: `hype-pulse ${Math.random() * 1 + 0.5}s infinite alternate` }">
                                </div>
                            </div>
                        </div>

                        <div class="relative z-10 flex h-full items-center">
                            <div class="flex-1 p-5 sm:p-7 flex flex-col justify-center">
                                <p class="text-brand-yellow text-[10px] sm:text-xs font-black uppercase tracking-[0.2em] mb-1 sm:mb-2">
                                    {{ featuredArtist.status === "live" ? "Currently Performing" : "Next Up" }}
                                </p>
                                <h3 class="text-2xl sm:text-4xl font-black italic tracking-tighter uppercase leading-none mb-3 sm:mb-4 truncate max-w-[200px] sm:max-w-md">
                                    {{ featuredArtist.name }}
                                </h3>
                                <div class="flex flex-wrap gap-2 sm:gap-3">
                                    <Link :href="`/artist/${featuredArtist.id}`" class="inline-flex items-center justify-center bg-white/10 hover:bg-white/20 text-white font-black py-2.5 px-4 sm:px-6 rounded-xl uppercase text-[9px] sm:text-[10px] tracking-widest transition-all w-max border border-white/10">
                                        View Profile
                                    </Link>
                                    <button v-if="featuredArtist.status === 'live' && featuredArtist.voting_started_at && !featuredArtist.hasVoted"
                                        @click="openVoting(featuredArtist)"
                                        class="inline-flex items-center justify-center bg-brand-yellow text-black font-black py-2.5 px-4 sm:px-6 rounded-xl uppercase text-[9px] sm:text-[10px] tracking-widest hover:scale-105 transition-all w-max shadow-[0_0_20px_rgba(255,205,0,0.4)]">
                                        Vote Now {{ timeRemaining }}
                                    </button>
                                </div>
                            </div>

                            <div class="w-1/3 sm:w-2/5 h-full relative overflow-hidden">
                                <img :src="featuredArtist.image" :alt="featuredArtist.name" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
                                <div class="absolute inset-0 bg-linear-to-r from-brand-gray via-transparent to-transparent opacity-60"></div>
                                <div v-if="featuredArtist.status === 'live'" class="absolute bottom-4 right-4">
                                    <div class="bg-red-600 px-2 py-1 rounded-lg flex items-center gap-1.5 shadow-2xl animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                        <span class="text-[8px] font-black uppercase tracking-widest">Live</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-for="artist in displayArtists" :key="artist.id" class="group relative p-2 sm:p-3 rounded-2xl transition-all duration-500"
                    :class="[ artist.status === 'live' ? 'artist-performing scale-[1.02]' : 'artist-card-border hover:scale-[1.01]' ]">
                    <div class="relative mb-3">
                        <Link :href="`/artist/${artist.id}`" class="block aspect-square rounded-xl overflow-hidden shadow-2xl transition-transform duration-500 group-active:scale-95">
                            <img :src="artist.image" :alt="artist.name" class="w-full h-full object-cover" />
                            <div class="absolute inset-x-0 bottom-0 p-3 bg-linear-to-t from-black/80 via-black/20 to-transparent">
                                <div v-if="artist.hasVoted" class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-green-500/20 backdrop-blur-md border border-green-500/30 text-[9px] uppercase font-black tracking-tight">
                                    <span class="w-1 h-1 rounded-full bg-green-500"></span>
                                    <span class="text-green-500">Voted</span>
                                </div>
                                <div v-else-if="artist.status === 'upcoming'" class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-black/60 backdrop-blur-md border border-white/5 text-[8px] uppercase font-bold text-gray-400">
                                    Upcoming
                                </div>
                            </div>
                        </Link>
                    </div>
                    <h3 class="font-bold text-base sm:text-lg leading-tight truncate px-1">
                        {{ artist.name }}
                    </h3>
                    <div class="flex items-center justify-between mt-1 px-1">
                        <p class="text-brand-yellow text-[9px] font-black uppercase tracking-widest">{{ artist.genre }}</p>
                        <p class="text-gray-500 text-[9px] font-black uppercase tracking-widest italic">{{ artist.scheduled_time }}</p>
                    </div>
                </div>

                <div v-if="displayArtists.length === 0" class="col-span-2 py-20 text-center glass-card rounded-3xl border-dashed border-white/10">
                    <div class="mb-4 text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-gray-500 font-bold uppercase tracking-widest text-xs">
                        {{ searchQuery ? "No matches found" : activeTab === "upcoming" ? "No upcoming artists" : "No artists have performed yet" }}
                    </p>
                </div>
            </div>
        </div>

        <div class="fixed bottom-8 left-1/2 -translate-x-1/2 w-[80%] max-w-xs z-40">
            <button v-if="liveArtist" @click="liveArtist.voting_started_at && !liveArtist.hasVoted ? openVoting(liveArtist) : null"
                :class="[ 'w-full py-4 rounded-full font-black uppercase tracking-tighter text-sm flex items-center justify-center gap-2 shadow-[0_10px_30px_rgba(255,107,0,0.4)] active:scale-95 transition-all text-white', liveArtist.hasVoted ? 'bg-green-600' : liveArtist.voting_started_at ? 'bg-brand-yellow' : 'bg-red-600/90' ]"
                :disabled="liveArtist.hasVoted">
                <span v-if="!liveArtist.hasVoted" class="w-2 h-2 rounded-full bg-white" :class="{ 'animate-pulse': liveArtist.voting_started_at && !liveArtist.is_voting_paused }"></span>
                <span v-if="liveArtist.hasVoted">Voted for {{ liveArtist.name }}</span>
                <span v-else-if="liveArtist.voting_started_at">Vote: {{ liveArtist.name }}{{ timeRemaining }}</span>
                <span v-else> Performing: {{ liveArtist.name }} </span>
            </button>
            <button v-else class="w-full glass-card py-4 rounded-full font-black uppercase tracking-tighter text-sm flex items-center justify-center gap-2 border-white/20 shadow-2xl opacity-80">
                Voting opens soon
            </button>
        </div>

        <VotingOverlay v-if="activeArtist" :show="showVoting" :artist="activeArtist" :questions="event?.questions || []" :isJudge="userRole === 'judge'" @close="showVoting = false" @submit="null" />
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

@keyframes hype-pulse {
    from { opacity: 0.2; transform: translateY(0); }
    to { opacity: 0.6; transform: translateY(-5px); }
}

.snap-x {
    scroll-snap-type: x mandatory;
}
.snap-center {
    scroll-snap-align: center;
}
</style>