<script setup lang="ts">
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import http from '@/api/http';
import { useAuthStore } from '@/stores/auth';

interface RoleRecord { id: number; name: string; guard_name: string; permissions_count: number; users_count: number; deleted_at: string | null }
interface PaginationMeta { current_page: number; last_page: number; total: number }
interface ApiError { message?: string; errors?: Record<string, string[]> }
const auth = useAuthStore();
const roles = ref<RoleRecord[]>([]);
const search = ref('');
const guard = ref('');
const loading = ref(false);
const processing = ref(false);
const action = ref('');
const selectedIds = ref<number[]>([]);
const message = ref('');
const errorMessage = ref('');
const pagination = ref<PaginationMeta>({ current_page: 1, last_page: 1, total: 0 });
const selectedCount = () => selectedIds.value.length;
const selectableIds = computed(() => roles.value.filter((role) => role.users_count === 0).map((role) => role.id));
const allSelected = computed(() => selectableIds.value.length > 0 && selectableIds.value.every((id) => selectedIds.value.includes(id)));
const canRestore = auth.can('roles.restore');
const canForceDelete = auth.can('roles.force_delete');
function handleError(error: unknown): void { errorMessage.value = axios.isAxiosError<ApiError>(error) ? error.response?.data?.message ?? 'Unable to complete the action.' : 'Unable to complete the action.'; }
async function fetchRoles(page = 1): Promise<void> { loading.value = true; selectedIds.value = []; try { const response = await http.get('/roles/trash', { params: { page, per_page: 10, search: search.value || undefined, guard: guard.value || undefined } }); roles.value = response.data.data; pagination.value = response.data.meta; } catch (error) { handleError(error); } finally { loading.value = false; } }
function toggleAll(event: Event): void { selectedIds.value = (event.target as HTMLInputElement).checked ? [...selectableIds.value] : []; }
function toggleSelected(id: number, event: Event): void { selectedIds.value = (event.target as HTMLInputElement).checked ? [...selectedIds.value, id] : selectedIds.value.filter((value) => value !== id); }
function formatDate(value: string | null): string { return value ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : '—'; }
async function runAction(next: 'restore' | 'force_delete', ids: number[]): Promise<void> { if (!ids.length) return; const label = next === 'restore' ? 'restore' : 'permanently delete'; if (!window.confirm(`Are you sure you want to ${label} ${ids.length} role(s)?`)) return; processing.value = true; try { await http.post('/roles/bulk-action', { action: next, ids }); message.value = `${ids.length} role(s) ${next === 'restore' ? 'restored' : 'permanently deleted'} successfully.`; action.value = ''; await fetchRoles(pagination.value.current_page); } catch (error) { handleError(error); } finally { processing.value = false; } }
async function applyBulkAction(): Promise<void> { if (action.value === 'restore' || action.value === 'force_delete') await runAction(action.value, selectedIds.value); }
async function restoreRole(role: RoleRecord): Promise<void> { await runAction('restore', [role.id]); }
async function forceRole(role: RoleRecord): Promise<void> { await runAction('force_delete', [role.id]); }
onMounted(() => fetchRoles());
</script>

