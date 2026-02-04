<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";

const step = ref("AGE_GATE");
const showCookieConsent = ref(false);
const isNewUser = ref(false);
const page = usePage();

const resendCooldown = ref(0);
let cooldownTimer = null;

const form = useForm({
    phone: "",
    nick_name: "",
    otp: ["", "", "", "", ""],
});

const isOtpComplete = computed(() => form.otp.every((v) => v !== ""));

const isValidKenyanPhone = (phone) => {
    const regex =
        /^(?:254|\+254|0)?(7|1)(?:(?:[0-9][0-9])|(?:0[0-8]))[0-9]{6}$/;
    return regex.test(phone.replace(/\s+/g, ""));
};

const acceptCookies = () => {
    localStorage.setItem("cookie_consent", "accepted");
    showCookieConsent.value = false;
};

const handleIdentify = () => {
    form.clearErrors("phone");
    if (!form.phone) {
        form.setError("phone", "Phone number is required");
        return;
    }

    if (!isValidKenyanPhone(form.phone)) {
        form.setError("phone", "Please enter a valid Kenyan phone number");
        return;
    }

    if (resendCooldown.value > 0) return;

    form.post("/login/identify", {
        onSuccess: () => {
            step.value = "OTP";
            startResendCooldown();
            // Check flash data from the server response
            if (page.props.flash?.is_new_user) {
                isNewUser.value = true;
            } else {
                isNewUser.value = false;
            }
        },
    });
};

const handleOtpInput = (index, event) => {
    form.clearErrors("otp");
    const val = event.data;
    if (val && !isNaN(val)) {
        form.otp[index] = val;
        if (index < 4) {
            const next = event.target.nextElementSibling;
            if (next) next.focus();
        }
    } else if (event.inputType === "deleteContentBackward") {
        form.otp[index] = "";
        if (index > 0) {
            const prev = event.target.previousElementSibling;
            if (prev) prev.focus();
        }
    }
};

const handleVerify = () => {
    form.clearErrors("otp");
    if (!isOtpComplete.value) return;

    form.post("/login/verify", {
        onError: () => {
            // Keep OTP values for easier correction
        },
    });
};

const startResendCooldown = () => {
    resendCooldown.value = 60;
    cooldownTimer = setInterval(() => {
        resendCooldown.value--;
        if (resendCooldown.value <= 0) {
            clearInterval(cooldownTimer);
        }
    }, 1000);
};

const handleResendOtp = () => {
    if (resendCooldown.value > 0) return;
    handleIdentify();
};

const images = [
    "/assets/carousel_1.avif",
    "/assets/carousel_2.avif",
    "/assets/carousel_3.avif",
];
const currentImageIndex = ref(0);
let timer = null;

onMounted(() => {
    timer = setInterval(() => {
        currentImageIndex.value = (currentImageIndex.value + 1) % images.length;
    }, 5000);
    if (!localStorage.getItem("cookie_consent")) showCookieConsent.value = true;
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
    if (cooldownTimer) clearInterval(cooldownTimer);
});
</script>

