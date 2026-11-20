<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, reactive, ref } from 'vue';
import { RouterLink } from 'vue-router';
import BaseModal from '@/components/common/BaseModal.vue';
import http from '@/api/http';
import { useAuthStore } from '@/stores/auth';

interface RoleOption { id: number; name: string }
interface UserRecord {
    id: number;
    name: string;
    email: string;
    status: 'active' | 'inactive';
    roles: RoleOption[];
    created_at: string | null;
}
interface PaginationMeta { current_page: number; last_page: number; total: number }
interface ApiError { message?: string; errors?: Record<string, string[]> }

const auth = useAuthStore();
const users = ref<UserRecord[]>([]);
const roles = ref<RoleOption[]>([]);
const loading = ref(false);
const submitting = ref(false);
const bulkSubmitting = ref(false);
const modalOpen = ref(false);
const detailsOpen = ref(false);
const editingUser = ref<UserRecord | null>(null);
const selectedUser = ref<UserRecord | null>(null);
const message = ref('');
const errorMessage = ref('');
const validationErrors = ref<Record<string, string[]>>({});
const filters = reactive({ search: '', role: '', status: '' });
const bulkAction = ref('');
const selectedIds = ref<number[]>([]);
const pagination = ref<PaginationMeta>({ current_page: 1, last_page: 1, total: 0 });
const form = reactive({ name: '', email: '', password: '', password_confirmation: '', roles: [] as string[] });

const canCreate = computed(() => auth.can('users.create') && auth.can('users.roles.assign'));
const canUpdate = computed(() => auth.can('users.update'));
const canAssignRoles = computed(() => auth.can('users.roles.assign'));
const canChangeStatus = computed(() => auth.can('users.status.update'));
const canDelete = computed(() => auth.can('users.delete'));
const canRestore = computed(() => auth.can('users.restore'));
const canForceDelete = computed(() => auth.can('users.force_delete'));
const selectedCount = computed(() => selectedIds.value.length);
const allSelected = computed(() => users.value.length > 0 && users.value.every((user) => selectedIds.value.includes(user.id)));

function fieldError(field: string): string { return validationErrors.value[field]?.[0] ?? ''; }

function handleError(error: unknown, fallback: string): void {
    if (axios.isAxiosError<ApiError>(error)) {
        validationErrors.value = error.response?.data?.errors ?? {};
        errorMessage.value = error.response?.data?.message ?? fallback;
        return;
    }
    errorMessage.value = fallback;
}

async function fetchUsers(page = 1): Promise<void> {
    loading.value = true;
    errorMessage.value = '';
    selectedIds.value = [];

    try {
        const response = await http.get('/users', {
            params: {
                page,
                per_page: 10,
                search: filters.search || undefined,
                role: filters.role || undefined,
                status: filters.status || undefined,
            },
        });
        users.value = response.data.data;
        pagination.value = response.data.meta;
    } catch (error) {
        handleError(error, 'Unable to load users.');
    } finally {
        loading.value = false;
    }
}

async function fetchRoles(): Promise<void> {
    if (!auth.can('roles.view')) return;

    try {
        const response = await http.get('/roles', { params: { per_page: 100 } });
        roles.value = response.data.data
            .filter((role: RoleOption) => auth.user?.roles.includes('Super Admin') || !['Super Admin', 'Admin'].includes(role.name))
            .map((role: RoleOption) => ({ id: role.id, name: role.name }));
    } catch (error) {
        handleError(error, 'Unable to load available roles.');
    }
}

function canManageUser(user: UserRecord): boolean {
    const isSuperAdmin = auth.user?.roles.includes('Super Admin') ?? false;
    if (user.roles.some((role) => role.name === 'Super Admin')) return isSuperAdmin;
    if (user.roles.some((role) => role.name === 'Admin') && !isSuperAdmin && user.id !== auth.user?.id) return false;
    return true;
}

function canSelectUser(user: UserRecord): boolean {
    return canManageUser(user) && user.id !== auth.user?.id;
}

function toggleAll(event: Event): void {
    selectedIds.value = (event.target as HTMLInputElement).checked
        ? users.value.filter(canSelectUser).map((user) => user.id)
        : [];
}

function toggleSelected(id: number, event: Event): void {
    const checked = (event.target as HTMLInputElement).checked;
    selectedIds.value = checked
        ? [...selectedIds.value, id]
        : selectedIds.value.filter((selectedId) => selectedId !== id);
}

