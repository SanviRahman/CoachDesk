<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, reactive, ref } from 'vue';
import { RouterLink } from 'vue-router';
import BaseModal from '@/components/common/BaseModal.vue';
import http from '@/api/http';
import PermissionSelector from '@/modules/roles/PermissionSelector.vue';
import { useAuthStore } from '@/stores/auth';

interface PermissionOption { id: number; name: string; group: string }
interface RoleRecord { id: number; name: string; guard_name: string; permissions_count: number; users_count: number; permissions?: string[]; is_system: boolean; is_protected: boolean; created_at: string | null }
interface ApiError { message?: string; errors?: Record<string, string[]> }
interface PaginationMeta { current_page: number; last_page: number; total: number }

const auth = useAuthStore();
const roles = ref<RoleRecord[]>([]);
const permissions = ref<PermissionOption[]>([]);
const loading = ref(false);
const submitting = ref(false);
const bulkSubmitting = ref(false);
const modalOpen = ref(false);
const detailsOpen = ref(false);
const editingRole = ref<RoleRecord | null>(null);
const selectedRole = ref<RoleRecord | null>(null);
const form = reactive({ name: '', permissions: [] as string[] });
const search = ref('');
const guard = ref('');
const bulkAction = ref('');
const selectedIds = ref<number[]>([]);
const message = ref('');
const errorMessage = ref('');
const validationErrors = ref<Record<string, string[]>>({});
const pagination = ref<PaginationMeta>({ current_page: 1, last_page: 1, total: 0 });
const canCreate = computed(() => auth.can('roles.create'));
const canUpdate = computed(() => auth.can('roles.update'));
const canDelete = computed(() => auth.can('roles.delete'));
const canRestore = computed(() => auth.can('roles.restore'));
const canForceDelete = computed(() => auth.can('roles.force_delete'));
const canAssignPermissions = computed(() => auth.can('permissions.assign'));
const selectedCount = computed(() => selectedIds.value.length);
const allSelected = computed(() => roles.value.filter((role) => !role.is_system && role.users_count === 0).length > 0 && roles.value.filter((role) => !role.is_system && role.users_count === 0).every((role) => selectedIds.value.includes(role.id)));

function fieldError(field: string): string { return validationErrors.value[field]?.[0] ?? ''; }
function handleError(error: unknown, fallback: string): void {
    if (axios.isAxiosError<ApiError>(error)) { validationErrors.value = error.response?.data?.errors ?? {}; errorMessage.value = error.response?.data?.message ?? fallback; }
    else errorMessage.value = fallback;
}

async function fetchRoles(page = 1): Promise<void> {
    loading.value = true; selectedIds.value = []; errorMessage.value = '';
    try {
        const response = await http.get('/roles', { params: { page, per_page: 10, search: search.value || undefined, guard: guard.value || undefined } });
        roles.value = response.data.data; pagination.value = response.data.meta;
    } catch (error) { handleError(error, 'Unable to load roles.'); }
    finally { loading.value = false; }
}

async function fetchPermissions(): Promise<void> {
    if (!canAssignPermissions.value) return;
    try { const response = await http.get('/permissions', { params: { per_page: 100 } }); permissions.value = response.data.data; }
    catch (error) { handleError(error, 'Unable to load permissions.'); }
}

function resetForm(): void { form.name = ''; form.permissions = []; validationErrors.value = {}; errorMessage.value = ''; }
async function openCreate(): Promise<void> { editingRole.value = null; resetForm(); await fetchPermissions(); modalOpen.value = true; }
async function openEdit(role: RoleRecord): Promise<void> {
    if (role.is_protected) return;
    editingRole.value = role; resetForm();
    try { const response = await http.get(`/roles/${role.id}`); const selected = response.data.data as RoleRecord; form.name = selected.name; form.permissions = selected.permissions ?? []; await fetchPermissions(); modalOpen.value = true; }
    catch (error) { handleError(error, 'Unable to load this role.'); }
}
function openDetails(role: RoleRecord): void { selectedRole.value = role; detailsOpen.value = true; }
function canSelectRole(role: RoleRecord): boolean { return !role.is_system && role.users_count === 0; }
function toggleAll(event: Event): void { selectedIds.value = (event.target as HTMLInputElement).checked ? roles.value.filter(canSelectRole).map((role) => role.id) : []; }
function toggleSelected(id: number, event: Event): void { selectedIds.value = (event.target as HTMLInputElement).checked ? [...selectedIds.value, id] : selectedIds.value.filter((value) => value !== id); }

