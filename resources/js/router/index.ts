import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import AdminLayout from '@/layouts/AdminLayout.vue';
import ComingSoonView from '@/views/ComingSoonView.vue';
import DashboardView from '@/views/DashboardView.vue';
import ForbiddenView from '@/views/ForbiddenView.vue';
import LoginView from '@/views/LoginView.vue';
import NotFoundView from '@/views/NotFoundView.vue';
import UserIndex from '@/modules/users/pages/UserIndex.vue';
import UserTrash from '@/modules/users/pages/UserTrash.vue';
import RoleIndex from '@/modules/roles/pages/RoleIndex.vue';
import RoleTrash from '@/modules/roles/pages/RoleTrash.vue';
import PermissionIndex from '@/modules/permissions/pages/PermissionIndex.vue';
import PermissionTrash from '@/modules/permissions/pages/PermissionTrash.vue';
import TrashOverviewView from '@/views/TrashOverviewView.vue';

const adminRoutes: RouteRecordRaw[] = [
    { path: '', redirect: { name: 'dashboard' } },
    {
        path: 'dashboard',
        name: 'dashboard',
        component: DashboardView,
        meta: { title: 'Dashboard', permission: 'dashboard.view' },
    },
    {
        path: 'users',
        name: 'users',
        component: UserIndex,
        meta: { title: 'Admin Users Management', description: 'Create accounts, assign roles and manage account status.', permission: 'users.view' },
    },
    { path: 'users/trash', name: 'users-trash', component: UserTrash, meta: { title: 'Trashed Admin Users', description: 'Restore or permanently delete user accounts.', permissionsAnyOf: ['users.restore', 'users.force_delete'] } },
    {
        path: 'categories',
        name: 'categories',
        component: ComingSoonView,
        meta: { title: 'Categories', description: 'Organize the top-level academic categories.', permission: 'categories.view' },
    },
    {
        path: 'sub-categories',
        name: 'sub-categories',
        component: ComingSoonView,
        meta: { title: 'Sub-categories', description: 'Organize academic sub-categories under their parent categories.', permission: 'subcategories.view' },
    },
    {
        path: 'courses',
        name: 'courses',
        component: ComingSoonView,
        meta: { title: 'Courses', description: 'Create and manage the coaching centre course catalogue.', permission: 'courses.view' },
    },
    {
        path: 'batches',
        name: 'batches',
        component: ComingSoonView,
        meta: { title: 'Batches', description: 'Manage course batches, schedules, and assigned teachers.', permission: 'batches.view' },
    },
    {
        path: 'students',
        name: 'students',
        component: ComingSoonView,
        meta: { title: 'Students', description: 'Manage student profiles and permitted student information.', permission: 'students.view' },
    },
    {
        path: 'teachers',
        name: 'teachers',
        component: ComingSoonView,
        meta: { title: 'Teachers', description: 'Manage teacher profiles and teaching assignments.', permission: 'teachers.view' },
    },
    {
        path: 'enrollments',
        name: 'enrollments',
        component: ComingSoonView,
        meta: { title: 'Enrolments', description: 'Manage student enrolments into course batches.', permission: 'enrollments.view' },
    },
    {
        path: 'class-sessions',
        name: 'class-sessions',
        component: ComingSoonView,
        meta: { title: 'Class Sessions', description: 'Schedule and manage individual class sessions.', permission: 'class_sessions.view' },
    },
    {
        path: 'attendance',
        name: 'attendance',
        component: ComingSoonView,
        meta: { title: 'Attendance', description: 'Record and review attendance for assigned class sessions.', permission: 'attendance.view' },
    },
    {
        path: 'learning-materials',
        name: 'learning-materials',
        component: ComingSoonView,
        meta: { title: 'Learning Materials', description: 'Share notes, documents, and learning resources with authorized users.', permission: 'learning_materials.view' },
    },
    {
        path: 'exams',
        name: 'exams',
        component: ComingSoonView,
        meta: { title: 'Exams', description: 'Plan and manage examinations after the core academic workflow is ready.', permission: 'exams.view' },
    },
    {
        path: 'results',
        name: 'results',
        component: ComingSoonView,
        meta: { title: 'Results', description: 'Manage and view permitted examination results.', permission: 'results.view' },
    },
    {
        path: 'fees',
        name: 'fees',
        component: ComingSoonView,
        meta: { title: 'Fee Invoices', description: 'Manage enrolment invoices and fee balances.', permission: 'fees.view' },
    },
    {
        path: 'payments',
        name: 'payments',
        component: ComingSoonView,
        meta: { title: 'Payments', description: 'Record and review fee payments and payment history.', permission: 'payments.view' },
    },
    {
        path: 'announcements',
        name: 'announcements',
        component: ComingSoonView,
        meta: { title: 'Announcements', description: 'Publish notices for the intended coaching-centre audience.', permission: 'announcements.view' },
    },
    {
        path: 'roles',
        name: 'roles',
        component: RoleIndex,
        meta: { title: 'Roles Management', description: 'Create roles and control access through permission assignments.', permission: 'roles.view' },
    },
    { path: 'roles/trash', name: 'roles-trash', component: RoleTrash, meta: { title: 'Trashed Roles', description: 'Restore or permanently delete unused custom roles.', permissionsAnyOf: ['roles.restore', 'roles.force_delete'] } },
    {
        path: 'permissions',
        name: 'permissions',
        component: PermissionIndex,
        meta: { title: 'Permission Management', description: 'Manage granular permissions used by roles and protected API endpoints.', permission: 'permissions.view' },
    },
    { path: 'permissions/trash', name: 'permissions-trash', component: PermissionTrash, meta: { title: 'Trashed Permissions', description: 'Restore or permanently delete eligible permissions.', permissionsAnyOf: ['permissions.restore', 'permissions.force_delete'] } },
    {
        path: 'reports',
        name: 'reports',
        component: ComingSoonView,
        meta: { title: 'Reports', description: 'View role-authorized academic and financial reports.', permission: 'reports.view' },
    },
    {
        path: 'settings',
        name: 'settings',
        component: ComingSoonView,
        meta: { title: 'Settings', description: 'Manage authorized application settings.', permission: 'settings.view' },
    },
    {
        path: 'trash',
        name: 'trash',
        component: TrashOverviewView,
        meta: {
            title: 'Trash Management',
            description: 'Review and recover eligible deleted records.',
            permissionsAnyOf: [
                'categories.restore', 'subcategories.restore', 'courses.restore', 'batches.restore',
                'students.restore', 'teachers.restore', 'users.restore', 'users.force_delete', 'roles.restore', 'roles.force_delete', 'permissions.restore', 'permissions.force_delete',
            ],
        },
    },
    {
        path: '403',
        name: 'forbidden',
        component: ForbiddenView,
        meta: { title: 'Access Denied' },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/login',
            name: 'login',
            component: LoginView,
            meta: { guestOnly: true },
        },
        {
            path: '/',
            component: AdminLayout,
            meta: { requiresAuth: true },
            children: adminRoutes,
        },
        {
            path: '/:pathMatch(.*)*',
            name: 'not-found',
            component: NotFoundView,
        },
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

    const permission = to.meta.permission as string | undefined;
    const permissionsAnyOf = to.meta.permissionsAnyOf as string[] | undefined;

    if (permission && !auth.can(permission)) {
        return { name: 'forbidden' };
    }

    if (permissionsAnyOf && !auth.canAny(permissionsAnyOf)) {
        return { name: 'forbidden' };
    }

    return true;
});

export default router;
