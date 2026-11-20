<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const auth = useAuthStore();
const router = useRouter();
const signingOut = ref(false);
const logoutError = ref('');

const displayName = computed(() => auth.user?.name ?? 'User');
const displayRole = computed(() => auth.user?.roles[0] ?? 'Member');
const initials = computed(() => displayName.value
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join(''));

async function logout(): Promise<void> {
    signingOut.value = true;
    logoutError.value = '';

    try {
        await auth.logout();
        await router.replace({ name: 'login' });
    } catch {
        logoutError.value = 'Unable to sign out. Please try again.';
    } finally {
        signingOut.value = false;
    }
}
</script>

<template>
    <nav class="app-header navbar navbar-expand bg-body border-bottom">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a
                        href="#"
                        class="nav-link"
                        data-lte-toggle="sidebar"
                        role="button"
                        aria-label="Toggle sidebar"
                        @click.prevent
                    >
                        <i class="bi bi-list fs-5"></i>
                    </a>
                </li>
            </ul>

            <div class="d-flex align-items-center ms-auto">
                <div class="dropdown">
                    <button
                        type="button"
                        class="btn btn-link text-decoration-none text-body d-flex align-items-center gap-2 px-2"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        <span class="navbar-avatar rounded-circle d-flex align-items-center justify-content-center">
                            {{ initials }}
                        </span>
                        <span class="d-none d-sm-flex flex-column align-items-start lh-sm">
                            <span class="small fw-semibold">{{ displayName }}</span>
                            <span class="text-body-secondary" style="font-size: 0.72rem">{{ displayRole }}</span>
                        </span>
                        <i class="bi bi-chevron-down small text-body-secondary"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li class="px-3 py-2">
                            <div class="fw-semibold">{{ displayName }}</div>
                            <div class="small text-body-secondary">{{ auth.user?.email }}</div>
                            <span class="badge text-bg-primary mt-2">{{ displayRole }}</span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="px-3" v-if="logoutError">
                            <div class="small text-danger" role="alert">{{ logoutError }}</div>
                        </li>
                        <li>
                            <button
                                type="button"
                                class="dropdown-item d-flex align-items-center gap-2"
                                :disabled="signingOut"
                                @click="logout"
                            >
                                <i class="bi bi-box-arrow-right"></i>
                                {{ signingOut ? 'Signing out...' : 'Sign out' }}
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</template>
