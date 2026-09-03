<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
    enabled: { type: Boolean, default: true },
});

const audioCtx = ref(null);
const isPlaying = ref(false);

const initAudio = () => {
    if (!audioCtx.value) {
        audioCtx.value = new (window.AudioContext || window.webkitAudioContext)();
    }
};

const playChime = () => {
    if (!props.enabled) return;
    initAudio();
    if (!audioCtx.value) return;

    isPlaying.value = true;
    const ctx = audioCtx.value;

    const frequencies = [523.25, 659.25, 783.99];
    const now = ctx.currentTime;

    frequencies.forEach((freq, i) => {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(0.15, now + i * 0.15);
        gain.gain.exponentialRampToValueAtTime(0.001, now + i * 0.15 + 0.4);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(now + i * 0.15);
        osc.stop(now + i * 0.15 + 0.4);
    });

    setTimeout(() => { isPlaying.value = false; }, 800);
};

defineExpose({ playChime });

onMounted(() => {
    document.addEventListener('click', initAudio, { once: true });
});
</script>

<template>
    <div v-if="isPlaying" class="fixed top-4 right-4 z-50 px-4 py-2 bg-emerald-500/20 border border-emerald-500/30 rounded-xl text-emerald-400 text-xs font-bold flex items-center gap-2 animate-pulse">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path></svg>
        Audio Alert
    </div>
</template>
