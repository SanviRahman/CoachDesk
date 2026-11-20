<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink, useRoute } from 'vue-router';

const route = useRoute();
const title = computed(() => String(route.meta.title ?? 'Module'));
const description = computed(() => String(route.meta.description ?? 'This module is planned for an upcoming development phase.'));
</script>

<template>
    <div class="row justify-content-center py-4 py-lg-5">
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body p-4 p-lg-5">
                    <div class="coming-soon-icon rounded-circle d-inline-flex align-items-center justify-content-center mb-4">
                        <i class="bi bi-tools fs-2 text-primary"></i>
                    </div>
                    <span class="badge bg-primary-subtle text-primary mb-3">UPCOMING MODULE</span>
                    <h4 class="mb-2">{{ title }} is coming soon</h4>
                    <p class="text-body-secondary mx-auto mb-4" style="max-width: 34rem">
                        {{ description }}
                    </p>
                    <RouterLink :to="{ name: 'dashboard' }" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Back to Dashboard
                    </RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>
