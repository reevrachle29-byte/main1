<script setup>
import { ref } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
});

const page = usePage();
const form = useForm({
    _method: 'PUT',
    name: props.user.name,
    email: props.user.email,
    contact: props.user.contact || '',
    photo: null,
});

const verificationLinkSent = ref(null);
const photoPreview = ref(null);
const photoInput = ref(null);
const successMessage = ref('');

const updateProfileInformation = () => {
    if (photoInput.value) {
        form.photo = photoInput.value.files[0];
    }

    form.post(route('user-profile-information.update'), {
        errorBag: 'updateProfileInformation',
        preserveScroll: true,
        onSuccess: () => {
            clearPhotoFileInput();
            successMessage.value = 'Profile updated successfully.';
            setTimeout(() => successMessage.value = '', 3000);
        },
    });
};

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];
    if (!photo) return;
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(photo);
};

const deletePhoto = () => {
    router.delete(route('current-user-photo.destroy'), {
        preserveScroll: true,
        onSuccess: () => {
            photoPreview.value = null;
            clearPhotoFileInput();
        },
    });
};

const clearPhotoFileInput = () => {
    if (photoInput.value?.value) {
        photoInput.value.value = null;
    }
};
</script>

<template>
    <div class="bg-slate-900/60 rounded-2xl border border-white/10 backdrop-blur-xl overflow-hidden">
        <!-- Card Header -->
        <div class="px-6 py-5 border-b border-white/5">
            <h3 class="text-lg font-bold text-white">Profile Information</h3>
            <p class="text-slate-400 text-xs mt-1">Update your account's profile information and email address.</p>
        </div>

        <!-- Card Body -->
        <div class="p-6">
            <!-- Success Message -->
            <div v-if="successMessage" class="mb-5 p-3 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl text-xs font-semibold animate-fade-in">
                {{ successMessage }}
            </div>

            <form @submit.prevent="updateProfileInformation" class="space-y-5">
                <!-- Profile Photo -->
                <div v-if="$page.props.jetstream.managesProfilePhotos">
                    <input
                        id="photo"
                        ref="photoInput"
                        type="file"
                        class="hidden"
                        @change="updatePhotoPreview"
                    >

                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Photo</label>

                    <div class="flex items-center gap-5">
                        <!-- Current Profile Photo -->
                        <div v-show="!photoPreview">
                            <img :src="user.profile_photo_url" :alt="user.name" class="rounded-full size-20 object-cover ring-2 ring-white/10">
                        </div>

                        <!-- New Profile Photo Preview -->
                        <div v-show="photoPreview">
                            <span
                                class="block rounded-full size-20 bg-cover bg-no-repeat bg-center ring-2 ring-amber-400/50"
                                :style="'background-image: url(\'' + photoPreview + '\');'"
                            />
                        </div>

                        <div class="space-y-2">
                            <button
                                type="button"
                                @click.prevent="selectNewPhoto"
                                class="text-xs font-semibold text-amber-400 hover:text-amber-300 transition"
                            >
                                Select A New Photo
                            </button>
                            <button
                                v-if="user.profile_photo_path"
                                type="button"
                                @click.prevent="deletePhoto"
                                class="block text-xs font-semibold text-red-400 hover:text-red-300 transition"
                            >
                                Remove Photo
                            </button>
                        </div>
                    </div>
                    <p v-if="form.errors.photo" class="text-rose-400 text-xs mt-2">{{ form.errors.photo }}</p>
                </div>

                <!-- Name -->
                <div class="space-y-1.5">
                    <label for="name" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Full Name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autocomplete="name"
                        class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                    />
                    <p v-if="form.errors.name" class="text-rose-400 text-xs mt-1">{{ form.errors.name }}</p>
                </div>

                <!-- Email -->
                <div class="space-y-1.5">
                    <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Email Address</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="username"
                        class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                    />
                    <p v-if="form.errors.email" class="text-rose-400 text-xs mt-1">{{ form.errors.email }}</p>

                    <div v-if="$page.props.jetstream.hasEmailVerification && user.email_verified_at === null">
                        <p class="text-sm mt-2 text-slate-400">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="underline text-sm text-amber-400 hover:text-amber-300 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 ml-1"
                                @click.prevent="verificationLinkSent = true"
                            >
                                Click here to re-send the verification email.
                            </Link>
                        </p>
                        <div v-show="verificationLinkSent" class="mt-2 font-medium text-sm text-amber-400">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>
                </div>

                <!-- Contact -->
                <div class="space-y-1.5">
                    <label for="contact" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Contact Number</label>
                    <input
                        id="contact"
                        v-model="form.contact"
                        type="text"
                        autocomplete="tel"
                        placeholder="e.g. 09123456789"
                        class="w-full bg-slate-950/50 border border-white/10 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-600 transition-all outline-none"
                    />
                    <p v-if="form.errors.contact" class="text-rose-400 text-xs mt-1">{{ form.errors.contact }}</p>
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
