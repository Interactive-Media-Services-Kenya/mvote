<script setup>
import { Head, usePage, useForm } from "@inertiajs/vue3";
import AdminLayout from "../../Layouts/AdminLayout.vue";
import HypeModal from "../../Components/HypeModal.vue";
import { ref } from "vue";

const props = defineProps({
    artists: Array,
    genres: Array,
});

const showAddModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: "",
    genre_id: "",
    bio: "",
    photo: null,
});

const genres = props.genres;
const artists = props.artists;

const openAddModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    showAddModal.value = true;
};

const openEditModal = (artist) => {
    isEditing.value = true;
    editingId.value = artist.id;
    form.name = artist.name;
    form.genre_id =
        props.genres.find((g) => g.title === artist.genre)?.id || "";
    form.bio = artist.bio || ""; // Note: Artist list might need bio if not loaded
    form.photo = null; // Don't pre-fill file input with URL
    showAddModal.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.post(`/admin/artists/${editingId.value}`, {
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
            },
            forceFormData: true,
            onBefore: () => {
                form._method = "PUT"; // Use common Laravel trick for file upload with PUT
            },
        });
    } else {
        form.post("/admin/artists", {
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteArtist = (artist) => {
    if (confirm(`Are you sure you want to delete ${artist.name}?`)) {
        form.delete(`/admin/artists/${artist.id}`);
    }
};

const moveArtist = (index, direction) => {
    const newIndex = index + direction;
    if (newIndex < 0 || newIndex >= artists.length) return;

    const list = [...artists];
    const item = list.splice(index, 1)[0];
    list.splice(newIndex, 0, item);

    // Immediate local update for snappy feel
    artists.splice(0, artists.length, ...list);

    router.post(
        "/admin/artists/reorder",
        {
            artists: list.map((a) => a.id),
        },
        {
            preserveScroll: true,
        },
    );
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
                    class="w-12 h-12 bg-brand-yellow text-black rounded-2xl flex items-center justify-center shadow-lg active:scale-95 transition-all"
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
                    v-for="(artist, index) in artists"
                    :key="artist.id"
                    class="glass-card p-3 rounded-3xl border-white/5 flex items-center gap-4 group active:scale-[0.99] transition-all relative overflow-hidden"
                >
                    <div
                        class="w-14 h-14 rounded-2xl overflow-hidden shrink-0 shadow-lg border border-white/10 group-hover:border-brand-yellow/30 transition-colors"
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
                        <div class="flex flex-col items-start gap-2">
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-[8px] font-black uppercase text-brand-yellow tracking-widest"
                                    >{{ artist.genre }}</span
                                >
                            </div>
                            <div class="flex gap-2">
                                <span
                                    class="text-[8px] font-black uppercase text-gray-500 tracking-widest"
                                    >{{ artist.status }}</span
                                >
                                <span
                                    class="text-[8px] font-black uppercase text-brand-yellow tracking-widest"
                                    >{{ artist.scheduled_time }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Reorder Controls -->
                        <div class="flex flex-col gap-1 mr-2">
                            <button
                                @click="moveArtist(index, -1)"
                                :disabled="index === 0"
                                class="w-6 h-6 rounded-md bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 hover:text-white disabled:opacity-30 disabled:hover:text-gray-400 transition-all"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-3 w-3"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="3"
                                        d="M5 15l7-7 7 7"
                                    />
                                </svg>
                            </button>
                            <button
                                @click="moveArtist(index, 1)"
                                :disabled="index === artists.length - 1"
                                class="w-6 h-6 rounded-md bg-white/5 border border-white/10 flex items-center justify-center text-gray-400 hover:text-white disabled:opacity-30 disabled:hover:text-gray-400 transition-all"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-3 w-3"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="3"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>
                            </button>
                        </div>
                        <div
                            v-if="artist.status === 'live'"
                            class="w-2 h-2 rounded-full bg-red-600 animate-pulse shadow-[0_0_8px_rgba(220,38,38,0.8)]"
                        ></div>
                        <button
                            @click="openEditModal(artist)"
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
                        <button
                            @click="deleteArtist(artist)"
                            class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-gray-400/50 hover:text-red-500 hover:bg-red-500/10 hover:border-red-500/20 transition-all"
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
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
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
            :title="isEditing ? 'Update Profile' : 'Onboard Artist'"
            :subtitle="
                isEditing ? 'Live Session Modification' : 'Lineup expansion'
            "
            maxWidth="max-w-md"
            @close="showAddModal = false"
        >
            <div class="space-y-6">
                <!-- Photo Upload Section -->
                <div class="flex flex-col items-center gap-4">
                    <div
                        class="w-24 h-24 rounded-3xl bg-white/5 border-2 border-dashed border-white/10 flex items-center justify-center relative overflow-hidden group hover:border-brand-yellow/30 transition-all"
                    >
                        <input
                            type="file"
                            @input="form.photo = $event.target.files[0]"
                            class="absolute inset-0 opacity-0 cursor-pointer z-10"
                            accept="image/*"
                        />
                        <img
                            v-if="form.photo"
                            :src="URL.createObjectURL(form.photo)"
                            class="w-full h-full object-cover"
                        />
                        <img
                            v-else-if="isEditing && editingId"
                            :src="
                                artists.find((a) => a.id === editingId)?.image
                            "
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="text-gray-500">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-8 w-8 text-white/20"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                    </div>
                    <span
                        class="text-[9px] font-black uppercase tracking-widest text-gray-500"
                    >
                        {{ form.photo ? "Photo Ready" : "Upload Artist Photo" }}
                    </span>
                </div>

                <!-- Basic Info Section -->
                <div class="space-y-2">
                    <label
                        class="text-[10px] font-black uppercase text-gray-500 tracking-widest ml-1"
                        >Artist Name</label
                    >
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="e.g. Burna Boy"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-yellow transition-all font-bold placeholder:text-gray-700 text-white"
                    />
                </div>
                <div class="space-y-2">
                    <label
                        class="text-[10px] font-black uppercase text-gray-500 tracking-widest ml-1"
                    >
                        Genre
                    </label>
                    <div class="relative group">
                        <select
                            v-model="form.genre_id"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-yellow transition-all font-bold text-white appearance-none cursor-pointer"
                        >
                            <option
                                value=""
                                disabled
                                selected
                                class="bg-zinc-900 text-gray-500"
                            >
                                Select Genre
                            </option>
                            <option
                                v-for="genre in genres"
                                :key="genre.id"
                                :value="genre.id"
                                class="bg-zinc-900 text-white py-2"
                            >
                                {{ genre.title }}
                            </option>
                        </select>

                        <div
                            class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500 group-focus-within:text-brand-yellow transition-colors"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label
                        class="text-[10px] font-black uppercase text-gray-500 tracking-widest ml-1"
                        >Biography</label
                    >
                    <textarea
                        v-model="form.bio"
                        placeholder="Tell the fans why they should vote..."
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 h-32 outline-none focus:border-brand-yellow transition-all font-medium resize-none placeholder:text-gray-700 leading-relaxed text-white"
                    ></textarea>
                </div>
            </div>

            <template #footer>
                <div class="flex flex-col gap-3">
                    <button
                        @click="submitForm"
                        :disabled="form.processing"
                        class="w-full bg-brand-yellow text-black py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl active:scale-95 transition-all disabled:opacity-50 relative overflow-hidden"
                    >
                        <span v-if="!form.processing">
                            {{
                                isEditing
                                    ? "Push Updates"
                                    : "Confirm Onboarding"
                            }}
                        </span>
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
                        {{
                            isEditing ? "Discard Changes" : "Cancel Onboarding"
                        }}
                    </button>
                </div>
            </template>
        </HypeModal>
    </AdminLayout>
</template>
