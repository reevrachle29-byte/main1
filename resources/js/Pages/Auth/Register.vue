<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    contact: '',
    role: 'student',
    password: '',
    password_confirmation: '',
    terms: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Create Account" />

    <div class="relative min-h-screen flex items-center justify-center bg-slate-950 font-sans overflow-hidden py-12 px-4 sm:px-6">
        
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

        <!-- Main Content Wrapper -->
        <div class="relative z-10 w-full max-w-lg mx-auto">
            
            <!-- Branding Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-400 to-amber-500 shadow-xl shadow-amber-500/20 mb-4">
                    <span class="font-black text-slate-950 text-3xl">V</span>
                </div>
                <h2 class="text-3xl font-black tracking-tight text-white leading-none">QUEUE<span class="text-amber-400">Vita</span></h2>
                <p class="text-slate-400 text-xs mt-2 uppercase tracking-widest font-semibold">Central Philippine Adventist College</p>
            </div>

            <!-- Glassmorphic Register Form Card -->
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl space-y-6">
                <div>
                    <h3 class="text-xl font-bold text-white">Create Account</h3>
                    <p class="text-slate-400 text-xs mt-1">Register to manage your queue tickets and access campus services.</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    
                    <!-- Full Name -->
                    <div class="space-y-1.5">
                        <label for="name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Full Name</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </span>
                            <input 
                                id="name"
                                v-model="form.name"
                                type="text" 
                                required
                                autofocus
                                placeholder="e.g. Juan Dela Cruz"
                                class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                            />
                        </div>
                        <p v-if="form.errors.name" class="text-xs text-rose-400 font-medium animate-fade-in">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email Address -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Campus Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </span>
                            <input 
                                id="email"
                                v-model="form.email"
                                type="email" 
                                required
                                placeholder="student@cpac.edu.ph"
                                class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                            />
                        </div>
                        <p v-if="form.errors.email" class="text-xs text-rose-400 font-medium animate-fade-in">{{ form.errors.email }}</p>
                    </div>

                    <!-- Contact & Role Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Contact Number -->
                        <div class="space-y-1.5">
                            <label for="contact" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Contact No.</label>
                            <input 
                                id="contact"
                                v-model="form.contact"
                                type="text" 
                                placeholder="09123456789"
                                class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                            />
                            <p v-if="form.errors.contact" class="text-xs text-rose-400 font-medium animate-fade-in">{{ form.errors.contact }}</p>
                        </div>

                        <!-- System Role Selection -->
                        <div class="space-y-1.5">
                            <label for="role" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Account Role</label>
                            <select 
                                id="role"
                                v-model="form.role"
                                required
                                class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-sm text-slate-100 transition-all outline-none"
                            >
                                <option value="student" class="bg-slate-900 text-slate-100">Student</option>
                                <option value="staff" class="bg-slate-900 text-slate-100">Employee / Staff</option>
                                <option value="admin" class="bg-slate-900 text-slate-100">Administrator</option>
                            </select>
                            <p v-if="form.errors.role" class="text-xs text-rose-400 font-medium animate-fade-in">{{ form.errors.role }}</p>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </span>
                            <input 
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="••••••••"
                                class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl pl-10 pr-12 py-2.5 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                            />
                            <button 
                                type="button" 
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs font-bold text-slate-400 hover:text-amber-400 transition"
                            >
                                {{ showPassword ? 'Hide' : 'Show' }}
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-xs text-rose-400 font-medium animate-fade-in">{{ form.errors.password }}</p>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Confirm Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </span>
                            <input 
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="••••••••"
                                class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                            />
                        </div>
                        <p v-if="form.errors.password_confirmation" class="text-xs text-rose-400 font-medium animate-fade-in">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <!-- Terms Checkbox -->
                    <div v-if="$page.props.jetstream?.hasTermsAndPrivacyPolicyFeature" class="pt-1">
                        <label class="flex items-start gap-2 cursor-pointer select-none">
                            <input 
                                type="checkbox" 
                                v-model="form.terms" 
                                class="mt-0.5 rounded border-slate-800 bg-slate-950 text-amber-500 focus:ring-0 focus:ring-offset-0 w-4 h-4"
                            />
                            <span class="text-xs text-slate-400">
                                I agree to the 
                                <a :href="route('terms.show')" target="_blank" class="text-amber-400 underline hover:text-amber-300">Terms of Service</a> 
                                and 
                                <a :href="route('policy.show')" target="_blank" class="text-amber-400 underline hover:text-amber-300">Privacy Policy</a>
                            </span>
                        </label>
                        <p v-if="form.errors.terms" class="text-xs text-rose-400 font-medium animate-fade-in mt-1">{{ form.errors.terms }}</p>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="w-full relative flex items-center justify-center gap-2 py-3 px-4 font-bold bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 hover:brightness-110 active:scale-[0.98] transition-all disabled:opacity-55 disabled:pointer-events-none mt-2"
                    >
                        <span v-if="form.processing" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ form.processing ? 'Creating Account...' : 'Register' }}</span>
                    </button>
                </form>

                <!-- Link Back to Login -->
                <div class="text-center pt-4 border-t border-white/5">
                    <p class="text-xs text-slate-500">
                        Already registered? 
                        <Link :href="route('login')" class="text-amber-400 font-bold hover:text-amber-300 transition ml-1">
                            Sign in to your account
                        </Link>
                    </p>
                </div>
            </div>

            <!-- Footer Meta & Copyright -->
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
