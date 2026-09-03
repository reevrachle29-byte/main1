<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AudioNotification from '@/Components/AudioNotification.vue';

const props = defineProps({
    myQueueRequests: { type: Array, default: () => [] },
    waitingCount: { type: Number, default: 0 },
    servingCount: { type: Number, default: 0 },
    offices: { type: Array, default: () => [] },
    user: { type: Object, default: () => ({}) },
});

const audioRef = ref(null);
const form = useForm({ service_id: null, category: 'regular' });
const cancelConfirm = ref({
    open: false,
    requestId: null,
    queueNumber: null,
});
const ticketConfirm = ref({
    open: false,
    serviceId: null,
    serviceName: null,
});
let pollInterval = null;

onMounted(() => {
    pollInterval = setInterval(() => {
        router.reload({ only: ['myQueueRequests', 'waitingCount', 'servingCount', 'notifications'], preserveScroll: true });
    }, 5000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

const takeTicket = (serviceId, serviceName = null) => {
    if (!serviceId) return;

    ticketConfirm.value = {
        open: true,
        serviceId,
        serviceName,
    };
};

const confirmTakeTicket = () => {
    if (!ticketConfirm.value.serviceId) return;

    form.service_id = ticketConfirm.value.serviceId;
    form.category = 'regular';

    form.post('/kiosk/generate', {
        preserveScroll: true,
        onSuccess: () => {
            form.service_id = null;
            form.category = 'regular';
            ticketConfirm.value = { open: false, serviceId: null, serviceName: null };
            if (audioRef.value) audioRef.value.playChime();
        }
    });
};

const closeTicketConfirm = () => {
    ticketConfirm.value = { open: false, serviceId: null, serviceName: null };
};

const activeRequests = computed(() =>
    props.myQueueRequests.filter(r => ['waiting', 'called', 'serving'].includes(r.status))
);

const pastRequests = computed(() =>
    props.myQueueRequests.filter(r => ['completed', 'cancelled', 'skipped'].includes(r.status))
);

const statusColor = (status) => {
    const colors = {
        waiting: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
        called: 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        serving: 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        completed: 'bg-slate-500/10 text-slate-400 border-slate-500/20',
        cancelled: 'bg-red-500/10 text-red-400 border-red-500/20',
        skipped: 'bg-orange-500/10 text-orange-400 border-orange-500/20',
    };
    return colors[status] || 'bg-slate-500/10 text-slate-400';
};

const cancelTicket = (requestId, queueNumber = null) => {
    if (!requestId) return;

    cancelConfirm.value = {
        open: true,
        requestId,
        queueNumber,
    };
};

const confirmCancelTicket = () => {
    if (!cancelConfirm.value.requestId) return;

    router.post(route('queue.cancelOwn', { requestId: cancelConfirm.value.requestId }), {}, {
        preserveScroll: true,
        onSuccess: () => {
            cancelConfirm.value = { open: false, requestId: null, queueNumber: null };
            router.reload({ only: ['myQueueRequests', 'waitingCount', 'servingCount'], preserveScroll: true });
        }
    });
};

const closeCancelModal = () => {
    cancelConfirm.value = { open: false, requestId: null, queueNumber: null };
};
</script>

<template>
    <AppLayout title="Student Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Welcome, {{ user.name }}</h2>
        </template>

        <AudioNotification ref="audioRef" />

        <div v-if="ticketConfirm.open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
            <div class="w-full max-w-md rounded-3xl border border-slate-700 bg-slate-900/95 p-6 shadow-2xl">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-800 text-xl text-slate-200">?</div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Confirmation</p>
                        <h3 class="text-xl font-black text-white">Get ticket</h3>
                    </div>
                </div>

                <p class="text-sm leading-6 text-slate-300">
                    Are you sure you want to get a <span class="font-bold text-amber-400">regular</span> ticket for <span class="font-bold text-amber-400">{{ ticketConfirm.serviceName || 'this service' }}</span>?
                </p>

                <div class="mt-6 flex gap-3">
                    <button
                        type="button"
                        @click="closeTicketConfirm"
                        class="flex-1 rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm font-bold text-slate-200 hover:bg-slate-700 transition"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="confirmTakeTicket"
                        class="flex-1 rounded-xl border border-slate-700 bg-amber-500/10 px-4 py-3 text-sm font-bold text-amber-300 hover:bg-amber-500/20 transition"
                    >
                        Get Ticket
                    </button>
                </div>
            </div>
        </div>

        <div v-if="cancelConfirm.open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
            <div class="w-full max-w-md rounded-3xl border border-slate-700 bg-slate-900/95 p-6 shadow-2xl">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-800 text-xl text-slate-200">?</div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Confirmation</p>
                        <h3 class="text-xl font-black text-white">Cancel ticket</h3>
                    </div>
                </div>

                <p class="text-sm leading-6 text-slate-300">
                    Are you sure you want to cancel ticket <span class="font-bold text-amber-400">#{{ cancelConfirm.queueNumber ?? 'this ticket' }}</span>?
                </p>

                <div class="mt-6 flex gap-3">
                    <button
                        type="button"
                        @click="closeCancelModal"
                        class="flex-1 rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm font-bold text-slate-200 hover:bg-slate-700 transition"
                    >
                        Keep it
                    </button>
                    <button
                        type="button"
                        @click="confirmCancelTicket"
                        class="flex-1 rounded-xl border border-slate-700 bg-amber-500/10 px-4 py-3 text-sm font-bold text-amber-300 hover:bg-amber-500/20 transition"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-5 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Your Active Tickets</p>
                        <p class="text-3xl font-black text-amber-400 mt-1">{{ activeRequests.length }}</p>
                    </div>
                    <div class="p-5 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">People Waiting</p>
                        <p class="text-3xl font-black text-yellow-400 mt-1">{{ waitingCount }}</p>
                    </div>
                    <div class="p-5 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Currently Serving</p>
                        <p class="text-3xl font-black text-blue-400 mt-1">{{ servingCount }}</p>
                    </div>
                </div>

                <!-- Grab a Ticket -->
                <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl p-6">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-5">Grab a Queue Ticket</h3>
                    <div v-if="!offices || offices.length === 0" class="text-center py-12 text-slate-600 italic">
                        No offices with open queue sessions at the moment.
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div v-for="office in offices" :key="office.office_id" class="p-5 bg-slate-950/40 rounded-2xl border border-slate-800/60 hover:border-amber-500/20 transition">
                            <h4 class="font-bold text-white mb-3 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                {{ office.name }}
                            </h4>
                            <div class="space-y-2">
                                <button
                                    v-for="service in office.services"
                                    :key="service.service_id"
                                    @click="takeTicket(service.service_id, service.service_name)"
                                    :disabled="form.processing"
                                    class="w-full text-left px-4 py-2.5 rounded-xl bg-slate-900/50 hover:bg-amber-500/10 border border-slate-800/60 hover:border-amber-500/30 text-sm font-semibold text-slate-300 hover:text-amber-400 transition disabled:opacity-50"
                                >
                                    {{ service.service_name }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Queue Requests -->
                <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl p-6">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-5">Your Active Tickets</h3>
                    <div v-if="activeRequests.length === 0" class="text-center py-12 text-slate-600 italic">
                        No active queue tickets. Grab one above!
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-800">
                            <thead class="bg-slate-900/80">
                                <tr>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Ticket</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tracking</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Service</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Office</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/80">
                                <tr v-for="req in activeRequests" :key="req.request_id" class="hover:bg-slate-800/30 transition">
                                    <td class="px-5 py-3.5 text-lg font-black text-amber-400">#{{ req.queue_number }}</td>
                                    <td class="px-5 py-3.5 text-xs font-mono text-slate-500">{{ req.tracking_code }}</td>
                                    <td class="px-5 py-3.5 text-sm text-slate-300">{{ req.service?.service_name }}</td>
                                    <td class="px-5 py-3.5 text-sm text-slate-400">{{ req.service?.office?.name }}</td>
                                    <td class="px-5 py-3.5">
                                        <span :class="statusColor(req.status)" class="px-3 py-1 inline-flex text-[10px] leading-5 font-bold rounded-full uppercase border">{{ req.status }}</span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <button
                                            type="button"
                                            @click="cancelTicket(req.request_id, req.queue_number)"
                                            class="rounded-lg border border-slate-700 bg-slate-800/70 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-300 hover:bg-slate-700 hover:text-white transition"
                                        >
                                            Cancel
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Queue History -->
                <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl p-6">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-5">Queue History</h3>
                    <div v-if="pastRequests.length === 0" class="text-center py-12 text-slate-600 italic">
                        No past queue records.
                    </div>
                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-800">
                            <thead class="bg-slate-900/80">
                                <tr>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Ticket</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Service</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Office</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/80">
                                <tr v-for="req in pastRequests" :key="req.request_id" class="hover:bg-slate-800/30 transition">
                                    <td class="px-5 py-3.5 text-lg font-black text-slate-600">#{{ req.queue_number }}</td>
                                    <td class="px-5 py-3.5 text-sm text-slate-500">{{ req.service?.service_name }}</td>
                                    <td class="px-5 py-3.5 text-sm text-slate-600">{{ req.service?.office?.name }}</td>
                                    <td class="px-5 py-3.5">
                                        <span :class="statusColor(req.status)" class="px-3 py-1 inline-flex text-[10px] leading-5 font-bold rounded-full uppercase border">{{ req.status }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
