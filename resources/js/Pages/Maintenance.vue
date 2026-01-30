<script setup>
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Coming Soon - MVote" />

    <div
        class="min-h-screen bg-brand-black flex flex-col items-center justify-center p-6 font-sans relative overflow-hidden"
    >
        <!-- Background Asset -->
        <div class="absolute inset-0 z-0">
            <div
                class="absolute inset-0 bg-brand-black/70 z-10 backdrop-blur-md"
            ></div>
            <img
                class="w-full h-full object-cover scale-110 blur-[2px]"
                alt="Background"
            />
        </div>

        <!-- Content -->
        <div class="relative z-10 text-center animate-maintenance-fade">
            <div
                class="inline-block px-4 py-1.5 rounded-full border border-brand-yellow/30 bg-brand-yellow/5 mb-8"
            >
                <span
                    class="text-brand-yellow text-[10px] font-black uppercase tracking-[0.3em] leading-none"
                    >System Calibration In Progress</span
                >
            </div>

            <h1
                class="text-6xl md:text-8xl font-black italic tracking-tighter text-white uppercase leading-none mb-4"
            >
                STAY <span class="text-brand-yellow">TUNED</span>
            </h1>
            <p
                class="text-gray-400 font-bold uppercase tracking-[0.4em] text-xs md:text-sm mb-12"
            >
                The Arena is preparing for the next mission.
            </p>

            <div class="flex flex-col items-center gap-6">
                <div class="flex gap-4">
                    <div
                        v-for="i in 3"
                        :key="i"
                        class="w-3 h-1 bg-brand-yellow/20 rounded-full overflow-hidden"
                    >
                        <div
                            class="h-full bg-brand-yellow animate-line-load"
                            :style="{ animationDelay: `${i * 0.2}s` }"
                        ></div>
                    </div>
                </div>

                <p
                    class="text-gray-600 text-[9px] font-black uppercase tracking-widest max-w-xs leading-relaxed"
                >
                    Our engineers are optimizing the stage energy. We expect to
                    be live shortly.
                </p>
            </div>
        </div>

        <!-- Bottom Branding -->
        <div class="absolute bottom-12 left-0 right-0 text-center z-10">
            <p
                class="text-white/20 font-black italic tracking-[0.2em] text-lg uppercase"
            >
                M<span class="text-brand-yellow/20">VOTE</span>
            </p>
        </div>
    </div>
</template>

<style scoped>
@keyframes line-load {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

.animate-line-load {
    animation: line-load 2s ease-in-out infinite;
}

@keyframes maint-fade {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-maintenance-fade {
    animation: maint-fade 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
