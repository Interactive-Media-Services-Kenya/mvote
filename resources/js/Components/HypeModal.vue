<script setup>
import { onUnmounted, watch } from "vue";

const props = defineProps({
    show: Boolean,
    title: String,
    subtitle: String,
    maxWidth: {
        type: String,
        default: "max-w-sm",
    },
});

const emit = defineEmits(["close"]);

const close = () => {
    emit("close");
};

// Prevent scrolling when modal is open
watch(
    () => props.show,
    (newVal) => {
        if (newVal) {
            document.body.style.overflow = "hidden";
        } else {
            document.body.style.overflow = "";
        }
    },
);

onUnmounted(() => {
    document.body.style.overflow = "";
});
</script>

<template>
    <Teleport to="body">
        <Transition name="overlay">
            <div
                v-if="show"
                class="fixed inset-0 z-100 flex items-center justify-center p-2"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/90 backdrop-blur-2xl"
                    @click="close"
                ></div>

                <!-- Content -->
                <div
                    :class="[
                        'relative w-full glass-card rounded-3xl p-6 overflow-hidden shadow-[0_0_80px_rgba(0,0,0,0.8)] border border-white/20 animate-fade-up flex flex-col',
                        maxWidth,
                    ]"
                >
                    <!-- Close Button -->
                    <button
                        @click="close"
                        class="absolute top-6 right-6 text-gray-500 hover:text-white transition-colors z-20"
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

                    <!-- Header -->
                    <div
                        v-if="title || subtitle"
                        class="mb-6 relative z-10 text-center sm:text-left"
                    >
                        <h3
                            v-if="subtitle"
                            class="text-[10px] font-black text-brand-yellow uppercase tracking-[0.2em] mb-1"
                        >
                            {{ subtitle }}
                        </h3>
                        <h2
                            v-if="title"
                            class="text-2xl font-black italic tracking-tighter text-white leading-tight"
                        >
                            {{ title }}
                        </h2>
                    </div>

                    <!-- Slot Content -->
                    <div class="relative z-10 flex-1">
                        <slot />
                    </div>

                    <!-- Optional Footer -->
                    <div
                        v-if="$slots.footer"
                        class="mt-8 pt-6 border-t border-white/5 relative z-10"
                    >
                        <slot name="footer" />
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

.animate-fade-up {
    animation: fadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>
