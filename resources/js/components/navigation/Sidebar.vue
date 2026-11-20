<script setup lang="ts">
import { computed, ref } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

interface MenuItem {
    label: string;
    routeName: string;
    permission?: string;
    permissionsAnyOf?: string[];
    icon: string;
    implemented?: boolean;
}

interface MenuGroup {
    id: string;
    title: string;
    icon: string;
    items: MenuItem[];
}

const auth = useAuthStore();
const route = useRoute();
const expandedGroups = ref<Record<string, boolean>>({});

const isStudent = computed(() => auth.user?.roles.includes('Student') ?? false);
const displayName = computed(() => auth.user?.name ?? 'User');
const displayRole = computed(() => auth.user?.roles[0] ?? 'Member');
const initials = computed(() => displayName.value
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join(''));

const groups = computed<MenuGroup[]>(() => [
    {
        id: 'academic',
        title: 'ACADEMIC SETUP',
        icon: 'bi-mortarboard',
        items: [
            { label: 'Categories', routeName: 'categories', permission: 'categories.view', icon: 'bi-tags' },
            { label: 'Sub-categories', routeName: 'sub-categories', permission: 'subcategories.view', icon: 'bi-diagram-3' },
            { label: 'Courses', routeName: 'courses', permission: 'courses.view', icon: 'bi-journal-bookmark' },
            { label: 'Batches', routeName: 'batches', permission: 'batches.view', icon: 'bi-collection' },
        ],
    },
    {
        id: 'people',
        title: 'PEOPLE',
        icon: 'bi-people',
        items: [
            { label: 'User Management', routeName: 'users', permission: 'users.view', icon: 'bi-person-gear', implemented: true },
            { label: isStudent.value ? 'My Profile' : 'Students', routeName: 'students', permission: 'students.view', icon: 'bi-people' },
            { label: 'Teachers', routeName: 'teachers', permission: 'teachers.view', icon: 'bi-person-workspace' },
            { label: 'Enrolments', routeName: 'enrollments', permission: 'enrollments.view', icon: 'bi-person-check' },
        ],
    },
    {
        id: 'classroom',
        title: 'CLASSROOM',
        icon: 'bi-easel2',
        items: [
            { label: 'Class Sessions', routeName: 'class-sessions', permission: 'class_sessions.view', icon: 'bi-calendar3' },
            { label: 'Attendance', routeName: 'attendance', permission: 'attendance.view', icon: 'bi-calendar-check' },
            { label: 'Learning Materials', routeName: 'learning-materials', permission: 'learning_materials.view', icon: 'bi-folder2-open' },
            { label: 'Exams', routeName: 'exams', permission: 'exams.view', icon: 'bi-clipboard2-check' },
            { label: 'Results', routeName: 'results', permission: 'results.view', icon: 'bi-award' },
        ],
    },
    {
        id: 'finance',
        title: 'FINANCE',
        icon: 'bi-wallet2',
        items: [
            { label: 'Fee Invoices', routeName: 'fees', permission: 'fees.view', icon: 'bi-receipt' },
            { label: 'Payments', routeName: 'payments', permission: 'payments.view', icon: 'bi-cash-stack' },
        ],
    },
    {
        id: 'reports',
        title: 'REPORTS',
        icon: 'bi-bar-chart-line',
        items: [
            { label: 'Reports', routeName: 'reports', permission: 'reports.view', icon: 'bi-file-earmark-bar-graph' },
        ],
    },
    {
        id: 'communication',
        title: 'COMMUNICATION',
        icon: 'bi-chat-left-text',
        items: [
            { label: 'Announcements', routeName: 'announcements', permission: 'announcements.view', icon: 'bi-megaphone' },
        ],
    },
    {
        id: 'system',
        title: 'SYSTEM',
        icon: 'bi-sliders',
        items: [
            { label: 'Roles', routeName: 'roles', permission: 'roles.view', icon: 'bi-shield-lock', implemented: true },
            { label: 'Permissions', routeName: 'permissions', permission: 'permissions.view', icon: 'bi-key', implemented: true },
            { label: 'Settings', routeName: 'settings', permission: 'settings.view', icon: 'bi-gear' },
            {
                label: 'Trash',
                routeName: 'trash',
                permissionsAnyOf: [
                    'categories.restore',
                    'subcategories.restore',
                    'courses.restore',
                    'batches.restore',
                    'students.restore',
                    'teachers.restore',
                    'users.restore', 'users.force_delete',
                    'roles.restore', 'roles.force_delete',
                    'permissions.restore', 'permissions.force_delete',
                ],
                icon: 'bi-trash3',
            },
        ],
    },
]);

