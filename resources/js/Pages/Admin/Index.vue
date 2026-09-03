<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    stats: { type: Object, default: () => ({}) },
});
</script>

<template>
    <AppLayout title="Admin Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Admin Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-5 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Offices</p>
                        <p class="text-3xl font-black text-amber-400 mt-1">{{ stats.totalOffices }}</p>
                        <p class="text-[10px] text-slate-600 mt-0.5">{{ stats.activeOffices }} active</p>
                    </div>
                    <div class="p-5 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Services</p>
                        <p class="text-3xl font-black text-blue-400 mt-1">{{ stats.totalServices }}</p>
                    </div>
                    <div class="p-5 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Users</p>
                        <p class="text-3xl font-black text-teal-400 mt-1">{{ stats.totalUsers }}</p>
                    </div>
                    <div class="p-5 bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl">
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Queue Today</p>
                        <p class="text-3xl font-black text-amber-400 mt-1">{{ stats.totalQueueToday }}</p>
                        <p class="text-[10px] text-slate-600 mt-0.5">{{ stats.waitingNow }} waiting / {{ stats.servingNow }} serving</p>
                    </div>
                </div>

                <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl p-6">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-5">Management</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <a :href="route('admin.offices.index')" class="block p-5 bg-slate-950/40 rounded-2xl border border-slate-800/60 hover:border-amber-500/20 hover:bg-amber-500/5 transition text-center">
                            <p class="font-bold text-white">Offices</p>
                            <p class="text-xs text-slate-500 mt-1">Manage campus offices</p>
                        </a>
                        <a :href="route('admin.services.index')" class="block p-5 bg-slate-950/40 rounded-2xl border border-slate-800/60 hover:border-amber-500/20 hover:bg-amber-500/5 transition text-center">
                            <p class="font-bold text-white">Services</p>
                            <p class="text-xs text-slate-500 mt-1">Manage office services</p>
                        </a>
                        <a :href="route('admin.users.index')" class="block p-5 bg-slate-950/40 rounded-2xl border border-slate-800/60 hover:border-amber-500/20 hover:bg-amber-500/5 transition text-center">
                            <p class="font-bold text-white">Users</p>
                            <p class="text-xs text-slate-500 mt-1">Manage system users</p>
                        </a>
                    </div>
                </div>

                <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl p-6">
                    <h3 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-5">Today's Queue Summary</h3>
                    <div class="grid grid-cols-3 gap-6 text-center">
                        <div>
                            <p class="text-4xl font-black text-yellow-400">{{ stats.waitingNow }}</p>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Waiting</p>
                        </div>
                        <div>
                            <p class="text-4xl font-black text-blue-400">{{ stats.servingNow }}</p>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Being Served</p>
                        </div>
                        <div>
                            <p class="text-4xl font-black text-amber-400">{{ stats.completedToday }}</p>
                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mt-1">Completed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
