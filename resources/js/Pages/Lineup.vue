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

const searchQuery = ref("");
const activeTab = ref("upcoming"); // 'upcoming' or 'closed'
const showVoting = ref(false);
const activeArtist = ref(null);
const timeRemaining = ref("");
const isTimerActive = ref(false);
let timerInterval = null;

const liveArtist = computed(() =>
    (props.artists || []).find((a) => a.status === "live"),
);

const featuredArtist = computed(() => {
    // 1. Give priority to Live artist
    if (liveArtist.value) return liveArtist.value;

    // 2. Otherwise provide the first upcoming artist
    const inLine = (props.artists || []).filter((a) => a.status === "upcoming");
    return inLine[0] || null;
});

const nextInLine = computed(() => {
    const inLine = (props.artists || []).filter((a) => a.status === "upcoming");

    // If featured is the LIVE artist, nextInLine is the first person in line
    if (liveArtist.value) {
        return inLine[0] || null;
    }

    // If featured is the NEXT artist (no one live), nextInLine is the second person in line
    return inLine[1] || null;
});

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

const carouselImages = [
    "/assets/tusker_1.png",
    "/assets/tusker_2.png",
    "/assets/tusker_3.png",
];
const currentSlide = ref(0);
let autoPlayInterval = null;

const startAutoPlay = () => {
    autoPlayInterval = setInterval(() => {
        currentSlide.value = (currentSlide.value + 1) % carouselImages.length;
    }, 5000);
};

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

