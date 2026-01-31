<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Head, router } from "@inertiajs/vue3";

const props = defineProps({
    activePerformance: Object,
    stats: Object,
});

// Timer Logic
const timeRemaining = ref("");
let timerInterval = null;
const liveVoterCount = ref(props.stats.activeFans || 0);

const calculateTimeLeft = () => {
    if (!props.activePerformance || !props.activePerformance.voting_ends_at) {
        timeRemaining.value = "";
        return;
    }

    if (props.activePerformance.is_voting_paused) {
        timeRemaining.value = "PAUSED";
        return;
    }

    const end = new Date(props.activePerformance.voting_ends_at).getTime();
    const now = new Date().getTime();
    const diff = end - now;

    if (diff <= 0) {
        timeRemaining.value = "CLOSED";
        return;
    }

    const minutes = Math.floor(diff / 1000 / 60);
    const seconds = Math.floor((diff / 1000) % 60);
    timeRemaining.value = `${minutes}:${seconds.toString().padStart(2, "0")}`;
};

const refreshData = () => {
    router.reload({
        preserveScroll: true,
        only: ["activePerformance", "stats"],
    });
};

onMounted(() => {
    timerInterval = setInterval(calculateTimeLeft, 1000);
    calculateTimeLeft();

    // Listen for real-time updates
    if (window.Echo) {
        window.Echo.channel("performances").listen(
            ".performance.updated",
            (e) => {
                refreshData();
            },
        );

        window.Echo.join("voters")
            .here((users) => {
                liveVoterCount.value = users.filter(
                    (u) => u.role === "fan",
                ).length;
            })
            .joining((user) => {
                if (user.role === "fan") liveVoterCount.value++;
            })
            .leaving((user) => {
                if (user.role === "fan") liveVoterCount.value--;
            });
    }
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
    if (window.Echo) {
        window.Echo.leaveChannel("performances");
    }
});

const voteProgress = computed(() => {
    // Arbitrary target for visual bar if needed, or just show count
    return Math.min(100, (props.activePerformance?.voteCount || 0) / 10);
});
</script>

