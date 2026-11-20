<script setup lang="ts">
const summaryCards = [
    { label: 'Students', value: '0', icon: 'bi-people', color: 'text-primary' },
    { label: 'Teachers', value: '0', icon: 'bi-person-workspace', color: 'text-success' },
    { label: 'Courses', value: '0', icon: 'bi-journal-bookmark', color: 'text-warning' },
    { label: 'Batches', value: '0', icon: 'bi-collection', color: 'text-danger' },
];
</script>

<template>
    <div class="app-wrapper">
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <span class="navbar-brand fw-semibold">CoachDesk</span>
                <span class="badge text-bg-success ms-auto">Vue SPA Ready</span>
            </div>
        </nav>

        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <h3 class="mb-0">Dashboard</h3>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    <div class="row g-3">
                        <div v-for="card in summaryCards" :key="card.label" class="col-12 col-sm-6 col-xl-3">
                            <div class="card h-100">
                                <div class="card-body d-flex align-items-center gap-3">
                                    <i :class="['bi', card.icon, card.color, 'fs-2']"></i>
                                    <div>
                                        <p class="text-body-secondary mb-1">{{ card.label }}</p>
                                        <h3 class="mb-0">{{ card.value }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title">Welcome to CoachDesk</h5>
                            <p class="card-text mb-0">
                                Laravel 13, Vue 3, Pinia, Vue Router and AdminLTE 4 are connected.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>