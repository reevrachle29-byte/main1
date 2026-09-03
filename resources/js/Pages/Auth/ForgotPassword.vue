<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'), {
        onFinish: () => form.reset('email'),
    });
};
</script>

<template>
    <Head title="Forgot Password" />

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
                <p class="text-slate-400 text-xs mt-2 uppercase tracking-widest font-semibold">Central Philippine Adventist College</p>
            </div>

            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-white">Reset Password</h3>
                    <p class="text-slate-400 text-sm mt-1">Forgot your password? Enter your email and we'll send you a reset link.</p>
                </div>

                <div v-if="status" class="p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl text-xs font-semibold mb-5">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                            </span>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                autofocus
                                class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl pl-10 pr-4 py-3 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                                placeholder="your@cpac.edu.ph"
                            />
                        </div>
                        <p v-if="form.errors.email" class="text-rose-400 text-xs mt-1.5">{{ form.errors.email }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 font-bold bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 active:scale-[0.98] transition-all disabled:opacity-50 disabled:pointer-events-none"
                    >
                        <span v-if="form.processing" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ form.processing ? 'Sending...' : 'Email Password Reset Link' }}</span>
                    </button>
                </form>

                <div class="text-center mt-6 pt-5 border-t border-white/5">
                    <Link :href="route('login')" class="text-xs text-slate-500 hover:text-amber-400 transition font-semibold">
                        Back to Sign In
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
