<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink, RouterView, useRoute } from 'vue-router';
import Sidebar from '@/components/navigation/Sidebar.vue';
import Navbar from '@/components/navigation/Navbar.vue';
import Footer from '@/components/navigation/Footer.vue';

const route = useRoute();

const pageTitle = computed(() => String(route.meta.title ?? 'Dashboard'));
const pageDescription = computed(() => String(route.meta.description ?? ''));
</script>

<template>
    <div class="app-wrapper">
        <Navbar />
        <Sidebar />

        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row align-items-center g-2">
                        <div class="col-sm-6">
                            <h3 class="mb-0">{{ pageTitle }}</h3>
                            <p v-if="pageDescription" class="text-body-secondary small mb-0 mt-1">
                                {{ pageDescription }}
                            </p>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end mb-0">
                                <li class="breadcrumb-item">
                                    <RouterLink :to="{ name: 'dashboard' }">Home</RouterLink>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    {{ pageTitle }}
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-content">
                <div class="container-fluid">
                    <RouterView />
                </div>
            </div>
        </main>

        <Footer />
    </div>
</template>
