<script setup>
import { nextTick, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const recoveryCodeInput = ref(null);
const codeInput = ref(null);

const toggleRecovery = async () => {
    recovery.value ^= true;
    await nextTick();
    if (recovery.value) {
        recoveryCodeInput.value.focus();
        form.code = '';
    } else {
        codeInput.value.focus();
        form.recovery_code = '';
    }
};

const submit = () => {
    form.post(route('two-factor.login'));
};
</script>

<template>
    <Head title="Two-Factor Confirmation" />

    <div class="relative min-h-screen flex items-center justify-center bg-slate-950 font-sans overflow-hidden">
        <!-- Video Background -->
        <video autoplay loop muted playsinline preload="auto" class="absolute inset-0 w-full h-full object-cover" src="/videos/welcome.mov"></video>
        <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-[2px]"></div>

        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-amber-500/10 blur-[150px] rounded-full"></div>
        </div>

        <div class="relative z-10 w-full max-w-md mx-4">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-400 to-amber-500 shadow-xl shadow-amber-500/20 mb-4">
                    <span class="font-black text-slate-950 text-3xl">Q</span>
                </div>
                <h2 class="text-3xl font-black tracking-tight text-white">QUEUE<span class="text-amber-400">Vita</span></h2>
            </div>

            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-white">Two-Factor Authentication</h3>
                    <p class="text-slate-400 text-sm mt-1">
                        <template v-if="!recovery">
                            Enter the authentication code from your authenticator app.
                        </template>
                        <template v-else>
                            Enter one of your emergency recovery codes.
                        </template>
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div v-if="!recovery">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Authentication Code</label>
                        <input
                            ref="codeInput"
                            v-model="form.code"
                            type="text"
                            inputmode="numeric"
                            autofocus
                            autocomplete="one-time-code"
                            class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none font-mono text-center text-lg tracking-[0.3em]"
                            placeholder="000000"
                        />
                        <p v-if="form.errors.code" class="text-rose-400 text-xs mt-1.5">{{ form.errors.code }}</p>
                    </div>

                    <div v-else>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Recovery Code</label>
                        <input
                            ref="recoveryCodeInput"
                            v-model="form.recovery_code"
                            type="text"
                            autocomplete="one-time-code"
                            class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none font-mono"
                            placeholder="Enter recovery code"
                        />
                        <p v-if="form.errors.recovery_code" class="text-rose-400 text-xs mt-1.5">{{ form.errors.recovery_code }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 font-bold bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 active:scale-[0.98] transition-all disabled:opacity-50 disabled:pointer-events-none"
                    >
                        <span v-if="form.processing" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ form.processing ? 'Verifying...' : 'Log in' }}</span>
                    </button>

                    <div class="text-center">
                        <button type="button" class="text-xs text-slate-500 hover:text-amber-400 transition font-semibold" @click.prevent="toggleRecovery">
                            <template v-if="!recovery">Use a recovery code</template>
                            <template v-else>Use an authentication code</template>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
