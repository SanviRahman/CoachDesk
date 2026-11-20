import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import DashboardView from '@/views/DashboardView.vue';
import LoginView from '@/views/LoginView.vue';
import NotFoundView from '@/views/NotFoundView.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', redirect: { name: 'dashboard' } },
        {
            path: '/login',
            name: 'login',
            component: LoginView,
            meta: { guestOnly: true },
        },
        {
            path: '/dashboard',
            name: 'dashboard',
            component: DashboardView,
            meta: { requiresAuth: true },
        },
        { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFoundView },
    ],
});

router.beforeEach(async (to) => {
    const auth = useAuthStore();

    if (!auth.initialized) {
        await auth.fetchUser();
    }

    const requiresAuth = to.matched.some((record) => record.meta.requiresAuth === true);
    const guestOnly = to.matched.some((record) => record.meta.guestOnly === true);

    if (requiresAuth && !auth.isAuthenticated) {
        return {
            name: 'login',
            query: { redirect: to.fullPath },
        };
    }

    if (guestOnly && auth.isAuthenticated) {
        return { name: 'dashboard' };
    }

    return true;
});

export default router;
