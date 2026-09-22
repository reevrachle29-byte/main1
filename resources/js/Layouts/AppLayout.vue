<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Banner from '@/Components/Banner.vue';
import FlashToast from '@/Components/FlashToast.vue';

defineProps({
    title: String,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const role = computed(() => (user.value?.role || '').toLowerCase());
const isAdmin = computed(() => role.value === 'admin' || role.value === 'administrator');
const isStaff = computed(() => role.value === 'staff' || role.value === 'employee' || isAdmin.value);

const showingMobileMenu = ref(false);

const logout = () => {
    router.post(route('logout'));
};

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
        return;
    }

    router.get(route('dashboard'));
};

const initials = computed(() => {
    if (!user.value?.name) return '?';
    return user.value.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
});
</script>

<template>
    <div class="min-h-screen bg-slate-950 font-sans">
        <Head :title="title" />
        <Banner />
        <FlashToast />

        <!-- Ambient Background -->
        <div class="fixed inset-0 pointer-events-none z-0">
            <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-amber-500/5 blur-[130px] rounded-full"></div>
        </div>

        <!-- Navbar -->
        <nav class="relative z-20 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Left: Logo + Nav Links -->
                    <div class="flex items-center gap-4">
                        <button
                            v-if="!route().current('dashboard')"
                            type="button"
                            @click="goBack"
                            aria-label="Go back"
                            class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-900/60 p-2 text-slate-200 transition hover:border-slate-500 hover:bg-slate-800"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <!-- Logo -->
                        <Link :href="route('dashboard')" class="flex items-center gap-2.5 shrink-0">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-400 to-amber-500 flex items-center justify-center shadow-lg shadow-amber-500/20 ring-1 ring-white/10">
                                <span class="font-black text-slate-950 text-lg tracking-tighter">Q</span>
                            </div>
                            <span class="font-black text-lg text-white tracking-tight hidden sm:block">QUEUE-MMS</span>
                        </Link>

                        <!-- Desktop Nav Links -->
                        <div class="hidden sm:flex items-center gap-1">
                            <Link
                                v-if="!isStaff"
                                :href="route('dashboard')"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold rounded-lg transition"
                                :class="route().current('dashboard') ? 'bg-amber-400/10 text-amber-400' : 'text-slate-400 hover:text-white hover:bg-slate-800'"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13h7V3H3v10zm0 8h7v-4H3v4zm11 0h7V11h-7v10zm0-14h7V3h-7v4z" /></svg>
                                Dashboard
                            </Link>
                            <Link
                                v-if="isStaff"
                                :href="route('dashboard.staff')"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold rounded-lg transition"
                                :class="route().current('dashboard.staff') ? 'bg-amber-400/10 text-amber-400' : 'text-slate-400 hover:text-white hover:bg-slate-800'"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 6V4a2 2 0 012-2h2a2 2 0 012 2v2m-9 0h14a2 2 0 012 2v11a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2zm-7 5h14m-7 0v3" /></svg>
                                Staff Dashboard
                            </Link>
                            <template v-if="isAdmin">
                                <Link
                                    :href="route('admin.offices.index')"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold rounded-lg transition"
                                    :class="route().current('admin.offices.*') ? 'bg-amber-400/10 text-amber-400' : 'text-slate-400 hover:text-white hover:bg-slate-800'"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 7h1m4 0h1M9 11h1m4 0h1M9 15h1m4 0h1M10 21v-3h4v3" /></svg>
                                    Offices
                                </Link>
                                <Link
                                    :href="route('admin.services.index')"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold rounded-lg transition"
                                    :class="route().current('admin.services.*') ? 'bg-amber-400/10 text-amber-400' : 'text-slate-400 hover:text-white hover:bg-slate-800'"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v4m6-4v4M5 9h14M7 7h10a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2z" /></svg>
                                    Services
                                </Link>
                                <Link
                                    :href="route('admin.users.index')"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold rounded-lg transition"
                                    :class="route().current('admin.users.*') ? 'bg-amber-400/10 text-amber-400' : 'text-slate-400 hover:text-white hover:bg-slate-800'"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m7-10a4 4 0 100-8 4 4 0 000 8zm7-1a4 4 0 010 8m4 3v-2a4 4 0 00-3-3.87" /></svg>
                                    Users
                                </Link>
                                <Link
                                    :href="route('admin.reports.index')"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-semibold rounded-lg transition"
                                    :class="route().current('admin.reports.*') ? 'bg-amber-400/10 text-amber-400' : 'text-slate-400 hover:text-white hover:bg-slate-800'"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19V5m0 14h16M8 16v-4m4 4V8m4 8v-7" /></svg>
                                    Reports
                                </Link>
                            </template>
                        </div>
                    </div>

                    <!-- Right: User Dropdown -->
                    <div class="hidden sm:flex items-center gap-3">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center gap-3 px-3 py-1.5 rounded-xl border border-slate-800 hover:border-slate-700 bg-slate-900/50 transition">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-400 to-amber-500 flex items-center justify-center text-xs font-black text-slate-950">
                                        {{ initials }}
                                    </div>
                                    <div class="text-left hidden md:block">
                                        <p class="text-sm font-semibold text-white leading-none">{{ user?.name }}</p>
                                        <p class="text-[10px] text-slate-500 font-medium mt-0.5 uppercase tracking-wider">{{ user?.role }}</p>
                                    </div>
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                </button>
                            </template>

                            <template #content>
                                <div class="w-48">
                                    <div class="block px-4 py-2 text-[10px] text-slate-500 uppercase tracking-widest font-bold">Account</div>
                                    <DropdownLink :href="route('profile.show')">Profile</DropdownLink>
                                    <div class="border-t border-slate-800" />
                                    <form @submit.prevent="logout">
                                        <DropdownLink as="button">
                                            <span class="text-red-400">Log Out</span>
                                        </DropdownLink>
                                    </form>
                                </div>
                            </template>
                        </Dropdown>
                    </div>

                    <!-- Mobile Hamburger -->
                    <div class="flex items-center sm:hidden">
                        <button @click="showingMobileMenu = !showingMobileMenu" class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!showingMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-if="showingMobileMenu" class="sm:hidden border-t border-slate-800 bg-slate-900/95 backdrop-blur-xl">
                <div class="px-4 py-3 space-y-1">
                    <Link v-if="!isStaff" :href="route('dashboard')" class="flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg" :class="route().current('dashboard') ? 'bg-amber-400/10 text-amber-400' : 'text-slate-400'"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13h7V3H3v10zm0 8h7v-4H3v4zm11 0h7V11h-7v10zm0-14h7V3h-7v4z" /></svg>Dashboard</Link>
                    <Link v-if="isStaff" :href="route('dashboard.staff')" class="flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg" :class="route().current('dashboard.staff') ? 'bg-amber-400/10 text-amber-400' : 'text-slate-400'"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 6V4a2 2 0 012-2h2a2 2 0 012 2v2m-9 0h14a2 2 0 012 2v11a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2zm-7 5h14m-7 0v3" /></svg>Staff Dashboard</Link>
                    <template v-if="isAdmin">
                        <Link :href="route('admin.offices.index')" class="flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg text-slate-400"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 7h1m4 0h1M9 11h1m4 0h1M9 15h1m4 0h1M10 21v-3h4v3" /></svg>Offices</Link>
                        <Link :href="route('admin.services.index')" class="flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg text-slate-400"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v4m6-4v4M5 9h14M7 7h10a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2z" /></svg>Services</Link>
                        <Link :href="route('admin.users.index')" class="flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg text-slate-400"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2m7-10a4 4 0 100-8 4 4 0 000 8zm7-1a4 4 0 010 8m4 3v-2a4 4 0 00-3-3.87" /></svg>Users</Link>
                        <Link :href="route('admin.reports.index')" class="flex items-center gap-2 px-3 py-2 text-sm font-semibold rounded-lg text-slate-400"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19V5m0 14h16M8 16v-4m4 4V8m4 8v-7" /></svg>Reports</Link>
                    </template>
                </div>
                <div class="border-t border-slate-800 px-4 py-3">
                    <p class="text-sm font-semibold text-white">{{ user?.name }}</p>
                    <p class="text-xs text-slate-500">{{ user?.email }}</p>
                    <form @submit.prevent="logout" class="mt-2">
                        <button class="text-sm text-red-400 font-semibold">Log Out</button>
                    </form>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        <header v-if="$slots.header" class="relative z-10 bg-slate-900/40 border-b border-slate-800/60 backdrop-blur-md">
            <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main class="relative z-10">
            <slot />
        </main>
    </div>
</template>