<template>
    <div class="module-page"><div class="module-page-heading"><div class="module-title-wrap"><span class="module-title-icon module-title-icon-danger"><i class="bi bi-trash3-fill"></i></span><div><h3 class="mb-1">Trashed Roles</h3><p class="text-body-secondary mb-0">Restore custom roles or permanently delete unused roles.</p></div></div><nav class="module-breadcrumb"><RouterLink :to="{ name: 'dashboard' }"><i class="bi bi-house-fill"></i></RouterLink><i class="bi bi-chevron-right"></i><span>Access Control</span><i class="bi bi-chevron-right"></i><RouterLink :to="{ name: 'roles' }">Roles</RouterLink><i class="bi bi-chevron-right"></i><strong>Trash</strong></nav></div>
        <div class="module-toolbar"><RouterLink :to="{ name: 'roles' }" class="btn btn-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Roles</RouterLink><div class="bulk-toolbar"><select v-model="action" class="form-select"><option value="">-- Bulk Actions --</option><option v-if="canRestore" value="restore">Restore</option><option v-if="canForceDelete" value="force_delete">Force Delete</option></select><button class="btn btn-secondary" :disabled="!selectedCount() || !action || processing" @click="applyBulkAction">{{ processing ? 'Applying...' : 'APPLY' }}</button><span v-if="selectedCount()" class="selected-count">{{ selectedCount() }} selected</span></div></div>
        <div v-if="message" class="alert alert-success">{{ message }}</div><div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
        <div class="card module-filter-card"><div class="card-body"><form class="row g-3 align-items-end" @submit.prevent="fetchRoles(1)"><div class="col-12 col-md-3"><label class="form-label">FILTER BY GUARD</label><select v-model="guard" class="form-select"><option value="">All Guards</option><option value="web">web</option></select></div><div class="col-12 col-md-7"><label class="form-label">SEARCH TRASHED ROLES</label><input v-model.trim="search" class="form-control" type="search" placeholder="Search role name..."></div><div class="col-12 col-md-2 d-flex gap-2"><button class="btn btn-primary flex-fill" type="submit"><i class="bi bi-search"></i></button><button class="btn btn-outline-danger flex-fill" type="button" @click="search = ''; guard = ''; fetchRoles(1)"><i class="bi bi-arrow-counterclockwise"></i>Reset</button></div></form></div></div>
        <div class="card module-list-card"><div class="card-header"><h5 class="card-title text-danger"><i class="bi bi-trash3 me-2"></i>Trash Bin</h5></div><div class="table-responsive"><table class="table table-hover align-middle module-table mb-0"><thead><tr><th class="text-center"><input class="form-check-input" type="checkbox" :checked="allSelected" :disabled="loading" @change="toggleAll"></th><th>Role Name</th><th>Guard Name</th><th>Permissions Count</th><th>Users Count</th><th>Deleted At</th><th class="text-end">Actions</th></tr></thead><tbody><tr v-if="loading"><td colspan="7" class="text-center py-5"><span class="spinner-border spinner-border-sm me-2"></span>Loading trash...</td></tr><tr v-else-if="!roles.length"><td colspan="7" class="module-empty"><i class="bi bi-shield-x d-block mb-2"></i>No Trashed Roles Found</td></tr><template v-else><tr v-for="role in roles" :key="role.id"><td class="text-center"><input class="form-check-input" type="checkbox" :checked="selectedIds.includes(role.id)" :disabled="role.users_count > 0" @change="toggleSelected(role.id, $event)"></td><td><strong>{{ role.name }}</strong></td><td><span class="badge text-bg-secondary">{{ role.guard_name }}</span></td><td>{{ role.permissions_count }}</td><td>{{ role.users_count }}</td><td>{{ formatDate(role.deleted_at) }}</td><td class="text-end"><div class="btn-group btn-group-sm"><button v-if="canRestore" class="btn btn-outline-success" title="Restore" :disabled="processing" @click="restoreRole(role)"><i class="bi bi-arrow-counterclockwise"></i></button><button v-if="canForceDelete" class="btn btn-outline-danger" title="Force Delete" :disabled="processing || role.users_count > 0" @click="forceRole(role)"><i class="bi bi-x-octagon"></i></button></div></td></tr></template></tbody></table></div><div class="card-footer d-flex justify-content-between align-items-center"><span class="small text-body-secondary">{{ pagination.total }} trashed roles</span><div class="btn-group btn-group-sm"><button class="btn btn-outline-secondary" :disabled="pagination.current_page <= 1 || loading" @click="fetchRoles(pagination.current_page - 1)">Previous</button><button class="btn btn-outline-secondary" disabled>Page {{ pagination.current_page }} of {{ pagination.last_page }}</button><button class="btn btn-outline-secondary" :disabled="pagination.current_page >= pagination.last_page || loading" @click="fetchRoles(pagination.current_page + 1)">Next</button></div></div></div>
    </div>
</template>
