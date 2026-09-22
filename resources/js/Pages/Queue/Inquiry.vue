<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    result: { type: Object, default: null },
    position: { type: Number, default: null },
    estimatedWait: { type: Number, default: null },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);

const form = useForm({
    tracking_code: '',
});

const search = () => {
    if (!form.tracking_code) return;
    form.post(route('queue.inquiry.search'), {
        preserveScroll: true,
    });
};

const statusColor = (status) => {
    const colors = {
        waiting: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
        called: 'bg-blue-500/10 text-blue-400 border-blue-500/30',
        serving: 'bg-amber-500/10 text-amber-400 border-amber-500/30',
        completed: 'bg-slate-500/10 text-slate-400 border-slate-500/30',
        cancelled: 'bg-red-500/10 text-red-400 border-red-500/30',
        skipped: 'bg-orange-500/10 text-orange-400 border-orange-500/30',
    };
    return colors[status] || 'bg-slate-500/10 text-slate-400';
};
</script>

<template>
    <div class="relative min-h-screen bg-slate-950 font-sans text-slate-100 flex flex-col justify-between overflow-x-hidden">
        <!-- Ambient Background -->
        <div class="fixed inset-0 pointer-events-none z-0">
            <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-amber-500/10 blur-[130px] rounded-full"></div>
            <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-teal-500/10 blur-[150px] rounded-full"></div>
        </div>

        <!-- Header -->
        <header class="relative z-10 w-full max-w-7xl mx-auto px-6 py-6 flex justify-between items-center border-b border-slate-800/80">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-400 via-teal-500 to-amber-600 flex items-center justify-center shadow-lg shadow-amber-500/20 ring-1 ring-white/20">
                    <span class="font-black text-slate-950 text-2xl tracking-tighter">Q</span>
                </div>
                <div>
                    <h1 class="font-black text-xl leading-none tracking-tight text-white">QUEUE-MMS</h1>
                    <span class="text-[11px] text-slate-400 font-medium tracking-wide">Queue Inquiry</span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="/kiosk" class="px-4 py-2 text-xs font-bold text-slate-300 hover:text-amber-400 hover:bg-slate-900 rounded-xl transition-all border border-transparent hover:border-slate-800">Kiosk</a>
                <a href="/monitor" class="px-4 py-2 text-xs font-bold text-slate-300 hover:text-amber-400 hover:bg-slate-900 rounded-xl transition-all border border-transparent hover:border-slate-800">Monitor</a>
            </div>
        </header>

        <!-- Main -->
        <main class="relative z-10 w-full max-w-2xl mx-auto px-6 py-16 flex-grow flex flex-col items-center">
            <div class="text-center mb-10 space-y-3">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-900/80 border border-amber-500/30 text-amber-400 text-xs font-bold tracking-wider uppercase shadow-xl backdrop-blur-md">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Track Your Queue
                </div>
                <h2 class="text-3xl sm:text-4xl font-black tracking-tight text-white">
                    Queue <span class="bg-gradient-to-r from-amber-400 via-teal-300 to-amber-500 bg-clip-text text-transparent">Status Lookup</span>
                </h2>
                <p class="text-slate-400 text-sm">Enter your tracking code to check your queue status and estimated waiting time.</p>
            </div>

            <!-- Search Form -->
            <form @submit.prevent="search" class="w-full max-w-lg mb-10">
                <div class="flex gap-3">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"></path></svg>
                        </span>
                        <input
                            v-model="form.tracking_code"
                            type="text"
                            placeholder="e.g. QV-AB12CD"
                            class="w-full bg-slate-900/60 border border-slate-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl pl-11 pr-4 py-3.5 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none font-mono tracking-wider"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-3.5 bg-gradient-to-r from-amber-500 to-teal-600 text-slate-950 font-bold rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 active:scale-[0.98] transition-all disabled:opacity-50 text-sm"
                    >
                        <span v-if="form.processing">Searching...</span>
                        <span v-else>Search</span>
                    </button>
                </div>
            </form>

            <!-- Flash Messages -->
            <div v-if="errorMessage" class="w-full max-w-lg mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-2xl text-red-400 text-sm font-semibold">
                {{ errorMessage }}
            </div>

            <!-- Result Card -->
            <div v-if="result" class="w-full max-w-lg bg-slate-900/60 rounded-3xl border border-slate-800/80 backdrop-blur-xl shadow-2xl p-8 space-y-6">
                <div class="text-center">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Your Queue Number</p>
                    <p class="text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-teal-300 to-amber-500">#{{ result.queue_number }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-950/50 rounded-xl border border-slate-800/60 text-center">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status</p>
                        <span :class="statusColor(result.status)" class="inline-block mt-1.5 px-3 py-1 text-xs font-bold rounded-full uppercase border">{{ result.status }}</span>
                    </div>
                    <div class="p-4 bg-slate-950/50 rounded-xl border border-slate-800/60 text-center">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Position</p>
                        <p class="text-2xl font-black text-white mt-1">{{ position || '—' }}</p>
                    </div>
                    <div class="p-4 bg-slate-950/50 rounded-xl border border-slate-800/60 text-center">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Office</p>
                        <p class="text-sm font-bold text-slate-200 mt-1.5">{{ result.service?.office?.name || '—' }}</p>
                    </div>
                    <div class="p-4 bg-slate-950/50 rounded-xl border border-slate-800/60 text-center">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Est. Wait</p>
                        <p class="text-2xl font-black text-teal-400 mt-1">{{ estimatedWait ? '~' + estimatedWait + 'm' : '—' }}</p>
                    </div>
                </div>

                <div class="p-4 bg-slate-950/50 rounded-xl border border-slate-800/60">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Tracking Code</p>
                    <p class="text-lg font-mono font-bold text-amber-400 tracking-wider">{{ result.tracking_code }}</p>
                </div>

                <div class="p-4 bg-slate-950/50 rounded-xl border border-slate-800/60">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Service</p>
                    <p class="text-sm font-semibold text-slate-200">{{ result.service?.service_name || '—' }}</p>
                </div>
            </div>
        </main>

        <footer class="relative z-10 w-full max-w-7xl mx-auto px-6 py-6 border-t border-slate-800/80 text-center text-xs text-slate-500">
            <p>&copy; 2026 QUEUE-MMS. All Rights Reserved.</p>
        </footer>
    </div>
</template>