async function submit(): Promise<void> {
    submitting.value = true; validationErrors.value = {}; errorMessage.value = '';
    const payload: Record<string, unknown> = { name: form.name };
    if (canAssignPermissions.value) payload.permissions = form.permissions;
    try {
        if (editingRole.value) { await http.put(`/roles/${editingRole.value.id}`, payload); message.value = 'Role updated successfully.'; }
        else { await http.post('/roles', payload); message.value = 'Role created successfully.'; }
        modalOpen.value = false; await fetchRoles(pagination.value.current_page);
    } catch (error) { handleError(error, 'Unable to save this role.'); }
    finally { submitting.value = false; }
}

async function moveToTrash(role: RoleRecord): Promise<void> {
    if (!canDelete || !canSelectRole(role) || !window.confirm(`Move the ${role.name} role to Trash?`)) return;
    try { await http.delete(`/roles/${role.id}`); message.value = 'Role moved to Trash.'; await fetchRoles(pagination.value.current_page); }
    catch (error) { handleError(error, 'Unable to move this role to Trash.'); }
}

async function applyBulkAction(): Promise<void> {
    if (bulkAction.value !== 'trash' || !selectedIds.value.length) return;
    if (!window.confirm(`Move ${selectedCount.value} selected role(s) to Trash?`)) return;
    bulkSubmitting.value = true;
    try { await http.post('/roles/bulk-action', { action: 'trash', ids: selectedIds.value }); message.value = 'Selected roles moved to Trash.'; bulkAction.value = ''; await fetchRoles(pagination.value.current_page); }
    catch (error) { handleError(error, 'Unable to apply the bulk action.'); }
    finally { bulkSubmitting.value = false; }
}

onMounted(async () => { await Promise.all([fetchRoles(), fetchPermissions()]); });
</script>

