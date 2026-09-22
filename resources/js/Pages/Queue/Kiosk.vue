<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onUnmounted } from 'vue';
import QRCode from 'qrcode';

const props = defineProps({
    offices: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    service_id: null,
    category: 'regular' 
});

const showCategoryModal = ref(false);
const showTicketConfirm = ref(false);
const showHelpModal = ref(false);
const selectedServiceId = ref(null);
const pendingCategory = ref('regular');
const selectedServiceName = computed(() => {
    if (!selectedServiceId.value) return 'this service';

    for (const office of props.offices) {
        const service = office.services?.find(item => (item.service_id || item.id) === selectedServiceId.value);
        if (service) {
            return service.service_name || service.name;
        }
    }

    return 'this service';
});

const availabilityClass = (status) => ({
    open: 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30',
    busy: 'bg-amber-500/10 text-amber-300 border-amber-500/30',
    unavailable: 'bg-slate-800 text-slate-500 border-slate-700',
}[status] || 'bg-slate-800 text-slate-500 border-slate-700');

const page = usePage();
const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);
const ticket = computed(() => page.props.flash?.ticket);
const qrCodeUrl = ref('');
let resetTimer = null;

watch(ticket, (value) => {
    if (resetTimer) clearTimeout(resetTimer);
    if (value) {
        resetTimer = setTimeout(resetKiosk, 30000);
        const trackingUrl = new URL(route('queue.inquiry'), window.location.origin);
        trackingUrl.searchParams.set('tracking_code', value.tracking_code);
        QRCode.toDataURL(trackingUrl.toString()).then((url) => { qrCodeUrl.value = url; });
    } else {
        qrCodeUrl.value = '';
    }
});

onUnmounted(() => {
    if (resetTimer) clearTimeout(resetTimer);
});

const takeTicket = (serviceId) => {
    if (!serviceId) return;
    
    selectedServiceId.value = serviceId;
    showCategoryModal.value = true;
};

const submitTicket = (category) => {
    if (!selectedServiceId.value) return;

    pendingCategory.value = category;
    showCategoryModal.value = false;
    showTicketConfirm.value = true;
};

const confirmTicketRequest = () => {
    if (!selectedServiceId.value) return;

    form.service_id = selectedServiceId.value;
    form.category = pendingCategory.value;

    form.post(route('queue.generate'), {
        preserveScroll: true,
        onSuccess: () => {
            form.service_id = null;
            form.category = 'regular';
            selectedServiceId.value = null;
            pendingCategory.value = 'regular';
            showTicketConfirm.value = false;
        }
    });
};

const closeTicketConfirm = () => {
    showTicketConfirm.value = false;
    pendingCategory.value = 'regular';
};

const copyTrackingCode = async () => {
    if (ticket.value?.tracking_code) {
        await navigator.clipboard.writeText(ticket.value.tracking_code);
    }
};

const showCancelConfirm = ref(false);

const cancelCurrentTicket = () => {
    if (!ticket.value?.request_id) return;
    showCancelConfirm.value = true;
};

const confirmCancelCurrentTicket = () => {
    if (!ticket.value?.request_id) return;

    const form = useForm({});
    form.post(route('queue.cancelOwn', { requestId: ticket.value.request_id }), {
        preserveScroll: true,
        onSuccess: () => {
            showCancelConfirm.value = false;
            resetKiosk();
        }
    });
};

const closeCancelConfirm = () => {
    showCancelConfirm.value = false;
};

const resetKiosk = () => window.location.assign(route('queue.kiosk'));
const printTicket = () => window.print();
</script>

