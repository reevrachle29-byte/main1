<script setup>
import { ref, computed } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    queueRequests: { type: Object, default: () => ({ data: [], links: [] }) },
    stats: { type: Object, default: () => ({}) },
    offices: { type: Array, default: () => [] },
    recentAuditLogs: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success);

const filterForm = useForm({
    office_id: props.filters.office_id || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    status: props.filters.status || '',
});

const applyFilters = () => {
    filterForm.get(route('admin.reports.index'), { preserveState: true });
};

const clearFilters = () => {
    filterForm.reset();
    router.get(route('admin.reports.index'));
};

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

const activeTab = ref('queue');
</script>

<template>
    <AppLayout title="Reports & Audit Trail">
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Reports & Audit Trail</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-5 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Today</p>
                        <p class="text-3xl font-black text-white mt-1">{{ stats.totalToday || 0 }}</p>
                    </div>
                    <div class="p-5 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Completed</p>
                        <p class="text-3xl font-black text-amber-400 mt-1">{{ stats.completedToday || 0 }}</p>
                    </div>
                    <div class="p-5 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Waiting Now</p>
                        <p class="text-3xl font-black text-yellow-400 mt-1">{{ stats.waitingNow || 0 }}</p>
                    </div>
                    <div class="p-5 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Avg. Wait</p>
                        <p class="text-3xl font-black text-teal-400 mt-1">{{ stats.avgWaitMinutes ? Math.round(stats.avgWaitMinutes) + 'm' : '—' }}</p>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex gap-1 bg-slate-900/60 p-1 rounded-xl border border-slate-800/80 w-fit">
                    <button @click="activeTab = 'queue'" :class="activeTab === 'queue' ? 'bg-amber-500/10 text-amber-400' : 'text-slate-500 hover:text-white'" class="px-4 py-2 text-sm font-bold rounded-lg transition">Queue History</button>
                    <button @click="activeTab = 'audit'" :class="activeTab === 'audit' ? 'bg-amber-500/10 text-amber-400' : 'text-slate-500 hover:text-white'" class="px-4 py-2 text-sm font-bold rounded-lg transition">Audit Trail</button>
                </div>

                <!-- Queue History Tab -->
                <div v-if="activeTab === 'queue'" class="space-y-6">
                    <!-- Filters -->
                    <div class="p-5 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl">
                        <form @submit.prevent="applyFilters" class="flex flex-wrap gap-4 items-end">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Office</label>
                                <select v-model="filterForm.office_id" class="bg-slate-950/50 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-200 focus:border-amber-500 outline-none">
                                    <option value="">All Offices</option>
                                    <option v-for="office in offices" :key="office.office_id" :value="office.office_id">{{ office.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Date From</label>
                                <input v-model="filterForm.date_from" type="date" class="bg-slate-950/50 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-200 focus:border-amber-500 outline-none" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Date To</label>
                                <input v-model="filterForm.date_to" type="date" class="bg-slate-950/50 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-200 focus:border-amber-500 outline-none" />
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Status</label>
                                <select v-model="filterForm.status" class="bg-slate-950/50 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-200 focus:border-amber-500 outline-none">
                                    <option value="">All</option>
                                    <option value="waiting">Waiting</option>
                                    <option value="called">Called</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="skipped">Skipped</option>
                                </select>
                            </div>
                            <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-lg transition">Filter</button>
                            <button type="button" @click="clearFilters" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-bold rounded-lg transition">Clear</button>
                        </form>
                    </div>

                    <!-- Table -->
                    <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-800">
                                <thead class="bg-slate-900/80">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Ticket</th>
                                        <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tracking</th>
                                        <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">User</th>
                                        <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Service</th>
                                        <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Office</th>
                                        <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status</th>
                                        <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Wait</th>
                                        <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Time</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/80">
                                    <tr v-for="req in queueRequests.data" :key="req.request_id" class="hover:bg-slate-800/30 transition">
                                        <td class="px-5 py-3.5 text-sm font-black text-amber-400">#{{ req.queue_number }}</td>
                                        <td class="px-5 py-3.5 text-xs font-mono text-slate-400">{{ req.tracking_code }}</td>
                                        <td class="px-5 py-3.5 text-sm text-slate-300">{{ req.user?.name || '—' }}</td>
                                        <td class="px-5 py-3.5 text-sm text-slate-300">{{ req.service?.service_name }}</td>
                                        <td class="px-5 py-3.5 text-sm text-slate-400">{{ req.service?.office?.name }}</td>
                                        <td class="px-5 py-3.5">
                                            <span :class="statusColor(req.status)" class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase border">{{ req.status }}</span>
                                        </td>
                                        <td class="px-5 py-3.5 text-sm text-slate-400">{{ req.transaction?.wait_minutes ? req.transaction.wait_minutes + 'm' : '—' }}</td>
                                        <td class="px-5 py-3.5 text-xs text-slate-500">{{ req.requested_at }}</td>
                                    </tr>
                                    <tr v-if="!queueRequests.data || queueRequests.data.length === 0">
                                        <td colspan="8" class="px-5 py-12 text-center text-sm text-slate-500">No records found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Audit Trail Tab -->
                <div v-if="activeTab === 'audit'" class="bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-800">
                            <thead class="bg-slate-900/80">
                                <tr>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Time</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">User</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Action</th>
                                    <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Description</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/80">
                                <tr v-for="log in recentAuditLogs" :key="log.log_id" class="hover:bg-slate-800/30 transition">
                                    <td class="px-5 py-3.5 text-xs text-slate-500 whitespace-nowrap">{{ log.created_at }}</td>
                                    <td class="px-5 py-3.5 text-sm text-slate-300">{{ log.user?.name || 'System' }}</td>
                                    <td class="px-5 py-3.5 text-sm font-semibold text-slate-200">{{ log.action }}</td>
                                    <td class="px-5 py-3.5 text-sm text-slate-400">{{ log.description }}</td>
                                </tr>
                                <tr v-if="!recentAuditLogs || recentAuditLogs.length === 0">
                                    <td colspan="4" class="px-5 py-12 text-center text-sm text-slate-500">No audit logs found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
