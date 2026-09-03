<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);
const successMessage = ref('');

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('user-password.update'), {
        errorBag: 'updatePassword',
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            successMessage.value = 'Password updated successfully.';
            setTimeout(() => successMessage.value = '', 3000);
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <div class="bg-slate-900/60 rounded-2xl border border-white/10 backdrop-blur-xl overflow-hidden">
        <!-- Card Header -->
        <div class="px-6 py-5 border-b border-white/5">
            <h3 class="text-lg font-bold text-white">Update Password</h3>
            <p class="text-slate-400 text-xs mt-1">Ensure your account is using a long, random password to stay secure.</p>
        </div>

        <!-- Card Body -->
        <div class="p-6">
            <!-- Success Message -->
            <div v-if="successMessage" class="mb-5 p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl text-xs font-semibold animate-fade-in">
                {{ successMessage }}
            </div>

            <form @submit.prevent="updatePassword" class="space-y-5">
                <!-- Current Password -->
                <div class="space-y-1.5">
                    <label for="current_password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Current Password</label>
                    <input
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        type="password"
                        autocomplete="current-password"
                        class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                        placeholder="Enter current password"
                    />
                    <p v-if="form.errors.current_password" class="text-rose-400 text-xs mt-1">{{ form.errors.current_password }}</p>
                </div>

                <!-- New Password -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">New Password</label>
                    <input
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        autocomplete="new-password"
                        class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                        placeholder="Enter new password"
                    />
                    <p v-if="form.errors.password" class="text-rose-400 text-xs mt-1">{{ form.errors.password }}</p>
                </div>

                <!-- Confirm Password -->
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Confirm Password</label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                        placeholder="Confirm new password"
                    />
                    <p v-if="form.errors.password_confirmation" class="text-rose-400 text-xs mt-1">{{ form.errors.password_confirmation }}</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3 pt-3">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center gap-2 px-5 py-2.5 font-bold bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 active:scale-[0.98] transition-all disabled:opacity-55 disabled:pointer-events-none text-sm"
                    >
                        <span v-if="form.processing" class="w-4 h-4 border-2 border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                        <span>{{ form.processing ? 'Saving...' : 'Save' }}</span>
                    </button>
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 translate-y-1"
                    >
                        <span v-if="form.recentlySuccessful" class="text-xs text-emerald-400 font-semibold">Saved.</span>
                    </Transition>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.25s ease-out forwards;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
