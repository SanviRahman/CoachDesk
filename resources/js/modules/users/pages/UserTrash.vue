<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, reactive, ref } from 'vue';
import { RouterLink } from 'vue-router';
import http from '@/api/http';
import { useAuthStore } from '@/stores/auth';

interface RoleOption { id: number; name: string }
interface UserRecord { id: number; name: string; email: string; status: 'active' | 'inactive'; roles: RoleOption[]; deleted_at: string | null }
interface PaginationMeta { current_page: number; last_page: number; total: number }
interface ApiError { message?: string; errors?: Record<string, string[]> }

const auth = useAuthStore();
const users = ref<UserRecord[]>([]);
const roles = ref<RoleOption[]>([]);
const loading = ref(false);
const processing = ref(false);
const search = ref('');
const role = ref('');
const status = ref('');
const action = ref('');
const selectedIds = ref<number[]>([]);
const message = ref('');
const errorMessage = ref('');
const pagination = ref<PaginationMeta>({ current_page: 1, last_page: 1, total: 0 });
const selectedCount = computed(() => selectedIds.value.length);
const allSelected = computed(() => users.value.length > 0 && users.value.every((user) => selectedIds.value.includes(user.id)));
const canRestore = computed(() => auth.can('users.restore'));
const canForceDelete = computed(() => auth.can('users.force_delete'));

function handleError(error: unknown): void {
    errorMessage.value = axios.isAxiosError<ApiError>(error)
        ? error.response?.data?.message ?? 'Unable to complete the action.'
        : 'Unable to complete the action.';
}

async function fetchUsers(page = 1): Promise<void> {
    loading.value = true;
    selectedIds.value = [];
    errorMessage.value = '';
    try {
        const response = await http.get('/users/trash', { params: { page, per_page: 10, search: search.value || undefined, role: role.value || undefined, status: status.value || undefined } });
        users.value = response.data.data;
        pagination.value = response.data.meta;
    } catch (error) { handleError(error); }
    finally { loading.value = false; }
}

async function fetchRoles(): Promise<void> {
    try {
        const response = await http.get('/roles', { params: { per_page: 100 } });
        roles.value = response.data.data.map((item: RoleOption) => ({ id: item.id, name: item.name }));
    } catch (error) { handleError(error); }
}

function toggleAll(event: Event): void {
    selectedIds.value = (event.target as HTMLInputElement).checked ? users.value.map((user) => user.id) : [];
}
function toggleSelected(id: number, event: Event): void {
    selectedIds.value = (event.target as HTMLInputElement).checked ? [...selectedIds.value, id] : selectedIds.value.filter((value) => value !== id);
}
function formatDate(value: string | null): string {
    return value ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : '—';
}

async function runAction(nextAction: 'restore' | 'force_delete', ids: number[]): Promise<void> {
    if (!ids.length) return;
    const label = nextAction === 'restore' ? 'restore' : 'permanently delete';
    if (!window.confirm(`Are you sure you want to ${label} ${ids.length} user(s)?`)) return;
    processing.value = true;
    errorMessage.value = '';
    try {
        await http.post('/users/bulk-action', { action: nextAction, ids });
        message.value = `${ids.length} user(s) ${nextAction === 'restore' ? 'restored' : 'permanently deleted'} successfully.`;
        action.value = '';
        await fetchUsers(pagination.value.current_page);
    } catch (error) { handleError(error); }
    finally { processing.value = false; }
}

async function applyBulkAction(): Promise<void> {
    if (action.value === 'restore' || action.value === 'force_delete') await runAction(action.value, selectedIds.value);
}

async function restoreUser(user: UserRecord): Promise<void> { await runAction('restore', [user.id]); }
async function forceDeleteUser(user: UserRecord): Promise<void> { await runAction('force_delete', [user.id]); }

onMounted(async () => Promise.all([fetchUsers(), fetchRoles()]));
</script>