<template>
    <div class="relative min-h-screen bg-slate-950 font-sans text-slate-100 flex flex-col justify-between overflow-x-hidden">
        
        <!-- Ambient Glowing Backgrounds -->
        <div class="fixed inset-0 pointer-events-none z-0">
            <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-amber-500/10 blur-[130px] rounded-full"></div>
            <div class="absolute top-1/2 -left-40 w-[450px] h-[450px] bg-teal-500/10 blur-[150px] rounded-full"></div>
            <div class="absolute -bottom-20 -right-20 w-[500px] h-[500px] bg-amber-600/10 blur-[150px] rounded-full"></div>
        </div>

        <!-- Header Bar -->
        <header class="relative z-10 w-full max-w-7xl mx-auto px-6 py-6 flex justify-between items-center border-b border-slate-800/80">
            <a href="/" class="flex items-center gap-3.5" aria-label="Go to CPAC QUEUE-MMS main page">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-400 via-teal-500 to-amber-600 flex items-center justify-center shadow-lg shadow-amber-500/20 ring-1 ring-white/20">
                    <span class="font-black text-slate-950 text-2xl tracking-tighter">Q</span>
                </div>
                <div>
                    <h1 class="font-black text-xl leading-none tracking-tight text-white flex items-center gap-1.5">
                        CPAC <span class="text-xs px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 font-semibold tracking-normal">Kiosk</span>
                    </h1>
                    <span class="text-[11px] text-slate-400 font-medium tracking-wide">CPAC QUEUE-MMS</span>
                </div>
            </a>

            <!-- Quick Action Links -->
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    @click="showHelpModal = true"
                    class="px-4 py-2 text-xs font-bold text-amber-300 hover:text-amber-200 hover:bg-amber-500/10 rounded-xl transition-all border border-amber-500/30 flex items-center gap-2"
                >
                    Need Help?
                </button>
                <a href="/monitor" class="px-4 py-2 text-xs font-bold text-slate-300 hover:text-amber-400 hover:bg-slate-900 rounded-xl transition-all border border-transparent hover:border-slate-800 flex items-center gap-2">
                    Monitor Screen
                </a>
                <a href="/login" class="px-4 py-2 text-xs font-bold bg-slate-900 hover:bg-slate-850 text-slate-200 rounded-xl transition border border-slate-800 shadow-sm flex items-center gap-2">
                    Login
                </a>
            </div>
        </header>

        <!-- Assistance Modal -->
        <div v-if="showHelpModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-3xl border border-amber-500/30 bg-slate-900/95 p-7 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-amber-400">Kiosk Assistance</p>
                        <h2 class="mt-2 text-2xl font-black text-white">Need help getting a ticket?</h2>
                    </div>
                    <button type="button" @click="showHelpModal = false" class="text-2xl leading-none text-slate-500 hover:text-white" aria-label="Close help">&times;</button>
                </div>

                <div class="mt-6 space-y-4 text-sm text-slate-300">
                    <div class="flex gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-500/15 font-black text-amber-300">1</span>
                        <p>Ask the nearest office staff member for assistance.</p>
                    </div>
                    <div class="flex gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-500/15 font-black text-amber-300">2</span>
                        <p>Tell staff which office service you need.</p>
                    </div>
                    <div class="flex gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-amber-500/15 font-black text-amber-300">3</span>
                        <p>Staff can create and print your ticket for you.</p>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl border border-slate-700 bg-slate-950/60 p-4 text-center text-sm text-slate-400">
                    Please do not take another ticket if staff has already printed one for you.
                </div>

                <button type="button" @click="showHelpModal = false" class="mt-6 w-full rounded-xl bg-amber-500 px-4 py-3 text-sm font-black text-slate-950 hover:bg-amber-400">Close</button>
            </div>
        </div>

        <!-- Main Body -->
        <main class="relative z-10 w-full max-w-6xl mx-auto px-6 py-10 flex-grow flex flex-col justify-center items-center">
            
            <div class="text-center max-w-2xl mx-auto mb-10 space-y-3">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-900/80 border border-amber-500/30 text-amber-400 text-xs font-bold tracking-wider uppercase shadow-xl backdrop-blur-md">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                    Digital Ticketing Terminal
                </div>
                <h2 class="text-4xl sm:text-5xl font-black tracking-tight text-white">
                    Select a <span class="bg-gradient-to-r from-amber-400 via-teal-300 to-amber-500 bg-clip-text text-transparent">Service</span>
                </h2>
                <p class="text-slate-400 text-sm sm:text-base">
                    Choose the office transaction you need to issue your queue ticket number.
                </p>
            </div>

            <div v-if="errorMessage" class="w-full max-w-lg mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-2xl text-center text-red-400 text-sm font-semibold">
                {{ errorMessage }}
            </div>

            <div v-if="ticket" class="w-full max-w-lg mb-10 p-7 bg-amber-500/10 border border-amber-500/30 rounded-3xl backdrop-blur-xl shadow-2xl text-center space-y-5">
                <p class="text-xs font-bold uppercase tracking-widest text-amber-400">Ticket Confirmed</p>
                <p class="text-6xl font-black text-white">#{{ ticket.queue_number }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-3"><span class="block text-xs text-slate-500">Office</span>{{ ticket.office }}</div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-3"><span class="block text-xs text-slate-500">Position</span>{{ ticket.position }}</div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-3"><span class="block text-xs text-slate-500">Service</span>{{ ticket.service }}</div>
                    <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-3"><span class="block text-xs text-slate-500">Est. wait</span>{{ ticket.estimated_wait ? `~${ticket.estimated_wait} min` : 'Calculating' }}</div>
                </div>
                <div class="rounded-xl border border-amber-500/30 bg-slate-950/50 p-3">
                    <span class="block text-xs text-slate-500">Tracking code</span>
                    <strong class="font-mono text-amber-400">{{ ticket.tracking_code }}</strong>
                </div>
                <div v-if="qrCodeUrl" class="flex flex-col items-center gap-2 rounded-xl border border-slate-800 bg-white p-3">
                    <img :src="qrCodeUrl" alt="QR code for tracking this ticket" class="h-32 w-32" />
                    <span class="text-xs text-slate-700">Scan to track your queue</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <button type="button" @click="copyTrackingCode" class="rounded-xl bg-slate-800 px-4 py-3 text-sm font-bold text-slate-200 hover:bg-slate-700">Copy code</button>
                    <button type="button" @click="printTicket" class="rounded-xl bg-teal-600 px-4 py-3 text-sm font-bold text-white hover:bg-teal-700">Print</button>
                    <button type="button" @click="cancelCurrentTicket" class="rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-3 text-sm font-bold text-slate-200 hover:bg-slate-700 hover:text-white transition">Cancel</button>
                </div>
            </div>

            <div v-if="showCancelConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
                <div class="w-full max-w-md rounded-3xl border border-slate-700 bg-slate-900/95 p-6 shadow-2xl">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-800 text-xl text-slate-200">?</div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Confirmation</p>
                            <h3 class="text-xl font-black text-white">Cancel ticket</h3>
                        </div>
                    </div>

                    <p class="text-sm leading-6 text-slate-300">
                        Are you sure you want to cancel ticket <span class="font-bold text-amber-400">#{{ ticket?.queue_number }}</span>?
                    </p>

                    <div class="mt-6 flex gap-3">
                        <button
                            type="button"
                            @click="closeCancelConfirm"
                            class="flex-1 rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm font-bold text-slate-200 hover:bg-slate-700 transition"
                        >
                            Keep it
                        </button>
                        <button
                            type="button"
                            @click="confirmCancelCurrentTicket"
                            class="flex-1 rounded-xl border border-slate-700 bg-amber-500/10 px-4 py-3 text-sm font-bold text-amber-300 hover:bg-amber-500/20 transition"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty Data Alert (If DB has no offices) -->
            <div v-if="!offices || offices.length === 0" class="w-full p-8 text-center bg-slate-900/50 rounded-2xl border border-slate-800 text-slate-400">
                <p class="font-semibold text-base">No offices or services found in the database.</p>
                <p class="text-xs text-slate-500 mt-1">Make sure you have seeded or inserted rows into the <code>offices</code> and <code>services</code> tables.</p>
            </div>

            <!-- Dynamic Offices & Services Grid -->
            <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-8 w-full">
                <div 
                    v-for="office in offices" 
                    :key="office.office_id || office.id" 
                    class="p-8 bg-slate-900/60 rounded-3xl border border-slate-800/80 backdrop-blur-xl shadow-2xl flex flex-col justify-between space-y-7"
                >
                    <div class="space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800/80 pb-4">
                            <h3 class="text-2xl font-bold text-white flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                                {{ office.name }}
                            </h3>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-800 text-slate-400">
                                {{ office.services?.length || 0 }} Services
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <span :class="availabilityClass(office.availability_status)" class="rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-wider">
                                {{ office.availability_label }}
                            </span>
                            <span class="text-right text-xs text-slate-500">{{ office.availability_message }}</span>
                        </div>
                        <p class="text-xs text-slate-500">Office hours: {{ office.office_hours }}</p>

                        <!-- Services List -->
                        <div class="grid grid-cols-1 gap-4">
                            <button 
                                v-for="service in office.services" 
                                :key="service.service_id || service.id"
                                @click="takeTicket(service.service_id || service.id)"
                                :disabled="form.processing || office.availability_status === 'unavailable'"
                                :aria-label="`Get queue ticket for ${service.service_name || service.name}`"
                                class="group relative w-full min-h-24 p-5 rounded-2xl bg-slate-950/50 hover:bg-amber-500/10 border border-slate-800 hover:border-amber-500/40 text-left transition-all duration-300 flex items-center justify-between gap-5 active:scale-[0.98] cursor-pointer disabled:cursor-not-allowed disabled:opacity-45 disabled:hover:bg-slate-950/50 disabled:hover:border-slate-800"
                            >
                                <span class="font-bold text-lg leading-snug text-slate-200 group-hover:text-amber-400 transition-colors">
                                    {{ service.service_name || service.name }}
                                </span>

                                <span class="shrink-0 rounded-xl bg-amber-500/10 px-4 py-3 text-xs font-black uppercase tracking-wider text-amber-300 group-hover:bg-amber-500 group-hover:text-slate-950 transition-colors">
                                    Get Queue Ticket
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <!-- Footer -->
        <footer class="relative z-10 w-full max-w-7xl mx-auto px-6 py-6 border-t border-slate-800/80 text-center text-xs text-slate-500">
            <p>&copy; 2026 QUEUE-MMS. All Rights Reserved.</p>
        </footer>

        <!-- Category Selection Modal -->
        <div v-if="showCategoryModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-slate-900/95 border border-slate-800 rounded-3xl shadow-2xl max-w-md w-full p-8 space-y-6">
                <div class="text-center space-y-2">
                    <h3 class="text-2xl font-bold text-white">Select Category</h3>
                    <p class="text-sm text-slate-400">Choose a queue category</p>
                </div>

                <div class="space-y-3">
                    <button
                        @click="submitTicket('pwd')"
                        :disabled="form.processing"
                        class="w-full p-4 rounded-2xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/40 hover:border-amber-500/60 text-left transition-all group"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center text-xl">
                                ♿
                            </div>
                            <div>
                                <div class="font-bold text-amber-400 group-hover:text-amber-300">PWD</div>
                                <div class="text-xs text-slate-500">Persons with disabilities</div>
                            </div>
                        </div>
                    </button>

                    <button
                        @click="submitTicket('senior')"
                        :disabled="form.processing"
                        class="w-full p-4 rounded-2xl bg-orange-500/10 hover:bg-orange-500/20 border border-orange-500/40 hover:border-orange-500/60 text-left transition-all group"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-orange-500/20 flex items-center justify-center text-xl">
                                👴
                            </div>
                            <div>
                                <div class="font-bold text-orange-400 group-hover:text-orange-300">Senior</div>
                                <div class="text-xs text-slate-500">Senior citizen</div>
                            </div>
                        </div>
                    </button>

                    <button
                        @click="submitTicket('regular')"
                        :disabled="form.processing"
                        class="w-full p-4 rounded-2xl bg-slate-800/50 hover:bg-slate-800 border border-slate-700 hover:border-slate-600 text-left transition-all group"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-slate-700/50 flex items-center justify-center text-xl">
                                👤
                            </div>
                            <div>
                                <div class="font-bold text-slate-300 group-hover:text-slate-200">Regular</div>
                                <div class="text-xs text-slate-500">General public</div>
                            </div>
                        </div>
                    </button>
                </div>

                <!-- Cancel Button -->
                <button
                    @click="showCategoryModal = false"
                    class="w-full p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-semibold transition-all text-sm"
                >
                    Cancel
                </button>
            </div>
        </div>

        <!-- Ticket Confirmation Modal -->
        <div v-if="showTicketConfirm" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
            <div class="w-full max-w-md rounded-3xl border border-slate-700 bg-slate-900/95 p-6 shadow-2xl">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-800 text-xl text-slate-200">?</div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Confirmation</p>
                        <h3 class="text-xl font-black text-white">Get ticket</h3>
                    </div>
                </div>

                <p class="text-sm leading-6 text-slate-300">
                    Are you sure you want to get a <span class="font-bold text-amber-400">{{ pendingCategory }}</span> ticket for <span class="font-bold text-amber-400">{{ selectedServiceName }}</span>?
                </p>

                <div class="mt-6 flex gap-3">
                    <button
                        type="button"
                        @click="closeTicketConfirm"
                        class="flex-1 rounded-xl border border-slate-700 bg-slate-800 px-4 py-3 text-sm font-bold text-slate-200 hover:bg-slate-700 transition"
                    >
                        Keep it
                    </button>
                    <button
                        type="button"
                        @click="confirmTicketRequest"
                        class="flex-1 rounded-xl border border-slate-700 bg-amber-500/10 px-4 py-3 text-sm font-bold text-amber-300 hover:bg-amber-500/20 transition"
                    >
                        Get Ticket
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>