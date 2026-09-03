<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head title="Email Verification" />

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
                    <h3 class="text-xl font-bold text-white">Verify Your Email</h3>
                    <p class="text-slate-400 text-sm mt-1">
                        Before continuing, please verify your email address by clicking the link we sent you.
                    </p>
                </div>

                <div v-if="verificationLinkSent" class="p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl text-xs font-semibold mb-5">
                    A new verification link has been sent to your email.
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 font-bold bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 active:scale-[0.98] transition-all disabled:opacity-50 disabled:pointer-events-none"
                    >
                        <span v-if="form.processing" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ form.processing ? 'Sending...' : 'Resend Verification Email' }}</span>
                    </button>

                    <div class="flex items-center justify-between text-xs">
                        <Link :href="route('profile.show')" class="text-slate-500 hover:text-amber-400 transition font-semibold">
                            Edit Profile
                        </Link>
                        <Link :href="route('logout')" method="post" as="button" class="text-slate-500 hover:text-red-400 transition font-semibold">
                            Log Out
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