<template>
    <div class="module-page">
        <div class="module-page-heading"><div class="module-title-wrap"><span class="module-title-icon module-title-icon-danger"><i class="bi bi-trash3-fill"></i></span><div><h3 class="mb-1">Trashed Admin Users</h3><p class="text-body-secondary mb-0">Restore accounts or permanently delete eligible records.</p></div></div><nav class="module-breadcrumb"><RouterLink :to="{ name: 'dashboard' }"><i class="bi bi-house-fill"></i></RouterLink><i class="bi bi-chevron-right"></i><span>Access Control</span><i class="bi bi-chevron-right"></i><RouterLink :to="{ name: 'users' }">Admin Users</RouterLink><i class="bi bi-chevron-right"></i><strong>Trash</strong></nav></div>
        <div class="module-toolbar"><RouterLink :to="{ name: 'users' }" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Admin Users</RouterLink><div class="bulk-toolbar"><select v-model="action" class="form-select"><option value="">-- Bulk Actions --</option><option v-if="canRestore" value="restore">Restore</option><option v-if="canForceDelete" value="force_delete">Force Delete</option></select><button class="btn btn-secondary" :disabled="!selectedCount || !action || processing" @click="applyBulkAction">{{ processing ? 'Applying...' : 'APPLY' }}</button><span v-if="selectedCount" class="selected-count">{{ selectedCount }} selected</span></div></div>
        <div v-if="message" class="alert alert-success">{{ message }}</div><div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
        <div class="card module-filter-card"><div class="card-body"><form class="row g-3 align-items-end" @submit.prevent="fetchUsers(1)"><div class="col-12 col-md-3"><label class="form-label">FILTER BY ROLE</label><select v-model="role" class="form-select"><option value="">All Roles</option><option v-for="item in roles" :key="item.id" :value="item.name">{{ item.name }}</option></select></div><div class="col-12 col-md-5"><label class="form-label">SEARCH TRASHED USERS</label><input v-model.trim="search" class="form-control" type="search" placeholder="Search by name, email..."></div><div class="col-6 col-md-2"><label class="form-label">STATUS</label><select v-model="status" class="form-select"><option value="">All Statuses</option><option value="active">Active</option><option value="inactive">Inactive</option></select></div><div class="col-6 col-md-2 d-flex gap-2"><button class="btn btn-primary flex-fill" type="submit"><i class="bi bi-search"></i></button><button class="btn btn-outline-danger flex-fill" type="button" @click="search = ''; role = ''; status = ''; fetchUsers(1)"><i class="bi bi-arrow-counterclockwise"></i>Reset</button></div></form></div></div>
        <div class="card module-list-card"><div class="card-header"><h5 class="card-title text-danger"><i class="bi bi-trash3 me-2"></i>Trash Bin</h5></div><div class="table-responsive"><table class="table table-hover align-middle module-table mb-0"><thead><tr><th class="text-center"><input class="form-check-input" type="checkbox" :checked="allSelected" :disabled="loading" @change="toggleAll"></th><th>Name</th><th>Email</th><th>Roles</th><th>Status</th><th>Deleted At</th><th class="text-end">Actions</th></tr></thead><tbody><tr v-if="loading"><td colspan="7" class="text-center py-5"><span class="spinner-border spinner-border-sm me-2"></span>Loading trash...</td></tr><tr v-else-if="!users.length"><td colspan="7" class="module-empty"><i class="bi bi-trash3 d-block mb-2"></i>No Trashed Users Found</td></tr><template v-else><tr v-for="user in users" :key="user.id"><td class="text-center"><input class="form-check-input" type="checkbox" :checked="selectedIds.includes(user.id)" @change="toggleSelected(user.id, $event)"></td><td><strong>{{ user.name }}</strong></td><td>{{ user.email }}</td><td><span v-for="item in user.roles" :key="item.id" class="badge text-bg-info me-1">{{ item.name }}</span></td><td><span class="badge" :class="user.status === 'active' ? 'text-bg-success' : 'text-bg-secondary'">{{ user.status }}</span></td><td>{{ formatDate(user.deleted_at) }}</td><td class="text-end"><div class="btn-group btn-group-sm"><button v-if="canRestore" class="btn btn-outline-success" title="Restore" :disabled="processing" @click="restoreUser(user)"><i class="bi bi-arrow-counterclockwise"></i></button><button v-if="canForceDelete" class="btn btn-outline-danger" title="Force Delete" :disabled="processing" @click="forceDeleteUser(user)"><i class="bi bi-x-octagon"></i></button></div></td></tr></template></tbody></table></div><div class="card-footer d-flex justify-content-between align-items-center"><span class="small text-body-secondary">{{ pagination.total }} trashed users</span><div class="btn-group btn-group-sm"><button class="btn btn-outline-secondary" :disabled="pagination.current_page <= 1 || loading" @click="fetchUsers(pagination.current_page - 1)">Previous</button><button class="btn btn-outline-secondary" disabled>Page {{ pagination.current_page }} of {{ pagination.last_page }}</button><button class="btn btn-outline-secondary" :disabled="pagination.current_page >= pagination.last_page || loading" @click="fetchUsers(pagination.current_page + 1)">Next</button></div></div></div>
    </div>
</template>