<template>
    <div class="module-page">
        <div class="module-page-heading"><div class="module-title-wrap"><span class="module-title-icon"><i class="bi bi-shield-lock-fill"></i></span><div><h3 class="mb-1">Roles &amp; Permissions Management</h3><p class="text-body-secondary mb-0">Create roles and assign access to CoachDesk modules.</p></div></div><nav class="module-breadcrumb"><RouterLink :to="{ name: 'dashboard' }"><i class="bi bi-house-fill"></i></RouterLink><i class="bi bi-chevron-right"></i><span>Access Control</span><i class="bi bi-chevron-right"></i><strong>Roles List</strong></nav></div>
        <div class="module-toolbar"><button v-if="canCreate" class="btn btn-primary" type="button" @click="openCreate"><i class="bi bi-plus-lg me-1"></i>Add New Role</button><RouterLink v-if="canRestore || canForceDelete" :to="{ name: 'roles-trash' }" class="btn btn-outline-danger"><i class="bi bi-trash3 me-1"></i>Trash Bin</RouterLink><div class="bulk-toolbar"><select v-model="bulkAction" class="form-select"><option value="">-- Bulk Actions --</option><option v-if="canDelete" value="trash">Move to Trash</option></select><button class="btn btn-secondary" :disabled="!selectedCount || !bulkAction || bulkSubmitting" @click="applyBulkAction">{{ bulkSubmitting ? 'Applying...' : 'APPLY' }}</button><span v-if="selectedCount" class="selected-count">{{ selectedCount }} selected</span></div></div>
        <div v-if="message" class="alert alert-success">{{ message }}</div><div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
        <div class="card module-filter-card"><div class="card-body"><form class="row g-3 align-items-end" @submit.prevent="fetchRoles(1)"><div class="col-12 col-md-3"><label class="form-label">FILTER BY GUARD</label><select v-model="guard" class="form-select"><option value="">All Guards</option><option value="web">web</option></select></div><div class="col-12 col-md-7"><label class="form-label">SEARCH ROLES</label><input v-model.trim="search" type="search" class="form-control" placeholder="Search role name..."></div><div class="col-12 col-md-2 d-flex gap-2"><button class="btn btn-primary flex-fill" type="submit"><i class="bi bi-search"></i></button><button class="btn btn-outline-danger flex-fill" type="button" @click="search = ''; guard = ''; fetchRoles(1)"><i class="bi bi-arrow-counterclockwise"></i>Reset</button></div></form></div></div>
        <div class="card module-list-card"><div class="card-header"><h5 class="card-title"><i class="bi bi-list-ul text-primary me-2"></i>Roles List</h5></div><div class="table-responsive"><table class="table table-hover align-middle module-table mb-0"><thead><tr><th class="text-center"><input class="form-check-input" type="checkbox" :checked="allSelected" :disabled="loading" @change="toggleAll"></th><th>Role Name</th><th>Guard Name</th><th>Permissions Count</th><th>Users Count</th><th class="text-end">Actions</th></tr></thead><tbody><tr v-if="loading"><td colspan="6" class="text-center py-5"><span class="spinner-border spinner-border-sm me-2"></span>Loading roles...</td></tr><tr v-else-if="!roles.length"><td colspan="6" class="module-empty">No roles found.</td></tr><template v-else><tr v-for="role in roles" :key="role.id"><td class="text-center"><input class="form-check-input" type="checkbox" :checked="selectedIds.includes(role.id)" :disabled="!canSelectRole(role)" @change="toggleSelected(role.id, $event)"></td><td><strong><i v-if="role.is_protected" class="bi bi-shield-lock-fill text-primary me-1"></i>{{ role.name }}</strong><span v-if="role.is_protected" class="badge text-bg-danger ms-2">Protected</span><span v-else-if="role.is_system" class="badge text-bg-secondary ms-2">Built-in</span></td><td><span class="badge text-bg-secondary">{{ role.guard_name }}</span></td><td><span class="badge text-bg-info">{{ role.permissions_count }}</span></td><td><span class="badge text-bg-success">{{ role.users_count }}</span></td><td class="text-end"><div class="btn-group btn-group-sm"><button class="btn btn-outline-info" title="View" @click="openDetails(role)"><i class="bi bi-eye"></i></button><button v-if="canUpdate && !role.is_protected" class="btn btn-outline-primary" title="Edit" @click="openEdit(role)"><i class="bi bi-pencil"></i></button><button v-if="canDelete && !role.is_system" class="btn btn-outline-danger" title="Move to Trash" :disabled="role.users_count > 0" @click="moveToTrash(role)"><i class="bi bi-trash3"></i></button></div></td></tr></template></tbody></table></div><div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2"><span class="small text-body-secondary">{{ pagination.total }} roles</span><div class="btn-group btn-group-sm"><button class="btn btn-outline-secondary" :disabled="pagination.current_page <= 1 || loading" @click="fetchRoles(pagination.current_page - 1)">Previous</button><button class="btn btn-outline-secondary" disabled>Page {{ pagination.current_page }} of {{ pagination.last_page }}</button><button class="btn btn-outline-secondary" :disabled="pagination.current_page >= pagination.last_page || loading" @click="fetchRoles(pagination.current_page + 1)">Next</button></div></div></div>
    </div>

    <BaseModal v-model="modalOpen" :title="editingRole ? 'Update Role' : 'Create Role'" size="xl"><form @submit.prevent="submit"><div class="modal-body"><div v-if="errorMessage" class="alert alert-danger py-2">{{ errorMessage }}</div><div class="mb-3"><label class="form-label fw-semibold">Role name</label><input v-model.trim="form.name" class="form-control" :class="{ 'is-invalid': fieldError('name') }" placeholder="e.g. Academic Manager" :disabled="!!editingRole?.is_system" required><div class="invalid-feedback">{{ fieldError('name') }}</div><div v-if="editingRole?.is_system" class="form-text">Built-in role names cannot be changed.</div></div><div v-if="canAssignPermissions"><PermissionSelector v-model="form.permissions" :permissions="permissions"/><div v-if="fieldError('permissions')" class="text-danger small mt-2">{{ fieldError('permissions') }}</div></div><div v-else class="alert alert-info mb-0">Permission assignment is restricted to authorized administrators.</div></div><div class="modal-footer"><button type="button" class="btn btn-light border" @click="modalOpen = false">Cancel</button><button type="submit" class="btn btn-primary" :disabled="submitting"><span v-if="submitting" class="spinner-border spinner-border-sm me-1"></span>{{ submitting ? 'Saving...' : editingRole ? 'Save Changes' : 'Create Role' }}</button></div></form></BaseModal>
    <BaseModal v-model="detailsOpen" title="Role Details"><div v-if="selectedRole" class="modal-body"><dl class="row mb-0"><dt class="col-sm-4">Role</dt><dd class="col-sm-8">{{ selectedRole.name }}</dd><dt class="col-sm-4">Guard</dt><dd class="col-sm-8">{{ selectedRole.guard_name }}</dd><dt class="col-sm-4">Users</dt><dd class="col-sm-8">{{ selectedRole.users_count }}</dd><dt class="col-sm-4">Permissions</dt><dd class="col-sm-8">{{ selectedRole.permissions_count }}</dd></dl></div><div class="modal-footer"><button class="btn btn-light border" @click="detailsOpen = false">Close</button></div></BaseModal>
</template>
