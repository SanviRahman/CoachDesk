<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            'dashboard.view',

            'users.view',
            'users.create',
            'users.update',
            'users.status.update',
            'users.roles.assign',

            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            'categories.restore',
            'categories.force_delete',

            'subcategories.view',
            'subcategories.create',
            'subcategories.update',
            'subcategories.delete',
            'subcategories.restore',
            'subcategories.force_delete',

            'courses.view',
            'courses.create',
            'courses.update',
            'courses.delete',
            'courses.restore',
            'courses.force_delete',

            'batches.view',
            'batches.create',
            'batches.update',
            'batches.delete',
            'batches.restore',
            'batches.force_delete',

            'students.view',
            'students.create',
            'students.update',
            'students.delete',
            'students.restore',
            'students.force_delete',

            'teachers.view',
            'teachers.create',
            'teachers.update',
            'teachers.delete',
            'teachers.restore',
            'teachers.force_delete',

            'enrollments.view',
            'enrollments.create',
            'enrollments.update',
            'enrollments.cancel',

            'class_sessions.view',
            'class_sessions.create',
            'class_sessions.update',
            'class_sessions.cancel',

            'attendance.view',
            'attendance.mark',
            'attendance.update',

            'fees.view',
            'fees.create',
            'fees.update',

            'payments.view',
            'payments.create',
            'payments.verify',

            'announcements.view',
            'announcements.create',
            'announcements.update',
            'announcements.delete',
            'announcements.publish',

            'learning_materials.view',
            'learning_materials.create',
            'learning_materials.update',
            'learning_materials.delete',

            'exams.view',
            'exams.create',
            'exams.update',
            'exams.delete',

            'results.view',
            'results.create',
            'results.update',
            'results.publish',

            'reports.view',
            'reports.export',

            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',

            'permissions.view',
            'permissions.create',
            'permissions.update',
            'permissions.delete',
            'permissions.assign',

            'settings.view',
            'settings.manage',
        ];

        foreach ($permissionNames as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $rolePermissions = [
            'Super Admin' => $permissionNames,

            'Admin' => array_values(array_diff(
                $permissionNames,
                [
                    'roles.create',
                    'roles.update',
                    'roles.delete',
                    'permissions.create',
                    'permissions.update',
                    'permissions.delete',
                    'permissions.assign',
                    'users.roles.assign',
                    'settings.manage',
                ]
            )),

            'Teacher' => [
                'dashboard.view',
                'categories.view',
                'subcategories.view',
                'courses.view',
                'batches.view',
                'students.view',
                'enrollments.view',
                'class_sessions.view',
                'class_sessions.create',
                'class_sessions.update',
                'attendance.view',
                'attendance.mark',
                'attendance.update',
                'announcements.view',
                'learning_materials.view',
                'learning_materials.create',
                'learning_materials.update',
                'exams.view',
                'results.view',
                'results.create',
                'results.update',
                'reports.view',
                'reports.export',
            ],

            'Accountant' => [
                'dashboard.view',
                'categories.view',
                'subcategories.view',
                'courses.view',
                'batches.view',
                'students.view',
                'enrollments.view',
                'fees.view',
                'fees.create',
                'fees.update',
                'payments.view',
                'payments.create',
                'payments.verify',
                'reports.view',
                'reports.export',
            ],

            'Student' => [
                'dashboard.view',
                'categories.view',
                'subcategories.view',
                'courses.view',
                'batches.view',
                'students.view',
                'enrollments.view',
                'class_sessions.view',
                'attendance.view',
                'fees.view',
                'payments.view',
                'announcements.view',
                'learning_materials.view',
                'results.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($permissions);
        }

        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        $superAdmin->syncRoles(['Super Admin']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
