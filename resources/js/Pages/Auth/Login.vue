<script setup>
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const handleLogin = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="relative min-h-screen flex items-center justify-center bg-slate-950 font-sans overflow-hidden">
        
        <!-- Video Background -->
        <video
            autoplay
            loop
            muted
            playsinline
            preload="auto"
            class="absolute inset-0 w-full h-full object-cover"
            src="/videos/welcome.mov"
        ></video>
        <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-[2px]"></div>

        <!-- Ambient Glow -->
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[150%] h-[150%] bg-radial-gradient from-amber-950/30 via-slate-950/80 to-slate-950 pointer-events-none opacity-80 blur-3xl"></div>
        </div>

        <!-- Main Wrapper -->
        <div class="relative z-10 w-full max-w-md mx-4">
            
            <!-- Logo Section -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-400 to-amber-500 shadow-xl shadow-amber-500/20 mb-4">
                    <span class="font-black text-slate-950 text-3xl">V</span>
                </div>
                <h2 class="text-3xl font-black tracking-tight text-white leading-none">QUEUE<span class="text-amber-400">Vita</span></h2>
                <p class="text-slate-400 text-xs mt-2 uppercase tracking-widest font-semibold">Central Philippine Adventist College</p>
            </div>

            <!-- Login Form Card -->
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl space-y-6">
                <div>
                    <h3 class="text-xl font-bold text-white">Sign In</h3>
                    <p class="text-slate-400 text-xs mt-1">Please enter your institutional credentials to continue.</p>
                </div>

                <!-- Backend Validation Error Banner -->
                <div v-if="form.errors.email || form.errors.password" class="p-3.5 bg-rose-500/10 border border-rose-500/30 text-rose-400 rounded-xl text-xs font-semibold animate-fade-in">
                    {{ form.errors.email || form.errors.password }}
                </div>

                <form @submit.prevent="handleLogin" class="space-y-5">
                    <!-- Email / Identifier -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Email or ID</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </span>
                            <input 
                                v-model="form.email"
                                type="text" 
                                required
                                autofocus
                                placeholder="e.g. admin@example.com"
                                class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl pl-10 pr-4 py-3 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                            />
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Password</label>
                            <Link :href="route('password.request')" class="text-xs font-semibold text-amber-400 hover:text-amber-300 transition">Forgot?</Link>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </span>
                            <input 
                                v-model="form.password"
                                type="password" 
                                required
                                placeholder="••••••••"
                                class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl pl-10 pr-4 py-3 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                            />
                        </div>
                    </div>

                    <!-- Remember Me Option -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input 
                                v-model="form.remember"
                                type="checkbox" 
                                class="rounded border-slate-800 bg-slate-950 text-amber-500 focus:ring-0 focus:ring-offset-0 w-4 h-4"
                            />
                            <span class="text-xs text-slate-400 font-medium">Keep me signed in</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="w-full relative flex items-center justify-center gap-2 py-3 px-4 font-bold bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 hover:brightness-110 active:scale-[0.98] transition-all disabled:opacity-55 disabled:pointer-events-none"
                    >
                        <span v-if="form.processing" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ form.processing ? 'Verifying...' : 'Login' }}</span>
                    </button>
                </form>

                <!-- Create Account Link -->
                <div class="text-center pt-4 border-t border-white/5">
                    <p class="text-xs text-slate-500">
                        Don't have an account? 
                        <Link :href="route('register')" class="text-amber-400 font-bold hover:text-amber-300 transition ml-1">Create Account</Link>
                    </p>
                </div>
            </div>

            <!-- Footer Meta/Copyright -->
            <div class="text-center mt-8 space-y-2 text-[10px] text-slate-600 font-semibold tracking-wide uppercase">
                <div class="flex items-center justify-center gap-4">
                    <a href="#" class="hover:text-slate-400 transition">Privacy Policy</a>
                    <span>•</span>
                    <a href="#" class="hover:text-slate-400 transition">Terms of Use</a>
                </div>
                <p>&copy; 2026 CPAC. All Rights Reserved.</p>
            </div>

        </div>
    </div>
</template>

<style scoped>
.bg-radial-gradient {
    background-image: radial-gradient(var(--tw-gradient-stops));
}

.animate-fade-in {
    animation: fadeIn 0.25s ease-out forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
