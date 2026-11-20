import { createRouter, createWebHistory } from 'vue-router';
import DashboardView from '@/views/DashboardView.vue';
import NotFoundView from '@/views/NotFoundView.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', redirect: { name: 'dashboard' } },
        { path: '/dashboard', name: 'dashboard', component: DashboardView },
        { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFoundView },
    ],
});

export default router;