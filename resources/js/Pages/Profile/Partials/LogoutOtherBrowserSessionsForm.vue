<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

defineProps({
    sessions: Array,
});

const confirmingLogout = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmLogout = () => {
    confirmingLogout.value = true;
    setTimeout(() => passwordInput.value.focus(), 250);
};

const logoutOtherBrowserSessions = () => {
    form.delete(route('other-browser-sessions.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingLogout.value = false;
    form.reset();
};
</script>

<template>
    <div class="bg-slate-900/60 rounded-2xl border border-white/10 backdrop-blur-xl overflow-hidden">
        <!-- Card Header -->
        <div class="px-6 py-5 border-b border-white/5">
            <h3 class="text-lg font-bold text-white">Browser Sessions</h3>
            <p class="text-slate-400 text-xs mt-1">Manage and log out your active sessions on other browsers and devices.</p>
        </div>

        <!-- Card Body -->
        <div class="p-6">
            <div class="max-w-xl text-sm text-slate-400">
                If necessary, you may log out of all of your other browser sessions across all of your devices. Some of your recent sessions are listed below; however, this list may not be exhaustive. If you feel your account has been compromised, you should also update your password.
            </div>

            <!-- Other Browser Sessions -->
            <div v-if="sessions.length > 0" class="mt-5 space-y-4">
                <div v-for="(session, i) in sessions" :key="i" class="flex items-center p-4 bg-slate-950/50 rounded-xl border border-white/5">
                    <div class="shrink-0">
                        <svg v-if="session.agent.is_desktop" class="size-8 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                        </svg>
                        <svg v-else class="size-8 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        </svg>
                    </div>

                    <div class="ms-4">
                        <div class="text-sm font-semibold text-white">
                            {{ session.agent.platform ? session.agent.platform : 'Unknown' }} - {{ session.agent.browser ? session.agent.browser : 'Unknown' }}
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            {{ session.ip_address }},
                            <span v-if="session.is_current_device" class="text-amber-400 font-semibold">This device</span>
                            <span v-else>Last active {{ session.last_active }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-5">
                <button
                    @click="confirmLogout"
                    class="inline-flex items-center gap-2 px-5 py-2.5 font-bold bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 active:scale-[0.98] transition-all text-sm"
                >
                    Log Out Other Browser Sessions
                </button>
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-1"
                >
                    <span v-if="form.recentlySuccessful" class="text-xs text-emerald-400 font-semibold">Done.</span>
                </Transition>
            </div>

            <!-- Log Out Other Devices Confirmation Modal -->
            <Teleport to="body">
                <Transition
                    enter-active-class="ease-out duration-200"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="ease-in duration-150"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div v-if="confirmingLogout" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0">
                        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>
                        <div class="relative bg-slate-900 border border-white/10 rounded-2xl p-6 max-w-md mx-auto shadow-2xl">
                            <h3 class="text-lg font-bold text-white mb-2">Log Out Other Browser Sessions</h3>
                            <p class="text-sm text-slate-400 mb-5">
                                Please enter your password to confirm you would like to log out of your other browser sessions across all of your devices.
                            </p>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
                                    <input
                                        ref="passwordInput"
                                        v-model="form.password"
                                        type="password"
                                        class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                                        placeholder="Enter your password"
                                        autocomplete="current-password"
                                        @keyup.enter="logoutOtherBrowserSessions"
                                    />
                                    <p v-if="form.errors.password" class="text-rose-400 text-xs mt-1">{{ form.errors.password }}</p>
                                </div>
                            </div>
                            <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-white/5">
                                <button
                                    @click="closeModal"
                                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-sm font-semibold transition"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="logoutOtherBrowserSessions"
                                    :disabled="form.processing"
                                    class="px-4 py-2 bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 font-bold rounded-xl text-sm transition disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Logging out...' : 'Log Out Other Browser Sessions' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </Teleport>
        </div>
    </div>
</template>
