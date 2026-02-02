<script setup>
import { Head, useForm, router } from "@inertiajs/vue3";
import AdminLayout from "../../Layouts/AdminLayout.vue";
import HypeModal from "../../Components/HypeModal.vue";
import { ref } from "vue";

const props = defineProps({
    event: Object,
});

const form = useForm({
    id: props.event?.id || null,
    name: props.event?.name || "",
    description: props.event?.description || "",
    is_active: props.event?.is_active ?? true,
    start_time: props.event?.start_time
        ? new Date(props.event.start_time).toISOString().slice(0, 16)
        : "",
    performance_duration: props.event?.performance_duration ?? 5,
    break_duration: props.event?.break_duration ?? 2,
    questions: props.event?.questions || [],
});

const showQuestionModal = ref(false);
const editingIndex = ref(null);
const isSaving = ref(false);

const questionForm = ref({
    id: null,
    question_text: "",
    type: "rating",
    low_label: "",
    high_label: "",
    target: "both",
});

const openQuestionModal = (index = null) => {
    if (index !== null) {
        editingIndex.value = index;
        questionForm.value = {
            id: form.questions[index].id || null,
            question_text: form.questions[index].question_text || "",
            type: form.questions[index].type || "rating",
            low_label: form.questions[index].low_label || "",
            high_label: form.questions[index].high_label || "",
            target: form.questions[index].target || "both",
        };
    } else {
        editingIndex.value = null;
        questionForm.value = {
            id: null,
            question_text: "",
            type: "rating",
            low_label: "Weak",
            high_label: "Fire!",
            target: "both",
        };
    }
    showQuestionModal.value = true;
};

