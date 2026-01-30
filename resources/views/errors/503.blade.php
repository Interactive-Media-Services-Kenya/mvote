<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Initialization - MVote</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            'brand-black': '#080808',
                            'brand-yellow': '#FFD700',
                        }
                    }
                }
            }
        </script>
        <style>
            @keyframes line-load {
                0% {
                    transform: translateX(-100%);
                }

                100% {
                    transform: translateX(100%);
                }
            }

            @keyframes fade-up {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes pulse-ring {
                0% {
                    transform: scale(0.8);
                    opacity: 0.5;
                }

                100% {
                    transform: scale(1.5);
                    opacity: 0;
                }
            }

            .animate-line-load {
                animation: line-load 2s ease-in-out infinite;
            }

            .animate-fade-up {
                animation: fade-up 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }

            .delay-1 {
                animation-delay: 0.1s;
            }

            .delay-2 {
                animation-delay: 0.3s;
            }

            .delay-3 {
                animation-delay: 0.5s;
            }
        </style>
    </head>

    <body class="bg-brand-black m-0 overflow-hidden font-sans">

        <div class="min-h-screen flex flex-col items-center justify-center p-6 relative">

            <div class="absolute inset-0 z-0">
                <div class="absolute inset-0 bg-gradient-to-t from-brand-black via-brand-black/90 to-transparent z-10">
                </div>
                <img src="/assets/carousel_1.png" class="w-full h-full object-cover opacity-30 blur-[4px] scale-105"
                    alt="Arena">
            </div>

            <div class="relative z-10 text-center animate-fade-up">

                <div
                    class="inline-flex items-center gap-3 px-5 py-2 rounded-2xl bg-white/5 border border-white/10 mb-12">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-yellow opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-yellow"></span>
                    </span>
                    <span class="text-brand-yellow text-[10px] font-black uppercase tracking-[0.4em] leading-none">
                        System Deployment in Progress
                    </span>
                </div>

                <div class="mb-10">
                    <h1
                        class="text-6xl md:text-9xl font-black italic tracking-tighter text-white uppercase leading-[0.8] mb-4">
                        SOMETHING <br> <span class="text-brand-yellow">ELITE</span>
                    </h1>
                    <h2 class="text-white/40 font-black italic text-2xl md:text-4xl uppercase tracking-tighter">
                        IS ARRIVING.
                    </h2>
                </div>

                <p
                    class="text-gray-400 font-bold uppercase tracking-[0.5em] text-[10px] md:text-xs mb-16 max-w-sm mx-auto leading-relaxed px-4">
                    We are currently engineering a new standard for stage energy and fan interaction. Prepare for the
                    ultimate brief.
                </p>

                <div class="flex flex-col items-center gap-8">
                    <div class="flex gap-4">
                        <div v-for="i in 3" class="w-16 h-[2px] bg-white/10 rounded-full overflow-hidden">
                            <div class="h-full bg-brand-yellow animate-line-load"></div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <span class="text-white/20 text-[8px] font-black uppercase tracking-[0.5em] animate-pulse">
                            Synchronizing Brief Catalog...
                        </span>
                    </div>
                </div>
            </div>

            <div class="absolute bottom-12 left-0 right-0 text-center z-10">
                <div class="flex flex-col items-center gap-4">
                    <p class="text-white/10 font-black italic tracking-[0.3em] text-3xl uppercase">
                        M<span class="text-brand-yellow/10">VOTE</span>
                    </p>
                    <div class="px-4 py-1 rounded border border-white/5 bg-white/2">
                        <span class="text-[7px] text-gray-600 font-bold uppercase tracking-widest">Est. 2026 ||
                            IMS</span>
                    </div>
                </div>
            </div>
        </div>

    </body>

</html>
