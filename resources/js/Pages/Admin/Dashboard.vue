<script setup>
import { Head, router } from "@inertiajs/vue3";
import { computed, ref, onMounted, onUnmounted, watch } from "vue";
import AdminLayout from "../../Layouts/AdminLayout.vue";

const props = defineProps({
    liveArtist: Object,
    stats: Array,
    upcomingArtists: Array,
});

const liveArtist = computed(() => props.liveArtist);
const stats = computed(() => props.stats);
const upcomingArtists = computed(() => props.upcomingArtists);

const activeTab = ref("upcoming"); // 'upcoming' or 'closed'

const linedUpArtists = computed(() =>
    props.upcomingArtists.filter((a) => a.status === "upcoming"),
);
const closedArtists = computed(() =>
    props.upcomingArtists.filter((a) => a.status === "closed"),
);

const liveFansCount = ref(0);
const liveJudgesCount = ref(0);

const displayStats = computed(() => {
    return props.stats.map((stat) => {
        if (stat.label === "Active Fans") {
            return {
                ...stat,
                value: liveFansCount.value || stat.value,
            };
        }
        if (stat.label === "Active Judges") {
            return {
                ...stat,
                value: liveJudgesCount.value || stat.value,
            };
        }
        return stat;
    });
});

// Timer Logic
const timeRemaining = ref("00:00");
const secondsLeft = ref(0);
let timerInterval = null;

const calculateTimeLeft = () => {
    if (!liveArtist.value || !liveArtist.value.voting_ends_at) {
        timeRemaining.value = "00:00";
        secondsLeft.value = 0;
        return;
    }

    if (liveArtist.value.is_voting_paused) {
        timeRemaining.value = "PAUSED";
        return;
    }

    const end = new Date(liveArtist.value.voting_ends_at).getTime();
    const now = new Date().getTime();
    const diff = end - now;

    if (diff <= 0) {
        timeRemaining.value = "00:00 - EXPIRED";
        secondsLeft.value = 0;
        return;
    }

    const minutes = Math.floor(diff / 1000 / 60);
    const seconds = Math.floor((diff / 1000) % 60);
    timeRemaining.value = `${minutes.toString().padStart(2, "0")}:${seconds.toString().padStart(2, "0")} Remaining`;
    secondsLeft.value = Math.floor(diff / 1000);
};

onMounted(() => {
    console.log("Echo listener starting..."); // Step 1: Check if this runs

    if (window.Echo) {
        window.Echo.channel("performances")
            .listen(".performance.updated", (e) => {
                // Step 2: The DOT is critical
                console.log("EVENT RECEIVED!", e);
                router.reload({ preserveScroll: true });
            })
            .listen(".lineup.updated", (e) => {
                router.reload({ preserveScroll: true });
            });

        window.Echo.join("voters")
            .here((users) => {
                liveFansCount.value = users.filter(
                    (u) => u.role === "fan",
                ).length;
                liveJudgesCount.value = users.filter(
                    (u) => u.role === "judge",
                ).length;
            })
            .joining((user) => {
                if (user.role === "fan") liveFansCount.value++;
                if (user.role === "judge") liveJudgesCount.value++;
            })
            .leaving((user) => {
                if (user.role === "fan") liveFansCount.value--;
                if (user.role === "judge") liveJudgesCount.value--;
            });
    } else {
        console.error("Echo is not defined on window!");
    }
});

onUnmounted(() => {
    if (window.Echo) {
        window.Echo.leaveChannel("performances");
    }
});

watch(
    () => props.liveArtist,
    () => {
        calculateTimeLeft();
    },
    { deep: true },
);

// Actions
const goLive = (artistId) => {
    if (
        confirm(
            "Set this artist to LIVE? This will close any active performance.",
        )
    ) {
        router.post("/admin/performance/start", { artist_id: artistId });
    }
};

const togglePause = () => {
    if (!liveArtist.value) return;
    router.post(
        `/admin/performance/${liveArtist.value.performance_id}/toggle-pause`,
    );
};

const addTime = (seconds) => {
    if (!liveArtist.value) return;
    router.post(
        `/admin/performance/${liveArtist.value.performance_id}/adjust-time`,
        { seconds },
        { preserveScroll: true },
    );
};

const endPerformance = () => {
    if (!liveArtist.value) return;
    if (confirm("Close this performance for voting?")) {
        router.post(
            `/admin/performance/${liveArtist.value.performance_id}/end`,
        );
    }
};

const openVoting = () => {
    if (!liveArtist.value) return;
    router.post(
        `/admin/performance/${liveArtist.value.performance_id}/open-voting`,
    );
};

const resetPerformance = (performanceId) => {
    if (
        confirm(
            "RESET this performance? This will delete all votes and allow the artist to perform again.",
        )
    ) {
        router.post(`/admin/performance/${performanceId}/reset`, {
            preserveScroll: true,
        });
    }
};

const encodeName = (name) => encodeURIComponent(name);
</script>

