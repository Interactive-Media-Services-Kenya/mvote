<script setup>
import { Head } from "@inertiajs/vue3";
import AdminLayout from "../../Layouts/AdminLayout.vue";
import HypeModal from "../../Components/HypeModal.vue";
import { artists } from "../../constants";
import { ref } from "vue";

const showAddModal = ref(false);
const isSubmitting = ref(false);
const newArtist = ref({
    name: "",
    genre: "",
    bio: "",
    discography: [{ title: "", year: "" }],
});

const addDiscographyRow = () => {
    newArtist.value.discography.push({ title: "", year: "" });
};

const onboardArtist = async () => {
    isSubmitting.value = true;
    await new Promise((resolve) => setTimeout(resolve, 2000));
    // Simulate addition to list
    isSubmitting.value = false;
    showAddModal.value = false;
    newArtist.value = {
        name: "",
        genre: "",
        bio: "",
        discography: [{ title: "", year: "" }],
    };
};
</script>

<template>
    <AdminLayout>
        <Head title="Artist Management - MVote" />

        <div class="animate-fade-up">
            <header class="flex items-center justify-between mb-8 px-1">
                <div>
                    <h2
                        class="text-3xl font-black italic tracking-tighter uppercase leading-none"
                    >
                        Lineup
                    </h2>
                    <p
                        class="text-gray-500 font-bold uppercase tracking-widest text-[9px] mt-1"
                    >
                        Onboard & Dynamic Session Control
                    </p>
                </div>
                <button
                    @click="showAddModal = true"
                    class="w-12 h-12 bg-brand-orange text-black rounded-2xl flex items-center justify-center shadow-lg active:scale-95 transition-all"
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
                </button>
            </header>

            <!-- Unified List Aesthetic -->
            <div class="space-y-4">
                <div
                    v-for="artist in artists"
                    :key="artist.id"
                    class="glass-card p-3 rounded-3xl border-white/5 flex items-center gap-4 group active:scale-[0.99] transition-all relative overflow-hidden"
                >
                    <div
                        class="w-14 h-14 rounded-2xl overflow-hidden shrink-0 shadow-lg border border-white/10 group-hover:border-brand-orange/30 transition-colors"
                    >
                        <img
                            :src="artist.image"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                        />
                    </div>

                    <div class="flex-1 min-w-0">
                        <h4
                            class="text-lg font-black italic tracking-tighter uppercase leading-tight truncate mb-0.5"
                        >
                            {{ artist.name }}
                        </h4>
                        <div class="flex items-center gap-2">
                            <span
                                class="text-[8px] font-black uppercase text-brand-orange tracking-widest"
                                >{{ artist.genre }}</span
                            >
                            <span
                                class="w-1 h-1 rounded-full bg-gray-700"
                            ></span>
                            <span
                                class="text-[8px] font-black uppercase text-gray-500 tracking-widest"
                                >{{ artist.status }}</span
                            >
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div
                            v-if="artist.status === 'live'"
                            class="w-2 h-2 rounded-full bg-red-600 animate-pulse shadow-[0_0_8px_rgba(220,38,38,0.8)]"
                        ></div>
                        <button
                            class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2.5"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                />
                            </svg>
                        </button>
                    </div>

                    <!-- Decor -->
                    <div
                        class="absolute -right-2 -bottom-2 opacity-[0.03] group-hover:opacity-[0.06] transition-opacity pointer-events-none"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-20 h-20"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                            />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Unified Hype Modal -->
        <HypeModal
            :show="showAddModal"
            title="Onboard Act"
            subtitle="Lineup expansion"
            maxWidth="max-w-md"
            @close="showAddModal = false"
        >
            <div class="space-y-6">
                <!-- Basic Info Section -->
                <div class="space-y-2">
                    <label
                        class="text-[10px] font-black uppercase text-gray-500 tracking-widest ml-1"
                        >Artist Name</label
                    >
                    <input
                        v-model="newArtist.name"
                        type="text"
                        placeholder="e.g. Burna Boy"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-orange transition-all font-bold placeholder:text-gray-700 text-white"
                    />
                </div>
                <div class="space-y-2">
                    <label
                        class="text-[10px] font-black uppercase text-gray-500 tracking-widest ml-1"
                        >Genre</label
                    >
                    <input
                        v-model="newArtist.genre"
                        type="text"
                        placeholder="e.g. Afro-Fusion"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-orange transition-all font-bold placeholder:text-gray-700 text-white"
                    />
                </div>

                <div class="space-y-2">
                    <label
                        class="text-[10px] font-black uppercase text-gray-500 tracking-widest ml-1"
                        >Biography</label
                    >
                    <textarea
                        v-model="newArtist.bio"
                        placeholder="Tell the fans why they should vote..."
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 h-32 outline-none focus:border-brand-orange transition-all font-medium resize-none placeholder:text-gray-700 leading-relaxed text-white"
                    ></textarea>
                </div>

                <!-- Discography Section -->
                <div class="space-y-4 pt-4 border-t border-white/5">
                    <div class="flex items-center justify-between px-1">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-1 h-3 bg-brand-orange rounded-full"
                            ></div>
                            <h4
                                class="text-[10px] font-black uppercase tracking-widest text-white"
                            >
                                Discography
                            </h4>
                        </div>
                        <button
                            @click="addDiscographyRow"
                            class="text-[9px] font-black uppercase tracking-widest text-brand-orange hover:text-white transition-colors"
                        >
                            + Add Entry
                        </button>
                    </div>

                    <div
                        class="space-y-3 max-h-48 overflow-y-auto custom-scrollbar pr-2"
                    >
                        <div
                            v-for="(album, i) in newArtist.discography"
                            :key="i"
                            class="flex gap-3 animate-fade-up"
                        >
                            <input
                                v-model="album.title"
                                type="text"
                                placeholder="Album Title"
                                class="flex-1 bg-white/5 border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-brand-orange transition-all font-bold text-xs text-white"
                            />
                            <input
                                v-model="album.year"
                                type="text"
                                placeholder="Year"
                                class="w-20 bg-white/5 border border-white/10 rounded-xl px-4 py-3 outline-none focus:border-brand-orange transition-all font-bold text-xs text-center text-white"
                            />
                            <button
                                v-if="newArtist.discography.length > 1"
                                @click="newArtist.discography.splice(i, 1)"
                                class="w-11 h-11 shrink-0 flex items-center justify-center text-gray-500 hover:text-red-500 transition-colors bg-white/5 rounded-xl border border-white/5"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex flex-col gap-3">
                    <button
                        @click="onboardArtist"
                        :disabled="isSubmitting"
                        class="w-full bg-brand-orange text-black py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl active:scale-95 transition-all disabled:opacity-50 relative overflow-hidden"
                    >
                        <span v-if="!isSubmitting">Confirm Onboarding</span>
                        <span
                            v-else
                            class="flex items-center justify-center gap-2"
                        >
                            <svg
                                class="animate-spin h-4 w-4 text-black"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                    <button
                        @click="showAddModal = false"
                        class="w-full py-2 rounded-xl font-black uppercase text-[10px] tracking-widest text-gray-500 hover:text-white transition-all"
                    >
                        Cancel Onboarding
                    </button>
                </div>
            </template>
        </HypeModal>
    </AdminLayout>
</template>