function resetForm(): void {
    form.name = '';
    form.email = '';
    form.password = '';
    form.password_confirmation = '';
    form.roles = [];
    validationErrors.value = {};
    errorMessage.value = '';
}

function openCreate(): void {
    editingUser.value = null;
    resetForm();
    modalOpen.value = true;
}

function openEdit(user: UserRecord): void {
    if (!canManageUser(user)) return;
    editingUser.value = user;
    resetForm();
    form.name = user.name;
    form.email = user.email;
    form.roles = user.roles.map((role) => role.name);
    modalOpen.value = true;
}

function openDetails(user: UserRecord): void {
    selectedUser.value = user;
    detailsOpen.value = true;
}

async function submit(): Promise<void> {
    submitting.value = true;
    validationErrors.value = {};
    errorMessage.value = '';

    const payload: Record<string, unknown> = { name: form.name, email: form.email };
    if (!editingUser.value || (canAssignRoles.value && editingUser.value.id !== auth.user?.id && !editingUser.value.roles.some((role) => role.name === 'Super Admin'))) {
        payload.roles = form.roles;
    }
    if (form.password) {
        payload.password = form.password;
        payload.password_confirmation = form.password_confirmation;
    }

    try {
        if (editingUser.value) {
            await http.put(`/users/${editingUser.value.id}`, payload);
            message.value = 'User updated successfully.';
        } else {
            payload.password = form.password;
            payload.password_confirmation = form.password_confirmation;
            await http.post('/users', payload);
            message.value = 'User created successfully.';
        }
        modalOpen.value = false;
        await fetchUsers(pagination.value.current_page);
    } catch (error) {
        handleError(error, 'Unable to save this user.');
    } finally {
        submitting.value = false;
    }
}

async function toggleStatus(user: UserRecord): Promise<void> {
    if (user.id === auth.user?.id || !canManageUser(user)) return;
    const nextStatus = user.status === 'active' ? 'inactive' : 'active';
    const action = nextStatus === 'active' ? 'activate' : 'deactivate';
    if (!window.confirm(`Are you sure you want to ${action} ${user.name}?`)) return;

    try {
        await http.patch(`/users/${user.id}/status`, { status: nextStatus });
        message.value = `User ${action}d successfully.`;
        await fetchUsers(pagination.value.current_page);
    } catch (error) {
        handleError(error, `Unable to ${action} this user.`);
    }
}

async function moveToTrash(user: UserRecord): Promise<void> {
    if (!canDelete || !canSelectUser(user)) return;
    if (!window.confirm(`Move ${user.name} to Trash?`)) return;

    try {
        await http.delete(`/users/${user.id}`);
        message.value = 'User moved to Trash.';
        await fetchUsers(pagination.value.current_page);
    } catch (error) {
        handleError(error, 'Unable to move this user to Trash.');
    }
}

async function applyBulkAction(): Promise<void> {
    if (!bulkAction.value || !selectedIds.value.length) return;
    const labels: Record<string, string> = { activate: 'activate', deactivate: 'deactivate', trash: 'move to Trash' };
    if (!window.confirm(`Are you sure you want to ${labels[bulkAction.value]} ${selectedCount.value} selected user(s)?`)) return;

    bulkSubmitting.value = true;
    errorMessage.value = '';
    try {
        await http.post('/users/bulk-action', { action: bulkAction.value, ids: selectedIds.value });
        message.value = `${selectedCount.value} selected user(s) updated successfully.`;
        bulkAction.value = '';
        await fetchUsers(pagination.value.current_page);
    } catch (error) {
        handleError(error, 'Unable to apply the selected bulk action.');
    } finally {
        bulkSubmitting.value = false;
    }
}

function initials(name: string): string {
    return name.split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part.charAt(0).toUpperCase()).join('');
}

function formatDate(value: string | null): string {
    if (!value) return '—';
    return new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(value));
}

onMounted(async () => Promise.all([fetchUsers(), fetchRoles()]));
</script>

