<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
const auth = useAuthStore();
const modules = computed(() => [
    { title: 'Admin Users', description: 'Restore or permanently delete user accounts.', route: 'users-trash', permissions: ['users.restore', 'users.force_delete'], icon: 'bi-people' },
    { title: 'Roles', description: 'Restore or permanently delete unused custom roles.', route: 'roles-trash', permissions: ['roles.restore', 'roles.force_delete'], icon: 'bi-shield-lock' },
    { title: 'Permissions', description: 'Restore or permanently delete eligible permissions.', route: 'permissions-trash', permissions: ['permissions.restore', 'permissions.force_delete'], icon: 'bi-key' },
].filter((item) => auth.canAny(item.permissions)));
</script>

<template>
    <div class="module-page"><div class="module-page-heading"><div class="module-title-wrap"><span class="module-title-icon module-title-icon-danger"><i class="bi bi-trash3-fill"></i></span><div><h3 class="mb-1">Trash Management</h3><p class="text-body-secondary mb-0">Choose a module to review its deleted records.</p></div></div></div><div class="row g-3"><div v-for="item in modules" :key="item.route" class="col-12 col-md-6 col-xl-4"><RouterLink :to="{ name: item.route }" class="card trash-module-card text-decoration-none h-100"><div class="card-body d-flex align-items-start gap-3"><span class="trash-module-icon"><i :class="['bi', item.icon]"></i></span><div><h5 class="mb-1 text-body">{{ item.title }}</h5><p class="mb-0 text-body-secondary small">{{ item.description }}</p></div></div></RouterLink></div></div><div v-if="!modules.length" class="alert alert-info">You do not have permission to access any Trash module.</div></div>
</template>
