<script setup>
import { ref, watch, onUnmounted } from 'vue';

const props = defineProps({
    message: { type: String, default: '' },
    type: { type: String, default: 'success' },
    duration: { type: Number, default: 4000 },
});

const visible = ref(false);
let timeout = null;

watch(() => props.message, (val) => {
    if (val) {
        visible.value = true;
        if (timeout) clearTimeout(timeout);
        timeout = setTimeout(() => { visible.value = false; }, props.duration);
    }
});

onUnmounted(() => { if (timeout) clearTimeout(timeout); });

const typeStyles = {
    success: 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400',
    error: 'bg-red-500/10 border-red-500/30 text-red-400',
    info: 'bg-blue-500/10 border-blue-500/30 text-blue-400',
    warning: 'bg-amber-500/10 border-amber-500/30 text-amber-400',
};
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
    >
        <div
            v-if="visible && message"
            class="fixed top-4 left-1/2 -translate-x-1/2 z-50 px-6 py-3 border rounded-2xl text-sm font-semibold shadow-2xl backdrop-blur-xl max-w-md"
            :class="typeStyles[type] || typeStyles.success"
        >
            {{ message }}
        </div>
    </Transition>
</template>
