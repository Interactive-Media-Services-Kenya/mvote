<script setup>
import { Head } from "@inertiajs/vue3";
import AdminLayout from "../../Layouts/AdminLayout.vue";
import HypeModal from "../../Components/HypeModal.vue";
import { votingQuestions } from "../../constants";
import { ref } from "vue";

const eventDetails = ref({
    title: "Kenya Music Awards 2026",
    location: "Nairobi National Park",
    totalCapacity: 5000,
});

const showQuestionModal = ref(false);
const editingQuestion = ref(null);
const isSaving = ref(false);
const questionForm = ref({
    id: "",
    text: "",
    lowLabel: "",
    highLabel: "",
    target: "both",
});

const openQuestionModal = (question = null) => {
    if (question) {
        editingQuestion.value = question;
        questionForm.value = { ...question };
    } else {
        editingQuestion.value = null;
        questionForm.value = {
            id: `MQ_${Math.floor(Math.random() * 1000)}`,
            text: "",
            lowLabel: "",
            highLabel: "",
            target: "both",
        };
    }
    showQuestionModal.value = true;
};

const saveQuestion = async () => {
    isSaving.value = true;
    await new Promise((resolve) => setTimeout(resolve, 1500));
    isSaving.value = false;
    showQuestionModal.value = false;
};
</script>

