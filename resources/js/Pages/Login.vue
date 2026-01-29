<script setup>
import { ref, reactive, computed } from "vue";
import { Head, router } from "@inertiajs/vue3";

const step = ref("IDENTIFY");

const form = reactive({
    phone: "",
    nickname: "",
    otp: ["", "", "", "", ""],
});

const errors = reactive({
    phone: "",
    otp: "",
});

const isProcessing = ref(false);

const isValidKenyanPhone = (phone) => {
    const regex = /^(?:254|\+254|0)?(7|1)(?:(?:[0-9][0-9])|(?:0[0-8]))[0-9]{6}$/;
    return regex.test(phone.replace(/\s+/g, ""));
};

const handleIdentify = () => {
    errors.phone = "";
    if (!form.phone) {
        errors.phone = "Phone number is required";
        return;
    }
    
    if (!isValidKenyanPhone(form.phone)) {
        errors.phone = "Please enter a valid Kenyan phone number";
        return;
    }

    isProcessing.value = true;

    setTimeout(() => {
        isProcessing.value = false;
        step.value = "OTP";
    }, 1000);
};

const handleOtpInput = (index, event) => {
    errors.otp = "";
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

const isOtpComplete = computed(() => form.otp.every((v) => v !== ""));

const handleVerify = () => {
    errors.otp = "";
    if (!isOtpComplete.value) return;
    
    isProcessing.value = true;

    // Simulate login + OTP validation (code: 12345)
    setTimeout(() => {
        const enteredOtp = form.otp.join("");
        if (enteredOtp !== "12345") {
            isProcessing.value = false;
            errors.otp = "Invalid OTP. Use 12345 for demo.";
            return;
        }

        isProcessing.value = false;
        router.visit("/lineup");
    }, 1500);
};
</script>

<template>
    <Head title="Login - MVote" />

    <div
        class="min-h-screen bg-brand-black flex flex-col items-center justify-center p-6 font-sans"
    >
        <!-- Logo/Header Area -->
        <div class="mb-12 text-center animate-fade-up">
            <h1
                class="text-5xl font-black italic tracking-tighter text-white uppercase"
            >
                M<span class="text-brand-orange">VOTE</span>
            </h1>
            <p class="text-gray-400 mt-2 font-medium">
                Your Voice, The Stage Energy.
            </p>
        </div>

        <!-- Flow Container -->
        <div class="w-full max-w-sm">
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
                                class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 transition-colors group-focus-within:text-brand-orange"
                                :class="{ 'text-red-500': errors.phone }"
                            >
                                Phone Number
                            </label>
                            <input
                                v-model="form.phone"
                                @input="errors.phone = ''"
                                type="tel"
                                placeholder="07XX XXX XXX"
                                class="w-full bg-brand-gray border-2 border-transparent focus:border-brand-orange text-white px-5 py-4 rounded-2xl outline-none transition-all text-lg font-bold"
                                :class="{ 'border-red-500/50 bg-red-500/5': errors.phone }"
                            />
                            <p v-if="errors.phone" class="text-red-500 text-[10px] font-bold mt-2 uppercase tracking-tight animate-shake">
                                {{ errors.phone }}
                            </p>
                        </div>

                        <div class="group">
                            <label
                                class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 transition-colors group-focus-within:text-brand-orange"
                            >
                                Nickname
                                <span class="text-[10px] opacity-50 font-normal"
                                    >(Optional)</span
                                >
                            </label>
                            <input
                                v-model="form.nickname"
                                type="text"
                                placeholder=""
                                class="w-full bg-brand-gray border-2 border-transparent focus:border-brand-orange text-white px-5 py-4 rounded-2xl outline-none transition-all font-medium"
                            />
                        </div>
                    </div>

                    <button
                        @click="handleIdentify"
                        :disabled="!form.phone || isProcessing"
                        class="w-full bg-brand-orange hover:bg-orange-500 text-black font-black py-4 rounded-2xl transition-all active:scale-95 disabled:opacity-50 disabled:grayscale uppercase tracking-tighter text-xl shadow-[0_0_20px_rgba(255,107,0,0.3)] animate-hype-pulse"
                    >
                        {{ isProcessing ? "Sending..." : "Get OTP" }}
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
                            class="w-full aspect-square bg-brand-gray border-2 border-transparent focus:border-brand-orange text-center text-2xl font-black rounded-xl outline-none transition-all"
                            :class="{ 'border-red-500/50 bg-red-500/5': errors.otp }"
                            @input="handleOtpInput(i, $event)"
                        />
                    </div>
                    <p v-if="errors.otp" class="text-red-500 text-center text-[10px] font-bold uppercase tracking-tight animate-shake">
                        {{ errors.otp }}
                    </p>

                    <div class="space-y-4">
                        <button
                            @click="handleVerify"
                            :disabled="!isOtpComplete || isProcessing"
                            class="w-full bg-white hover:bg-gray-200 text-black font-black py-4 rounded-2xl transition-all active:scale-95 disabled:opacity-50 uppercase tracking-tighter text-xl"
                        >
                            {{ isProcessing ? "Verifying..." : "Enter Arena" }}
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
</style>
