<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    offices: { type: Array, default: () => [] },
    staffUsers: { type: Array, default: () => [] },
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const activeOfficeId = ref(null);
const isDeleteModalOpen = ref(false);
const officeToDelete = ref(null);

const form = useForm({ name: '', user_id: '', is_active: true });

const getOfficeId = (office) => office?.office_id ?? office?.id;
const closeModal = () => { isModalOpen.value = false; form.reset(); form.clearErrors(); activeOfficeId.value = null; };
const openCreateModal = () => { isEditing.value = false; form.reset(); form.clearErrors(); isModalOpen.value = true; };
const openDeleteModal = (office) => { officeToDelete.value = office; isDeleteModalOpen.value = true; };
const closeDeleteModal = () => { isDeleteModalOpen.value = false; officeToDelete.value = null; };

const openEditModal = (office) => {
    isEditing.value = true;
    activeOfficeId.value = getOfficeId(office);
    form.clearErrors();
    form.name = office.name || '';
    form.user_id = office.user_id ?? (office.user ? office.user.user_id : '');
    form.is_active = Boolean(office.is_active);
    isModalOpen.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('admin.offices.update', activeOfficeId.value), { onSuccess: () => closeModal(), preserveScroll: true });
    } else {
        form.post(route('admin.offices.store'), { onSuccess: () => closeModal(), preserveScroll: true });
    }
};

const deleteOffice = () => {
    if (!officeToDelete.value) return;

    router.delete(route('admin.offices.destroy', getOfficeId(officeToDelete.value)), {
        preserveScroll: true,
        onFinish: () => closeDeleteModal(),
    });
};
</script>

<template>
    <AppLayout title="Campus Offices">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white leading-tight">Campus Offices</h2>
                <button @click="openCreateModal" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition shadow">+ Add Office</button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-slate-900/60 rounded-2xl border border-slate-800/80 backdrop-blur-xl overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-800">
                        <thead class="bg-slate-900/80">
                            <tr>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">ID</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Name</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Staff</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status</th>
                                <th class="px-5 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            <tr v-for="office in offices" :key="getOfficeId(office)" class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3.5 text-sm text-slate-500 font-mono">#{{ getOfficeId(office) }}</td>
                                <td class="px-5 py-3.5 font-semibold text-white text-sm">{{ office.name }}</td>
                                <td class="px-5 py-3.5 text-sm text-slate-400">{{ office.user ? office.user.name : 'Unassigned' }}</td>
                                <td class="px-5 py-3.5">
                                    <span :class="office.is_active ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20'" class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase border">{{ office.is_active ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-right text-sm space-x-3">
                                    <button @click="openEditModal(office)" class="text-amber-400 hover:text-amber-300 font-semibold text-sm">Edit</button>
                                    <button @click="openDeleteModal(office)" class="text-red-400 hover:text-red-300 font-semibold text-sm">Delete</button>
                                </td>
                            </tr>
                            <tr v-if="!offices || offices.length === 0">
                                <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-600">No offices configured.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <ConfirmationModal :show="isDeleteModalOpen" @close="closeDeleteModal" max-width="md">
            <template #title>Delete office</template>
            <template #content>
                <p>
                    Are you sure you want to delete <span class="font-semibold text-white">{{ officeToDelete?.name }}</span>? This action cannot be undone.
                </p>
            </template>
            <template #footer>
                <button type="button" @click="closeDeleteModal" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-sm font-semibold transition">Cancel</button>
                <button type="button" @click="deleteOffice" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl text-sm font-semibold transition">Delete</button>
            </template>
        </ConfirmationModal>

        <Modal :show="isModalOpen" @close="closeModal" max-width="md">
            <div class="border border-slate-800 bg-slate-900/95 text-slate-100 shadow-[0_30px_80px_rgba(15,23,42,0.7)] backdrop-blur-xl">
                <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                    <h3 class="text-lg font-bold text-white">{{ isEditing ? 'Edit Office' : 'Add Office' }}</h3>
                </div>

                <form @submit.prevent="submitForm" class="space-y-5 p-5 sm:p-6">
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Office Name</label>
                        <input v-model="form.name" type="text" required class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-sm text-slate-100 outline-none transition focus:border-amber-500" placeholder="e.g. Registrar" />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-rose-400">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Assign Staff</label>
                        <select v-model="form.user_id" class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-sm text-slate-100 outline-none transition focus:border-amber-500">
                            <option value="">Unassigned</option>
                            <option v-for="user in staffUsers" :key="user.user_id" :value="user.user_id">{{ user.name }} ({{ user.email }})</option>
                        </select>
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
