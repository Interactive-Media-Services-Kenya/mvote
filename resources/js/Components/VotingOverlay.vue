<script setup>
import { ref, computed } from "vue";
import { watch, onUnmounted } from "vue";
import { votingQuestions } from "../constants";

const props = defineProps({
    show: Boolean,
    artist: Object,
});

const emit = defineEmits(["close", "submit"]);

const step = ref("QUESTIONS"); // QUESTIONS | COMMENT | SUCCESS | EXPIRED
const currentQuestionIndex = ref(0);
const answers = ref({});
const comment = ref("");
const isSubmitting = ref(false);

// Timer Logic
const secondsRemaining = ref(props.artist?.votingSecondsRemaining || 10);
const isPaused = ref(props.artist?.isVotingPaused || false);
let timerInterval = null;

const startTimer = () => {
    if (timerInterval) clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        if (!isPaused.value && secondsRemaining.value > 0) {
            secondsRemaining.value--;
        } else if (secondsRemaining.value === 0 && step.value !== "SUCCESS") {
            step.value = "EXPIRED";
            clearInterval(timerInterval);
        }
    }, 1000);
};

const formatTime = (seconds) => {
    const min = Math.floor(seconds / 60);
    const sec = seconds % 60;
    return `${min}:${sec.toString().padStart(2, "0")}`;
};

watch(
    () => props.show,
    (newVal) => {
        if (newVal) {
            startTimer();
        } else {
            if (timerInterval) clearInterval(timerInterval);
        }
    },
);

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
});

const currentQuestion = computed(
    () => votingQuestions[currentQuestionIndex.value],
);

const handleRating = (rating) => {
    if (isPaused.value) return; // Prevent voting while paused
    answers.value[currentQuestion.value.id] = rating;

    // Auto advance
    setTimeout(() => {
        if (currentQuestionIndex.value < votingQuestions.length - 1) {
            currentQuestionIndex.value++;
        } else {
            step.value = "COMMENT";
        }
    }, 300);
};

const submitVote = () => {
    isSubmitting.value = true;
    // Simulate API call
    setTimeout(() => {
        isSubmitting.value = false;
        step.value = "SUCCESS";
        emit("submit", {
            ratings: answers.value,
            comment: comment.value,
        });
    }, 1500);
};

const close = () => {
    emit("close");
    // Reset state for next time
    setTimeout(() => {
        step.value = "QUESTIONS";
        currentQuestionIndex.value = 0;
        answers.value = {};
        comment.value = "";
    }, 1000);
};

const progressWidth = computed(() => {
    if (step.value === "COMMENT") return "100%";
    if (step.value === "SUCCESS") return "100%";
    return `${((currentQuestionIndex.value + 1) / (votingQuestions.length + 1)) * 100}%`;
});
</script>

