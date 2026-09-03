<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    waitingQueue: { type: Array, default: () => [] },
    servingQueue: { type: Array, default: () => [] },
    offices: { type: Array, default: () => [] },
    openSessions: { type: Array, default: () => [] },
    selectedOffice: { type: Object, default: null },
});

let pollInterval = null;

onMounted(() => {
    pollInterval = setInterval(() => {
        router.reload({ only: ['waitingQueue', 'servingQueue', 'openSessions'], preserveScroll: true });
    }, 5000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
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

const manualForm = useForm({ service_id: '', category: 'regular' });
const showManualModal = ref(false);

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
                        class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/20 font-bold py-2 px-4 rounded-xl text-sm transition"
                    >
                        Recall Last
                    </button>
                    <button
                        @click="showManualModal = true"
                        class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold py-2 px-4 rounded-xl text-sm transition"
                    >
                        + Walk-in
                    </button>
                    <button
                        @click="callNext()"
                        class="bg-gradient-to-r from-amber-500 to-teal-600 text-slate-950 font-bold py-2 px-6 rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 transition text-sm"
                    >
                        Call Next Client
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl p-6">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Office Dashboard</h3>
                    <div class="flex flex-wrap gap-3">
                        <button
                            v-for="office in offices"
                            :key="office.office_id"
                            type="button"
                            @click="selectOffice(office)"
                            class="rounded-xl border px-4 py-2.5 text-sm font-semibold transition"
                            :class="selectedOffice && selectedOffice.office_id === office.office_id
                                ? 'border-amber-500/40 bg-amber-500/10 text-amber-300'
                                : 'border-slate-700 bg-slate-950/40 text-slate-300 hover:border-slate-600 hover:bg-slate-800/50'"
                        >
                            {{ office.name }}
                        </button>
                    </div>
                </div>

                <!-- Queue Session Toggles -->
                <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl p-6">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Queue Sessions</h3>
                    <div class="flex flex-wrap gap-3">
                        <div v-for="office in offices" :key="office.office_id" class="flex items-center gap-3 px-4 py-2.5 bg-slate-950/50 rounded-xl border border-slate-800/60">
                            <span class="text-sm font-semibold text-slate-200">{{ office.name }}</span>
                            <button
                                @click="toggleSession(office.office_id)"
                                class="relative w-11 h-6 rounded-full transition-colors duration-200"
                                :class="isSessionOpen(office.office_id) ? 'bg-amber-500' : 'bg-slate-700'"
                            >
                                <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200" :class="isSessionOpen(office.office_id) ? 'translate-x-5' : ''"></span>
                            </button>
                            <span class="text-[10px] font-bold uppercase tracking-widest" :class="isSessionOpen(office.office_id) ? 'text-amber-400' : 'text-slate-600'">
                                {{ isSessionOpen(office.office_id) ? 'Open' : 'Closed' }}
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
                            <option value="pwd">♿ PWD (Priority)</option>
                            <option value="senior">👴 Senior Citizen (Priority)</option>
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
