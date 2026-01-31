<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { usePage, router } from "@inertiajs/vue3";

const page = usePage();
const user = computed(() => page.props.auth?.user);

const isOpen = ref(false);
const dropdownRef = ref(null);
const activeVoters = ref([]);

const updateVoterCount = () => {
    // If the page has an activeFans prop (AudienceDisplay), we can try to update it
    // But better to just emit or rely on a global store if available.
    // For now, let's just log and maintain state here.
};

const toggleMenu = () => {
    isOpen.value = !isOpen.value;
};

const handleLogout = () => {
    router.post("/logout");
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener("mousedown", handleClickOutside);

    if (window.Echo) {
        window.Echo.join("voters")
            .here((users) => {
                activeVoters.value = users;
            })
            .joining((user) => {
                activeVoters.value.push(user);
            })
            .leaving((user) => {
                activeVoters.value = activeVoters.value.filter(
                    (u) => u.id !== user.id,
                );
            });
    }
});

onUnmounted(() => {
    document.removeEventListener("mousedown", handleClickOutside);
    if (window.Echo) {
        window.Echo.leave("voters");
    }
});

const avatarUrl = computed(() => {
    const seed = user.value?.nick_name || user.value?.phone || "guest";
    return `https://api.dicebear.com/7.x/pixel-art/svg?seed=${seed}&backgroundColor=b6e3f4,c0aede,d1d4f9`;
});
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <button
            @click.stop="toggleMenu"
            class="w-10 h-10 rounded-full bg-brand-yellow/20 border border-brand-yellow/30 flex items-center justify-center overflow-hidden transition-all hover:border-brand-yellow/50 active:scale-95"
        >
            <img
                :src="avatarUrl"
                alt="Avatar"
                class="w-full h-full object-cover"
            />
        </button>

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform scale-95 opacity-0 -translate-y-2"
            enter-to-class="transform scale-100 opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="transform scale-100 opacity-100 translate-y-0"
            leave-to-class="transform scale-95 opacity-0 -translate-y-2"
        >
            <div
                v-if="isOpen"
                class="absolute right-0 mt-3 w-48 glass-card rounded-2xl border border-white/10 shadow-2xl py-2 z-100 origin-top-right"
            >
                <div class="px-4 py-3 border-b border-white/5 mb-2">
                    <p
                        class="text-xs font-black text-brand-yellow uppercase tracking-widest leading-none mb-1"
                    >
                        {{ user?.role?.name || "User" }}
                    </p>
                    <p class="text-[10px] text-white font-bold truncate">
                        {{ user?.nick_name || user?.phone }}
                    </p>
                </div>

                <button
                    @click="handleLogout"
                    class="w-full px-4 py-3 text-left hover:bg-white/5 transition-colors group flex items-center gap-3"
                >
                    <div
                        class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center text-red-500 group-hover:bg-red-500 group-hover:text-black transition-all"
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
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3m0-10V5a3 3 0 00-3-3H6a3 3 0 00-3 3v14a3 3 0 003 3h4a3 3 0 003-3v-1"
                            />
                        </svg>
                    </div>
                    <span
                        class="text-xs font-black uppercase text-gray-400 group-hover:text-white transition-colors"
                    >
                        Logout
                    </span>
                </button>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.glass-card {
    background: rgba(18, 18, 18, 0.9);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}
</style>
