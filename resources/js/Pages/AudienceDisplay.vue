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
</script>

<template>
    <div
        class="min-h-screen bg-[#050505] text-white font-sans overflow-hidden flex flex-col selection:bg-brand-yellow/30"
    >
        <Head title="Stage Display - MVote" />

        <div class="fixed inset-0 pointer-events-none z-0">
            <div
                class="absolute top-0 left-1/4 w-1/2 h-1/2 bg-brand-yellow/5 blur-[120px] rounded-full"
            ></div>
            <div
                class="absolute inset-0 opacity-[0.02] bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"
            ></div>
        </div>

        <header
            class="z-20 px-12 py-8 flex justify-between items-center bg-black/60 backdrop-blur-md border-b border-white/5"
        >
            <div class="flex items-center gap-8">
                <div class="flex flex-col">
                    <h1
                        class="text-4xl font-black italic tracking-tighter leading-none"
                    >
                        M<span class="text-brand-yellow">VOTE</span>
                    </h1>
                    <span
                        class="text-[8px] uppercase tracking-[0.5em] text-gray-500 font-bold"
                        >Live Adjudication</span
                    >
                </div>

                <div class="h-8 w-px bg-white/10 mx-2"></div>
                <div class="flex flex-col">
                    <span
                        class="text-brand-yellow font-black italic text-xl uppercase leading-none"
                        >{{ event?.name || "Grand Finale" }}</span
                    >
                    <span
                        class="text-[10px] text-gray-500 uppercase font-bold tracking-widest"
                        >Live Broadcast</span
                    >
                </div>
            </div>

            <div class="flex gap-12 items-center">
                <div class="flex flex-col items-end">
                    <span
                        class="text-[9px] font-black text-gray-500 uppercase tracking-widest"
                        >Active Audience</span
                    >
                    <div class="flex items-center gap-2">
                        <span
                            class="w-2 h-2 rounded-full bg-green-500 animate-pulse"
                        ></span>
                        <span class="text-4xl font-black tabular-nums">{{
                            liveVoterCount || stats.activeFans
                        }}</span>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 z-10 relative flex">
            <template v-if="activePerformance">
                <div class="w-[55%] relative overflow-hidden group">
                    <img
                        :src="activePerformance.artist_image"
                        class="absolute inset-0 w-full h-full object-cover grayscale-[0.3] group-hover:grayscale-0 transition-all duration-700"
                    />
                    <div
                        class="absolute inset-0 bg-linear-to-r from-black/80 via-black/20 to-transparent"
                    ></div>

                    <div class="absolute bottom-20 left-12 space-y-2">
                        <div
                            class="flex items-center gap-3 bg-red-600 px-4 py-1 self-start w-fit rounded-sm mb-4"
                        >
                            <span
                                class="text-xs font-black uppercase tracking-tighter"
                                >Live Performance</span
                            >
                        </div>
                        <p
                            class="text-brand-yellow text-2xl font-black italic uppercase tracking-widest"
                        >
                            {{ activePerformance.genre }}
                        </p>
                        <h2
                            class="text-[8vw] font-black italic tracking-tighter leading-[0.85] uppercase drop-shadow-lg"
                        >
                            {{ activePerformance.artist_name }}
                        </h2>
                    </div>

                    <div
                        v-if="activePerformance.voting_started_at"
                        class="absolute top-12 left-12 flex flex-col bg-white text-black p-6 rounded-xl shadow-2xl"
                    >
                        <span
                            class="text-[10px] font-black uppercase tracking-widest mb-1 opacity-50"
                            >Voting Closes In</span
                        >
                        <span
                            class="text-6xl font-black italic tabular-nums leading-none tracking-tighter"
                        >
                            {{ timeRemaining }}
                        </span>
                    </div>
                </div>

                <div
                    class="w-[45%] bg-[#080808] border-l border-white/5 flex flex-col p-12 justify-center"
                >
                    <div
                        class="grid grid-cols-1 gap-8 max-w-2xl mx-auto w-full"
                    >
                        <div
                            class="relative p-8 rounded-3xl bg-linear-to-br from-white/5 to-transparent border border-white/10"
                        >
                            <p
                                class="text-[10px] font-black text-brand-yellow uppercase tracking-[0.4em] mb-4"
                            >
                                Current Standing
                            </p>
                            <div class="flex items-end justify-between">
                                <h3
                                    class="text-4xl font-black italic uppercase tracking-tighter"
                                >
                                    Average Score
                                </h3>
                                <div class="flex items-baseline gap-2">
                                    <span
                                        class="text-8xl font-black italic text-brand-yellow"
                                        >{{ activePerformance.avgRating }}</span
                                    >
                                    <span
                                        class="text-xl font-bold text-gray-600"
                                        >/ 5.0</span
                                    >
                                </div>
                            </div>
                            <div
                                class="mt-6 h-2 bg-white/5 rounded-full overflow-hidden"
                            >
                                <div
                                    class="h-full bg-brand-yellow shadow-[0_0_20px_rgba(255,107,0,0.5)] transition-all duration-1000"
                                    :style="{
                                        width:
                                            (activePerformance.avgRating / 5) *
                                                100 +
                                            '%',
                                    }"
                                ></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div
                                class="p-8 rounded-3xl bg-white/2 border border-white/5"
                            >
                                <p
                                    class="text-[9px] font-black text-gray-500 uppercase tracking-widest mb-4"
                                >
                                    Fan Votes
                                </p>
                                <span
                                    class="text-6xl font-black italic tabular-nums"
                                    >{{ activePerformance.fan_votes }}</span
                                >
                            </div>
                            <div
                                class="p-8 rounded-3xl bg-white/2 border border-white/5"
                            >
                                <p
                                    class="text-[9px] font-black text-gray-500 uppercase tracking-widest mb-4"
                                >
                                    Judge Votes
                                </p>
                                <span
                                    class="text-6xl font-black italic tabular-nums"
                                    >{{ activePerformance.judge_votes }}</span
                                >
                            </div>
                        </div>

                        <div
                            class="flex items-center justify-between px-8 py-6 rounded-3xl bg-brand-yellow text-black"
                        >
                            <div>
                                <p
                                    class="text-[9px] font-black uppercase tracking-widest opacity-60"
                                >
                                    Total Engagement
                                </p>
                                <h4
                                    class="text-2xl font-black italic uppercase tracking-tight leading-none"
                                >
                                    Verified Voters
                                </h4>
                            </div>
                            <span
                                class="text-6xl font-black italic tabular-nums tracking-tighter"
                                >{{ activePerformance.voteCount }}</span
                            >
                        </div>
                    </div>
                </div>
            </template>

            <div
                v-else
                class="flex-1 flex flex-col items-center justify-center p-20 text-center"
            >
                <div class="relative">
                    <div
                        class="absolute inset-0 bg-brand-yellow/20 blur-3xl rounded-full"
                    ></div>
                    <h2
                        class="relative text-[10vw] font-black italic tracking-tighter text-white uppercase leading-none opacity-20"
                    >
                        Stay Tuned
                    </h2>
                </div>
                <p
                    class="mt-8 text-2xl text-brand-yellow font-black italic uppercase tracking-[0.6em] animate-pulse"
                >
                    Up Next: Next Performance
                </p>
            </div>
        </main>

        <footer class="h-2 bg-black border-t border-white/5 flex items-center">
            <div
                class="h-full bg-brand-yellow animate-sweep shadow-[0_0_20px_#ff6b00]"
                style="width: 15%"
            ></div>
        </footer>
    </div>
</template>

<style scoped>
@keyframes sweep {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(700%);
    }
}
.animate-sweep {
    animation: sweep 4s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

@keyframes pulse-slow {
    0%,
    100% {
        opacity: 0.3;
        transform: scale(1);
    }
    50% {
        opacity: 0.6;
        transform: scale(1.05);
    }
}
.animate-pulse-slow {
    animation: pulse-slow 8s ease-in-out infinite;
}

@keyframes pulse-slow-delayed {
    0%,
    100% {
        opacity: 0.2;
        transform: scale(1);
    }
    50% {
        opacity: 0.4;
        transform: scale(1.1);
    }
}
.animate-pulse-slow-delayed {
    animation: pulse-slow-delayed 12s ease-in-out infinite 2s;
}

@keyframes bounce-slow {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-20px);
    }
}
.animate-bounce-slow {
    animation: bounce-slow 4s ease-in-out infinite;
}

@keyframes hype-pulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.4);
        opacity: 0.7;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
.animate-hype-pulse {
    animation: hype-pulse 1.5s ease-in-out infinite;
}

.glass-card {
    backdrop-filter: blur(40px);
}

/* Custom fade in up for genre */
@keyframes fade-in-up {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in-up {
    animation: fade-in-up 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
