<script setup>
import { ref, computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import UserMenu from "../Components/UserMenu.vue";
import VotingOverlay from "../Components/VotingOverlay.vue";
import { router } from "@inertiajs/vue3";
const props = defineProps({
    artist: Object,
    event: Object,
    activePerformance: Object,
    userRole: String,
});

const showVoting = ref(false);
const artist = computed(() => props.artist);
const isLive = computed(() => artist.value?.status === "live");

// Timer Logic
const timeRemaining = ref("");
let timerInterval = null;

const calculateTimeLeft = () => {
    if (!isLive.value || !artist.value.voting_ends_at) {
        timeRemaining.value = "";
        return;
    }

    if (artist.value.is_voting_paused) {
        timeRemaining.value = " (PAUSED)";
        return;
    }

    const end = new Date(artist.value.voting_ends_at).getTime();
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

import { onMounted, onUnmounted, watch } from "vue";

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

watch(
    () => artist.value,
    () => {
        calculateTimeLeft();
    },
    { deep: true },
);

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <Head :title="`${artist.name} - MVote`" />

    <div class="min-h-screen bg-brand-black text-white font-sans pb-20">
        <!-- Hero Image + Nav Wrap -->
        <div class="relative h-[45vh] w-full overflow-hidden">
            <img
                :src="artist.image"
                :alt="artist.name"
                class="w-full h-full object-cover"
            />
            <div
                class="absolute inset-0 bg-linear-to-b from-black/20 via-black/40 to-brand-black"
            ></div>

            <!-- Floating Back Button -->
            <button
                @click="goBack"
                class="absolute top-4 left-4 size-10 glass-card rounded-full flex items-center justify-center text-white z-20"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
            </button>

            <!-- User Menu -->
            <div class="absolute top-4 right-4 z-20">
                <UserMenu />
            </div>
        </div>

        <!-- Content Body (Slide up) -->
        <div class="px-6 -mt-20 relative z-10 animate-fade-up">
            <div class="mb-8">
                <p
                    class="text-brand-yellow text-sm font-black uppercase tracking-[0.2em] mb-1"
                >
                    {{ artist.genre }}
                </p>
                <h1
                    class="text-5xl font-black tracking-tighter italic leading-none mb-4"
                >
                    {{ artist.name }}
                </h1>

                <!-- Schedule & Live Status -->
                <div class="flex flex-wrap gap-3 mb-6 animate-fade-up">
                    <div
                        v-if="artist.is_performing"
                        class="bg-red-600/20 px-3 py-2 rounded-xl flex items-center gap-1.5 shadow-[0_0_15px_rgba(220,38,38,0.2)] border border-red-600/30"
                    >
                        <span
                            class="w-1.5 h-1.5 rounded-full bg-red-600 animate-pulse"
                        ></span>
                        <span
                            class="text-[10px] font-black uppercase tracking-tighter text-red-500"
                            >Performing Live</span
                        >
                    </div>

                    <div
                        v-if="artist.is_voting_open"
                        class="bg-brand-yellow/20 px-3 py-2 rounded-xl flex items-center gap-1.5 shadow-[0_0_15px_rgba(255,205,0,0.2)] border border-brand-yellow/30"
                    >
                        <span
                            class="w-1.5 h-1.5 rounded-full bg-brand-yellow animate-pulse"
                        ></span>
                        <span
                            class="text-[10px] font-black uppercase tracking-tighter text-brand-yellow"
                            >Voting Open</span
                        >
                    </div>

                    <div
                        v-if="!isLive"
                        class="glass-card px-4 py-2 rounded-xl border-white/10 flex items-center gap-2"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 text-gray-400"
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
                        <span
                            class="text-xs font-bold uppercase tracking-widest text-gray-300"
                        >
                            Scheduled: {{ artist.scheduledTime }}
                        </span>
                    </div>

                    <div
                        v-if="artist.is_performing && artist.is_voting_paused"
                        class="bg-orange-600/20 px-3 py-2 rounded-xl border border-orange-600/30"
                    >
                        <span
                            class="text-[10px] font-black uppercase tracking-tighter text-orange-500"
                            >Voting Paused</span
                        >
                    </div>
                </div>

                <div class="flex gap-4 mb-8">
                    <div v-if="artist.hasVoted" class="flex-1 flex gap-4">
                        <div
                            class="glass-card px-6 py-3 rounded-2xl border-white/10 flex-1 flex flex-col justify-center"
                        >
                            <span
                                class="text-[8px] font-black uppercase text-gray-500 tracking-widest"
                                >Your Rating</span
                            >
                            <span
                                class="text-xl font-black italic text-brand-yellow"
                                >{{ artist.voterRating?.points }}/{{
                                    artist.voterRating?.max
                                }}</span
                            >
                        </div>
                        <div
                            v-if="artist.globalRating"
                            class="glass-card px-6 py-3 rounded-2xl border-white/10 flex-1 flex flex-col justify-center"
                        >
                            <span
                                class="text-[8px] font-black uppercase text-gray-500 tracking-widest"
                                >Global Avg</span
                            >
                            <span class="text-xl font-black italic text-white"
                                >{{ artist.globalRating.average_points }}/{{
                                    artist.globalRating.max_points
                                }}</span
                            >
                        </div>
                    </div>
                    <button
                        v-else
                        @click="
                            artist.is_voting_open && !artist.hasVoted
                                ? (showVoting = true)
                                : null
                        "
                        :disabled="!artist.is_voting_open || artist.hasVoted"
                        :class="[
                            'px-8 py-3 rounded-full font-bold flex-1 active:scale-95 transition-transform uppercase text-sm tracking-widest',
                            artist.hasVoted
                                ? 'bg-green-600 text-white'
                                : artist.is_voting_open
                                  ? 'bg-brand-yellow text-black animate-hype-pulse shadow-[0_0_20px_rgba(255,107,0,0.4)]'
                                  : artist.is_performing
                                    ? 'bg-red-600/20 text-red-500 border border-red-600/30'
                                    : 'bg-white text-black',
                        ]"
                    >
                        {{
                            artist.hasVoted
                                ? "Rated"
                                : artist.is_voting_open
                                  ? `Rate Now ${timeRemaining}`
                                  : artist.is_performing
                                    ? "Performing Now"
                                    : "Follow"
                        }}
                    </button>
                    <button
                        class="glass-card px-4 py-3 rounded-full active:scale-95 transition-transform border-white/20"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"
                            />
                        </svg>
                    </button>
                </div>

                <!-- About Section -->
                <div class="glass-card p-6 rounded-3xl mb-10 border-white/5">
                    <h3
                        class="text-sm font-bold uppercase text-gray-400 mb-3 tracking-widest"
                    >
                        About
                    </h3>
                    <p class="text-gray-200 leading-relaxed font-medium italic">
                        "{{ artist.bio }}"
                    </p>
                </div>

                <!-- Discography Section (Apple Music Style) -->
                <section class="mt-12">
                    <div class="flex items-center justify-between mb-8">
                        <div class="flex flex-col">
                            <h2
                                class="text-3xl font-black italic tracking-tighter uppercase"
                            >
                                Discography
                            </h2>
                            <span
                                class="text-[10px] uppercase tracking-[0.4em] text-gray-500 font-bold"
                                >Latest Releases</span
                            >
                        </div>
                        <button
                            v-if="artist.discography.length > 3"
                            class="text-brand-yellow text-[10px] font-black uppercase tracking-widest border border-brand-yellow/30 px-4 py-2 rounded-full hover:bg-brand-yellow/10 transition-colors"
                        >
                            View All
                        </button>
                    </div>

                    <div v-if="artist.discography.length > 0" class="space-y-3">
                        <div
                            v-for="(album, i) in artist.discography"
                            :key="album.id"
                            class="group relative overflow-hidden rounded-[2.5rem] p-1 transition-all duration-500 hover:scale-[1.02] active:scale-[0.98]"
                        >
                            <!-- Animated Gradient Background on Hover -->
                            <div
                                class="absolute inset-0 bg-linear-to-r from-brand-yellow/20 via-transparent to-brand-yellow/10 opacity-0 group-hover:opacity-100 transition-opacity duration-700"
                            ></div>

                            <div
                                class="relative glass-card bg-white/[0.03] backdrop-blur-3xl p-5 rounded-[2.2rem] flex items-center gap-6 border border-white/10 group-hover:border-white/20 transition-all duration-300 shadow-2xl"
                            >
                                <!-- Album Art Placeholder with Glass Effect -->
                                <div
                                    class="w-20 h-20 bg-linear-to-br from-brand-gray to-black rounded-3xl shrink-0 flex items-center justify-center font-black text-brand-yellow/20 text-3xl italic overflow-hidden shadow-xl border border-white/5 group-hover:border-brand-yellow/20 transition-all"
                                >
                                    <img
                                        v-if="i === 0"
                                        :src="artist.image"
                                        class="w-full h-full object-cover opacity-60 group-hover:scale-110 transition-transform duration-700"
                                    />
                                    <span v-else>{{
                                        album.title.charAt(0)
                                    }}</span>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4
                                            class="font-black text-xl italic tracking-tight truncate group-hover:text-brand-yellow transition-colors"
                                        >
                                            {{ album.title }}
                                        </h4>
                                        <span
                                            v-if="i === 0"
                                            class="bg-brand-yellow/10 text-brand-yellow text-[7px] font-black px-1.5 py-0.5 rounded-sm uppercase tracking-widest"
                                            >Latest</span
                                        >
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <p
                                            class="text-gray-500 text-xs font-bold tracking-widest uppercase"
                                        >
                                            {{ album.year }}
                                        </p>
                                        <div
                                            class="w-1 h-1 rounded-full bg-gray-700"
                                        ></div>
                                        <p
                                            class="text-gray-500 text-[10px] font-medium truncate italic opacity-60"
                                        >
                                            {{
                                                album.description ||
                                                "Studio Album"
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <a
                                    v-if="album.link"
                                    :href="album.link"
                                    target="_blank"
                                    class="w-12 h-12 glass-card rounded-2xl flex items-center justify-center text-white/40 hover:text-brand-yellow hover:scale-110 active:scale-90 transition-all border border-white/10"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                        />
                                    </svg>
                                </a>
                                <div
                                    v-else
                                    class="w-12 h-12 flex items-center justify-center text-white/5"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-8 w-8"
                                        fill="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Empty State -->
                    <div
                        v-else
                        class="glass-card p-12 rounded-[2.5rem] border-white/5 border-dashed border flex flex-col items-center justify-center text-center group"
                    >
                        <div
                            class="w-20 h-20 rounded-full bg-white/5 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-500"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-10 w-10 text-white/20"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"
                                />
                            </svg>
                        </div>
                        <h4
                            class="text-white/40 font-black italic uppercase tracking-widest text-sm"
                        >
                            No Releases Found
                        </h4>
                        <p class="text-[10px] text-gray-600 font-bold mt-2">
                            Stay tuned for updates from {{ artist.name }}
                        </p>
                    </div>
                </section>
            </div>
        </div>

        <!-- Voting Overlay -->
        <VotingOverlay
            v-if="artist"
            :show="showVoting"
            :artist="artist"
            :questions="event?.questions || []"
            :isJudge="userRole === 'judge'"
            @close="showVoting = false"
            @submit="null"
        />
    </div>
</template>

<style scoped>
/* Specific profile animations */
</style>
