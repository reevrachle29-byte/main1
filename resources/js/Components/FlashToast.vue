<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const message = ref('');
const type = ref('success');
const show = ref(false);
let autoHideTimer = null;

const clearAutoHide = () => {
    if (autoHideTimer) {
        clearTimeout(autoHideTimer);
        autoHideTimer = null;
    }
};

watch(
    () => page.props.flash,
    (flash) => {
        clearAutoHide();

        if (flash?.success) {
            message.value = flash.success;
            type.value = 'success';
            show.value = true;
        } else if (flash?.error) {
            message.value = flash.error;
            type.value = 'error';
            show.value = true;
        } else if (flash?.info) {
            message.value = flash.info;
            type.value = 'info';
            show.value = true;
        } else {
            message.value = '';
            show.value = false;
            return;
        }

        autoHideTimer = setTimeout(() => {
            show.value = false;
            message.value = '';
        }, 2000);
    },
    { immediate: true }
);

onBeforeUnmount(() => {
    clearAutoHide();
});

const closeBanner = () => {
    clearAutoHide();
    show.value = false;
    message.value = '';
};

const typeStyles = {
    success: {
        shell: 'border-emerald-500/30 bg-slate-950/80 text-emerald-300',
        pill: 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
        glow: 'from-emerald-500/20 via-transparent to-transparent',
        hover: 'hover:bg-emerald-500/10',
    },
    error: {
        shell: 'border-red-500/30 bg-slate-950/80 text-red-300',
        pill: 'bg-red-500/15 text-red-300 border-red-500/30',
        glow: 'from-red-500/20 via-transparent to-transparent',
        hover: 'hover:bg-red-500/10',
    },
    info: {
        shell: 'border-blue-500/30 bg-slate-950/80 text-blue-300',
        pill: 'bg-blue-500/15 text-blue-300 border-blue-500/30',
        glow: 'from-blue-500/20 via-transparent to-transparent',
        hover: 'hover:bg-blue-500/10',
    },
    warning: {
        shell: 'border-amber-500/30 bg-slate-950/80 text-amber-300',
        pill: 'bg-amber-500/15 text-amber-300 border-amber-500/30',
        glow: 'from-amber-500/20 via-transparent to-transparent',
        hover: 'hover:bg-amber-500/10',
    },
};

const typeIcons = {
    success: 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    error: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
    info: 'M12 9v3.75m0 3.75h.007M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    warning: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
};
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-3"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-3"
    >
        <div
            v-if="show && message"
            class="fixed left-1/2 top-4 z-50 w-[calc(100%-1.5rem)] max-w-2xl -translate-x-1/2 overflow-hidden rounded-2xl border shadow-[0_20px_60px_rgba(15,23,42,0.55)] backdrop-blur-xl"
            :class="typeStyles[type]?.shell || typeStyles.success.shell"
            role="status"
            aria-live="polite"
        >
            <div class="absolute inset-0 bg-gradient-to-r" :class="typeStyles[type]?.glow || typeStyles.success.glow"></div>

            <div class="relative flex items-center gap-3 px-4 py-3 sm:px-5">
                <span class="flex h-9 w-9 items-center justify-center rounded-full border border-current/20 bg-slate-900/70 shadow-inner shadow-black/20" :class="typeStyles[type]?.pill || typeStyles.success.pill">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path :d="typeIcons[type] || typeIcons.success" />
                    </svg>
                </span>

                <p class="flex-1 text-sm font-semibold leading-6 tracking-wide text-slate-100">
                    {{ message }}
                </p>

                <button
                    type="button"
                    class="ml-auto flex h-8 w-8 items-center justify-center rounded-full border border-current/20 text-slate-200 transition duration-200 focus:outline-none focus:ring-2 focus:ring-slate-400/60"
                    :class="typeStyles[type]?.hover || typeStyles.success.hover"
                    aria-label="Dismiss notification"
                    @click="closeBanner"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </Transition>
</template>
