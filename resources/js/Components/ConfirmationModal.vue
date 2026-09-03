<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <div class="border border-slate-800 bg-slate-900/95 text-slate-100 shadow-[0_30px_80px_rgba(15,23,42,0.7)] backdrop-blur-xl">
            <div class="px-5 pt-5 pb-4 sm:p-6 sm:pb-5">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto shrink-0 flex items-center justify-center size-12 rounded-full border border-red-500/30 bg-red-500/10 text-red-400 sm:mx-0 sm:size-11">
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </div>

                    <div class="mt-3 text-center sm:mt-0 sm:ms-4 sm:text-start">
                        <h3 class="text-lg font-bold text-white">
                            <slot name="title" />
                        </h3>

                        <div class="mt-3 text-sm text-slate-300 leading-6">
                            <slot name="content" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-row justify-end gap-2 border-t border-slate-800 bg-slate-950/60 px-5 py-4 sm:px-6">
                <slot name="footer" />
            </div>
        </div>
    </Modal>
</template>
