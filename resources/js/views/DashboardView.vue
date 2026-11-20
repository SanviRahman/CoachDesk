<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();
const loggingOut = ref(false);
const logoutError = ref('');

const summaryCards = [
    { label: 'Students', value: '0', icon: 'bi-people', color: 'text-primary' },
    { label: 'Teachers', value: '0', icon: 'bi-person-workspace', color: 'text-success' },
    { label: 'Courses', value: '0', icon: 'bi-journal-bookmark', color: 'text-warning' },
    { label: 'Batches', value: '0', icon: 'bi-collection', color: 'text-danger' },
];

async function logout(): Promise<void> {
    loggingOut.value = true;
    logoutError.value = '';

    try {
        await auth.logout();
        await router.replace({ name: 'login' });
    } catch {
        logoutError.value = 'Unable to sign out. Please try again.';
    } finally {
        loggingOut.value = false;
    }
}
</script>

<template>
    <div class="app-wrapper">
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <span class="navbar-brand fw-semibold">CoachDesk</span>
                <div class="d-flex align-items-center gap-3 ms-auto">
                    <span class="small text-body-secondary">{{ auth.user?.name }}</span>
                    <button type="button" class="btn btn-outline-secondary btn-sm" :disabled="loggingOut" @click="logout">
                        <i class="bi bi-box-arrow-right me-1"></i>
                        {{ loggingOut ? 'Signing out...' : 'Sign out' }}
                    </button>
                </div>
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
                    <div v-if="logoutError" class="alert alert-danger" role="alert">
                        {{ logoutError }}
                    </div>
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
                            <h5 class="card-title">Welcome, {{ auth.user?.name }}</h5>
                            <p class="card-text mb-0">
                                You are signed in to CoachDesk.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
