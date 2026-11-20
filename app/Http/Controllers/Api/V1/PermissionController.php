<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Permissions\BulkPermissionActionRequest;
use App\Http\Requests\Api\V1\Permissions\StorePermissionRequest;
use App\Http\Requests\Api\V1\Permissions\UpdatePermissionRequest;
use App\Http\Resources\Api\V1\Permissions\PermissionResource;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    private const PROTECTED_GROUPS = ['users', 'roles', 'permissions'];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:125'],
            'group' => ['nullable', 'string', 'max:60'],
            'guard' => ['nullable', 'string', 'max:125'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = $this->permissionQuery(Permission::query());

        if (! empty($filters['guard'])) {
            $query->where('guard_name', $filters['guard']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.trim($filters['search']).'%');
        }

        if (! empty($filters['group'])) {
            $query->where('name', 'like', $filters['group'].'.%');
        }

        return PermissionResource::collection($query->orderBy('name')->paginate($filters['per_page'] ?? 15)->withQueryString());
    }

    public function trash(Request $request)
    {
        abort_unless($request->user()->can('permissions.restore') || $request->user()->can('permissions.force_delete'), 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:125'],
            'group' => ['nullable', 'string', 'max:60'],
            'guard' => ['nullable', 'string', 'max:125'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = $this->permissionQuery(Permission::onlyTrashed());

        if (! empty($filters['guard'])) {
            $query->where('guard_name', $filters['guard']);
        }

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.trim($filters['search']).'%');
        }

        if (! empty($filters['group'])) {
            $query->where('name', 'like', $filters['group'].'.%');
        }

        return PermissionResource::collection($query->orderByDesc('deleted_at')->paginate($filters['per_page'] ?? 15)->withQueryString());
    }

    public function store(StorePermissionRequest $request)
    {
        $permission = DB::transaction(function () use ($request): Permission {
            $permission = Permission::create([
                'name' => $request->validated('name'),
                'guard_name' => 'web',
            ]);

            Role::query()->where('name', 'Super Admin')->where('guard_name', 'web')->first()?->givePermissionTo($permission);

            return $permission;
        });

        $this->forgetPermissionCache();

        return (new PermissionResource($this->permissionQuery(Permission::query()->whereKey($permission->id))->firstOrFail()))
            ->response()->setStatusCode(201);
    }

    public function show(Permission $permission): PermissionResource
    {
        $this->ensureWebPermission($permission);

        return new PermissionResource($this->permissionQuery(Permission::query()->whereKey($permission->id))->firstOrFail());
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): PermissionResource
    {
        $this->ensureWebPermission($permission);
        $this->ensureNotProtected($permission);
        $name = $request->validated('name');

        if ($name !== $permission->name && $this->isAssigned($permission)) {
            throw ValidationException::withMessages(['name' => ['This permission is assigned. Remove it from all roles and users before renaming it.']]);
        }

        $permission->update(['name' => $name]);
        $this->forgetPermissionCache();

        return new PermissionResource($this->permissionQuery(Permission::query()->whereKey($permission->id))->firstOrFail());
    }

    public function destroy(Request $request, Permission $permission)
    {
        abort_unless($request->user()->can('permissions.delete'), 403);
        $this->ensureCanTrash($permission);
        $permission->delete();
        $this->forgetPermissionCache();

        return response()->json(['message' => 'Permission moved to Trash.']);
    }

    public function restore(Request $request, int $permission)
    {
        abort_unless($request->user()->can('permissions.restore'), 403);
        $trashedPermission = Permission::onlyTrashed()->findOrFail($permission);
        $this->ensureNotProtected($trashedPermission);
        $trashedPermission->restore();
        $this->forgetPermissionCache();
        Role::query()->where('name', 'Super Admin')->where('guard_name', 'web')->first()?->givePermissionTo($trashedPermission);
        $this->forgetPermissionCache();

        return new PermissionResource($this->permissionQuery(Permission::query()->whereKey($trashedPermission->id))->firstOrFail());
    }

    public function forceDelete(Request $request, int $permission)
    {
        abort_unless($request->user()->can('permissions.force_delete'), 403);
        $trashedPermission = Permission::onlyTrashed()->findOrFail($permission);
        $this->ensureCanForceDelete($trashedPermission);
        $trashedPermission->forceDelete();
        $this->forgetPermissionCache();

        return response()->json(['message' => 'Permission permanently deleted.']);
    }

    public function bulkAction(BulkPermissionActionRequest $request)
    {
        $validated = $request->validated();
        $action = $validated['action'];
        $permissionName = match ($action) {
            'trash' => 'permissions.delete',
            'restore' => 'permissions.restore',
            'force_delete' => 'permissions.force_delete',
        };

        abort_unless($request->user()->can($permissionName), 403);

        DB::transaction(function () use ($action, $validated): void {
            $query = $action === 'trash' ? Permission::query() : Permission::onlyTrashed();
            $permissions = $query->whereKey($validated['ids'])->lockForUpdate()->get();

            if ($permissions->count() !== count($validated['ids'])) {
                throw ValidationException::withMessages(['ids' => ['One or more selected permissions are not available for this action.']]);
            }

            foreach ($permissions as $permission) {
                if ($action === 'trash') {
                    $this->ensureCanTrash($permission);
                    $permission->delete();
                } elseif ($action === 'restore') {
                    $this->ensureNotProtected($permission);
                    $permission->restore();
                    $this->forgetPermissionCache();
                    Role::query()->where('name', 'Super Admin')->where('guard_name', 'web')->first()?->givePermissionTo($permission);
                } else {
                    $this->ensureCanForceDelete($permission);
                    $permission->forceDelete();
                }
            }
        });

        $this->forgetPermissionCache();

        return response()->json(['message' => 'Bulk permission action completed successfully.']);
    }

    private function permissionQuery(Builder $query): Builder
    {
        return $query->where('guard_name', 'web')->withCount([
            'roles' => fn (Builder $roles) => $roles->withTrashed(),
            'roles as other_roles_count' => fn (Builder $roles) => $roles->withTrashed()->where('name', '!=', 'Super Admin'),
            'users' => fn (Builder $users) => $users->withTrashed(),
        ]);
    }

    private function ensureWebPermission(Permission $permission): void
    {
        abort_unless($permission->guard_name === 'web', 404);
    }

    private function ensureNotProtected(Permission $permission): void
    {
        $group = explode('.', $permission->name, 2)[0];

        if (in_array($group, self::PROTECTED_GROUPS, true)) {
            abort(403, 'Core access-control permissions are protected.');
        }
    }

    private function isAssigned(Permission $permission): bool
    {
        return $permission->roles()->withTrashed()->exists() || $permission->users()->withTrashed()->exists();
    }

    private function hasProtectedAssignments(Permission $permission): bool
    {
        return $permission->roles()->withTrashed()->where('name', '!=', 'Super Admin')->exists()
            || $permission->users()->withTrashed()->exists();
    }

    private function ensureCanTrash(Permission $permission): void
    {
        $this->ensureWebPermission($permission);
        $this->ensureNotProtected($permission);

        if ($this->hasProtectedAssignments($permission)) {
            throw ValidationException::withMessages(['permission' => ['Remove this permission from all non-Super-Admin roles and direct user assignments before moving it to Trash.']]);
        }
    }

    private function ensureCanForceDelete(Permission $permission): void
    {
        $this->ensureWebPermission($permission);
        $this->ensureNotProtected($permission);

        if ($this->hasProtectedAssignments($permission)) {
            throw ValidationException::withMessages(['permission' => ['This permission is assigned to a role or user and cannot be permanently deleted.']]);
        }
    }

    private function forgetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
