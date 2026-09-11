<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    users: { type: Array, default: () => [] },
    offices: { type: Array, default: () => [] },
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const activeUserId = ref(null);
const isDeleteModalOpen = ref(false);
const userToDelete = ref(null);

const form = useForm({ name: '', email: '', password: '', password_confirmation: '', role: 'student', contact: '', office_id: '' });

const getUserId = (u) => u?.user_id ?? u?.id;
const closeModal = () => { isModalOpen.value = false; form.reset(); form.clearErrors(); activeUserId.value = null; };
const openCreateModal = () => { isEditing.value = false; form.reset(); form.clearErrors(); isModalOpen.value = true; };
const openDeleteModal = (user) => { userToDelete.value = user; isDeleteModalOpen.value = true; };
const closeDeleteModal = () => { isDeleteModalOpen.value = false; userToDelete.value = null; };
const canAssignOffice = () => ['staff', 'employee'].includes(String(form.role).toLowerCase());
const handleRoleChange = () => {
    if (!canAssignOffice()) form.office_id = '';
};

const openEditModal = (user) => {
    isEditing.value = true;
    activeUserId.value = getUserId(user);
    form.clearErrors();
    form.name = user.name || '';
    form.email = user.email || '';
    form.password = '';
    form.password_confirmation = '';
    form.role = user.role || 'student';
    form.contact = user.contact || '';
    form.office_id = canAssignOffice() ? (user.office_id ?? '') : '';
    isModalOpen.value = true;
};

const submitForm = () => {
    handleRoleChange();

    if (isEditing.value) {
        form.put(route('admin.users.update', activeUserId.value), { onSuccess: () => closeModal(), preserveScroll: true });
    } else {
        form.post(route('admin.users.store'), { onSuccess: () => closeModal(), preserveScroll: true });
    }
};

const deleteUser = () => {
    if (!userToDelete.value) return;

    router.delete(route('admin.users.destroy', getUserId(userToDelete.value)), {
        preserveScroll: true,
        onFinish: () => closeDeleteModal(),
    });
};

const roleBadge = (role) => {
    const r = (role || '').toLowerCase();
    if (r === 'admin' || r === 'administrator') return 'bg-purple-500/10 text-purple-400 border-purple-500/20';
    if (r === 'staff' || r === 'employee') return 'bg-blue-500/10 text-blue-400 border-blue-500/20';
    return 'bg-slate-500/10 text-slate-400 border-slate-500/20';
};
</script>

<template>
    <AppLayout title="User Management">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white leading-tight">User Management</h2>
                <button @click="openCreateModal" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold px-4 py-2 rounded-xl text-sm transition shadow">+ Add User</button>
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
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Email</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Role</th>
                                <th class="px-5 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-widest">Office</th>
                                <th class="px-5 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-widest">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            <tr v-for="user in users" :key="getUserId(user)" class="hover:bg-slate-800/30 transition">
                                <td class="px-5 py-3.5 text-sm text-slate-500 font-mono">#{{ getUserId(user) }}</td>
                                <td class="px-5 py-3.5 font-semibold text-white text-sm">{{ user.name }}</td>
                                <td class="px-5 py-3.5 text-sm text-slate-400">{{ user.email }}</td>
                                <td class="px-5 py-3.5">
                                    <span :class="roleBadge(user.role)" class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase border">{{ user.role }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-sm text-slate-400">{{ user.office ? user.office.name : '—' }}</td>
                                <td class="px-5 py-3.5 text-right text-sm space-x-3">
                                    <button @click="openEditModal(user)" class="text-amber-400 hover:text-amber-300 font-semibold text-sm">Edit</button>
                                    <button @click="openDeleteModal(user)" class="text-red-400 hover:text-red-300 font-semibold text-sm">Delete</button>
                                </td>
                            </tr>
                            <tr v-if="!users || users.length === 0">
                                <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-600">No users found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <ConfirmationModal :show="isDeleteModalOpen" @close="closeDeleteModal" max-width="md">
            <template #title>Delete user</template>
            <template #content>
                <p>
                    Are you sure you want to delete <span class="font-semibold text-white">{{ userToDelete?.name }}</span>? This action cannot be undone.
                </p>
            </template>
            <template #footer>
                <button type="button" @click="closeDeleteModal" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-sm font-semibold transition">Cancel</button>
                <button type="button" @click="deleteUser" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl text-sm font-semibold transition">Delete</button>
            </template>
        </ConfirmationModal>

        <Modal :show="isModalOpen" @close="closeModal" max-width="md">
            <div class="border border-slate-800 bg-slate-900/95 text-slate-100 shadow-[0_30px_80px_rgba(15,23,42,0.7)] backdrop-blur-xl">
                <div class="border-b border-slate-800 px-5 py-4 sm:px-6">
                    <h3 class="text-lg font-bold text-white">{{ isEditing ? 'Edit User' : 'Add User' }}</h3>
                </div>

                <form @submit.prevent="submitForm" class="max-h-[80vh] space-y-5 overflow-y-auto p-5 sm:p-6">
                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Full Name</label>
                        <input v-model="form.name" type="text" required class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-sm text-slate-100 outline-none transition focus:border-amber-500" />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-rose-400">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Email</label>
                        <input v-model="form.email" type="email" required class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-sm text-slate-100 outline-none transition focus:border-amber-500" />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-rose-400">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Password {{ isEditing ? '(blank to keep)' : '' }}</label>
                        <input v-model="form.password" :required="!isEditing" type="password" class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-sm text-slate-100 outline-none transition focus:border-amber-500" />
                        <p v-if="form.errors.password" class="mt-1 text-xs text-rose-400">{{ form.errors.password }}</p>
                    </div>

                    <div v-if="!isEditing">
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Confirm Password</label>
                        <input v-model="form.password_confirmation" type="password" required class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-sm text-slate-100 outline-none transition focus:border-amber-500" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Role</label>
                        <select v-model="form.role" @change="handleRoleChange" class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-sm text-slate-100 outline-none transition focus:border-amber-500">
                            <option value="student">Student</option>
                            <option value="staff">Staff</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Contact</label>
                        <input v-model="form.contact" type="text" class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-sm text-slate-100 outline-none transition focus:border-amber-500" />
                    </div>

                    <div v-if="canAssignOffice()">
                        <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Assigned Office</label>
                        <select v-model="form.office_id" class="w-full rounded-xl border border-slate-700 bg-slate-950/50 px-4 py-2.5 text-sm text-slate-100 outline-none transition focus:border-amber-500">
                            <option value="">None</option>
                            <option v-for="office in offices" :key="office.office_id" :value="office.office_id">{{ office.name }}</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-slate-800 pt-4">
                        <button type="button" @click="closeModal" class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-300 transition hover:bg-slate-700">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700 disabled:opacity-50">
                            {{ form.processing ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AppLayout>
</template>