<template>
    <AdminLayout>
        <Head title="Admin Dashboard - MVote" />

        <div class="animate-fade-up">
            <header class="mb-6">
                <h2
                    class="text-3xl font-black italic tracking-tighter uppercase leading-none"
                >
                    Command Dashboard
                </h2>
                <p
                    class="text-gray-500 font-bold uppercase tracking-widest text-[9px] mt-1"
                >
                    Real-time Advanced Event Analytics
                </p>
            </header>

            <!-- Stats Grid - Mobile First Single Column -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div
                    v-for="stat in displayStats"
                    :key="stat.label"
                    class="glass-card p-4 rounded-2xl border-white/5 relative overflow-hidden group"
                >
                    <div
                        class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-8 w-8"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                            />
                        </svg>
                    </div>
                    <p
                        class="text-[8px] font-black uppercase text-gray-400 tracking-widest mb-1"
                    >
                        {{ stat.label }}
                    </p>
                    <div class="flex items-baseline gap-2">
                        <p class="text-3xl font-black italic tracking-tighter">
                            {{ stat.value }}
                        </p>
                        <span
                            class="text-[9px] font-bold text-brand-yellow uppercase"
                            >{{ stat.trend }}</span
                        >
                    </div>
                </div>
            </div>

            <section class="mb-6">
                <div class="flex items-center justify-between mb-3 px-1">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"
                        ></div>
                        <h3
                            class="text-sm font-black italic uppercase tracking-wider"
                        >
                            Live Feed
                        </h3>
                    </div>
                    <button
                        class="text-[8px] font-bold text-gray-500 uppercase tracking-widest hover:text-white transition-colors"
                    >
                        Broadcast Switch
                    </button>
                </div>

                <div
                    v-if="liveArtist"
                    class="bg-[#0A0A0A] p-4 rounded-3xl border border-white/10 flex flex-col items-center gap-4 overflow-hidden relative shadow-2xl"
                >
                    <div
                        class="w-full aspect-square sm:w-24 sm:h-24 rounded-2xl overflow-hidden shrink-0 border-2 border-brand-yellow relative group"
                    >
                        <img
                            :src="liveArtist.image"
                            class="w-full h-full object-cover"
                        />
                        <div
                            class="absolute inset-0 bg-brand-yellow/20 animate-pulse"
                        ></div>
                    </div>

                    <div class="text-center w-full">
                        <h4
                            class="text-2xl font-black italic tracking-tighter uppercase leading-none mb-1"
                        >
                            {{ liveArtist.name }}
                        </h4>
                        <p
                            class="text-[9px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-3"
                        >
                            Transmission Active
                        </p>

                        <div class="flex justify-center gap-2 mb-4">
                            <div
                                class="flex items-center gap-1.5 bg-green-500/10 border border-green-500/20 px-2.5 py-1 rounded-xl"
                            >
                                <span
                                    class="text-[10px] font-black italic text-green-500"
                                    >{{ liveArtist.fan_voters }}</span
                                >
                                <span
                                    class="text-[8px] font-bold uppercase text-gray-500"
                                    >Fans</span
                                >
                            </div>
                            <div
                                class="flex items-center gap-1.5 bg-blue-500/10 border border-blue-500/20 px-2.5 py-1 rounded-xl"
                            >
                                <span
                                    class="text-[10px] font-black italic text-blue-500"
                                    >{{ liveArtist.judge_voters }}</span
                                >
                                <span
                                    class="text-[8px] font-bold uppercase text-gray-500"
                                    >Judges</span
                                >
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 relative z-10">
                            <template v-if="liveArtist.voting_started_at">
                                <div
                                    class="bg-white/5 border border-white/5 py-2.5 rounded-xl mb-2"
                                >
                                    <p
                                        class="text-[10px] font-black italic tracking-tighter text-brand-yellow"
                                        :class="{
                                            'animate-pulse':
                                                !liveArtist.is_voting_paused,
                                        }"
                                    >
                                        {{ timeRemaining }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        @click="togglePause"
                                        class="w-full bg-white/10 text-white font-black py-3 rounded-xl uppercase text-[9px] tracking-widest hover:bg-white/20 transition-all"
                                    >
                                        {{
                                            liveArtist.is_voting_paused
                                                ? "Resume"
                                                : "Pause"
                                        }}
                                    </button>
                                    <button
                                        @click="endPerformance"
                                        class="w-full bg-red-500/10 text-red-500 border border-red-500/20 font-black py-3 rounded-xl uppercase text-[9px] tracking-widest hover:bg-red-500/20 transition-all"
                                    >
                                        End Session
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        @click="addTime(60)"
                                        class="w-full glass-card py-3 rounded-xl font-black uppercase text-[9px] tracking-widest border-white/10 hover:bg-white/5 active:scale-95 transition-all"
                                    >
                                        +60s
                                    </button>
                                    <button
                                        @click="addTime(-30)"
                                        class="w-full glass-card py-3 rounded-xl font-black uppercase text-[9px] tracking-widest border-white/10 hover:bg-white/5 active:scale-95 transition-all"
                                    >
                                        -30s
                                    </button>
                                </div>
                            </template>
                            <template v-else>
                                <button
                                    @click="openVoting"
                                    class="w-full bg-brand-yellow text-black font-black py-4 rounded-xl uppercase tracking-widest text-[10px] shadow-[0_0_20px_rgba(255,107,0,0.3)] animate-hype-pulse mb-2"
                                >
                                    Open Voting Window
                                </button>
                                <button
                                    @click="endPerformance"
                                    class="w-full bg-white/5 text-gray-500 font-black py-3 rounded-xl uppercase text-[9px] tracking-widest hover:bg-white/10 transition-all"
                                >
                                    Cancel Performance
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
                <div
                    v-else
                    class="bg-white/5 border border-dashed border-white/10 p-12 rounded-3xl flex flex-col items-center justify-center text-center"
                >
                    <p
                        class="text-gray-500 font-bold uppercase tracking-widest text-[10px] mb-4"
                    >
                        No Transmissions Active
                    </p>
                    <p
                        class="text-white/30 text-[9px] max-w-50 leading-relaxed uppercase font-black italic"
                    >
                        Select an artist from the roster below to begin
                        broadcasting.
                    </p>
                </div>
            </section>

            <!-- On Deck / Finished Tabs -->
            <section class="mb-12">
                <div
                    class="flex items-center gap-6 mb-6 border-b border-white/5 px-2"
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
                        Lined Up
                        <div
                            v-if="activeTab === 'upcoming'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-brand-yellow"
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
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-brand-yellow"
                        ></div>
                    </button>
                </div>

                <div
                    v-if="activeTab === 'upcoming'"
                    class="grid grid-cols-1 gap-4"
                >
                    <div
                        v-for="artist in linedUpArtists"
                        :key="artist.id"
                        class="glass-card p-4 rounded-3xl border-white/5 flex items-center gap-4 transition-all group hover:border-brand-yellow/30"
                    >
                        <div class="flex-1">
                            <h4
                                class="text-xs font-black uppercase italic tracking-tighter"
                            >
                                {{ artist.name }}
                            </h4>
                            <p
                                class="text-[8px] font-bold uppercase tracking-widest text-gray-500"
                            >
                                Awaiting Stage
                            </p>
                        </div>
                        <button
                            @click="goLive(artist.id)"
                            class="px-4 py-2 bg-brand-yellow text-black text-[9px] font-black uppercase tracking-widest rounded-xl hover:scale-105 transition-all"
                        >
                            Set live
                        </button>
                    </div>
                    <div
                        v-if="linedUpArtists.length === 0"
                        class="col-span-full py-12 text-center text-gray-600 font-bold uppercase text-[10px] tracking-widest border border-dashed border-white/5 rounded-3xl"
                    >
                        No more artists lined up
                    </div>
                </div>

                <div v-else class="grid grid-cols-1 gap-4">
                    <div
                        v-for="artist in closedArtists"
                        :key="artist.id"
                        class="glass-card p-4 rounded-3xl border-white/5 flex items-center gap-4 transition-all group opacity-80"
                    >
                        <div class="flex-1">
                            <h4
                                class="text-xs font-black uppercase italic tracking-tighter mb-2"
                            >
                                {{ artist.name }}
                            </h4>
                            <div class="flex gap-2">
                                <div
                                    class="flex items-center gap-1.5 bg-green-500/10 border border-green-500/20 px-2 py-0.5 rounded-lg"
                                >
                                    <span
                                        class="text-[8px] font-black italic text-green-500"
                                        >{{ artist.fan_voters }}</span
                                    >
                                    <span
                                        class="text-[7px] font-bold uppercase text-gray-500"
                                        >Fans</span
                                    >
                                </div>
                                <div
                                    class="flex items-center gap-1.5 bg-blue-500/10 border border-blue-500/20 px-2 py-0.5 rounded-lg"
                                >
                                    <span
                                        class="text-[8px] font-black italic text-blue-500"
                                        >{{ artist.judge_voters }}</span
                                    >
                                    <span
                                        class="text-[7px] font-bold uppercase text-gray-500"
                                        >Judges</span
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <div
                                class="px-3 py-1 bg-white/5 text-white/20 text-[8px] font-black uppercase tracking-widest rounded-lg border border-white/5"
                            >
                                Done
                            </div>
                            <button
                                v-if="artist.performance_id"
                                @click="resetPerformance(artist.performance_id)"
                                class="px-3 py-1 bg-white/10 text-white/60 text-[8px] font-black uppercase tracking-widest rounded-lg border border-white/5 hover:bg-white/20 transition-all"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                    <div
                        v-if="closedArtists.length === 0"
                        class="col-span-full py-12 text-center text-gray-600 font-bold uppercase text-[10px] tracking-widest border border-dashed border-white/5 rounded-3xl"
                    >
                        No history available yet
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
