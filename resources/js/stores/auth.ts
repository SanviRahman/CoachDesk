import axios from 'axios';
import { computed, ref } from 'vue';
import { defineStore } from 'pinia';
import http from '@/api/http';

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    status: 'active' | 'inactive';
    roles: string[];
    permissions: string[];
}

interface ApiResponse<T> {
    data: T;
    message?: string;
}

interface LoginCredentials {
    email: string;
    password: string;
    remember: boolean;
}

export const useAuthStore = defineStore('auth', () => {
    const user = ref<AuthUser | null>(null);
    const initialized = ref(false);
    const isAuthenticated = computed(() => user.value !== null);

    function can(permission: string): boolean {
        return user.value?.permissions.includes(permission) ?? false;
    }

    function canAny(permissions: string[]): boolean {
        return permissions.some((permission) => can(permission));
    }

    async function fetchUser(): Promise<boolean> {
        try {
            const response = await http.get<ApiResponse<AuthUser>>('/auth/me');
            user.value = response.data.data;
            initialized.value = true;

            return true;
        } catch (error) {
            if (axios.isAxiosError(error) && error.response?.status === 401) {
                user.value = null;
                initialized.value = true;

                return false;
            }

            throw error;
        }
    }

    async function login(credentials: LoginCredentials): Promise<void> {
        await axios.get('/sanctum/csrf-cookie', {
            withCredentials: true,
            withXSRFToken: true,
        });

        const response = await http.post<ApiResponse<AuthUser>>('/auth/login', credentials);
        user.value = response.data.data;
        initialized.value = true;
    }

    async function logout(): Promise<void> {
        await http.post('/auth/logout');
        user.value = null;
        initialized.value = true;
    }

    return {
        user,
        initialized,
        isAuthenticated,
        can,
        canAny,
        fetchUser,
        login,
        logout,
    };
});