<template>
    <div class="module-page">
        <div class="module-page-heading">
            <div class="module-title-wrap">
                <span class="module-title-icon"><i class="bi bi-people-fill"></i></span>
                <div>
                    <h3 class="mb-1">Admin Users Management</h3>
                    <p class="text-body-secondary mb-0">Manage accounts, roles and account status.</p>
                </div>
            </div>
            <nav class="module-breadcrumb" aria-label="breadcrumb">
                <RouterLink :to="{ name: 'dashboard' }"><i class="bi bi-house-fill"></i></RouterLink>
                <i class="bi bi-chevron-right"></i><span>Access Control</span>
                <i class="bi bi-chevron-right"></i><strong>Admin Users</strong>
            </nav>
        </div>

        <div class="module-toolbar">
            <button v-if="canCreate" type="button" class="btn btn-primary" @click="openCreate"><i class="bi bi-plus-lg me-1"></i>Add New Admin</button>
            <RouterLink v-if="canRestore || canForceDelete" :to="{ name: 'users-trash' }" class="btn btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Trash Bin</RouterLink>
            <div class="bulk-toolbar ms-md-1">
                <select v-model="bulkAction" class="form-select" aria-label="Bulk actions">
                    <option value="">-- Bulk Actions --</option>
                    <option v-if="canChangeStatus" value="activate">Activate</option>
                    <option v-if="canChangeStatus" value="deactivate">Deactivate</option>
                    <option v-if="canDelete" value="trash">Move to Trash</option>
                </select>
                <button type="button" class="btn btn-secondary" :disabled="!selectedCount || !bulkAction || bulkSubmitting" @click="applyBulkAction">{{ bulkSubmitting ? 'Applying...' : 'APPLY' }}</button>
                <span v-if="selectedCount" class="selected-count">{{ selectedCount }} selected</span>
            </div>
        </div>

        <div v-if="message" class="alert alert-success alert-dismissible" role="alert">{{ message }}<button type="button" class="btn-close" @click="message = ''"></button></div>
        <div v-if="errorMessage" class="alert alert-danger" role="alert">{{ errorMessage }}</div>

        <div class="card module-filter-card">
            <div class="card-body">
                <form class="row g-3 align-items-end" @submit.prevent="fetchUsers(1)">
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="user-role-filter">FILTER BY ROLE</label>
                        <select id="user-role-filter" v-model="filters.role" class="form-select">
                            <option value="">All Roles</option>
                            <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label" for="user-search">SEARCH ADMINS</label>
                        <input id="user-search" v-model.trim="filters.search" type="search" class="form-control" placeholder="Search by name, email...">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label" for="user-status-filter">STATUS</label>
                        <select id="user-status-filter" v-model="filters.status" class="form-select">
                            <option value="">All Statuses</option><option value="active">Active</option><option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill" aria-label="Search"><i class="bi bi-search"></i></button>
                        <button type="button" class="btn btn-outline-danger flex-fill" @click="filters.search = ''; filters.role = ''; filters.status = ''; fetchUsers(1)"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card module-list-card">
            <div class="card-header"><h5 class="card-title"><i class="bi bi-list-ul text-primary me-2"></i>Admin Users List</h5></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle module-table mb-0">
                    <thead><tr>
                        <th class="text-center"><input class="form-check-input" type="checkbox" :checked="allSelected" :disabled="loading || !users.some(canSelectUser)" @change="toggleAll"></th>
                        <th>Preview</th><th>Name</th><th>Email</th><th>Roles</th><th>Status</th><th class="text-end">Actions</th>
                    </tr></thead>
                    <tbody>
                        <tr v-if="loading"><td colspan="7" class="text-center py-5"><span class="spinner-border spinner-border-sm me-2"></span>Loading users...</td></tr>
                        <tr v-else-if="!users.length"><td colspan="7" class="module-empty">No users found.</td></tr>
                        <template v-else><tr v-for="user in users" :key="user.id">
                            <td class="text-center"><input class="form-check-input" type="checkbox" :checked="selectedIds.includes(user.id)" :disabled="!canSelectUser(user)" @change="toggleSelected(user.id, $event)"></td>
                            <td><span class="module-avatar">{{ initials(user.name) }}</span></td>
                            <td><strong>{{ user.name }}</strong><span v-if="user.id === auth.user?.id" class="badge text-bg-danger ms-2">You</span></td>
                            <td>{{ user.email }}</td>
                            <td><span v-for="role in user.roles" :key="role.id" class="badge text-bg-info me-1">{{ role.name }}</span><span v-if="!user.roles.length" class="text-body-secondary">—</span></td>
                            <td><span class="badge" :class="user.status === 'active' ? 'text-bg-success' : 'text-bg-secondary'">{{ user.status }}</span></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info" title="View" @click="openDetails(user)"><i class="bi bi-eye"></i></button>
                                    <button v-if="canUpdate && canManageUser(user)" type="button" class="btn btn-outline-primary" title="Edit" @click="openEdit(user)"><i class="bi bi-pencil"></i></button>
                                    <button v-if="canChangeStatus && canManageUser(user) && user.id !== auth.user?.id" type="button" class="btn btn-outline-warning" :title="user.status === 'active' ? 'Deactivate' : 'Activate'" @click="toggleStatus(user)"><i :class="user.status === 'active' ? 'bi bi-person-slash' : 'bi bi-person-check'"></i></button>
                                    <button v-if="canDelete && canSelectUser(user)" type="button" class="btn btn-outline-danger" title="Move to Trash" @click="moveToTrash(user)"><i class="bi bi-trash3"></i></button>
                                </div>
                            </td>
                        </tr>
                    </template></tbody>
                </table>
            </div>
            <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span class="small text-body-secondary">{{ pagination.total }} users</span>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary" :disabled="pagination.current_page <= 1 || loading" @click="fetchUsers(pagination.current_page - 1)">Previous</button>
                    <button class="btn btn-outline-secondary" disabled>Page {{ pagination.current_page }} of {{ pagination.last_page }}</button>
                    <button class="btn btn-outline-secondary" :disabled="pagination.current_page >= pagination.last_page || loading" @click="fetchUsers(pagination.current_page + 1)">Next</button>
                </div>
            </div>
        </div>
    </div>

    <BaseModal v-model="modalOpen" :title="editingUser ? 'Update Admin User' : 'Add New Admin User'" size="lg">
        <form @submit.prevent="submit">
            <div class="modal-body">
                <div v-if="errorMessage" class="alert alert-danger py-2">{{ errorMessage }}</div>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Full name</label><input v-model.trim="form.name" class="form-control" :class="{ 'is-invalid': fieldError('name') }" required><div class="invalid-feedback">{{ fieldError('name') }}</div></div>
                    <div class="col-md-6"><label class="form-label">Email address</label><input v-model.trim="form.email" type="email" class="form-control" :class="{ 'is-invalid': fieldError('email') }" required><div class="invalid-feedback">{{ fieldError('email') }}</div></div>
                    <div v-if="!editingUser || editingUser.id !== auth.user?.id" class="col-md-6"><label class="form-label">{{ editingUser ? 'New password (optional)' : 'Password' }}</label><input v-model="form.password" type="password" class="form-control" :class="{ 'is-invalid': fieldError('password') }" :required="!editingUser" autocomplete="new-password"><div class="invalid-feedback">{{ fieldError('password') }}</div></div>
                    <div v-if="!editingUser || editingUser.id !== auth.user?.id" class="col-md-6"><label class="form-label">Confirm password</label><input v-model="form.password_confirmation" type="password" class="form-control" :required="!editingUser && !!form.password" autocomplete="new-password"></div>
                    <div v-if="canAssignRoles && (!editingUser || (editingUser.id !== auth.user?.id && !editingUser.roles.some((role) => role.name === 'Super Admin')))" class="col-12">
                        <label class="form-label fw-semibold">Assign roles</label>
                        <div class="row g-2"><div v-for="role in roles" :key="role.id" class="col-sm-6 col-lg-4"><label class="form-check border rounded-3 px-3 py-2 h-100 d-flex align-items-center gap-2 mb-0"><input v-model="form.roles" class="form-check-input mt-0" type="checkbox" :value="role.name"><span class="form-check-label">{{ role.name }}</span></label></div></div>
                        <div v-if="fieldError('roles')" class="text-danger small mt-1">{{ fieldError('roles') }}</div>
                        <div class="form-text">Administrators can assign operational roles. Only Super Admin can assign Admin or Super Admin.</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light border" @click="modalOpen = false">Cancel</button><button type="submit" class="btn btn-primary" :disabled="submitting"><span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>{{ submitting ? 'Saving...' : editingUser ? 'Save Changes' : 'Create User' }}</button></div>
        </form>
    </BaseModal>

    <BaseModal v-model="detailsOpen" title="Admin User Details">
        <div v-if="selectedUser" class="modal-body"><dl class="row mb-0"><dt class="col-sm-4">Name</dt><dd class="col-sm-8">{{ selectedUser.name }}</dd><dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ selectedUser.email }}</dd><dt class="col-sm-4">Roles</dt><dd class="col-sm-8">{{ selectedUser.roles.map((role) => role.name).join(', ') || 'None' }}</dd><dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge" :class="selectedUser.status === 'active' ? 'text-bg-success' : 'text-bg-secondary'">{{ selectedUser.status }}</span></dd><dt class="col-sm-4">Created</dt><dd class="col-sm-8">{{ formatDate(selectedUser.created_at) }}</dd></dl></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" @click="detailsOpen = false">Close</button></div>
    </BaseModal>
</template>
