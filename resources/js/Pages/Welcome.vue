<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const bgVideo = ref(null);

onMounted(() => {
    if (bgVideo.value) {
        bgVideo.value.playbackRate = 0.5;
    }
});
</script>

<template>
    <Head title="Welcome - QUEUEVita" />

    <div class="min-h-screen bg-slate-900 relative flex flex-col justify-between p-6 md:p-12 overflow-hidden">

        <!-- Video Background -->
        <video
            ref="bgVideo"
            autoplay
            loop
            muted
            playsinline
            preload="auto"
            class="absolute inset-0 w-full h-full object-cover"
            src="/videos/welcome.mp4"
        ></video>

        <!-- Dark Overlay -->
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px]"></div>

        <!-- Header / Branding -->
        <header class="relative z-10 flex justify-between items-center max-w-7xl mx-auto w-full">
            <div class="flex items-center space-x-2">
                <span class="text-3xl font-extrabold text-white tracking-tight">
                    QUEUE<span class="text-amber-400">Vita.</span>
                </span>
            </div>
            
            <div v-if="$page.props.auth.user" class="flex items-center space-x-3">
                <span class="text-slate-200 text-xs bg-white/10 px-3 py-1.5 rounded-full border border-white/10">
                    Logged in as: <strong class="text-amber-400">{{ $page.props.auth.user.name }}</strong> ({{ $page.props.auth.user.role }})
                </span>
                <Link :href="route('logout')" method="post" as="button" class="text-xs text-red-300 hover:text-red-100 bg-red-500/20 hover:bg-red-500/40 px-3 py-1.5 rounded-full transition">
                    Log Out
                </Link>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="relative z-10 max-w-7xl mx-auto w-full my-auto py-12">
            
            <!-- STATE A: User is ALREADY Logged In -->
            <div v-if="$page.props.auth.user" class="max-w-xl mx-auto bg-slate-900/80 backdrop-blur-md border border-white/10 rounded-2xl p-8 text-center text-white shadow-2xl">
                <div class="w-16 h-16 bg-amber-400/20 text-amber-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h2 class="text-2xl font-bold mb-2">Welcome Back!</h2>
                <p class="text-slate-300 text-sm mb-6">
                    You are currently authenticated as <span class="text-amber-400 font-semibold">{{ $page.props.auth.user.role }}</span>.
                </p>

                <!-- Dynamic Redirection Button Based on Active User Role -->
                <Link v-if="$page.props.auth.user.role === 'admin'" 
                      :href="route('admin.offices.index')" 
                      class="w-full inline-block bg-amber-400 hover:bg-amber-500 text-slate-900 font-bold py-3 px-6 rounded-xl transition shadow-lg">
                    Go to Admin Offices Management &rarr;
                </Link>

                <Link v-else-if="$page.props.auth.user.role === 'staff' || $page.props.auth.user.role === 'employee'" 
                      :href="route('dashboard.staff')" 
                      class="w-full inline-block bg-amber-400 hover:bg-amber-500 text-slate-900 font-bold py-3 px-6 rounded-xl transition shadow-lg">
                    Go to Staff Queue Counter &rarr;
                </Link>

                <Link v-else 
                      :href="route('dashboard')" 
                      class="w-full inline-block bg-amber-400 hover:bg-amber-500 text-slate-900 font-bold py-3 px-6 rounded-xl transition shadow-lg">
                    Go to Student Dashboard &rarr;
                </Link>
            </div>

            <!-- STATE B: User is NOT Logged In (Guest View) -->
            <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- 1. Student Portal Card -->
                <div class="bg-slate-900/60 backdrop-blur-md border border-white/10 rounded-2xl p-8 text-white flex flex-col justify-between hover:border-amber-400/50 transition group">
                    <div>
                        <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center mb-6 group-hover:bg-amber-400 group-hover:text-slate-900 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                        </div>
                        <h2 class="text-2xl font-bold mb-2">Student <span class="text-amber-400">Portal</span></h2>
                        <p class="text-slate-300 text-sm leading-relaxed mb-6">
                            Access live campus queue status, request virtual tickets, and track real-time transaction updates from your mobile or laptop.
                        </p>
                    </div>
                    <Link :href="route('login', { role: 'Student' })" 
                          class="w-full text-center bg-amber-400 hover:bg-amber-500 text-slate-900 font-semibold py-3 px-4 rounded-xl transition shadow-lg">
                        Login as Student &rarr;
                    </Link>
                </div>

                <!-- 2. Employee / Staff Portal Card -->
                <div class="bg-slate-900/60 backdrop-blur-md border border-white/10 rounded-2xl p-8 text-white flex flex-col justify-between hover:border-amber-400/50 transition group">
                    <div>
                        <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center mb-6 group-hover:bg-amber-400 group-hover:text-slate-900 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <h2 class="text-2xl font-bold mb-2">Employee <span class="text-amber-400">Portal</span></h2>
                        <p class="text-slate-300 text-sm leading-relaxed mb-6">
                            Manage counter queues, call next tickets, skip or complete transactions, and monitor daily office service volume.
                        </p>
                    </div>
                    <Link :href="route('login', { role: 'Employee' })" 
                          class="w-full text-center bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold py-3 px-4 rounded-xl transition">
                        Login as Employee &rarr;
                    </Link>
                </div>

                <!-- 3. Admin Portal Card -->
                <div class="bg-slate-900/60 backdrop-blur-md border border-white/10 rounded-2xl p-8 text-white flex flex-col justify-between hover:border-amber-400/50 transition group">
                    <div>
                        <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center mb-6 group-hover:bg-amber-400 group-hover:text-slate-900 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <h2 class="text-2xl font-bold mb-2">Administrator <span class="text-amber-400">Portal</span></h2>
                        <p class="text-slate-300 text-sm leading-relaxed mb-6">
                            Configure campus offices, assign staff roles, manage system services, and access system-wide analytics.
                        </p>
                    </div>
                    <Link :href="route('login', { role: 'Administrator' })" 
                          class="w-full text-center bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold py-3 px-4 rounded-xl transition">
                        Login as Admin &rarr;
                    </Link>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 text-center text-slate-400 text-xs">
            Central Philippine Adventist College &copy; 2026 QUEUEVita System.
        </footer>
    </div>
</template>