<template>
    <div
        class="min-h-screen bg-brand-black text-white font-sans overflow-hidden flex flex-col"
    >
        <Head title="Audience Display - MVote" />

        <!-- Background Ambience -->
        <div class="fixed inset-0 pointer-events-none">
            <div
                class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-brand-yellow/10 blur-[120px] rounded-full"
            ></div>
            <div
                class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-brand-yellow/5 blur-[150px] rounded-full"
            ></div>
        </div>

        <!-- Top Header Stats -->
        <div
            class="z-10 px-12 py-10 flex justify-between items-center border-b border-white/5 bg-black/20 backdrop-blur-md"
        >
            <h1 class="text-4xl font-black italic tracking-tighter">
                M<span class="text-brand-yellow">VOTE</span>
            </h1>

            <div class="flex gap-12">
                <div class="text-right">
                    <p
                        class="text-gray-500 text-xs font-black uppercase tracking-widest mb-1"
                    >
                        Active Fans
                    </p>
                    <p
                        class="text-4xl font-black italic tabular-nums text-green-500"
                    >
                        {{ liveVoterCount || stats.activeFans }}
                    </p>
                </div>
                <div class="text-right">
                    <p
                        class="text-gray-500 text-xs font-black uppercase tracking-widest mb-1"
                    >
                        Total Energy
                    </p>
                    <p class="text-4xl font-black italic tabular-nums">
                        {{ stats.totalVotes }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Stage -->
        <main class="flex-1 flex items-center justify-center p-12 z-10">
            <div
                v-if="activePerformance"
                class="w-full max-w-6xl grid grid-cols-2 gap-20 items-center"
            >
                <!-- Artist Visual -->
                <div class="relative group">
                    <div
                        class="absolute -inset-4 bg-brand-yellow/20 blur-3xl opacity-50 rounded-full"
                    ></div>
                    <div
                        class="relative aspect-square rounded-[3rem] overflow-hidden border-4 border-white/10 shadow-[0_40px_100px_rgba(0,0,0,0.8)]"
                    >
                        <img
                            :src="activePerformance.artist_image"
                            :alt="activePerformance.artist_name"
                            class="w-full h-full object-cover scale-105 group-hover:scale-110 transition-transform duration-700"
                        />
                        <!-- Live Badge Overlay -->
                        <div
                            class="absolute top-8 right-8 bg-red-600 px-6 py-2 rounded-2xl flex items-center gap-3 shadow-2xl border border-red-400/20"
                        >
                            <span
                                class="w-3 h-3 rounded-full bg-white animate-pulse"
                            ></span>
                            <span
                                class="text-xl font-black uppercase tracking-tighter"
                                >Live</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Voting Data -->
                <div class="space-y-12">
                    <div>
                        <p
                            class="text-brand-yellow text-2xl font-black uppercase tracking-[0.3em] mb-2"
                        >
                            {{ activePerformance.genre }}
                        </p>
                        <h2
                            class="text-8xl font-black italic tracking-tighter leading-none mb-6"
                        >
                            {{ activePerformance.artist_name }}
                        </h2>
                    </div>

                    <div class="grid grid-cols-2 gap-8">
                        <div
                            class="glass-card p-10 rounded-[2.5rem] border-white/10 shadow-2xl"
                        >
                            <p
                                class="text-gray-500 text-sm font-black uppercase tracking-widest mb-4"
                            >
                                Total Votes
                            </p>
                            <div class="flex items-baseline gap-2">
                                <span
                                    class="text-7xl font-black italic tabular-nums"
                                    >{{ activePerformance.voteCount }}</span
                                >
                                <span
                                    class="text-brand-yellow text-xl font-bold uppercase"
                                    >Cast</span
                                >
                            </div>
                        </div>

                        <div
                            class="glass-card p-10 rounded-[2.5rem] border-white/10 shadow-2xl"
                        >
                            <p
                                class="text-gray-500 text-sm font-black uppercase tracking-widest mb-4"
                            >
                                Avg Rating
                            </p>
                            <div class="flex items-baseline gap-2">
                                <span
                                    class="text-7xl font-black italic tabular-nums text-brand-yellow"
                                    >{{ activePerformance.avgRating }}</span
                                >
                                <span
                                    class="text-gray-400 text-xl font-bold uppercase"
                                    >/ 5.0</span
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Live Timer -->
                    <div
                        v-if="activePerformance.voting_started_at"
                        class="bg-white text-black p-8 rounded-[2rem] flex items-center justify-between shadow-[0_20px_50px_rgba(255,255,255,0.1)]"
                    >
                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-widest opacity-50"
                            >
                                Voting Window
                            </p>
                            <p
                                class="text-3xl font-black italic tracking-tighter"
                            >
                                {{
                                    activePerformance.is_voting_paused
                                        ? "VOTING PAUSED"
                                        : "WINDOW CLOSES IN"
                                }}
                            </p>
                        </div>
                        <div
                            class="text-6xl font-black italic tabular-nums"
                            :class="{
                                'text-red-600':
                                    timeRemaining !== 'PAUSED' &&
                                    timeRemaining !== 'CLOSED' &&
                                    timeRemaining.split(':')[0] === '0',
                            }"
                        >
                            {{ timeRemaining }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center space-y-8 max-w-2xl">
                <div
                    class="w-32 h-32 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-12"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-16 w-16 text-gray-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"
                        />
                    </svg>
                </div>
                <h2
                    class="text-6xl font-black italic tracking-tighter text-gray-400"
                >
                    Waiting for Next Artist
                </h2>
                <p class="text-2xl text-gray-600 font-medium italic">
                    "The stage is getting ready for something big..."
                </p>
            </div>
        </main>

        <!-- Dynamic Bottom Border -->
        <div class="h-2 bg-brand-yellow/20 relative w-full overflow-hidden">
            <div
                class="absolute inset-y-0 left-0 bg-brand-yellow animate-loading shadow-[0_0_20px_#FF6B00]"
                style="width: 30%"
            ></div>
        </div>
    </div>
</template>

<style scoped>
@keyframes loading {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(400%);
    }
}
.animate-loading {
    animation: loading 3s linear infinite;
}
</style>
