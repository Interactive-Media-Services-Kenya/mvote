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
    <div class="min-h-screen bg-brand-black text-white font-sans pb-32">
        <Head title="Lineup - MVote" />

        <!-- Top Sticky Nav -->
        <nav
            class="sticky top-0 z-50 glass-nav px-6 py-4 flex items-center justify-between"
        >
            <h1 class="text-2xl font-black italic tracking-tighter">
                M<span class="text-brand-yellow">VOTE</span>
            </h1>
            <UserMenu />
        </nav>

        <div class="px-6 pt-8 animate-fade-up">
            <header class="mb-8">
                <h2 class="text-4xl font-extrabold tracking-tight">
                    Artist Lineup
                </h2>
                <p class="text-gray-400 font-medium">
                    Tonight's performing artists
                </p>
            </header>

            <!-- Search Bar -->
            <div class="relative mb-10">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search artist or genre..."
                    class="w-full bg-brand-gray/50 border border-white/10 px-12 py-4 rounded-2xl outline-none focus:border-brand-yellow/50 transition-all font-medium"
                />
                <div
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"
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

            <!-- Tabs Section -->
            <div
                class="flex items-center gap-6 mb-8 border-b border-white/5 px-2"
            >
                <button
                    @click="activeTab = 'upcoming'"
                    class="pb-4 text-xs font-black uppercase tracking-widest transition-all relative"
                    :class="
                        activeTab === 'upcoming'
                            ? 'text-brand-yellow'
                            : 'text-gray-500 hover:text-white'
                    "
                >
                    On Deck
                    <div
                        v-if="activeTab === 'upcoming'"
                        class="absolute bottom-0 left-0 right-0 h-0.5 bg-brand-yellow shadow-[0_0_10px_#ffcd00]"
                    ></div>
                </button>
                <button
                    @click="activeTab = 'closed'"
                    class="pb-4 text-xs font-black uppercase tracking-widest transition-all relative"
                    :class="
                        activeTab === 'closed'
                            ? 'text-brand-yellow'
                            : 'text-gray-500 hover:text-white'
                    "
                >
                    Performed
                    <div
                        v-if="activeTab === 'closed'"
                        class="absolute bottom-0 left-0 right-0 h-0.5 bg-brand-yellow shadow-[0_0_10px_#ffcd00]"
                    ></div>
                </button>
            </div>

            <!-- Artist Grid -->
            <div class="grid grid-cols-2 gap-4 sm:gap-6">
                <!-- Featured Artist (Spans 2 columns) -->
                <div
                    v-if="featuredArtist && !searchQuery"
                    class="col-span-2 mb-2 animate-fade-up"
                >
                    <div
                        class="relative group rounded-3xl overflow-hidden border border-white/10 glass-card h-45 sm:h-55"
                        :class="{
                            'artist-performing':
                                featuredArtist.status === 'live',
                        }"
                    >
                        <!-- Background Graphic Content -->
                        <div
                            class="absolute inset-0 z-0 flex items-center justify-center opacity-20 pointer-events-none overflow-hidden"
                        >
                            <div class="flex gap-1 items-end">
                                <div
                                    v-for="i in 12"
                                    :key="i"
                                    class="w-2 sm:w-3 bg-brand-yellow/40 rounded-full"
                                    :style="{
                                        height: Math.random() * 80 + 20 + '%',
                                        animation: `hype-pulse ${
                                            Math.random() * 1 + 0.5
                                        }s infinite alternate`,
                                    }"
                                ></div>
                            </div>
                        </div>

                        <div class="relative z-10 flex h-full items-center">
                            <!-- Left: Content -->
                            <div
                                class="flex-1 p-5 sm:p-7 flex flex-col justify-center"
                            >
                                <div class="flex flex-wrap gap-2 mb-2 sm:mb-3">
                                    <span
                                        v-if="featuredArtist.is_performing"
                                        class="px-2 py-0.5 rounded-md bg-red-600/20 border border-red-600/30 text-[8px] sm:text-[10px] font-black uppercase tracking-widest text-red-500 flex items-center gap-1.5 shadow-[0_0_15px_rgba(220,38,38,0.2)]"
                                    >
                                        <span
                                            class="w-1 h-1 rounded-full bg-red-500 animate-pulse"
                                        ></span>
                                        Performing
                                    </span>
                                    <span
                                        v-if="featuredArtist.is_voting_open"
                                        class="px-2 py-0.5 rounded-md bg-brand-yellow/20 border border-brand-yellow/30 text-[8px] sm:text-[10px] font-black uppercase tracking-widest text-brand-yellow flex items-center gap-1.5 shadow-[0_0_15px_rgba(255,205,0,0.2)]"
                                    >
                                        <span
                                            class="w-1 h-1 rounded-full bg-brand-yellow animate-pulse"
                                        ></span>
                                        Voting Open
                                    </span>
                                    <span
                                        v-else-if="
                                            featuredArtist.is_performing &&
                                            featuredArtist.is_voting_paused
                                        "
                                        class="px-2 py-0.5 rounded-md bg-orange-600/20 border border-orange-600/30 text-[8px] sm:text-[10px] font-black uppercase tracking-widest text-orange-500"
                                    >
                                        Voting Paused
                                    </span>
                                </div>
                                <h3
                                    class="text-2xl sm:text-4xl font-black italic tracking-tighter uppercase leading-none mb-3 sm:mb-4 truncate max-w-50 sm:max-w-md"
                                >
                                    {{ featuredArtist.name }}
                                </h3>

                                <div class="flex flex-wrap gap-2 sm:gap-3">
                                    <Link
                                        :href="`/artist/${featuredArtist.id}`"
                                        class="inline-flex items-center justify-center bg-white/10 hover:bg-white/20 text-white font-black py-2.5 px-4 sm:px-6 rounded-xl uppercase text-[9px] sm:text-[10px] tracking-widest transition-all w-max border border-white/10"
                                    >
                                        View Profile
                                    </Link>
                                    <button
                                        v-if="
                                            featuredArtist.is_voting_open &&
                                            !featuredArtist.hasVoted
                                        "
                                        @click="openVoting(featuredArtist)"
                                        class="inline-flex items-center justify-center bg-brand-yellow text-black font-black py-2.5 px-4 sm:px-6 rounded-xl uppercase text-[9px] sm:text-[10px] tracking-widest hover:scale-105 transition-all w-max shadow-[0_0_20px_rgba(255,205,0,0.4)]"
                                    >
                                        Rate Now {{ timeRemaining }}
                                    </button>
                                    <div
                                        v-if="featuredArtist.hasVoted"
                                        class="flex items-center gap-4"
                                    >
                                        <div
                                            class="inline-flex flex-col items-start"
                                        >
                                            <span
                                                class="text-[8px] font-black uppercase text-gray-500 tracking-widest"
                                                >Your Rating</span
                                            >
                                            <span
                                                class="text-sm font-black italic text-brand-yellow"
                                                >{{
                                                    featuredArtist.voterRating
                                                        ?.points
                                                }}/{{
                                                    featuredArtist.voterRating
                                                        ?.max
                                                }}</span
                                            >
                                        </div>
                                        <div
                                            v-if="featuredArtist.globalRating"
                                            class="inline-flex flex-col items-start border-l border-white/10 pl-4"
                                        >
                                            <span
                                                class="text-[8px] font-black uppercase text-gray-500 tracking-widest"
                                                >Global Avg</span
                                            >
                                            <span
                                                class="text-sm font-black italic text-white"
                                                >{{
                                                    featuredArtist.globalRating
                                                        .average_points
                                                }}/{{
                                                    featuredArtist.globalRating
                                                        .max_points
                                                }}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Photo -->
                            <div
                                class="w-1/3 sm:w-2/5 h-full relative overflow-hidden"
                            >
                                <img
                                    :src="featuredArtist.image"
                                    :alt="featuredArtist.name"
                                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                                />
                                <div
                                    class="absolute inset-0 bg-linear-to-r from-brand-gray via-transparent to-transparent opacity-60"
                                ></div>

                                <div
                                    class="absolute bottom-4 right-4 flex flex-col gap-2 items-end"
                                >
                                    <div
                                        v-if="featuredArtist.is_performing"
                                        class="bg-red-600 px-2 py-1 rounded-lg flex items-center gap-1.5 shadow-2xl animate-pulse"
                                    >
                                        <span
                                            class="w-1.5 h-1.5 rounded-full bg-white"
                                        ></span>
                                        <span
                                            class="text-[8px] font-black uppercase tracking-widest"
                                            >Performing</span
                                        >
                                    </div>
                                    <div
                                        v-if="featuredArtist.is_voting_open"
                                        class="bg-brand-yellow px-2 py-1 rounded-lg flex items-center gap-1.5 shadow-2xl animate-pulse"
                                    >
                                        <span
                                            class="w-1.5 h-1.5 rounded-full bg-black"
                                        ></span>
                                        <span
                                            class="text-[8px] font-black uppercase tracking-widest text-black"
                                            >Voting Open</span
                                        >
                                    </div>
                                </div>
                                Riverside
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    v-for="artist in displayArtists"
                    :key="artist.id"
                    class="group relative p-2 sm:p-3 rounded-2xl transition-all duration-500"
                    :class="[
                        artist.status === 'live'
                            ? 'artist-performing scale-[1.02]'
                            : 'artist-card-border hover:scale-[1.01]',
                    ]"
                >
                    <div class="relative mb-3">
                        <Link
                            :href="`/artist/${artist.id}`"
                            class="block aspect-square rounded-xl overflow-hidden shadow-2xl transition-transform duration-500 group-active:scale-95"
                        >
                            <img
                                :src="artist.image"
                                :alt="artist.name"
                                class="w-full h-full object-cover"
                            />

                            <!-- Status Overlays -->
                            <div
                                class="absolute inset-x-0 bottom-0 p-3 bg-linear-to-t from-black/80 via-black/20 to-transparent"
                            >
                                <div
                                    v-if="artist.hasVoted"
                                    class="flex flex-col gap-1 px-2 py-1.5 rounded-lg bg-black/60 backdrop-blur-md border border-white/10"
                                >
                                    <div
                                        class="flex items-center justify-between gap-4"
                                    >
                                        <span
                                            class="text-[8px] font-black uppercase text-gray-500 tracking-tighter"
                                            >You</span
                                        >
                                        <span
                                            class="text-[9px] font-black italic text-brand-yellow"
                                            >{{ artist.voterRating?.points }}/{{
                                                artist.voterRating?.max
                                            }}</span
                                        >
                                    </div>
                                    <div
                                        v-if="artist.globalRating"
                                        class="flex items-center justify-between gap-4 border-t border-white/5 pt-1"
                                    >
                                        <span
                                            class="text-[8px] font-black uppercase text-gray-500 tracking-tighter"
                                            >Global</span
                                        >
                                        <span
                                            class="text-[9px] font-black italic text-white"
                                            >{{
                                                artist.globalRating
                                                    .average_points
                                            }}/{{
                                                artist.globalRating.max_points
                                            }}</span
                                        >
                                    </div>
                                </div>
                                <div
                                    v-else-if="artist.is_performing"
                                    class="flex flex-col gap-1"
                                >
                                    <div
                                        class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-red-600/20 backdrop-blur-md border border-red-600/30 text-[8px] uppercase font-black tracking-tight shadow-[0_0_10px_rgba(220,38,38,0.2)]"
                                    >
                                        <span
                                            class="w-1 h-1 rounded-full bg-red-500 animate-pulse"
                                        ></span>
                                        <span class="text-red-500"
                                            >Performing</span
                                        >
                                    </div>
                                    <div
                                        v-if="artist.is_voting_open"
                                        class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-brand-yellow/20 backdrop-blur-md border border-brand-yellow/30 text-[8px] uppercase font-black tracking-tight shadow-[0_0_10px_rgba(255,205,0,0.2)]"
                                    >
                                        <span
                                            class="w-1 h-1 rounded-full bg-brand-yellow animate-pulse"
                                        ></span>
                                        <span class="text-brand-yellow"
                                            >Voting Open</span
                                        >
                                    </div>
                                </div>
                                <div
                                    v-else-if="artist.status === 'upcoming'"
                                    class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg bg-black/60 backdrop-blur-md border border-white/5 text-[8px] uppercase font-bold text-gray-400"
                                >
                                    Upcoming
                                </div>
                            </div>
                        </Link>
                    </div>

                    <h3
                        class="font-bold text-base sm:text-lg leading-tight truncate px-1"
                    >
                        {{ artist.name }}
                    </h3>
                    <div class="flex items-center justify-between mt-1 px-1">
                        <p
                            class="text-brand-yellow text-[9px] font-black uppercase tracking-widest"
                        >
                            {{ artist.genre }}
                        </p>
                        <p
                            class="text-gray-500 text-[9px] font-black uppercase tracking-widest italic"
                        >
                            {{ artist.scheduled_time }}
                        </p>
                    </div>
                </div>

                <!-- Empty States -->
                <div
                    v-if="displayArtists.length === 0"
                    class="col-span-2 py-20 text-center glass-card rounded-3xl border-dashed border-white/10"
                >
                    <div class="mb-4 text-gray-600">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-12 w-12 mx-auto"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                    <p
                        class="text-gray-500 font-bold uppercase tracking-widest text-xs"
                    >
                        {{
                            searchQuery
                                ? "No matches found"
                                : activeTab === "upcoming"
                                  ? "No more artists on deck"
                                  : "No history available yet"
                        }}
                    </p>
                </div>
            </div>
        </div>

        <div
            class="fixed bottom-8 left-1/2 -translate-x-1/2 w-[90%] max-w-sm z-40"
        >
            <div v-if="liveArtist" class="flex flex-col gap-2">
                <button
                    v-if="!liveArtist.hasVoted && liveArtist.is_voting_open"
                    @click="openVoting(liveArtist)"
                    class="w-full py-4 rounded-full font-black uppercase tracking-tighter text-sm flex items-center justify-center gap-2 shadow-[0_10px_30px_rgba(255,107,0,0.4)] active:scale-95 transition-all text-black bg-brand-yellow"
                >
                    <span
                        class="w-2 h-2 rounded-full bg-black animate-pulse"
                    ></span>
                    Rate Now{{ timeRemaining }}
                </button>

                <div
                    v-if="nextInLine"
                    class="glass-card px-6 py-3 rounded-full border border-white/10 flex items-center justify-between backdrop-blur-3xl shadow-2xl"
                >
                    <div class="flex flex-col">
                        <span
                            class="text-[8px] font-black uppercase text-gray-500 tracking-widest"
                            >Incoming</span
                        >
                        <span
                            class="text-[10px] font-black italic uppercase tracking-tighter"
                            >{{ nextInLine.name }}</span
                        >
                    </div>
                    <div
                        class="px-2 py-1 rounded-lg bg-white/5 border border-white/5 text-[8px] font-bold text-gray-400 uppercase"
                    >
                        Scheduled
                    </div>
                </div>
            </div>

            <div
                v-else-if="nextInLine"
                class="glass-card px-6 py-4 rounded-full border border-white/10 flex items-center justify-between backdrop-blur shadow-2xl"
            >
                <div class="flex flex-col">
                    <span
                        class="text-[8px] font-black uppercase text-white tracking-widest"
                        >Next to Stage</span
                    >
                    <span
                        class="text-xs font-black italic uppercase tracking-tighter text-brand-yellow"
                        >{{ nextInLine.name }}</span
                    >
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-[9px] font-black italic text-white">{{
                        nextInLine.scheduled_time
                    }}</span>
                    <div
                        class="w-1.5 h-1.5 rounded-full bg-brand-yellow/50"
                    ></div>
                </div>
            </div>
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
/* Scoped specific glass effects if needed */
</style>
