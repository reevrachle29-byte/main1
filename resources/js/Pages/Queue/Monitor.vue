<script setup>
import { onMounted, onUnmounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    activeTickets: {
        type: Array,
        default: () => []
    },
    waitingTickets: {
        type: Array,
        default: () => []
    }
});

let pollInterval = null;

// Poll backend every 3 seconds for newly called tickets
onMounted(() => {
    pollInterval = setInterval(() => {
        router.reload({ only: ['activeTickets', 'waitingTickets'], preserveScroll: true });
    }, 3000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

// Primary active ticket (Most recently called)
const currentCalling = computed(() => {
    return props.activeTickets.length > 0 ? props.activeTickets[0] : null;
});

// Secondary active tickets (Other counters actively serving)
const secondaryCalling = computed(() => {
    return props.activeTickets.length > 1 ? props.activeTickets.slice(1) : [];
});
</script>

<template>
    <div class="relative min-h-screen bg-slate-950 font-sans text-slate-100 flex flex-col justify-between overflow-hidden select-none">
        
        <!-- Ambient Glowing TV Backdrops -->
        <div class="fixed inset-0 pointer-events-none z-0">
            <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-amber-500/10 blur-[160px] rounded-full"></div>
            <div class="absolute -bottom-40 -left-40 w-[600px] h-[600px] bg-teal-500/10 blur-[180px] rounded-full"></div>
            <div class="absolute -bottom-40 -right-40 w-[600px] h-[600px] bg-amber-600/10 blur-[180px] rounded-full"></div>
        </div>

        <!-- TV Header Bar -->
        <header class="relative z-10 w-full px-8 py-6 border-b border-slate-800/80 bg-slate-900/40 backdrop-blur-md flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-400 via-teal-500 to-amber-600 flex items-center justify-center shadow-lg shadow-amber-500/20 ring-1 ring-white/20">
                    <span class="font-black text-slate-950 text-2xl tracking-tighter">Q</span>
                </div>
                <div>
                    <h1 class="font-black text-2xl tracking-tight text-white flex items-center gap-2">
                        CPAC <span class="text-xs px-2.5 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 font-bold uppercase tracking-wider">Live Monitor</span>
                    </h1>
                    <span class="text-xs text-slate-400 font-medium">Central Philippine Adventist College Services</span>
                </div>
            </div>

            <!-- Live Broadcast Status Indicator -->
            <div class="flex items-center gap-3 bg-slate-900/90 border border-slate-800 px-4 py-2 rounded-2xl">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                </span>
                <span class="text-xs font-bold text-slate-300 uppercase tracking-widest">Live Updates</span>
            </div>
        </header>

        <!-- Main TV Display Area -->
        <main class="relative z-10 w-full max-w-7xl mx-auto px-8 py-8 flex-grow flex flex-col gap-8">
            
            <!-- Hero Section: Currently Called / Now Serving Number -->
            <div v-if="currentCalling" class="relative w-full p-8 rounded-3xl bg-gradient-to-b from-slate-900/90 to-slate-950/90 border-2 border-amber-500/40 backdrop-blur-2xl shadow-2xl shadow-amber-500/10 flex flex-col lg:flex-row items-center justify-between gap-6 overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="space-y-2 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-black uppercase tracking-widest">
                        <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15.536a5 5 0 001.414 1.414m2.828-9.9a9 9 0 010 12.728"></path></svg>
                        Now Calling
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-300">
                        {{ currentCalling.service?.office?.name || currentCalling.service?.service_name || 'Counter Service' }}
                    </h2>
                    <p class="text-slate-400 text-sm">Please proceed to your respective counter window.</p>
                </div>

                <!-- Big Callout Ticket Number -->
                <div class="flex items-center gap-6 bg-slate-950/80 border border-amber-500/30 px-8 py-4 rounded-2xl shadow-inner">
                    <div class="text-center">
                        <span class="block text-xs font-bold text-slate-500 uppercase tracking-widest">Ticket No.</span>
                        <span class="text-6xl sm:text-7xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-teal-300 to-amber-500 tracking-wider">
                            {{ currentCalling.queue_number || currentCalling.request_id || '---' }}
                        </span>
                    </div>
                    <div class="h-12 w-px bg-slate-800 hidden sm:block"></div>
                    <div class="text-center hidden sm:block">
                        <span class="block text-xs font-bold text-slate-500 uppercase tracking-widest">Counter</span>
                        <span class="text-3xl font-black text-white">
                            {{ currentCalling.counter_number || currentCalling.counter || 'Window 1' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Empty State (When no active numbers are called) -->
            <div v-else class="w-full py-20 px-8 text-center bg-slate-900/40 rounded-3xl border border-slate-800/80 backdrop-blur-xl flex flex-col items-center justify-center space-y-3">
                <div class="w-16 h-16 rounded-2xl bg-slate-800/80 border border-slate-700 flex items-center justify-center text-slate-500 mb-2">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 01-6 0v-1m6 0H9"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-white">Waiting for Active Calls</h3>
                <p class="text-slate-400 text-sm max-w-md">No numbers are currently being called. Next tickets will display automatically once called by staff.</p>
            </div>

            <!-- Multi-Counter Grid Section -->
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-slate-300 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
                    Active Counters
                </h3>

                <!-- Grid View -->
                <div v-if="activeTickets.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div 
                        v-for="(ticket, index) in activeTickets" 
                        :key="ticket.request_id || index"
                        class="p-6 bg-slate-900/60 rounded-2xl border transition-all duration-300 flex items-center justify-between shadow-xl backdrop-blur-xl"
                        :class="index === 0 ? 'border-amber-500/50 bg-slate-900/90' : 'border-slate-800/80 hover:border-slate-700'"
                    >
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">
                                {{ ticket.service?.office?.name || 'Department' }}
                            </span>
                            <div class="text-2xl font-black text-white">
                                {{ ticket.queue_number || ticket.request_id || '---' }}
                            </div>
                            <span class="text-xs text-slate-400 block">
                                Service: <strong class="text-slate-200">{{ ticket.service?.service_name || 'General' }}</strong>
                            </span>
                        </div>

                        <div class="text-right">
                            <span class="text-[10px] font-extrabold px-2.5 py-1 rounded-md bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase tracking-widest block mb-1">
                                Serving
                            </span>
                            <span class="text-sm font-black text-slate-300">
                                {{ ticket.counter_number || 'Window 1' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-else class="p-6 bg-slate-900/30 rounded-2xl border border-slate-800 text-center text-slate-500 text-xs">
                    Counter window queue lists are currently idle.
                </div>
            </div>

            <!-- Upcoming / Waiting Queue Section -->
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-slate-300 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                    Upcoming Queue Numbers
                </h3>
                <div v-if="waitingTickets && waitingTickets.length > 0" class="flex flex-wrap gap-4">
                    <div
                        v-for="ticket in waitingTickets"
                        :key="ticket.request_id"
                        class="p-4 bg-slate-900/40 rounded-xl border border-slate-800/60 flex items-center gap-4"
                    >
                        <span class="text-2xl font-black text-yellow-400">#{{ ticket.queue_number }}</span>
                        <div>
                            <span class="text-xs font-bold text-slate-400 block">{{ ticket.service?.office?.name || 'Office' }}</span>
                            <span class="text-[10px] text-slate-600">{{ ticket.service?.service_name || 'Service' }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="p-4 bg-slate-900/30 rounded-xl border border-slate-800 text-center text-slate-600 text-xs">
                    No upcoming tickets.
                </div>
            </div>

        </main>

        <!-- TV Footer -->
        <footer class="relative z-10 w-full px-8 py-4 border-t border-slate-800/80 bg-slate-900/40 text-center text-xs text-slate-500 flex flex-col sm:flex-row justify-between items-center gap-2">
            <p>&copy; 2026 QUEUE-MMS</p>
        </footer>

    </div>
</template>