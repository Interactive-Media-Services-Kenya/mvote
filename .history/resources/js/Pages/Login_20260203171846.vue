<script setup>
import { ref, computed, onMounted } from "vue";
import { Head, useForm } from "@inertiajs/vue3";

const step = ref("IDENTIFY");
const showCookieConsent = ref(false);

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

    form.post("/login/identify", {
        onSuccess: () => {
            step.value = "OTP";
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
            form.otp = ["", "", "", "", ""];
        },
    });
};

onMounted(() => {
    if (!localStorage.getItem("cookie_consent")) {
        showCookieConsent.value = true;
    }
});
</script>

<template>
    <Head title="Login - MVote" />

    <div
        class="min-h-screen bg-brand-black flex flex-col items-center justify-center p-6 font-sans relative overflow-hidden"
    >
        <!-- Static Background -->
        <div class="absolute inset-0 z-0">
            <div
                class="absolute inset-0 bg-brand-black/60 z-10 backdrop-blur-[2px]"
            ></div>
            <img
                :src="'/assets/carousel_1.png'"
                class="w-full h-full object-cover animate-ken-burns"
                alt="Background"
            />
        </div>

        <!-- Logo/Header Area -->
<div class="mb-8 text-center animate-fade-up relative z-10 flex flex-col items-center">
    <img 
        :src="'/assets/star-yako-logo.png'" 
        alt="Star Yako Logo" 
        class="h-32 w-auto mx-auto object-contain drop-shadow-2xl"
    />
    
    <p class="text-gray-200 -mt-6 font-black uppercase tracking-[0.3em] text-[10px] drop-shadow-lg leading-none">
        Your Voice, The Stage Energy.
    </p>
</div>

        <!-- Flow Container -->
        <div class="w-full max-w-sm relative z-10">
            <Transition name="fade-slide" mode="out-in">
                <!-- STEP 1: IDENTIFY -->
                <div
                    v-if="step === 'IDENTIFY'"
                    key="identify"
                    class="space-y-8 animate-fade-up"
                >
                    <div class="space-y-4">
                        <div class="group">
                            <label
                                class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 transition-colors group-focus-within:text-brand-yellow"
                                :class="{ 'text-red-500': form.errors.phone }"
                            >
                                Phone Number
                            </label>
                            <input
                                v-model="form.phone"
                                @input="form.clearErrors('phone')"
                                type="tel"
                                placeholder="07XX XXX XXX"
                                class="w-full bg-brand-gray border-2 border-transparent focus:border-brand-yellow text-white px-5 py-4 rounded-2xl outline-none transition-all text-lg font-bold"
                                :class="{
                                    'border-red-500/50 bg-red-500/5':
                                        form.errors.phone,
                                }"
                            />
                            <p
                                v-if="form.errors.phone"
                                class="text-red-500 text-[10px] font-bold mt-2 uppercase tracking-tight animate-shake"
                            >
                                {{ form.errors.phone }}
                            </p>
                        </div>

                        <div class="group">
                            <label
                                class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 transition-colors group-focus-within:text-brand-yellow"
                            >
                                Nickname
                                <span class="text-[10px] opacity-50 font-normal"
                                    >(Optional)</span
                                >
                            </label>
                            <input
                                v-model="form.nick_name"
                                type="text"
                                placeholder=""
                                class="w-full bg-brand-gray border-2 border-transparent focus:border-brand-yellow text-white px-5 py-4 rounded-2xl outline-none transition-all font-medium"
                            />
                        </div>
                    </div>

                    <button
                        @click="handleIdentify"
                        :disabled="!form.phone || form.processing"
                        class="w-full bg-brand-yellow hover:bg-yellow-500 text-black font-black py-4 rounded-2xl transition-all active:scale-95 disabled:opacity-50 disabled:grayscale uppercase tracking-tighter text-xl shadow-[0_0_20px_rgba(255,107,0,0.3)] animate-hype-pulse"
                    >
                        {{ form.processing ? "Sending..." : "Get OTP" }}
                    </button>

                    <p class="text-center text-xs text-gray-600 px-4">
                        By continuing, you agree to experience the hype and vote
                        fairly.
                    </p>
                </div>

                <!-- STEP 2: OTP -->
                <div v-else key="otp" class="space-y-8 animate-fade-up">
                    <div class="text-center">
                        <h2 class="text-xl font-bold">Check your phone</h2>
                        <p class="text-gray-400 text-sm mt-1">
                            Sent code to
                            <span class="text-white">{{ form.phone }}</span>
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
                            class="w-full aspect-square bg-brand-gray border-2 border-transparent focus:border-brand-yellow text-center text-2xl font-black rounded-xl outline-none transition-all"
                            :class="{
                                'border-red-500/50 bg-red-500/5':
                                    form.errors.otp,
                            }"
                            @input="handleOtpInput(i, $event)"
                        />
                    </div>
                    <p
                        v-if="form.errors.otp"
                        class="text-red-500 text-center text-[10px] font-bold uppercase tracking-tight animate-shake"
                    >
                        {{ form.errors.otp }}
                    </p>

                    <div class="space-y-4">
                        <button
                            @click="handleVerify"
                            :disabled="!isOtpComplete || form.processing"
                            class="w-full bg-white hover:bg-gray-200 text-black font-black py-4 rounded-2xl transition-all active:scale-95 disabled:opacity-50 uppercase tracking-tighter text-xl"
                        >
                            {{
                                form.processing ? "Verifying..." : "Enter Arena"
                            }}
                        </button>

                        <button
                            @click="step = 'IDENTIFY'"
                            class="w-full text-gray-500 font-bold py-2 hover:text-white transition-colors uppercase text-xs tracking-widest"
                        >
                            Back to info
                        </button>
                    </div>
                </div>
            </Transition>
            <!-- Cookie Consent Prompt -->
            <Transition name="fade-up">
                <div
                    v-if="showCookieConsent"
                    class="fixed bottom-6 left-6 right-6 z-60 glass-card rounded-2xl p-6 flex flex-col md:flex-row items-center justify-between gap-6 border-brand-yellow/20 shadow-[0_-10px_50px_rgba(0,0,0,0.5)] animate-fade-up max-w-lg mx-auto md:max-w-none"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-brand-yellow/10 flex items-center justify-center text-brand-yellow shrink-0"
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
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-white">
                                We Value Your Hype
                            </h3>
                            <p class="text-gray-400 text-xs">
                                We use cookies to enhance your arena experience
                                and analyze site traffic.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 w-full md:w-auto">
                        <button
                            @click="showCookieConsent = false"
                            class="flex-1 md:flex-none px-6 py-3 text-xs font-bold text-gray-400 hover:text-white transition-colors"
                        >
                            Close
                        </button>
                        <button
                            @click="acceptCookies"
                            class="flex-1 md:flex-none px-8 py-3 bg-brand-yellow text-black font-black text-xs uppercase tracking-widest rounded-xl hover:bg-white transition-all transform active:scale-95 shadow-[0_4px_15px_rgba(255,205,0,0.3)]"
                        >
                            Accept
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
    </div>
</template>

<style scoped>
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

@keyframes ken-burns {
    0% {
        transform: scale(1) translate(0, 0);
    }
    100% {
        transform: scale(1.1) translate(-1%, -1%);
    }
}

.animate-ken-burns {
    animation: ken-burns 20s linear infinite alternate;
}
</style>
