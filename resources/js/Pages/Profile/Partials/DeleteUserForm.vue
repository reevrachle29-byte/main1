<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    setTimeout(() => passwordInput.value.focus(), 250);
};

const deleteUser = () => {
    form.delete(route('current-user.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.reset();
};
</script>

<template>
    <div class="bg-slate-900/60 rounded-2xl border border-red-500/10 backdrop-blur-xl overflow-hidden">
        <!-- Card Header -->
        <div class="px-6 py-5 border-b border-white/5">
            <h3 class="text-lg font-bold text-white">Delete Account</h3>
            <p class="text-slate-400 text-xs mt-1">Permanently delete your account.</p>
        </div>

        <!-- Card Body -->
        <div class="p-6">
            <div class="max-w-xl text-sm text-slate-400">
                Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.
            </div>

            <div class="mt-5">
                <button
                    @click="confirmUserDeletion"
                    class="inline-flex items-center gap-2 px-5 py-2.5 font-bold bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 rounded-xl transition text-sm"
                >
                    Delete Account
                </button>
            </div>
        </div>

        <!-- Delete Account Confirmation Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="confirmingUserDeletion" class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0">
                    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeModal"></div>
                    <div class="relative bg-slate-900 border border-white/10 rounded-2xl p-6 max-w-md mx-auto shadow-2xl">
                        <h3 class="text-lg font-bold text-white mb-2">Delete Account</h3>
                        <p class="text-sm text-slate-400 mb-5">
                            Are you sure you want to delete your account? Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm.
                        </p>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
                                <input
                                    ref="passwordInput"
                                    v-model="form.password"
                                    type="password"
                                    class="w-full bg-slate-950/50 border border-white/10 focus:border-red-500 focus:ring-1 focus:ring-red-500 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                                    placeholder="Enter your password"
                                    autocomplete="current-password"
                                    @keyup.enter="deleteUser"
                                />
                                <p v-if="form.errors.password" class="text-rose-400 text-xs mt-1">{{ form.errors.password }}</p>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-white/5">
                            <button
                                @click="closeModal"
                                class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-sm font-semibold transition"
                            >
                                Cancel
                            </button>
                            <button
                                @click="deleteUser"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-semibold transition disabled:opacity-50"
                            >
                                {{ form.processing ? 'Deleting...' : 'Delete Account' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
