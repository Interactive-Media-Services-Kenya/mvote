<script setup>
import { ref, computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { artists } from "../constants";
import VotingOverlay from "../Components/VotingOverlay.vue";
import UserMenu from "../Components/UserMenu.vue";

const searchQuery = ref("");
const showVoting = ref(false);
const activeArtist = ref(null);

const liveArtist = computed(() => artists.find((a) => a.status === "live"));

const filteredArtists = computed(() => {
    if (!searchQuery.value) return artists;
    return artists.filter(
        (a) =>
            a.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            a.genre.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );
});

const openVoting = (artist) => {
    activeArtist.value = artist;
    showVoting.value = true;
};

const formatVotes = (count) => {
    if (count >= 1000) return (count / 1000).toFixed(1) + "k";
    return count;
};
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
                <h2 class="text-4xl font-extrabold tracking-tight">Lineup</h2>
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

            <!-- Artist Grid -->
            <div class="grid grid-cols-2 gap-6">
                <div
                    v-for="artist in filteredArtists"
                    :key="artist.id"
                    class="group relative"
                >
                    <div class="relative mb-3">
                        <Link
                            :href="`/artist/${artist.id}`"
                            class="block aspect-square rounded-2xl overflow-hidden shadow-2xl transition-transform duration-500 group-active:scale-95 group-hover:scale-[1.02]"
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
                                <!-- Vote Count Badge -->
                                <div
                                    v-if="artist.status !== 'upcoming'"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-black/60 backdrop-blur-md border border-white/10 text-[10px] uppercase font-black tracking-tight shadow-2xl"
                                >
                                    <span
                                        class="w-1.5 h-1.5 rounded-full bg-brand-yellow shadow-[0_0_8px_#FF6B00]"
                                    ></span>
                                    <span class="text-white drop-shadow-sm"
                                        >{{
                                            formatVotes(artist.voteCount)
                                        }}
                                        Votes</span
                                    >
                                </div>
                                <div
                                    v-else
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-black/60 backdrop-blur-md border border-white/5 text-[9px] uppercase font-bold text-gray-300 shadow-xl"
                                >
                                    Upcoming
                                </div>
                            </div>

                            <!-- Live Badge -->
                            <div
                                v-if="artist.status === 'live'"
                                class="absolute top-3 right-3 bg-red-600 text-[10px] font-black uppercase px-2.5 py-1 rounded-lg flex items-center gap-1.5 shadow-[0_4px_12px_rgba(220,38,38,0.5)] border border-red-400/20"
                            >
                                <span
                                    class="w-1 h-1 rounded-full bg-white animate-pulse"
                                ></span>
                                Live
                            </div>
                        </Link>

                        <!-- Quick Vote Action -->
                        <!-- <button
                            v-if="artist.status === 'live'"
                            @click="openVoting(artist)"
                            class="absolute -bottom-2 -right-2 w-10 h-10 bg-brand-yellow text-black rounded-xl flex items-center justify-center shadow-xl active:scale-90 transition-transform z-10"
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
                                    stroke-width="3"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>
                        </button> -->
                    </div>

                    <h3 class="font-bold text-lg leading-tight truncate px-1">
                        {{ artist.name }}
                    </h3>
                    <p
                        class="text-brand-yellow text-[10px] font-black uppercase tracking-widest mt-0.5 px-1"
                    >
                        {{ artist.genre }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Global Floating Action -->
        <div
            class="fixed bottom-8 left-1/2 -translate-x-1/2 w-[80%] max-w-xs z-40"
        >
            <button
                v-if="liveArtist"
                @click="openVoting(liveArtist)"
                class="w-full bg-brand-yellow py-4 rounded-full font-black uppercase tracking-tighter text-sm flex items-center justify-center gap-2 shadow-[0_10px_30px_rgba(255,107,0,0.4)] active:scale-95 transition-all text-white"
            >
                <span
                    class="w-2 h-2 rounded-full bg-white animate-pulse"
                ></span>
                Vote: {{ liveArtist.name }}
            </button>
            <button
                v-else
                class="w-full glass-card py-4 rounded-full font-black uppercase tracking-tighter text-sm flex items-center justify-center gap-2 border-white/20 shadow-2xl opacity-50"
            >
                Voting opens soon
            </button>
        </div>

        <VotingOverlay
            v-if="activeArtist"
            :show="showVoting"
            :artist="activeArtist"
            @close="showVoting = false"
            @submit="null"
        />
    </div>
</template>

<style scoped>
/* Scoped specific glass effects if needed */
</style>
