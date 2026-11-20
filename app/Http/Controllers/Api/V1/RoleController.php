<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Roles\BulkRoleActionRequest;
use App\Http\Requests\Api\V1\Roles\StoreRoleRequest;
use App\Http\Requests\Api\V1\Roles\UpdateRoleRequest;
use App\Http\Resources\Api\V1\Roles\RoleResource;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    private const SYSTEM_ROLES = ['Super Admin', 'Admin', 'Teacher', 'Accountant', 'Student'];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'guard' => ['nullable', 'string', 'max:125'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Role::query()
            ->withCount(['permissions', 'users' => fn ($users) => $users->withTrashed()])
            ->orderBy('name');

        if (! empty($filters['guard'])) {
            $query->where('guard_name', $filters['guard']);
        }

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where('name', 'like', "%{$search}%");
        }

        return RoleResource::collection($query->paginate($filters['per_page'] ?? 10)->withQueryString());
    }

    public function trash(Request $request)
    {
        abort_unless($request->user()->can('roles.restore') || $request->user()->can('roles.force_delete'), 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'guard' => ['nullable', 'string', 'max:125'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Role::onlyTrashed()
            ->withCount(['permissions', 'users' => fn ($users) => $users->withTrashed()])
            ->orderByDesc('deleted_at');

        if (! empty($filters['guard'])) {
            $query->where('guard_name', $filters['guard']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.trim($filters['search']).'%');
        }

        return RoleResource::collection($query->paginate($filters['per_page'] ?? 10)->withQueryString());
    }

    public function store(StoreRoleRequest $request)
    {
        $validated = $request->validated();
        $permissions = $validated['permissions'] ?? [];

        if (array_key_exists('permissions', $validated) && ! $request->user()->can('permissions.assign')) {
            abort(403);
        }

        $role = DB::transaction(function () use ($validated, $permissions): Role {
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);

            if ($permissions !== []) {
                $role->syncPermissions($permissions);
            }

            return $role;
        });

        $this->forgetPermissionCache();

        return (new RoleResource($role->load('permissions')->loadCount(['permissions', 'users' => fn ($users) => $users->withTrashed()])))->response()->setStatusCode(201);
    }

    public function show(Role $role): RoleResource
    {
        $this->ensureWebRole($role);

        return new RoleResource($role->load('permissions')->loadCount(['permissions', 'users' => fn ($users) => $users->withTrashed()]));
    }

    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        $this->ensureWebRole($role);

        if ($role->name === 'Super Admin') {
            abort(403, 'The Super Admin role is protected.');
        }

        $validated = $request->validated();

        if (in_array($role->name, self::SYSTEM_ROLES, true) && $validated['name'] !== $role->name) {
            throw ValidationException::withMessages(['name' => ['Built-in role names cannot be changed.']]);
        }

        if (array_key_exists('permissions', $validated) && ! $request->user()->can('permissions.assign')) {
            abort(403);
        }

        DB::transaction(function () use ($role, $validated): void {
            if ($role->name !== $validated['name']) {
                $role->update(['name' => $validated['name']]);
            }

            if (array_key_exists('permissions', $validated)) {
                $role->syncPermissions($validated['permissions']);
            }
        });

        $this->forgetPermissionCache();

        return new RoleResource($role->fresh()->load('permissions')->loadCount(['permissions', 'users' => fn ($users) => $users->withTrashed()]));
    }

    public function destroy(Request $request, Role $role)
    {
        abort_unless($request->user()->can('roles.delete'), 403);
        $this->ensureCanTrash($role);
        $role->delete();
        $this->forgetPermissionCache();

        return response()->json(['message' => 'Role moved to Trash.']);
    }

    public function restore(Request $request, int $role)
    {
        abort_unless($request->user()->can('roles.restore'), 403);
        $trashedRole = Role::onlyTrashed()->findOrFail($role);

        if (in_array($trashedRole->name, self::SYSTEM_ROLES, true)) {
            abort(403, 'Built-in roles cannot be restored from Trash.');
        }

        $trashedRole->restore();
        $this->forgetPermissionCache();

        return new RoleResource($trashedRole->fresh()->load('permissions')->loadCount(['permissions', 'users' => fn ($users) => $users->withTrashed()]));
    }

    public function forceDelete(Request $request, int $role)
    {
        abort_unless($request->user()->can('roles.force_delete'), 403);
        $trashedRole = Role::onlyTrashed()->findOrFail($role);
        $this->ensureCanForceDelete($trashedRole);
        $trashedRole->forceDelete();
        $this->forgetPermissionCache();

        return response()->json(['message' => 'Role permanently deleted.']);
    }

    public function bulkAction(BulkRoleActionRequest $request)
    {
        $validated = $request->validated();
        $actor = $request->user();
        $action = $validated['action'];
        $permission = match ($action) {
            'trash' => 'roles.delete',
            'restore' => 'roles.restore',
            'force_delete' => 'roles.force_delete',
        };

        abort_unless($actor->can($permission), 403);

        DB::transaction(function () use ($action, $validated): void {
            $query = $action === 'trash' ? Role::query() : Role::onlyTrashed();
            $roles = $query->whereKey($validated['ids'])->lockForUpdate()->get();

            if ($roles->count() !== count($validated['ids'])) {
                throw ValidationException::withMessages(['ids' => ['One or more selected roles are not available for this action.']]);
            }

            foreach ($roles as $role) {
                if ($action === 'trash') {
                    $this->ensureCanTrash($role);
                    $role->delete();
                } elseif ($action === 'restore') {
                    if (in_array($role->name, self::SYSTEM_ROLES, true)) {
                        throw ValidationException::withMessages(['ids' => ['Built-in roles cannot be restored from Trash.']]);
                    }
                    $role->restore();
                } else {
                    $this->ensureCanForceDelete($role);
                    $role->forceDelete();
                }
            }
        });

        $this->forgetPermissionCache();

        return response()->json(['message' => 'Bulk role action completed successfully.']);
    }

    private function ensureWebRole(Role $role): void
    {
        abort_unless($role->guard_name === 'web', 404);
    }

    private function ensureCanTrash(Role $role): void
    {
        $this->ensureWebRole($role);

        if (in_array($role->name, self::SYSTEM_ROLES, true)) {
            abort(403, 'Built-in roles cannot be moved to Trash.');
        }

        if ($role->users()->withTrashed()->exists()) {
            throw ValidationException::withMessages(['role' => ['Reassign this role from all user accounts before moving it to Trash.']]);
        }
    }

    private function ensureCanForceDelete(Role $role): void
    {
        $this->ensureWebRole($role);

        if (in_array($role->name, self::SYSTEM_ROLES, true)) {
            abort(403, 'Built-in roles cannot be permanently deleted.');
        }

        if ($role->users()->withTrashed()->exists()) {
            throw ValidationException::withMessages(['role' => ['This role is still assigned to user accounts and cannot be permanently deleted.']]);
        }
    }

    private function forgetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