<template>
    <Teleport to="body">
        <Transition name="overlay">
            <div
                v-if="show"
                class="fixed inset-0 z-100 flex items-center justify-center p-6"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/90 backdrop-blur-2xl"
                ></div>

                <!-- Content -->
                <div
                    class="relative w-full max-w-sm glass-card rounded-[2.5rem] p-8 overflow-hidden shadow-[0_0_80px_rgba(0,0,0,0.8)] border border-white/20 animate-fade-up"
                >
                    <!-- Progress Bar -->
                    <div
                        class="absolute top-0 left-0 h-1 bg-brand-yellow/20 w-full"
                    >
                        <div
                            class="h-full bg-brand-yellow transition-all duration-500 shadow-[0_0_10px_rgba(255,107,0,0.8)]"
                            :style="{ width: progressWidth }"
                        ></div>
                    </div>

                    <!-- Header with Timer -->
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex flex-col">
                            <h3
                                class="text-xs font-black text-gray-500 uppercase tracking-widest"
                            >
                                {{ artist.name }}
                            </h3>
                            <div
                                v-if="step !== 'SUCCESS' && step !== 'EXPIRED'"
                                :class="[
                                    'text-2xl font-black italic tracking-tighter flex items-center gap-2',
                                    isPaused
                                        ? 'text-yellow-500'
                                        : secondsRemaining < 30
                                          ? 'text-red-500 animate-pulse'
                                          : 'text-white',
                                ]"
                            >
                                <svg
                                    v-if="isPaused"
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-5 w-5"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                                <span>{{
                                    isPaused
                                        ? "PAUSED"
                                        : formatTime(secondsRemaining)
                                }}</span>
                            </div>
                        </div>
                        <button
                            @click="close"
                            class="text-gray-500 hover:text-white transition-colors"
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
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>

                    <div class="text-center">
                        <Transition name="fade-slide" mode="out-in">
                            <!-- STEP: QUESTIONS -->
                            <div
                                v-if="step === 'QUESTIONS'"
                                :key="currentQuestion.id"
                                class="space-y-8"
                            >
                                <header>
                                    <div
                                        class="flex items-center justify-center gap-2 mb-2"
                                    >
                                        <span
                                            class="text-brand-yellow text-[10px] font-black uppercase tracking-widest"
                                            >Question</span
                                        >
                                        <span
                                            class="w-1 h-1 rounded-full bg-gray-600"
                                        ></span>
                                        <span
                                            class="text-gray-500 text-[10px] font-bold"
                                            >{{ currentQuestionIndex + 1 }}/{{
                                                votingQuestions.length
                                            }}</span
                                        >
                                    </div>
                                    <h2
                                        class="text-xl font-black italic tracking-tight text-white leading-tight min-h-12"
                                    >
                                        {{ currentQuestion.text }}
                                    </h2>
                                </header>

                                <div v-if="isPaused" class="py-10">
                                    <div
                                        class="bg-yellow-500/10 border border-yellow-500/20 p-6 rounded-3xl"
                                    >
                                        <p
                                            class="text-yellow-500 font-bold uppercase tracking-widest text-xs mb-1"
                                        >
                                            Voting Paused
                                        </p>
                                        <p class="text-gray-400 text-[10px]">
                                            The admin has temporarily paused
                                            voting. Please hang tight!
                                        </p>
                                    </div>
                                </div>

                                <div v-else class="space-y-6">
                                    <div class="flex justify-between gap-2">
                                        <button
                                            v-for="i in 5"
                                            :key="i"
                                            @click="handleRating(i)"
                                            :class="[
                                                'w-12 h-12 rounded-2xl flex items-center justify-center text-xl font-black transition-all active:scale-90',
                                                answers[currentQuestion.id] ===
                                                i
                                                    ? 'bg-brand-yellow text-black scale-110 shadow-[0_0_15px_rgba(255,107,0,0.5)]'
                                                    : 'bg-brand-gray text-gray-400 border border-white/5',
                                            ]"
                                        >
                                            {{ i }}
                                        </button>
                                    </div>

                                    <div class="flex justify-between px-1">
                                        <span
                                            class="text-[10px] font-bold uppercase text-gray-500 tracking-wider"
                                            >{{
                                                currentQuestion.lowLabel
                                            }}</span
                                        >
                                        <span
                                            class="text-[10px] font-bold uppercase text-gray-500 tracking-wider text-right"
                                            >{{
                                                currentQuestion.highLabel
                                            }}</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- STEP: COMMENT -->
                            <div
                                v-else-if="step === 'COMMENT'"
                                key="comment"
                                class="space-y-6"
                            >
                                <header>
                                    <p
                                        class="text-brand-yellow text-xs font-black uppercase tracking-widest mb-2"
                                    >
                                        Final Step
                                    </p>
                                    <h2
                                        class="text-2xl font-black italic tracking-tighter text-white"
                                    >
                                        Any words for {{ artist.name }}?
                                    </h2>
                                </header>

                                <textarea
                                    v-model="comment"
                                    placeholder="Tell them why they rocks! (Optional)"
                                    class="w-full bg-brand-gray border border-white/10 rounded-2xl p-4 min-h-30 outline-none focus:border-brand-yellow transition-all font-medium text-white resize-none"
                                    :disabled="isSubmitting || isPaused"
                                ></textarea>

                                <div
                                    v-if="isPaused"
                                    class="bg-yellow-500/10 border border-yellow-500/20 p-4 rounded-2xl text-center"
                                >
                                    <p
                                        class="text-yellow-500 font-bold text-[10px] uppercase tracking-widest"
                                    >
                                        Submission Paused by Admin
                                    </p>
                                </div>

                                <div v-else class="space-y-3">
                                    <button
                                        @click="submitVote"
                                        :disabled="isSubmitting"
                                        class="w-full bg-brand-yellow text-black font-black py-4 rounded-2xl uppercase tracking-tighter text-lg animate-hype-pulse shadow-[0_0_20px_rgba(255,107,0,0.3)]"
                                    >
                                        {{
                                            isSubmitting
                                                ? "Summitting..."
                                                : "Submit Vote"
                                        }}
                                    </button>
                                    <button
                                        @click="
                                            step = 'QUESTIONS';
                                            currentQuestionIndex =
                                                votingQuestions.length - 1;
                                        "
                                        class="w-full text-xs font-bold text-gray-500 uppercase tracking-widest"
                                    >
                                        Review Ratings
                                    </button>
                                </div>
                            </div>

                            <!-- SUCCESS -->
                            <div
                                v-else-if="step === 'SUCCESS'"
                                key="success"
                                class="py-10 space-y-4"
                            >
                                <div
                                    class="w-20 h-20 bg-green-500 rounded-full mx-auto flex items-center justify-center text-black mb-6"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-10 w-10"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <h2
                                    class="text-3xl font-black italic tracking-tighter"
                                >
                                    Energy Sent!
                                </h2>
                                <p class="text-gray-400 font-medium mb-6">
                                    Your ratings for {{ artist.name }} have been
                                    cast.
                                </p>
                                <button
                                    @click="close"
                                    class="w-full bg-white text-black font-black py-4 rounded-2xl uppercase tracking-tighter"
                                >
                                    Dismiss
                                </button>
                            </div>

                            <!-- EXPIRED -->
                            <div
                                v-else-if="step === 'EXPIRED'"
                                key="expired"
                                class="py-10 space-y-6"
                            >
                                <div
                                    class="w-20 h-20 bg-red-600 rounded-full mx-auto flex items-center justify-center text-white mb-6"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-10 w-10"
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
                                </div>
                                <h2
                                    class="text-3xl font-black italic tracking-tighter text-red-500"
                                >
                                    Time's Up!
                                </h2>
                                <p class="text-gray-400 font-medium">
                                    Voting for {{ artist.name }} has closed.
                                    Keep the hype going for the next act!
                                </p>
                                <button
                                    @click="close"
                                    class="w-full bg-white text-black font-black py-4 rounded-2xl uppercase tracking-tighter"
                                >
                                    Back to Lineup
                                </button>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.overlay-enter-active,
.overlay-leave-active {
    transition: opacity 0.4s ease;
}
.overlay-enter-from,
.overlay-leave-to {
    opacity: 0;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s ease;
}
.fade-slide-enter-from {
    opacity: 0;
    transform: translateX(20px);
}
.fade-slide-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}
</style>