const visibleGroups = computed(() => groups.value
    .map((group) => ({
        ...group,
        items: group.items.filter((item) => item.permission
            ? auth.can(item.permission)
            : auth.canAny(item.permissionsAnyOf ?? [])),
    }))
    .filter((group) => group.items.length > 0));

function isGroupActive(group: MenuGroup): boolean {
    return group.items.some((item) => route.name === item.routeName || route.path.startsWith(`/${item.routeName}/`));
}

function isGroupOpen(group: MenuGroup): boolean {
    return expandedGroups.value[group.id] ?? isGroupActive(group);
}

function toggleGroup(group: MenuGroup): void {
    expandedGroups.value[group.id] = !isGroupOpen(group);
}
</script>

<template>
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <RouterLink :to="{ name: 'dashboard' }" class="brand-link text-decoration-none">
                <span class="brand-image d-flex align-items-center justify-content-center rounded bg-primary shadow-sm">
                    <i class="bi bi-mortarboard-fill text-white"></i>
                </span>
                <span class="brand-text fw-semibold">CoachDesk</span>
            </RouterLink>
        </div>

        <div class="sidebar-wrapper">
            <div class="user-panel d-flex align-items-center px-3 py-3">
                <div class="user-avatar rounded-circle d-flex align-items-center justify-content-center me-2">
                    {{ initials }}
                </div>
                <div class="user-info min-w-0">
                    <span class="d-block text-truncate fw-medium">{{ displayName }}</span>
                    <span class="d-block text-white-50 small text-truncate">{{ displayRole }}</span>
                </div>
            </div>

            <nav class="mt-2 pb-3" aria-label="Main navigation">
                <ul class="nav sidebar-menu flex-column" role="menu">
                    <li class="nav-header">MAIN MENU</li>
                    <li class="nav-item">
                        <RouterLink
                            :to="{ name: 'dashboard' }"
                            class="nav-link"
                            active-class="active"
                            exact-active-class="active"
                        >
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>Dashboard</p>
                        </RouterLink>
                    </li>

                    <template v-for="group in visibleGroups" :key="group.id">
                        <li class="nav-item" :class="{ 'menu-open': isGroupOpen(group) }">
                            <a
                                href="#"
                                class="nav-link"
                                :class="{ active: isGroupActive(group) }"
                                :aria-expanded="isGroupOpen(group)"
                                role="button"
                                @click.prevent="toggleGroup(group)"
                            >
                                <i :class="['nav-icon', 'bi', group.icon]"></i>
                                <p>
                                    {{ group.title }}
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>

                            <ul v-show="isGroupOpen(group)" class="nav nav-treeview">
                                <li v-for="item in group.items" :key="item.routeName" class="nav-item">
                                    <RouterLink
                                        :to="{ name: item.routeName }"
                                        class="nav-link"
                                        active-class="active"
                                        exact-active-class="active"
                                    >
                                        <i :class="['nav-icon', 'bi', item.icon]"></i>
                                        <p>{{ item.label }}</p>
                                        <span v-if="!item.implemented" class="nav-badge badge text-bg-secondary me-2">Soon</span>
                                    </RouterLink>
                                </li>
                            </ul>
                        </li>
                    </template>
                </ul>
            </nav>
        </div>
    </aside>
</template>
