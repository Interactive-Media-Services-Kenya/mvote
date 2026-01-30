<script setup>
import { Head, router, useForm, usePage } from "@inertiajs/vue3";
import AdminLayout from "../../Layouts/AdminLayout.vue";
import HypeModal from "../../Components/HypeModal.vue";
import { ref } from "vue";

const showInviteModal = ref(false);
const isSending = ref(false);
const judges = usePage().props.judges;
const inviteForm = useForm({
    nick_name: "",
    phone: "",
});

const sendInvite = async () => {
    isSending.value = true;
    inviteForm.post("judges");
};
</script>

<template>
    <AdminLayout>
        <Head title="Manage Judges - MVote" />

        <div class="animate-fade-up">
            <header class="flex items-center justify-between mb-8 px-1">
                <div>
                    <h2
                        class="text-3xl font-black italic tracking-tighter uppercase leading-none"
                    >
                        Judges
                    </h2>
                    <p
                        class="text-gray-500 font-bold uppercase tracking-widest text-[9px] mt-1"
                    >
                        Professional Oversight Panel
                    </p>
                </div>
                <button
                    @click="showInviteModal = true"
                    class="w-12 h-12 bg-brand-yellow text-black rounded-2xl flex items-center justify-center shadow-lg active:scale-95 transition-all"
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
                            stroke-width="3"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                </button>
            </header>

            <!-- Unified List Aesthetic -->
            <div class="space-y-4">
                <div
                    v-for="judge in judges"
                    :key="judge.id"
                    class="glass-card p-4 rounded-3xl border-white/5 relative overflow-hidden group active:scale-[0.99] transition-all"
                >
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-gray-500 group-hover:text-brand-yellow group-hover:bg-brand-yellow/10 group-hover:border-brand-yellow/20 transition-all duration-500 overflow-hidden"
                        >
                            <span class="text-xs font-black uppercase">{{
                                judge.nick_name.substring(0, 2)
                            }}</span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h4
                                class="text-lg font-black italic tracking-tighter uppercase leading-tight mb-0.5 truncate"
                            >
                                {{ judge.nick_name }}
                            </h4>
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-[8px] font-black uppercase text-brand-yellow tracking-widest"
                                    >{{ judge.role }}</span
                                >
                                <span
                                    class="w-1 h-1 rounded-full bg-gray-700"
                                ></span>
                                <span
                                    class="text-[8px] font-black uppercase text-gray-500 tracking-widest"
                                    >{{ judge.invites }} Dispatches</span
                                >
                            </div>
                        </div>

                        <div
                            class="px-3 py-1.5 rounded-xl border text-[8px] font-black uppercase tracking-widest transition-all"
                            :class="[
                                judge.status === 'active'
                                    ? 'bg-green-500/10 border-green-500/20 text-green-500'
                                    : 'bg-yellow-500/10 border-yellow-500/20 text-yellow-500',
                            ]"
                        >
                            {{ judge.status }}
                        </div>
                    </div>

                    <!-- Decorative BG -->
                    <div
                        class="absolute -right-2 -bottom-2 opacity-[0.03] group-hover:opacity-[0.06] transition-opacity pointer-events-none"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-20 h-20"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                            />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reusable Hype Modal -->
        <HypeModal
            :show="showInviteModal"
            title="Dispatch Access"
            subtitle="Security Protocol"
            @close="showInviteModal = false"
        >
            <div class="space-y-6">
                <div class="space-y-2 text-left">
                    <label
                        class="text-[10px] font-black uppercase text-gray-500 tracking-widest ml-1"
                        >Judge Name</label
                    >
                    <input
                        v-model="inviteForm.nick_name"
                        type="text"
                        placeholder="e.g. Sauti Sol member"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-yellow transition-all font-bold placeholder:text-gray-700 text-white"
                    />
                </div>
                <div class="space-y-2 text-left">
                    <label
                        class="text-[10px] font-black uppercase text-gray-400 tracking-widest ml-1"
                        >Phone Number (SMS Target)</label
                    >
                    <input
                        v-model="inviteForm.phone"
                        type="text"
                        placeholder="e.g. 0712 345 678"
                        class="w-full bg-white/5 border border-white/10 rounded-2xl px-5 py-4 outline-none focus:border-brand-yellow transition-all font-bold placeholder:text-gray-700 font-mono text-white"
                    />
                </div>

                <div
                    class="bg-blue-500/5 border border-blue-500/10 p-4 rounded-3xl"
                >
                    <p
                        class="text-[9px] text-blue-400 font-bold leading-relaxed uppercase tracking-wider italic text-center"
                    >
                        * Automated transmission will include a secure one-time
                        login link.
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex flex-col gap-3">
                    <button
                        @click="sendInvite"
                        :disabled="isSending"
                        class="w-full bg-brand-yellow text-black py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl active:scale-95 transition-all disabled:opacity-50 relative overflow-hidden"
                    >
                        <span v-if="!isSending">Transmit Dispatch</span>
                        <span
                            v-else
                            class="flex items-center justify-center gap-2"
                        >
                            <svg
                                class="animate-spin h-4 w-4 text-black"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            Transmitting...
                        </span>
                    </button>
                    <button
                        @click="showInviteModal = false"
                        class="w-full py-2 rounded-xl font-black uppercase text-[10px] tracking-widest text-gray-500 hover:text-white transition-all"
                    >
                        Discard Session
                    </button>
                </div>
            </template>
        </HypeModal>
    </AdminLayout>
</template>
