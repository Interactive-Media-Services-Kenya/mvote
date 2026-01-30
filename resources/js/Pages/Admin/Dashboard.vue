<script setup>
import { Head } from "@inertiajs/vue3";
import AdminLayout from "../../Layouts/AdminLayout.vue";
import { artists } from "../../constants";
import { computed } from "vue";

const liveArtist = computed(() => artists.find((a) => a.status === "live"));
const stats = [
    {
        label: "Total Votes Cast",
        value: "45.2k",
        trend: "+12%",
        color: "text-brand-yellow",
    },
    {
        label: "Active Fans",
        value: "1,240",
        trend: "Live",
        color: "text-green-500",
    },
    { label: "Avg Rating", value: "4.8", trend: "Stars", color: "text-white" },
];
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
            <div class="grid grid-cols-1 gap-4 mb-6">
                <div
                    v-for="stat in stats"
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
                            class="text-[9px] font-bold text-gray-500 uppercase tracking-[0.2em] mb-4"
                        >
                            Transmission Active
                        </p>

                        <div class="flex flex-col gap-2 relative z-10">
                            <div
                                class="bg-white/5 border border-white/5 py-2.5 rounded-xl mb-2"
                            >
                                <p
                                    class="text-[10px] font-black italic tracking-tighter text-brand-yellow animate-pulse"
                                >
                                    03:45 Remaining
                                </p>
                            </div>

                            <button
                                class="w-full bg-yellow-500 text-black font-black py-3 rounded-xl uppercase text-[9px] tracking-widest shadow-lg active:scale-95 transition-all"
                            >
                                Pause Session
                            </button>
                            <button
                                class="w-full glass-card py-3 rounded-xl font-black uppercase text-[9px] tracking-widest border-white/10 hover:bg-white/5 active:scale-95 transition-all"
                            >
                                Add +60s
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