const formatVotes = (count) => {
    if (count >= 1000) return (count / 1000).toFixed(1) + "k";
    return count;
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
    startAutoPlay();

    // Listen for real-time updates
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
    <div class="min-h-screen bg-black text-white font-sans pb-32">
        <Head title="Lineup - MVote" />

        <nav
            class="sticky top-0 z-50 glass-nav px-6 py-3 flex items-center justify-between border-b border-white/10"
        >
            <div class="flex items-center">
                <img
                    :src="'/assets/star-yako-logo.png'"
                    alt="Star Yako Logo"
                    class="h-8 w-auto object-contain"
                />
            </div>
            <UserMenu />
        </nav>

        <div class="px-4 pt-4">
            <div
                class="p-4 rounded-2xl border border-brand-yellow/30 bg-linear-to-b from-brand-yellow/5 to-transparent shadow-[0_0_50px_rgba(255,205,0,0.03)]"
            >
                <div
                    class="relative mb-6 overflow-hidden rounded-xl aspect-square sm:aspect-21/9 bg-zinc-900 border border-white/5"
                >
                    <div
                        v-for="(img, index) in carouselImages"
                        :key="index"
                        class="absolute inset-0 transition-opacity duration-1000"
                        :class="
                            currentSlide === index
                                ? 'opacity-100 z-10'
                                : 'opacity-0'
                        "
                    >
                        <img :src="img" class="w-full h-full object-cover" />
                        <div
                            class="absolute inset-0 bg-linear-to-t from-black via-transparent to-transparent"
                        ></div>
                    </div>
                </div>

                <header class="mb-4 px-1">
                    <h2
                        class="text-3xl font-black tracking-tight italic uppercase leading-none"
                    >
                        Artist Lineup
                    </h2>
                    <p class="text-zinc-500 font-medium text-xs mt-1">
                        Tonight's performing artists
                    </p>
                </header>

                <div class="relative mb-6">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search artist..."
                        class="w-full bg-white/5 border border-white/10 px-12 py-3.5 rounded-xl outline-none focus:border-brand-yellow/40 transition-all text-sm"
                    />
                    <div
                        class="absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                    </div>
                </div>

                <div
                    class="flex items-center gap-6 mb-6 border-b border-white/5 px-1"
                >
                    <button
                        @click="activeTab = 'upcoming'"
                        class="pb-4 text-[10px] font-black uppercase tracking-widest transition-all relative"
                        :class="
                            activeTab === 'upcoming'
                                ? 'text-brand-yellow'
                                : 'text-zinc-500 hover:text-white'
                        "
                    >
                        Lined Up
                        <div
                            v-if="activeTab === 'upcoming'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-brand-yellow"
                        ></div>
                    </button>
                    <button
                        @click="activeTab = 'closed'"
                        class="pb-4 text-[10px] font-black uppercase tracking-widest transition-all relative"
                        :class="
                            activeTab === 'closed'
                                ? 'text-brand-yellow'
                                : 'text-zinc-500 hover:text-white'
                        "
                    >
                        Performed
                        <div
                            v-if="activeTab === 'closed'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-brand-yellow"
                        ></div>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div
                        v-if="featuredArtist && !searchQuery"
                        class="col-span-2 mb-1"
                    >
                        <div
                            class="relative rounded-xl overflow-hidden border border-white/10 bg-white/3 h-47.5"
                        >
                            <div class="relative z-10 flex h-full">
                                <div
                                    class="flex-1 p-6 flex flex-col justify-center"
                                >
                                    <span
                                        class="text-brand-yellow text-[9px] font-black uppercase mb-1"
                                        >{{
                                            featuredArtist.status === "live"
                                                ? "Performing Now"
                                                : "Next Up"
                                        }}</span
                                    >
                                    <h3
                                        class="text-2xl font-black italic uppercase leading-none mb-4 truncate"
                                    >
                                        {{ featuredArtist.name }}
                                    </h3>
                                    <div class="flex gap-2">
                                        <Link
                                            :href="`/artist/${featuredArtist.id}`"
                                            class="bg-white/10 border border-white/10 text-[9px] font-black uppercase px-5 py-2.5 rounded-lg"
                                            >Profile</Link
                                        >
                                        <button
                                            v-if="
                                                featuredArtist.status ===
                                                    'live' &&
                                                !featuredArtist.hasVoted
                                            "
                                            @click="openVoting(featuredArtist)"
                                            class="bg-brand-yellow text-black text-[9px] font-black uppercase px-5 py-2.5 rounded-lg"
                                        >
                                            Vote Now
                                        </button>
                                    </div>
                                </div>
                                <div class="w-2/5 h-full relative">
                                    <img
                                        :src="featuredArtist.image"
                                        class="w-full h-full object-cover img-fade-bottom"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-for="artist in displayArtists"
                        :key="artist.id"
                        class="relative p-1 rounded-xl transition-all border"
                        :class="
                            artist.status === 'live'
                                ? 'border-brand-yellow/50 bg-brand-yellow/5'
                                : 'border-white/10 bg-white/2'
                        "
                    >
                        <div
                            class="relative aspect-square rounded-lg overflow-hidden mb-3"
                        >
                            <img
                                :src="artist.image"
                                class="w-full h-full object-cover img-fade-bottom"
                            />
                        </div>
                        <div class="px-2 pb-3">
                            <h3
                                class="font-bold text-sm uppercase italic truncate"
                            >
                                {{ artist.name }}
                            </h3>
                            <div class="flex justify-between items-center mt-1">
                                <span
                                    class="text-brand-yellow text-[8px] font-black uppercase"
                                    >{{ artist.genre }}</span
                                >
                                <span
                                    class="text-zinc-500 text-[9px] italic font-bold"
                                    >{{ artist.scheduled_time }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="fixed bottom-8 left-1/2 -translate-x-1/2 w-[90%] max-w-sm z-40"
        >
            <button
                v-if="liveArtist"
                @click="!liveArtist.hasVoted ? openVoting(liveArtist) : null"
                class="w-full py-4 rounded-full font-black uppercase text-[11px] tracking-widest flex items-center justify-center gap-2 transition-all shadow-2xl"
                :class="
                    liveArtist.hasVoted
                        ? 'bg-green-600 text-white'
                        : 'bg-brand-yellow text-black'
                "
            >
                <div
                    v-if="!liveArtist.hasVoted"
                    class="w-2 h-2 rounded-full bg-black animate-pulse"
                ></div>
                <span>{{
                    liveArtist.hasVoted
                        ? "Voted for " + liveArtist.name
                        : "Vote: " + liveArtist.name + timeRemaining
                }}</span>
            </button>
        </div>

        <VotingOverlay
            v-if="activeArtist"
            :show="showVoting"
            :artist="activeArtist"
            :questions="event?.questions || []"
            :isJudge="userRole === 'judge'"
            @close="showVoting = false"
            @submit="null"
        />
    </div>
</template>

<style scoped>
.glass-nav {
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(20px);
}
.img-fade-bottom {
    mask-image: linear-gradient(to bottom, black 70%, transparent 100%);
    -webkit-mask-image: linear-gradient(to bottom, black 70%, transparent 100%);
}

/* Global modal background overrides */
:deep(.modal-background),
:deep(.glass-card) {
    background-image:
        linear-gradient(to bottom, rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.95)),
        url("/assets/carousel_2.png") !important;
    background-size: cover !important;
    background-position: center !important;
}
</style>
