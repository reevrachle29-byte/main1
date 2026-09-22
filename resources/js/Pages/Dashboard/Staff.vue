<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AudioNotification from '@/Components/AudioNotification.vue';

const props = defineProps({
    waitingQueue: { type: Array, default: () => [] },
    servingQueue: { type: Array, default: () => [] },
    recentTicketHistory: { type: Array, default: () => [] },
    offices: { type: Array, default: () => [] },
    openSessions: { type: Array, default: () => [] },
    selectedOffice: { type: Object, default: null },
    officeNotifications: { type: Array, default: () => [] },
});

let pollInterval = null;
const audioRef = ref(null);
const currentAlert = ref(null);
const seenNotificationIds = new Set();
let alertTimeout = null;

const showNewNotification = (notifications) => {
    const newest = notifications.find(notification => !seenNotificationIds.has(notification.notif_id));

    notifications.forEach(notification => seenNotificationIds.add(notification.notif_id));

    if (!newest) return;

    currentAlert.value = newest;
    if (audioRef.value) audioRef.value.playChime();

    if (alertTimeout) clearTimeout(alertTimeout);
    alertTimeout = setTimeout(() => { currentAlert.value = null; }, 5000);
};

watch(() => props.officeNotifications, showNewNotification, { deep: true });

onMounted(() => {
    props.officeNotifications.forEach(notification => seenNotificationIds.add(notification.notif_id));
    pollInterval = setInterval(() => {
        router.reload({ only: ['waitingQueue', 'servingQueue', 'recentTicketHistory', 'openSessions', 'officeNotifications'], preserveScroll: true });
    }, 5000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
    if (alertTimeout) clearTimeout(alertTimeout);
});

const callNext = () => {
    router.post(route('queue.callNext'));
};

const recallLast = () => {
    router.post(route('queue.recallLast'));
};

const completeTicket = (id) => {
    router.post(route('queue.complete', id));
};

const skipTicket = (id) => {
    router.post(route('queue.skip', id));
};

const cancelTicket = (id) => {
    router.post(route('queue.cancel', id));
};

const isSessionOpen = (officeId) => {
    return props.openSessions.some(s => s.office_id === officeId);
};

const toggleSession = (officeId) => {
    if (isSessionOpen(officeId)) {
        router.post(route('queueSession.close'), {
            office_id: officeId,
            preserveScroll: true,
        }, {
            onSuccess: () => {
                router.reload({ only: ['waitingQueue', 'servingQueue', 'openSessions'], preserveScroll: true });
            },
        });
    } else {
        router.post(route('queueSession.open'), {
            office_id: officeId,
            preserveScroll: true,
        }, {
            onSuccess: () => {
                router.reload({ only: ['waitingQueue', 'servingQueue', 'openSessions'], preserveScroll: true });
            },
        });
    }
};

const selectOffice = (office) => {
    if (!office?.office_id) return;

    router.get(route('dashboard.staff', { officeId: office.office_id }), {}, {
        preserveScroll: true,
    });
};

const officeIcon = (office) => {
    const name = String(office?.name || '').toLowerCase();

    if (name.includes('registrar') || name.includes('records')) {
        return 'M6 2h9l3 3v17H6V2zm9 1.5V6h2.5M9 11h6m-6 4h6m-6 4h4';
    }

    if (name.includes('library')) {
        return 'M4 5.5A2.5 2.5 0 016.5 3H20v15H6.5A2.5 2.5 0 004 15.5v-10zM4 15.5A2.5 2.5 0 016.5 13H20M8 6h8m-8 3h6';
    }

    if (name.includes('finance') || name.includes('account')) {
        return 'M12 3v18m4-15.5c-.8-.7-2.1-1.2-4-1.2-2.8 0-4.5 1.2-4.5 3s1.7 3 4.5 3 4.5 1.2 4.5 3-1.7 3-4.5 3c-1.9 0-3.2-.5-4-1.2';
    }

    if (name.includes('admission') || name.includes('student') || name.includes('human resource')) {
        return 'M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m7-10a4 4 0 100-8 4 4 0 000 8zm7-1a4 4 0 010 8m4 3v-2a4 4 0 00-3-3.87';
    }

    if (name.includes('health') || name.includes('clinic') || name.includes('medical')) {
        return 'M12 3a9 9 0 100 18 9 9 0 000-18zm0 5v8m-4-4h8';
    }

    if (name.includes('it') || name.includes('information') || name.includes('computer')) {
        return 'M4 5h16v11H4V5zm-2 14h20M9 19h6';
    }

    return 'M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 7h1m4 0h1M9 11h1m4 0h1M9 15h1m4 0h1M10 21v-3h4v3';
};

const manualForm = useForm({ service_id: '', category: 'regular' });
const showManualModal = ref(false);
const page = usePage();
const isAdmin = computed(() => ['admin', 'administrator'].includes(String(page.props.auth?.user?.role || '').toLowerCase()));
const hideWalkInTicket = ref(false);
const walkInTicket = computed(() => {
    const ticket = page.props.flash?.ticket;
    return ticket?.walk_in && !hideWalkInTicket.value ? ticket : null;
});

watch(() => page.props.flash?.ticket, () => { hideWalkInTicket.value = false; });

const printWalkInTicket = () => window.print();

const submitManual = () => {
    manualForm.post(route('queue.manualGenerate'), {
        onSuccess: () => { showManualModal.value = false; manualForm.reset(); },
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Staff Queue Console">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white leading-tight">
                    {{ selectedOffice ? selectedOffice.name + ' Queue Console' : 'Staff Queue Console' }}
                </h2>
                <div class="flex gap-2">
                    <button
                        @click="recallLast"
                        class="inline-flex items-center gap-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/20 font-bold py-2 px-4 rounded-xl text-sm transition"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h7V3m0 7A9 9 0 1112 21a9 9 0 01-8.49-6" />
                        </svg>
                        Recall Last
                    </button>
                    <button
                        @click="showManualModal = true"
                        class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold py-2 px-4 rounded-xl text-sm transition"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 20a6 6 0 00-12 0m6-8a4 4 0 100-8 4 4 0 000 8zm7-3v6m3-3h-6" />
                        </svg>
                        Walk-in
                    </button>
                    <button
                        @click="callNext()"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-teal-600 text-slate-950 font-bold py-2 px-6 rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 transition text-sm"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M18.364 5.636a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707A1 1 0 0112 5v14a1 1 0 01-1.707.707L5.586 15z" />
                        </svg>
                        Call Next Client
                    </button>
                </div>
            </div>
        </template>

        <AudioNotification ref="audioRef" />

        <div v-if="walkInTicket" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4 backdrop-blur-sm">
            <div class="walk-in-print-ticket w-full max-w-sm rounded-2xl border border-slate-700 bg-white p-6 text-center text-slate-900 shadow-2xl">
                <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-500">CPAC Queue Ticket</p>
                <p class="mt-4 text-7xl font-black tracking-tight">#{{ walkInTicket.queue_number }}</p>
                <div class="mt-4 space-y-2 border-y border-slate-200 py-4 text-sm">
                    <p><span class="font-bold">Office:</span> {{ walkInTicket.office }}</p>
                    <p><span class="font-bold">Service:</span> {{ walkInTicket.service }}</p>
                    <p><span class="font-bold">Position:</span> {{ walkInTicket.position }}</p>
                    <p><span class="font-bold">Estimated wait:</span> ~{{ walkInTicket.estimated_wait }} minutes</p>
                </div>
                <p class="mt-4 text-xs text-slate-500">Tracking code</p>
                <p class="font-mono text-lg font-bold tracking-widest">{{ walkInTicket.tracking_code }}</p>
                <div class="mt-6 flex gap-3 print:hidden">
                    <button type="button" @click="printWalkInTicket" class="flex-1 rounded-xl bg-amber-500 px-4 py-3 text-sm font-bold text-slate-950 hover:bg-amber-400">Print Ticket</button>
                    <button type="button" @click="hideWalkInTicket = true" class="rounded-xl bg-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-300">Close</button>
                </div>
            </div>
        </div>

        <Transition name="office-alert">
            <div v-if="currentAlert" class="fixed right-5 top-5 z-50 w-[min(24rem,calc(100vw-2.5rem))] rounded-2xl border border-amber-400/40 bg-slate-900/95 p-4 shadow-2xl shadow-amber-950/30 backdrop-blur-xl">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/15 text-amber-300">!</div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-amber-400">New Queue Ticket</p>
                        <p class="mt-1 text-sm font-semibold leading-5 text-white">{{ currentAlert.message }}</p>
                    </div>
                    <button type="button" @click="currentAlert = null" class="ml-auto text-slate-500 hover:text-white" aria-label="Dismiss notification">&times;</button>
                </div>
            </div>
        </Transition>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <div v-if="isAdmin" class="bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl p-6">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Office Dashboard</h3>
                    <div class="flex flex-wrap gap-3">
                        <button
                            v-for="office in offices"
                            :key="office.office_id"
                            type="button"
                            @click="selectOffice(office)"
                            class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition"
                            :class="selectedOffice && selectedOffice.office_id === office.office_id
                                ? 'border-amber-500/40 bg-amber-500/10 text-amber-300'
                                : 'border-slate-700 bg-slate-950/40 text-slate-300 hover:border-slate-600 hover:bg-slate-800/50'"
                        >
                            <svg class="h-4 w-4 shrink-0" :class="selectedOffice && selectedOffice.office_id === office.office_id ? 'text-amber-400' : 'text-slate-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="officeIcon(office)" />
                            </svg>
                            {{ office.name }}
                        </button>
                    </div>
                </div>

                <!-- Queue Session Toggles -->
                <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl p-6">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Queue Sessions</h3>
                    <p class="text-sm text-slate-400 mb-5">Open a session to make an office available for new queue tickets. Close it when the office is no longer accepting requests.</p>
                    <div class="flex flex-wrap gap-3">
                        <div v-for="office in offices" :key="office.office_id" class="flex items-center gap-3 px-4 py-2.5 bg-slate-950/50 rounded-xl border border-slate-800/60">
                            <svg class="h-4 w-4 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="officeIcon(office)" />
                            </svg>
                            <span class="text-sm font-semibold text-slate-200">{{ office.name }}</span>
                            <button
                                @click="toggleSession(office.office_id)"
                                class="relative w-11 h-6 rounded-full transition-colors duration-200"
                                :class="isSessionOpen(office.office_id) ? 'bg-amber-500' : 'bg-slate-700'"
                            >
                                <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200" :class="isSessionOpen(office.office_id) ? 'translate-x-5' : ''"></span>
                            </button>
                            <span class="whitespace-nowrap text-[10px] font-bold uppercase tracking-widest" :class="isSessionOpen(office.office_id) ? 'text-amber-400' : 'text-slate-600'">
                                {{ isSessionOpen(office.office_id) ? 'Open - accepting tickets' : 'Closed - not accepting tickets' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <!-- Currently Serving -->
                    <div class="lg:col-span-1 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl p-6">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Currently Serving</h3>

                        <div v-if="servingQueue && servingQueue.length > 0" class="space-y-4">
                            <div v-for="active in servingQueue" :key="active.request_id" class="p-6 bg-amber-500/5 border border-amber-500/20 rounded-2xl text-center">
                                <div class="text-[10px] font-bold text-amber-400 uppercase tracking-widest">Ticket Number</div>
                                <div class="text-5xl font-black text-amber-400 my-2">#{{ active.queue_number }}</div>
                                <div class="text-sm font-bold text-slate-300 mb-1">{{ active.service?.service_name }}</div>
                                <div class="text-[10px] font-mono text-slate-500 mb-4">{{ active.tracking_code }}</div>

                                <div class="flex flex-col space-y-2">
                                    <button @click="completeTicket(active.request_id)" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-4 rounded-xl transition text-sm">
                                        Mark Completed
                                    </button>
                                    <div class="grid grid-cols-2 gap-2">
                                        <button @click="skipTicket(active.request_id)" class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/20 font-bold py-2 px-3 rounded-xl text-sm transition">Skip</button>
                                        <button @click="cancelTicket(active.request_id)" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 font-bold py-2 px-3 rounded-xl text-sm transition">Cancel</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-12 text-slate-600 italic bg-slate-950/30 rounded-2xl border border-dashed border-slate-800">
                            No active client. Click "Call Next Client" to start.
                        </div>
                    </div>

                    <!-- Waiting Queue -->
                    <div class="lg:col-span-2 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl p-6">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">
                            Waiting Line ({{ waitingQueue ? waitingQueue.length : 0 }})
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-800">
                                <thead class="bg-slate-900/80">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Ticket</th>
                                        <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tracking</th>
                                        <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Category</th>
                                        <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Service</th>
                                        <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/80">
                                    <tr v-for="student in waitingQueue" :key="student.request_id" class="hover:bg-slate-800/30 transition">
                                        <td class="px-5 py-3.5 text-xl font-black text-amber-400">#{{ student.queue_number }}</td>
                                        <td class="px-5 py-3.5 text-xs font-mono text-slate-500">{{ student.tracking_code }}</td>
                                        <td class="px-5 py-3.5">
                                            <span v-if="student.category === 'pwd'" class="px-3 py-1 inline-flex text-[10px] leading-5 font-bold rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20 uppercase">
                                                ♿ PWD
                                            </span>
                                            <span v-else-if="student.category === 'senior'" class="px-3 py-1 inline-flex text-[10px] leading-5 font-bold rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 uppercase">
                                                👴 Senior
                                            </span>
                                            <span v-else class="px-3 py-1 inline-flex text-[10px] leading-5 font-bold rounded-full bg-slate-500/10 text-slate-400 border border-slate-500/20 uppercase">
                                                Regular
                                            </span>
                                        </td>
                                        <td class="px-5 py-3.5 text-sm text-slate-300">{{ student.service?.service_name }}</td>
                                        <td class="px-5 py-3.5">
                                            <span class="px-3 py-1 inline-flex text-[10px] leading-5 font-bold rounded-full bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 uppercase">
                                                {{ student.status }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!waitingQueue || waitingQueue.length === 0">
                                        <td colspan="5" class="text-center py-12 text-slate-600 italic">
                                            Queue is empty.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl overflow-hidden">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-800 px-6 py-5">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500">Recent Ticket History</h3>
                            <p class="text-xs text-slate-600 mt-1">Latest completed, skipped, and cancelled tickets for this office</p>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-600">{{ recentTicketHistory.length }} records</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-800">
                            <thead class="bg-slate-900/80">
                                <tr>
                                    <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-500">Ticket</th>
                                    <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-500">Service</th>
                                    <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-500">Status</th>
                                    <th class="px-6 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-500">Requested</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/80">
                                <tr v-for="ticket in recentTicketHistory" :key="ticket.request_id" class="hover:bg-slate-800/30 transition">
                                    <td class="px-6 py-3.5 text-sm font-black text-amber-400">#{{ ticket.queue_number }}</td>
                                    <td class="px-6 py-3.5 text-sm text-slate-300">{{ ticket.service?.service_name || '—' }}</td>
                                    <td class="px-6 py-3.5">
                                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-full border" :class="ticket.status === 'completed' ? 'bg-teal-500/10 text-teal-400 border-teal-500/20' : ticket.status === 'cancelled' ? 'bg-red-500/10 text-red-400 border-red-500/20' : 'bg-orange-500/10 text-orange-400 border-orange-500/20'">
                                            {{ ticket.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 text-xs text-slate-500">{{ ticket.requested_at }}</td>
                                </tr>
                                <tr v-if="!recentTicketHistory.length">
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-600">No ticket history yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Walk-in Modal -->
        <div v-if="showManualModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl">
                <h3 class="text-lg font-bold text-white mb-4">Generate Walk-in Ticket</h3>
                <form @submit.prevent="submitManual" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Service</label>
                        <select v-model="manualForm.service_id" required class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 focus:border-amber-500 outline-none">
                            <option value="">Select a service</option>
                            <template v-for="office in offices" :key="'svc-' + office.office_id">
                                <option v-for="service in office.services" :key="service.service_id" :value="service.service_id">
                                    {{ office.name }} — {{ service.service_name }}
                                </option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Category</label>
                        <select v-model="manualForm.category" class="w-full bg-slate-950/50 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 focus:border-amber-500 outline-none">
                            <option value="pwd">PWD</option>
                            <option value="senior">Senior</option>
                            <option value="regular">Regular</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-800">
                        <button type="button" @click="showManualModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-sm font-semibold transition">Cancel</button>
                        <button type="submit" :disabled="manualForm.processing" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold transition disabled:opacity-50">
                            {{ manualForm.processing ? 'Creating...' : 'Generate Ticket' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@media print {
    :global(body) * {
        visibility: hidden;
    }

    .walk-in-print-ticket,
    .walk-in-print-ticket * {
        visibility: visible;
    }

    .walk-in-print-ticket {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        max-width: none;
        border: 0;
        box-shadow: none;
    }
}
</style>
