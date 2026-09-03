<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    services: { type: Array, default: () => [] },
    offices: { type: Array, default: () => [] },
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const activeServiceId = ref(null);
const isDeleteModalOpen = ref(false);
const serviceToDelete = ref(null);

const form = useForm({ office_id: '', service_name: '', is_active: true });

const getServiceId = (s) => s?.service_id ?? s?.id;
const closeModal = () => { isModalOpen.value = false; form.reset(); form.clearErrors(); activeServiceId.value = null; };
const openCreateModal = () => { isEditing.value = false; form.reset(); form.clearErrors(); isModalOpen.value = true; };
const openDeleteModal = (service) => { serviceToDelete.value = service; isDeleteModalOpen.value = true; };
const closeDeleteModal = () => { isDeleteModalOpen.value = false; serviceToDelete.value = null; };

const openEditModal = (service) => {
    isEditing.value = true;
    activeServiceId.value = getServiceId(service);
    form.clearErrors();
    form.office_id = service.office_id ?? '';
    form.service_name = service.service_name || '';
    form.is_active = Boolean(service.is_active);
    isModalOpen.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('admin.services.update', activeServiceId.value), { onSuccess: () => closeModal(), preserveScroll: true });
    } else {
        form.post(route('admin.services.store'), { onSuccess: () => closeModal(), preserveScroll: true });
    }
};

const deleteService = () => {
    if (!serviceToDelete.value) return;

    router.delete(route('admin.services.destroy', getServiceId(serviceToDelete.value)), {
        preserveScroll: true,
        onFinish: () => closeDeleteModal(),
    });
};
</script>

<template>
    <AppLayout title="Campus Services">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white leading-tight">Campus Services</h2>
                <button @click="openCreateModal" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition shadow">+ Add Service</button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-800">
                        <thead class="bg-slate-900/80">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">ID</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Service Name</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Office</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status</th>
                                <th class="px-5 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            <tr v-for="service in services" :key="getServiceId(service)" class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3.5 text-sm text-slate-500 font-mono">#{{ getServiceId(service) }}</td>
                                <td class="px-5 py-3.5 font-semibold text-white text-sm">{{ service.service_name }}</td>
                                <td class="px-5 py-3.5 text-sm text-slate-400">{{ service.office ? service.office.name : '—' }}</td>
                                <td class="px-5 py-3.5">
                                    <span :class="service.is_active ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20'" class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase border">{{ service.is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-right text-sm space-x-3">
                                    <button @click="openEditModal(service)" class="text-amber-400 hover:text-amber-300 font-semibold text-sm">Edit</button>
                                    <button @click="openDeleteModal(service)" class="text-red-400 hover:text-red-300 font-semibold text-sm">Delete</button>
                                </td>
                            </tr>
                            <tr v-if="!services || services.length === 0">
                                <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-600">No services configured.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <ConfirmationModal :show="isDeleteModalOpen" @close="closeDeleteModal" max-width="md">
            <template #title>Delete service</template>
            <template #content>
                <p>
                    Are you sure you want to delete <span class="font-semibold text-white">{{ serviceToDelete?.service_name }}</span>? This action cannot be undone.
                </p>
            </template>
            <template #footer>
                <button type="button" @click="closeDeleteModal" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-sm font-semibold transition">Cancel</button>
                <button type="button" @click="deleteService" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl text-sm font-semibold transition">Delete</button>
            </template>
        </ConfirmationModal>

        <Modal :show="isModalOpen" @close="closeModal" max-width="md">
            <div class="border border-slate-800 bg-slate-900/95 text-slate-100 shadow-[0_30px_80px_rgba(15,23,42,0.7)] backdrop-blur-xl">
                <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                    <h3 class="text-lg font-bold text-white">{{ isEditing ? 'Edit Service' : 'Add Service' }}</h3>
                </div>

                <form @submit.prevent="submitForm" class="space-y-5 p-5 sm:p-6">
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Office</label>
                        <select v-model="form.office_id" required class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-sm text-slate-100 outline-none transition focus:border-amber-500">
                            <option value="">Select office</option>
                            <option v-for="office in offices" :key="office.office_id" :value="office.office_id">{{ office.name }}</option>
                        </select>
                        <p v-if="form.errors.office_id" class="mt-1 text-xs text-rose-400">{{ form.errors.office_id }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Service Name</label>
                        <input v-model="form.service_name" type="text" required class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-sm text-slate-100 outline-none transition focus:border-amber-500" placeholder="e.g. Transcript Request" />
                        <p v-if="form.errors.service_name" class="mt-1 text-xs text-rose-400">{{ form.errors.service_name }}</p>
                    </div>

                    <label class="flex items-center gap-3 text-sm text-slate-300">
                        <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-500" />
                        Active
                    </label>

                    <div class="flex justify-end gap-2 border-t border-slate-800 pt-4">
                        <button type="button" @click="closeModal" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-700">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700 disabled:opacity-50">
                            {{ form.processing ? 'Saving...' : (isEditing ? 'Update' : 'Save') }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
