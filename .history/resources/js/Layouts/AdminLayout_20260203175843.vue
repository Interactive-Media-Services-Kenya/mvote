<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import UserMenu from "../Components/UserMenu.vue";

const page = usePage();
const currentRoute = computed(() => page.url);

const navItems = [
    {
        name: "Dashboard",
        href: "/admin/dashboard",
        icon: "M13 7h8m0 0v8m0-8l-8 8-4-4-6 6",
    },
    {
        name: "Event Config",
        href: "/admin/event",
        icon: "M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z",
    },
    {
        name: "Artists",
        href: "/admin/artists",
        icon: "M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z",
    },
    {
        name: "Judges",
        href: "/admin/judges",
        icon: "M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z",
    },
];
</script>

<template>
    <div class="min-h-screen bg-brand-black text-white font-sans flex flex-col">
        <!-- Admin Top Nav - Matches Lineup.vue exactly -->
        <nav class="sticky top-0 z-50 glass-nav px-6 py-4 border-b border-white/5 bg-brand-black/50 backdrop-blur-2xl">
            <div class="max-w-md mx-auto flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link href="/lineup"
                        class="text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-brand-yellow transition-all flex items-center gap-1.5">
                        <span class="text-xs">&larr;</span> Exit
                    </Link>
                    <div class="h-4 w-px bg-white/10"></div>

                    <Link href="/admin/dashboard">
                        <img src="/assets/star-yako-logo.png" alt="Star Yako Logo" class="h-7 w-auto object-contain" />
                    </Link>
                </div>

                <UserMenu />
            </div>
        </nav>

        <!-- Main Layout Wrapper -->
        <div class="flex flex-1 overflow-hidden">
            <!-- Content Area -->
            <main class="flex-1 overflow-y-auto px-6 pt-8 pb-32">
                <div class="max-w-md mx-auto">
                    <slot />
                </div>
            </main>
        </div>

        <nav class="fixed bottom-2 w-full max-w-md z-50 pointer-events-none">
            <div
                class="bg-brand-black/80 backdrop-blur-2xl rounded-full p-2 px-6 flex items-center justify-between border border-white/20 shadow-[0_20px_50px_rgba(0,0,0,0.8)] pointer-events-auto mx-4">
                <Link v-for="item in navItems" :key="item.name" :href="item.href"
                    class="flex items-center justify-center w-12 h-12 rounded-full transition-all duration-300 relative group"
                    :class="[
                        currentRoute.startsWith(item.href)
                            ? 'bg-brand-yellow text-black shadow-[0_4px_15px_rgba(255,107,0,0.4)]'
                            : 'text-gray-500 hover:text-white hover:bg-white/5',
                    ]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" :d="item.icon" />
                    </svg>
                </Link>
            </div>
        </nav>
    </div>
</template>