<template>
    <Head title="Login - MVote" />

    <div
        class="min-h-screen bg-brand-black flex flex-col items-center justify-center p-6 font-sans relative overflow-hidden"
    >
        <div class="absolute inset-0 z-0">
            <div
                class="absolute inset-0 bg-black/70 z-10 backdrop-blur-xs"
            ></div>
            <Transition name="fade-overlay">
                <img
                    :key="images[currentImageIndex]"
                    :src="images[currentImageIndex]"
                    class="absolute inset-0 w-full h-full object-cover animate-ken-burns"
                />
            </Transition>
        </div>

        <div class="mb-10 text-center relative z-10 animate-fade-up">
            <div class="flex flex-col items-center">
                <!-- <img
                    :src="'/assets/star-yako-logo.png'"
                    alt="Star Yako Logo"
                    class="h-14 w-auto mb-2 drop-shadow-glow"
                /> -->

                <h2
                    class="text-white text-3xl font-black tracking-tighter uppercase italic leading-none mb-1"
                >
                    Artist Rating Platform
                </h2>
                <p
                    class="text-white/40 text-[9px] font-black uppercase tracking-[0.2em] mb-6 italic"
                >
                    Join the crowd and cast your vote in seconds.
                </p>

                <div
                    class="flex gap-4 text-[10px] font-bold text-brand-yellow/80 uppercase tracking-widest"
                >
                    <span class="flex items-center gap-1.5">
                        <svg
                            class="w-3 h-3"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z"
                            />
                        </svg>
                        Secure Verification
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg
                            class="w-3 h-3"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                        >
                            <path
                                d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"
                            />
                        </svg>
                        Mobile-First
                    </span>
                </div>
            </div>
        </div>

        <div class="w-full max-w-sm relative z-10">
            <div
                class="h-px w-full bg-linear-to-r from-transparent via-white/20 to-transparent mb-10"
            ></div>

            <Transition name="fade-slide" mode="out-in">
                <div
                    v-if="step === 'AGE_GATE'"
                    key="age-gate"
                    class="space-y-8 animate-fade-up"
                >
                    <div class="text-center">
                        <div
                            class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-brand-yellow/10 border border-brand-yellow/20 mb-6"
                        >
                            <span
                                class="text-2xl font-black text-brand-yellow italic"
                                >18+</span
                            >
                        </div>
                        <h2
                            class="text-3xl font-black text-white italic tracking-tighter uppercase leading-none mb-4"
                        >
                            Age Verification
                        </h2>
                        <p
                            class="text-white/40 text-[10px] font-bold uppercase tracking-widest leading-relaxed"
                        >
                            This platform contains content intended for adults.
                            Please confirm you are of legal age to continue.
                        </p>
                    </div>

                    <button
                        @click="step = 'IDENTIFY'"
                        class="w-full bg-linear-to-r from-brand-yellow to-yellow-600 text-black font-black py-5 rounded-full transition-all active:scale-[0.98] shadow-xl group overflow-hidden"
                    >
                        <span class="uppercase tracking-tighter text-lg"
                            >I am 18 or older</span
                        >
                    </button>

                    <p
                        class="text-center text-[9px] text-gray-600 uppercase tracking-[0.2em]"
                    >
                        By proceeding, you agree to our Terms of Service &
                        Privacy Policy
                    </p>
                </div>

                <div
                    v-else-if="step === 'IDENTIFY'"
                    key="identify"
                    class="space-y-6"
                >
                    <div class="space-y-2">
                        <label
                            class="block text-[10px] font-black text-gray-300 uppercase tracking-[0.3em] ml-1"
                        >
                            Phone Number
                        </label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            placeholder="Enter your phone number"
                            class="w-full bg-black/40 border border-white/30 text-white px-6 py-4 rounded-full outline-none focus:border-brand-yellow/50 focus:ring-4 focus:ring-brand-yellow/5 transition-all text-base placeholder:text-gray-600 shadow-inner"
                        />
                        <p
                            v-if="form.errors.phone"
                            class="text-red-500 text-[10px] font-bold mt-2 ml-4 animate-shake uppercase"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <button
                        @click="handleIdentify"
                        :disabled="form.processing"
                        class="w-full bg-linear-to-r from-brand-yellow to-yellow-600 text-black font-black py-4 rounded-full transition-all active:scale-[0.98] flex items-center justify-center gap-2 group overflow-hidden shadow-xl"
                    >
                        <span class="uppercase tracking-tighter text-lg">
                            {{ form.processing ? "Processing..." : "Continue" }}
                        </span>
                        <svg
                            v-if="!form.processing"
                            class="w-5 h-5 transition-transform group-hover:translate-x-1"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="3"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </button>
                </div>

                <div v-else key="otp" class="space-y-8 animate-fade-up">
                    <div class="text-center">
                        <h2
                            class="text-3xl font-black text-grey-300 italic tracking-tighter uppercase leading-none"
                        >
                            Verify Identity
                        </h2>
                        <p
                            class="text-white/40 text-[10px] font-bold uppercase tracking-widest mt-2"
                        >
                            Code sent to
                            <span class="text-brand-yellow">{{
                                form.phone
                            }}</span>
                        </p>
                    </div>

                    <div class="flex justify-between gap-2">
                        <input
                            v-for="(digit, i) in 5"
                            :key="i"
                            v-model="form.otp[i]"
                            type="text"
                            maxlength="1"
                            inputmode="numeric"
                            class="w-full aspect-square bg-black/40 border border-white/30 focus:border-brand-yellow text-center text-2xl font-black rounded-2xl outline-none transition-all text-white"
                            :class="{
                                'border-red-500/50 bg-red-500/5':
                                    form.errors.otp,
                            }"
                            @input="handleOtpInput(i, $event)"
                        />
                    </div>

                    <p
                        v-if="form.errors.otp"
                        class="text-red-500 text-[10px] font-black mt-2 text-center animate-shake uppercase tracking-widest"
                    >
                        {{ form.errors.otp }}
                    </p>

                    <transition name="fade-up">
                        <div v-if="isNewUser" class="group animate-fade-up">
                            <label
                                class="block text-[10px] font-black text-gray-500 uppercase tracking-[0.3em] mb-2 transition-colors group-focus-within:text-brand-yellow"
                                :class="{
                                    'text-red-500': form.errors.nick_name,
                                }"
                            >
                                What should we call you?
                            </label>
                            <input
                                v-model="form.nick_name"
                                type="text"
                                placeholder="Enter your nickname"
                                class="w-full bg-brand-gray border-2 border-transparent focus:border-brand-yellow text-white px-5 py-4 rounded-2xl outline-none transition-all font-medium"
                                :class="{
                                    'border-red-500/50 bg-red-500/5':
                                        form.errors.nick_name,
                                }"
                            />
                            <p
                                v-if="form.errors.nick_name"
                                class="text-red-500 text-[10px] font-black mt-2 uppercase animate-shake tracking-widest"
                            >
                                {{ form.errors.nick_name }}
                            </p>
                        </div>
                    </transition>

                    <div class="space-y-4">
                        <button
                            @click="handleVerify"
                            :disabled="!isOtpComplete || form.processing"
                            class="w-full bg-white text-black font-black py-4 rounded-full transition-all active:scale-95 uppercase tracking-widest text-sm shadow-lg mb-4"
                        >
                            Enter Arena
                        </button>

                        <div class="flex flex-col items-center gap-4">
                            <button
                                @click="handleResendOtp"
                                :disabled="
                                    resendCooldown > 0 || form.processing
                                "
                                class="text-[10px] font-black uppercase tracking-widest transition-colors"
                                :class="
                                    resendCooldown > 0
                                        ? 'text-gray-600'
                                        : 'text-brand-yellow hover:text-white'
                                "
                            >
                                {{
                                    resendCooldown > 0
                                        ? `Resend Code in ${resendCooldown}s`
                                        : "Resend Code"
                                }}
                            </button>

                            <button
                                @click="step = 'IDENTIFY'"
                                class="text-gray-500 font-bold py-2 hover:text-white transition-colors uppercase text-[10px] tracking-widest border-t border-white/5 w-full pt-4"
                            >
                                Change Phone Number
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
    </div>
</template>

<style scoped>
.drop-shadow-glow {
    filter: drop-shadow(0 0 15px rgba(255, 107, 0, 0.4));
}

/* Page Step Transitions */
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.fade-slide-enter-from {
    opacity: 0;
    transform: translateY(10px);
}
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* Carousel Cross-fade */
.fade-overlay-enter-active,
.fade-overlay-leave-active {
    transition: opacity 2s ease-in-out;
}
.fade-overlay-enter-from,
.fade-overlay-leave-to {
    opacity: 0;
}

@keyframes ken-burns {
    0% {
        transform: scale(1);
    }
    100% {
        transform: scale(1.15);
    }
}
.animate-ken-burns {
    animation: ken-burns 30s linear infinite alternate;
}

@keyframes shake {
    0%,
    100% {
        transform: translateX(0);
    }
    25% {
        transform: translateX(-4px);
    }
    75% {
        transform: translateX(4px);
    }
}
.animate-shake {
    animation: shake 0.2s ease-in-out 0s 2;
}
</style>
