<script setup>
import { ref, computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { artists } from "../constants";
import VotingOverlay from "../Components/VotingOverlay.vue";

const props = defineProps({
    id: String,
});

const showVoting = ref(false);
const liveArtistId = ref(1);
const artist = computed(
    () => artists.find((a) => a.id === parseInt(props.id)) || artists[0],
);

const isLive = computed(() => artist.value.id === liveArtistId.value);

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
                class="absolute top-4 left-4 size-10 glass-card rounded-full flex items-center justify-center text-white z-10"
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
                <div class="flex items-center gap-3 mb-6 animate-fade-up">
                    <div
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
                            {{
                                isLive
                                    ? "On Stage Now"
                                    : `Scheduled: ${artist.scheduledTime}`
                            }}
                        </span>
                    </div>

                    <div
                        v-if="isLive"
                        class="bg-red-600 px-3 py-2 rounded-xl flex items-center gap-1.5 shadow-[0_0_15px_rgba(220,38,38,0.4)] animate-pulse"
                    >
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        <span
                            class="text-[10px] font-black uppercase tracking-tighter"
                            >Live</span
                        >
                    </div>
                </div>

                <div class="flex gap-4 mb-8">
                    <button
                        @click="isLive ? (showVoting = true) : null"
                        :class="[
                            'px-8 py-3 rounded-full font-bold flex-1 active:scale-95 transition-transform uppercase text-sm tracking-widest',
                            isLive
                                ? 'bg-brand-yellow text-black animate-hype-pulse shadow-[0_0_20px_rgba(255,107,0,0.4)]'
                                : 'bg-white text-black',
                        ]"
                    >
                        {{ isLive ? "Vote Now" : "Follow" }}
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

                <!-- Discography Section (Glass List) -->
                <section>
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-black italic">Discography</h2>
                        <span
                            class="text-brand-yellow text-xs font-bold uppercase"
                            >See all</span
                        >
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(album, i) in artist.discography"
                            :key="i"
                            class="glass-card p-4 rounded-2xl flex items-center gap-4 border-white/5 group active:bg-white/10 transition-colors"
                        >
                            <div
                                class="w-16 h-16 bg-brand-gray rounded-xl shrink-0 flex items-center justify-center font-black text-brand-yellow/40 text-xl italic overflow-hidden"
                            >
                                <img
                                    v-if="i === 0"
                                    :src="artist.image"
                                    class="w-full h-full object-cover opacity-60"
                                />
                                <span v-else>{{ album.title.charAt(0) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-lg truncate">
                                    {{ album.title }}
                                </h4>
                                <p class="text-gray-500 text-sm font-medium">
                                    {{ album.year }}
                                </p>
                            </div>
                            <button
                                class="text-white/20 group-active:text-white transition-colors"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Voting Overlay -->
        <VotingOverlay
            :show="showVoting"
            :artist="artist"
            @close="showVoting = false"
            @submit="null"
        />
    </div>
</template>

<style scoped>
/* Specific profile animations */
</style>
