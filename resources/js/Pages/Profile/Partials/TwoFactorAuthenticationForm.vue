<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import ConfirmsPassword from '@/Components/ConfirmsPassword.vue';

const props = defineProps({
    requiresConfirmation: Boolean,
});

const page = usePage();
const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);

const confirmationForm = useForm({
    code: '',
});

const twoFactorEnabled = computed(
    () => !enabling.value && page.props.auth.user?.two_factor_enabled,
);

watch(twoFactorEnabled, () => {
    if (!twoFactorEnabled.value) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
    }
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;
    router.post(route('two-factor.enable'), {}, {
        preserveScroll: true,
        onSuccess: () => Promise.all([
            showQrCode(),
            showSetupKey(),
            showRecoveryCodes(),
        ]),
        onFinish: () => {
            enabling.value = false;
            confirming.value = props.requiresConfirmation;
        },
    });
};

const showQrCode = () => {
    return axios.get(route('two-factor.qr-code')).then(response => {
        qrCode.value = response.data.svg;
    });
};

const showSetupKey = () => {
    return axios.get(route('two-factor.secret-key')).then(response => {
        setupKey.value = response.data.secretKey;
    });
};

const showRecoveryCodes = () => {
    return axios.get(route('two-factor.recovery-codes')).then(response => {
        recoveryCodes.value = response.data;
    });
};

const confirmTwoFactorAuthentication = () => {
    confirmationForm.post(route('two-factor.confirm'), {
        errorBag: "confirmTwoFactorAuthentication",
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            qrCode.value = null;
            setupKey.value = null;
        },
    });
};

const regenerateRecoveryCodes = () => {
    axios.post(route('two-factor.recovery-codes')).then(() => showRecoveryCodes());
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;
    router.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
        },
    });
};
</script>

<template>
    <div class="bg-slate-900/60 rounded-2xl border border-white/10 backdrop-blur-xl overflow-hidden">
        <!-- Card Header -->
        <div class="px-6 py-5 border-b border-white/5">
            <h3 class="text-lg font-bold text-white">Two Factor Authentication</h3>
            <p class="text-slate-400 text-xs mt-1">Add additional security to your account using two factor authentication.</p>
        </div>

        <!-- Card Body -->
        <div class="p-6 space-y-5">
            <h3 v-if="twoFactorEnabled && !confirming" class="text-lg font-bold text-white">
                You have enabled two factor authentication.
            </h3>

            <h3 v-else-if="twoFactorEnabled && confirming" class="text-lg font-bold text-white">
                Finish enabling two factor authentication.
            </h3>

            <h3 v-else class="text-lg font-bold text-white">
                You have not enabled two factor authentication.
            </h3>

            <div class="max-w-xl text-sm text-slate-400">
                <p>
                    When two factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application.
                </p>
            </div>

            <div v-if="twoFactorEnabled">
                <div v-if="qrCode">
                    <div class="max-w-xl text-sm text-slate-400">
                        <p v-if="confirming" class="font-semibold text-slate-300">
                            To finish enabling two factor authentication, scan the following QR code using your phone's authenticator application or enter the setup key and provide the generated OTP code.
                        </p>
                        <p v-else>
                            Two factor authentication is now enabled. Scan the following QR code using your phone's authenticator application or enter the setup key.
                        </p>
                    </div>

                    <div class="mt-4 p-3 inline-block bg-white rounded-xl" v-html="qrCode" />

                    <div v-if="setupKey" class="mt-4 text-sm text-slate-400">
                        <p class="font-semibold text-slate-300">
                            Setup Key: <span v-html="setupKey" class="font-mono"></span>
                        </p>
                    </div>

                    <div v-if="confirming" class="mt-4 space-y-1.5">
                        <label for="code" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Code</label>
                        <input
                            id="code"
                            v-model="confirmationForm.code"
                            type="text"
                            name="code"
                            class="w-full max-w-xs bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none font-mono"
                            inputmode="numeric"
                            autofocus
                            autocomplete="one-time-code"
                            @keyup.enter="confirmTwoFactorAuthentication"
                        />
                        <p v-if="confirmationForm.errors.code" class="text-rose-400 text-xs mt-1">{{ confirmationForm.errors.code }}</p>
                    </div>
                </div>

                <div v-if="recoveryCodes.length > 0 && !confirming">
                    <div class="mt-4 max-w-xl text-sm text-slate-400">
                        <p class="font-semibold text-slate-300">
                            Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two factor authentication device is lost.
                        </p>
                    </div>

                    <div class="grid gap-1 max-w-xl mt-4 px-4 py-4 font-mono text-sm bg-slate-950/50 border border-white/10 text-slate-200 rounded-xl">
                        <div v-for="code in recoveryCodes" :key="code">
                            {{ code }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 pt-3">
                <div v-if="!twoFactorEnabled">
                    <ConfirmsPassword @confirmed="enableTwoFactorAuthentication">
                        <button
                            type="button"
                            :disabled="enabling"
                            class="inline-flex items-center gap-2 px-5 py-2.5 font-bold bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 active:scale-[0.98] transition-all disabled:opacity-55 disabled:pointer-events-none text-sm"
                        >
                            <span v-if="enabling" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                            Enable
                        </button>
                    </ConfirmsPassword>
                </div>

                <template v-else>
                    <ConfirmsPassword @confirmed="confirmTwoFactorAuthentication">
                        <button
                            v-if="confirming"
                            type="button"
                            :disabled="enabling || confirmationForm.processing"
                            class="inline-flex items-center gap-2 px-5 py-2.5 font-bold bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 active:scale-[0.98] transition-all disabled:opacity-55 disabled:pointer-events-none text-sm"
                        >
                            Confirm
                        </button>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="regenerateRecoveryCodes">
                        <button
                            v-if="recoveryCodes.length > 0 && !confirming"
                            type="button"
                            class="inline-flex items-center gap-2 px-5 py-2.5 font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-white/10 rounded-xl transition text-sm"
                        >
                            Regenerate Recovery Codes
                        </button>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="showRecoveryCodes">
                        <button
                            v-if="recoveryCodes.length === 0 && !confirming"
                            type="button"
                            class="inline-flex items-center gap-2 px-5 py-2.5 font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-white/10 rounded-xl transition text-sm"
                        >
                            Show Recovery Codes
                        </button>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
                        <button
                            v-if="confirming"
                            type="button"
                            :disabled="disabling"
                            class="inline-flex items-center gap-2 px-5 py-2.5 font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-white/10 rounded-xl transition text-sm disabled:opacity-55 disabled:pointer-events-none"
                        >
                            Cancel
                        </button>
                    </ConfirmsPassword>

                    <ConfirmsPassword @confirmed="disableTwoFactorAuthentication">
                        <button
                            v-if="!confirming"
                            type="button"
                            :disabled="disabling"
                            class="inline-flex items-center gap-2 px-5 py-2.5 font-bold bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 rounded-xl transition text-sm disabled:opacity-55 disabled:pointer-events-none"
                        >
                            Disable
                        </button>
                    </ConfirmsPassword>
                </template>
            </div>
        </div>
    </div>
</template>