<template>
    <AdminLayout>
        <Head title="Event Setup - MVote" />

        <div class="animate-fade-up">
            <header class="mb-8 px-1">
                <h2
                    class="text-3xl font-black italic tracking-tighter uppercase leading-none"
                >
                    Settings
                </h2>
                <p
                    class="text-gray-500 font-bold uppercase tracking-widest text-[9px] mt-1"
                >
                    Configure Mission Parameters
                </p>
            </header>

            <div class="flex flex-col gap-10 pb-32">
                <!-- Metadata Form -->
                <section class="space-y-4">
                    <div class="flex items-center gap-2 px-1">
                        <div class="w-1 h-3 bg-brand-orange rounded-full"></div>
                        <h3
                            class="text-[10px] font-black uppercase tracking-widest text-white"
                        >
                            Core Configuration
                        </h3>
                    </div>

                    <div
                        class="glass-card p-6 rounded-3xl border-white/5 space-y-6"
                    >
                        <div class="space-y-2">
                            <label
                                class="text-[9px] font-black uppercase text-gray-500 tracking-widest ml-1"
                                >Event Title</label
                            >
                            <input
                                v-model="eventDetails.title"
                                type="text"
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-orange transition-all font-bold text-white placeholder:text-gray-700"
                            />
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[9px] font-black uppercase text-gray-500 tracking-widest ml-1"
                                >Deployment Venue</label
                            >
                            <input
                                v-model="eventDetails.location"
                                type="text"
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-orange transition-all font-bold text-white placeholder:text-gray-700"
                            />
                        </div>
                        <button
                            class="w-full bg-white/5 text-white/50 border border-white/10 py-4 rounded-2xl font-black uppercase text-[10px] tracking-widest hover:text-white hover:bg-white/10 transition-all active:scale-[0.98]"
                        >
                            Apply Changes
                        </button>
                    </div>
                </section>

                <!-- Voting Questions -->
                <section class="space-y-4">
                    <div class="flex items-center justify-between px-1">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-1 h-3 bg-brand-orange rounded-full"
                            ></div>
                            <h3
                                class="text-[10px] font-black uppercase tracking-widest text-white"
                            >
                                Metric Architecture
                            </h3>
                        </div>
                        <span
                            class="text-[8px] font-black text-brand-orange uppercase tracking-widest"
                            >{{ votingQuestions.length }} Slots Active</span
                        >
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="q in votingQuestions"
                            :key="q.id"
                            class="glass-card p-4 rounded-3xl border-white/5 group relative overflow-hidden active:scale-[0.99] transition-all"
                        >
                            <div class="relative z-10">
                                <div
                                    class="flex items-start justify-between mb-3"
                                >
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="px-2 py-1 rounded-lg text-[7px] font-black uppercase tracking-widest border"
                                            :class="[
                                                q.target === 'fan'
                                                    ? 'border-brand-orange/30 text-brand-orange bg-brand-orange/5'
                                                    : q.target === 'judge'
                                                      ? 'border-purple-500/30 text-purple-400 bg-purple-500/5'
                                                      : 'border-green-500/30 text-green-400 bg-green-500/5',
                                            ]"
                                        >
                                            {{
                                                q.target === "both"
                                                    ? "Hybrid"
                                                    : q.target
                                            }}
                                        </div>
                                        <span
                                            class="text-[8px] font-black uppercase text-gray-700 tracking-widest"
                                            >{{ q.id }}</span
                                        >
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            @click="openQuestionModal(q)"
                                            class="w-8 h-8 rounded-xl flex items-center justify-center text-gray-500 hover:text-white transition-all bg-white/5 border border-white/10"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-3.5 w-3.5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2.5"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <h4
                                    class="text-base font-black italic text-white mb-4 pr-6 leading-tight uppercase tracking-tighter"
                                >
                                    {{ q.text }}
                                </h4>

                                <div class="flex gap-2">
                                    <div
                                        class="flex-1 px-3 py-2 bg-white/2 rounded-xl border border-white/5"
                                    >
                                        <span
                                            class="text-[6px] font-black text-gray-600 uppercase tracking-[0.2em] block mb-0.5"
                                            >Min Label</span
                                        >
                                        <span
                                            class="text-[9px] font-black uppercase text-brand-orange italic"
                                            >{{ q.lowLabel }}</span
                                        >
                                    </div>
                                    <div
                                        class="flex-1 px-3 py-2 bg-white/2 rounded-xl border border-white/5"
                                    >
                                        <span
                                            class="text-[6px] font-black text-gray-600 uppercase tracking-[0.2em] block mb-0.5"
                                            >Max Label</span
                                        >
                                        <span
                                            class="text-[9px] font-black uppercase text-brand-orange italic"
                                            >{{ q.highLabel }}</span
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button
                            @click="openQuestionModal()"
                            class="w-full glass-card py-4 rounded-3xl border-dashed border-white/10 text-[9px] font-black uppercase tracking-[0.3em] text-gray-600 hover:text-brand-orange hover:border-brand-orange/30 transition-all active:scale-[0.98]"
                        >
                            + Add Metric Block
                        </button>
                    </div>
                </section>
            </div>
        </div>

        <!-- Unified Hype Modal -->
        <HypeModal
            :show="showQuestionModal"
            :title="editingQuestion ? 'Modify Metric' : 'Deploy Metric'"
            subtitle="Protocol Configuration"
            maxWidth="max-w-md"
            @close="showQuestionModal = false"
        >
            <div class="space-y-6">
                <div class="space-y-2 text-left">
                    <label
                        class="text-[10px] font-black uppercase text-gray-500 tracking-widest ml-1"
                        >Metric Description</label
                    >
                    <textarea
                        v-model="questionForm.text"
                        placeholder="e.g. How much did you enjoy the energy?"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 h-24 outline-none focus:border-brand-orange transition-all font-bold placeholder:text-gray-700 leading-tight text-white resize-none"
                    ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2 text-left">
                        <label
                            class="text-[10px] font-black uppercase text-gray-500 tracking-widest ml-1"
                            >Min. Label (1)</label
                        >
                        <input
                            v-model="questionForm.lowLabel"
                            type="text"
                            placeholder="e.g. Weak"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-orange transition-all font-bold text-xs text-white placeholder:text-gray-700"
                        />
                    </div>
                    <div class="space-y-2 text-left">
                        <label
                            class="text-[10px] font-black uppercase text-gray-500 tracking-widest ml-1"
                            >Max. Label (5)</label
                        >
                        <input
                            v-model="questionForm.highLabel"
                            type="text"
                            placeholder="e.g. Fire!"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-orange transition-all font-bold text-xs text-white placeholder:text-gray-700"
                        />
                    </div>
                </div>

                <!-- Targeting Options -->
                <div class="space-y-3 text-left">
                    <label
                        class="text-[10px] font-black uppercase text-gray-500 tracking-widest ml-1"
                        >Target Permission Level</label
                    >
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            v-for="target in ['fan', 'judge', 'both']"
                            :key="target"
                            @click="questionForm.target = target"
                            class="py-3 rounded-2xl border text-[9px] font-black uppercase tracking-widest transition-all"
                            :class="[
                                questionForm.target === target
                                    ? 'bg-brand-orange text-black border-brand-orange shadow-lg shadow-brand-orange/20'
                                    : 'bg-white/5 border-white/10 text-gray-500',
                            ]"
                        >
                            {{ target === "both" ? "Hybrid" : target }}
                        </button>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex flex-col gap-3">
                    <button
                        @click="saveQuestion"
                        :disabled="isSaving"
                        class="w-full bg-white text-black py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl active:scale-95 transition-all disabled:opacity-50 relative overflow-hidden"
                    >
                        <span v-if="!isSaving">{{
                            editingQuestion
                                ? "Sync Changes"
                                : "Initialize Metric"
                        }}</span>
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
                            Syncing...
                        </span>
                    </button>
                    <button
                        @click="showQuestionModal = false"
                        class="w-full py-2 rounded-xl font-black uppercase text-[10px] tracking-widest text-gray-500 hover:text-white transition-all"
                    >
                        Abandon Config
                    </button>
                </div>
            </template>
        </HypeModal>
    </AdminLayout>
</template>
