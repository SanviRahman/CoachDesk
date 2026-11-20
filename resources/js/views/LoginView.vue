<script setup lang="ts">
import axios from 'axios';
import { computed, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

interface LoginErrorResponse {
    message?: string;
    errors?: Record<string, string[]>;
}

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();

const form = reactive({
    email: '',
    password: '',
    remember: false,
});

const errors = ref<Record<string, string[]>>({});
const errorMessage = ref('');
const submitting = ref(false);

const emailError = computed(() => errors.value.email?.[0] ?? '');
const passwordError = computed(() => errors.value.password?.[0] ?? '');

async function submit(): Promise<void> {
    submitting.value = true;
    errors.value = {};
    errorMessage.value = '';

    try {
        await auth.login(form);

        const redirect = typeof route.query.redirect === 'string'
            ? route.query.redirect
            : '/dashboard';

        await router.replace(redirect);
    } catch (error) {
        if (axios.isAxiosError<LoginErrorResponse>(error)) {
            errors.value = error.response?.data?.errors ?? {};
            errorMessage.value = error.response?.data?.message ?? 'Unable to sign in. Please try again.';
        } else {
            errorMessage.value = 'Something went wrong. Please try again.';
        }
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <div class="login-page bg-body-secondary">
        <div class="login-box">
            <div class="login-logo">
                <RouterLink to="/" class="text-decoration-none text-body">
                    <strong>Coach</strong>Desk
                </RouterLink>
            </div>

            <div class="card shadow-sm">
                <div class="card-body login-card-body">
                    <p class="login-box-msg">Sign in to start your session</p>

                    <div v-if="errorMessage" class="alert alert-danger py-2" role="alert">
                        {{ errorMessage }}
                    </div>

                    <form @submit.prevent="submit">
                        <div class="mb-3">
                            <div class="input-group">
                                <input
                                    v-model.trim="form.email"
                                    type="email"
                                    class="form-control"
                                    :class="{ 'is-invalid': emailError }"
                                    placeholder="Email address"
                                    autocomplete="username"
                                    required
                                >
                                <div class="input-group-text">
                                    <span class="bi bi-envelope"></span>
                                </div>
                            </div>
                            <div v-if="emailError" class="invalid-feedback d-block">
                                {{ emailError }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="input-group">
                                <input
                                    v-model="form.password"
                                    type="password"
                                    class="form-control"
                                    :class="{ 'is-invalid': passwordError }"
                                    placeholder="Password"
                                    autocomplete="current-password"
                                    required
                                >
                                <div class="input-group-text">
                                    <span class="bi bi-lock"></span>
                                </div>
                            </div>
                            <div v-if="passwordError" class="invalid-feedback d-block">
                                {{ passwordError }}
                            </div>
                        </div>

                        <div class="row align-items-center">
                            <div class="col-7">
                                <div class="form-check">
                                    <input
                                        id="remember"
                                        v-model="form.remember"
                                        type="checkbox"
                                        class="form-check-input"
                                    >
                                    <label for="remember" class="form-check-label">Remember me</label>
                                </div>
                            </div>
                            <div class="col-5">
                                <button type="submit" class="btn btn-primary w-100" :disabled="submitting">
                                    <span v-if="submitting" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                                    {{ submitting ? 'Signing in...' : 'Sign In' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <p class="text-center text-body-secondary small mt-3 mb-0">
                Coaching Management System
            </p>
        </div>
    </div>
</template>
