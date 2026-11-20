<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const primaryRole = computed(() => auth.user?.roles[0] ?? 'Member');

const summaryCards = [
    { label: 'Students', value: '0', icon: 'bi-people', color: 'text-primary' },
    { label: 'Teachers', value: '0', icon: 'bi-person-workspace', color: 'text-success' },
    { label: 'Courses', value: '0', icon: 'bi-journal-bookmark', color: 'text-warning' },
    { label: 'Batches', value: '0', icon: 'bi-collection', color: 'text-danger' },
];
</script>

<template>
    <div class="row g-3">
        <div v-for="card in summaryCards" :key="card.label" class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dashboard-stat-icon rounded-3 d-flex align-items-center justify-content-center">
                        <i :class="['bi', card.icon, card.color, 'fs-3']"></i>
                    </div>
                    <div>
                        <p class="text-body-secondary mb-1">{{ card.label }}</p>
                        <h3 class="mb-0 fw-semibold">{{ card.value }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <h5 class="card-title fw-semibold mb-2">
                        Welcome back, {{ auth.user?.name }}!
                    </h5>
                    <p class="text-body-secondary mb-0">
                        You are signed in to CoachDesk and your workspace is ready.
                    </p>
                </div>
                <span class="badge bg-primary-subtle text-primary px-3 py-2">
                    {{ primaryRole }}
                </span>
            </div>
        </div>
    </div>
</template>