const removeQuestion = (index) => {
    const question = form.questions[index];

    if (question.id) {
        if (
            confirm(
                "This metric is already live. Are you sure you want to delete it permanently?",
            )
        ) {
            router.delete(`/admin/event/questions/${question.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    form.questions.splice(index, 1);
                },
            });
        }
    } else {
        form.questions.splice(index, 1);
    }
};

const saveQuestion = () => {
    if (editingIndex.value !== null) {
        form.questions.splice(editingIndex.value, 1, { ...questionForm.value });
    } else {
        form.questions.push({ ...questionForm.value });
    }
    showQuestionModal.value = false;
};

const submitEvent = () => {
    form.post("/admin/event", {
        preserveScroll: true,
        onSuccess: (page) => {
            const updatedEvent = page.props.event;
            if (updatedEvent) {
                form.id = updatedEvent.id;
                form.questions = [...updatedEvent.questions];
            }
        },
    });
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
                        <div class="w-1 h-3 bg-brand-yellow rounded-full"></div>
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
                                v-model="form.name"
                                type="text"
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-yellow transition-all font-bold text-white placeholder:text-gray-700"
                                :class="{
                                    'border-red-500/50': form.errors.name,
                                }"
                            />
                            <p
                                v-if="form.errors.name"
                                class="text-[8px] text-red-400 font-bold uppercase ml-1"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[9px] font-black uppercase text-gray-500 tracking-widest ml-1"
                                >Brief Description</label
                            >
                            <input
                                v-model="form.description"
                                type="text"
                                class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-yellow transition-all font-bold text-white placeholder:text-gray-700"
                                :class="{
                                    'border-red-500/50':
                                        form.errors.description,
                                }"
                            />
                            <p
                                v-if="form.errors.description"
                                class="text-[8px] text-red-400 font-bold uppercase ml-1"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Scheduling Config -->
                        <div class="grid grid-cols-1 gap-6">
                            <div class="space-y-2">
                                <label
                                    class="text-[9px] font-black uppercase text-gray-500 tracking-widest ml-1"
                                    >Event Kick-off Time</label
                                >
                                <input
                                    v-model="form.start_time"
                                    type="datetime-local"
                                    class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-yellow transition-all font-bold text-white"
                                    :class="{
                                        'border-red-500/50':
                                            form.errors.start_time,
                                    }"
                                />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label
                                        class="text-[9px] font-black uppercase text-gray-500 tracking-widest ml-1"
                                        >Perf. Duration (Mins)</label
                                    >
                                    <input
                                        v-model="form.performance_duration"
                                        type="number"
                                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-yellow transition-all font-bold text-white"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-[9px] font-black uppercase text-gray-500 tracking-widest ml-1"
                                        >Setup Break (Mins)</label
                                    >
                                    <input
                                        v-model="form.break_duration"
                                        type="number"
                                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-yellow transition-all font-bold text-white"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between px-1">
                            <span
                                class="text-[9px] font-black uppercase text-gray-500 tracking-widest"
                                >Event Visibility</span
                            >
                            <button
                                @click="form.is_active = !form.is_active"
                                class="px-4 py-2 rounded-xl text-[8px] font-black uppercase tracking-widest transition-all"
                                :class="
                                    form.is_active
                                        ? 'bg-green-500/10 text-green-500 border border-green-500/20'
                                        : 'bg-red-500/10 text-red-500 border border-red-500/20'
                                "
                            >
                                {{ form.is_active ? "Active" : "Inactive" }}
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Voting Questions -->
                <section class="space-y-4">
                    <div class="flex items-center justify-between px-1">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-1 h-3 bg-brand-yellow rounded-full"
                            ></div>
                            <h3
                                class="text-[10px] font-black uppercase tracking-widest text-white"
                            >
                                Metric Architecture
                            </h3>
                        </div>
                        <span
                            class="text-[8px] font-black text-brand-yellow uppercase tracking-widest"
                            >{{ form.questions.length }} Slots Active</span
                        >
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="(q, index) in form.questions"
                            :key="index"
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
                                                    ? 'border-brand-yellow/30 text-brand-yellow bg-brand-yellow/5'
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
                                        <div
                                            class="px-2 py-1 rounded-lg text-[7px] font-black uppercase tracking-widest border border-white/10 bg-white/5 text-gray-400"
                                        >
                                            {{ q.type }}
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            @click="openQuestionModal(index)"
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
                                        <button
                                            @click="removeQuestion(index)"
                                            class="w-8 h-8 rounded-xl flex items-center justify-center text-red-500/50 hover:text-red-500 transition-all bg-white/5 border border-white/10"
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
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <h4
                                    class="text-base font-black italic text-white mb-1 pr-6 leading-tight uppercase tracking-tighter"
                                    :class="{
                                        'text-red-400':
                                            form.errors[
                                                `questions.${index}.question_text`
                                            ],
                                    }"
                                >
                                    {{ q.question_text }}
                                </h4>
                                <p
                                    v-if="
                                        form.errors[
                                            `questions.${index}.question_text`
                                        ]
                                    "
                                    class="text-[8px] text-red-500 font-bold uppercase mb-4"
                                >
                                    {{
                                        form.errors[
                                            `questions.${index}.question_text`
                                        ]
                                    }}
                                </p>

                                <div
                                    v-if="q.type === 'rating'"
                                    class="flex gap-2"
                                >
                                    <div
                                        class="flex-1 px-3 py-2 bg-white/2 rounded-xl border border-white/5"
                                    >
                                        <span
                                            class="text-[6px] font-black text-gray-600 uppercase tracking-[0.2em] block mb-0.5"
                                            >Min Label</span
                                        >
                                        <span
                                            class="text-[9px] font-black uppercase text-brand-yellow italic"
                                            >{{ q.low_label }}</span
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
                                            class="text-[9px] font-black uppercase text-brand-yellow italic"
                                            >{{ q.high_label }}</span
                                        >
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="text-[9px] font-black uppercase text-gray-500 tracking-widest italic"
                                >
                                    Full Text Input Field
                                </div>
                            </div>
                        </div>

                        <button
                            @click="openQuestionModal()"
                            class="w-full glass-card py-4 rounded-3xl border-dashed border-white/10 text-[9px] font-black uppercase tracking-[0.3em] text-gray-600 hover:text-brand-yellow hover:border-brand-yellow/30 transition-all active:scale-[0.98]"
                        >
                            + Add Metric Block
                        </button>
                    </div>

                    <div class="pt-6">
                        <button
                            @click="submitEvent"
                            :disabled="form.processing"
                            class="w-full bg-brand-yellow text-black py-5 rounded-3xl font-black uppercase text-xs tracking-[0.2em] shadow-[0_20px_40px_rgba(255,107,0,0.3)] hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? "Syncing Event..."
                                    : "DEPLOY ALL SETTINGS & METRICS"
                            }}
                        </button>
                        <p
                            v-if="form.recentlySuccessful"
                            class="text-center text-green-500 text-[9px] font-black uppercase tracking-widest mt-4 animate-pulse"
                        >
                            Successfully Deployed to Mission Control!
                        </p>
                    </div>
                </section>
            </div>
        </div>

        <HypeModal
            :show="showQuestionModal"
            :title="editingIndex !== null ? 'Modify Metric' : 'Deploy Metric'"
            subtitle="Protocol Configuration"
            maxWidth="max-w-md"
            @close="showQuestionModal = false"
        >
            <div class="space-y-6">
                <!-- Question Text -->
                <div class="space-y-2 text-left">
                    <label
                        class="text-[10px] font-black uppercase text-gray-500 tracking-widest ml-1"
                        >Metric Description</label
                    >
                    <textarea
                        v-model="questionForm.question_text"
                        placeholder="e.g. How much did you enjoy the energy?"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 h-24 outline-none focus:border-brand-yellow transition-all font-bold placeholder:text-gray-700 leading-tight text-white resize-none"
                    ></textarea>
                </div>

                <!-- Type Selector -->
                <div class="space-y-3 text-left">
                    <label
                        class="text-[10px] font-black uppercase text-gray-500 tracking-widest ml-1"
                        >Response Type</label
                    >
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            v-for="type in ['rating', 'text']"
                            :key="type"
                            @click="questionForm.type = type"
                            class="py-3 rounded-2xl border text-[9px] font-black uppercase tracking-widest transition-all"
                            :class="[
                                questionForm.type === type
                                    ? 'bg-brand-yellow text-black border-brand-yellow shadow-lg shadow-brand-yellow/20'
                                    : 'bg-white/5 border-white/10 text-gray-500',
                            ]"
                        >
                            {{ type }}
                        </button>
                    </div>
                </div>

                <!-- Labels (Only for Rating) -->
                <div
                    v-if="questionForm.type === 'rating'"
                    class="grid grid-cols-2 gap-4"
                >
                    <div class="space-y-2 text-left">
                        <label
                            class="text-[10px] font-black uppercase text-gray-500 tracking-widest ml-1"
                            >Min. Label (1)</label
                        >
                        <input
                            v-model="questionForm.low_label"
                            type="text"
                            placeholder="e.g. Weak"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-yellow transition-all font-bold text-xs text-white placeholder:text-gray-700"
                        />
                    </div>
                    <div class="space-y-2 text-left">
                        <label
                            class="text-[10px] font-black uppercase text-gray-500 tracking-widest ml-1"
                            >Max. Label (5)</label
                        >
                        <input
                            v-model="questionForm.high_label"
                            type="text"
                            placeholder="e.g. Fire!"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-yellow transition-all font-bold text-xs text-white placeholder:text-gray-700"
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
                                    ? 'bg-brand-yellow text-black border-brand-yellow shadow-lg shadow-brand-yellow/20'
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
                        class="w-full bg-white text-black py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl active:scale-95 transition-all relative overflow-hidden"
                    >
                        <span>{{
                            editingIndex !== null
                                ? "Sync Changes"
                                : "Initialize Metric"
                        }}</span>
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
