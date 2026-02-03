<template>
    <div class="min-h-screen bg-brand-black text-white font-sans pb-32">
        <Head title="Lineup - Star Yako" />

        <nav
            class="sticky top-0 z-50 glass-nav px-6 py-4 flex items-center justify-between"
        >
            <img 
                :src="'/assets/star-yako-logo.png'" 
                alt="Star Yako Logo" 
                class="h-8 w-auto object-contain" 
            />
            <UserMenu />
        </nav>

        <div class="px-6 pt-8 animate-fade-up">
            <header class="mb-8">
                <h2 class="text-4xl font-extrabold tracking-tight">
                    Artist Lineup
                </h2>
                <p class="text-gray-400 font-medium">
                    Tonight's performing artists
                </p>
            </header>

            <div class="relative mb-10">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search artist or genre..."
                    class="w-full bg-brand-gray/50 border border-white/10 px-12 py-4 rounded-2xl outline-none focus:border-brand-yellow/50 transition-all font-medium"
                />
                <div
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                </div>
            </div>

            </div>
        
        </div>
</template>