<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Users\BulkUserActionRequest;
use App\Http\Requests\Api\V1\Users\StoreUserRequest;
use App\Http\Requests\Api\V1\Users\UpdateUserRequest;
use App\Http\Requests\Api\V1\Users\UpdateUserStatusRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return $this->listing($request, false);
    }

    public function trash(Request $request)
    {
        abort_unless($request->user()->can('users.restore') || $request->user()->can('users.force_delete'), 403);

        return $this->listing($request, true);
    }

    private function listing(Request $request, bool $trashed)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:125'],
            'status' => ['nullable', 'in:active,inactive'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = ($trashed ? User::onlyTrashed() : User::query())
            ->with('roles')
            ->latest('id');

        $actor = $request->user();

        if (! $actor->hasRole('Super Admin')) {
            $query->where(function (Builder $users) use ($actor): void {
                $users->whereDoesntHave('roles', fn (Builder $roles) => $roles
                    ->whereIn('name', ['Super Admin', 'Admin'])
                    ->where('guard_name', 'web'))
                    ->orWhereKey($actor->id);
            });
        }

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(fn (Builder $users) => $users
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        if (! empty($filters['role'])) {
            $query->whereHas('roles', fn (Builder $roles) => $roles
                ->where('name', $filters['role'])
                ->where('guard_name', 'web'));
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return UserResource::collection($query->paginate($filters['per_page'] ?? 10)->withQueryString());
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $roleNames = $this->assignableRoleNames($validated['roles'], $request->user());

        $user = DB::transaction(function () use ($validated, $roleNames): User {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'status' => 'active',
            ]);

            $user->syncRoles($roleNames);

            return $user;
        });

        return (new UserResource($user->load('roles')))->response()->setStatusCode(201);
    }

    public function show(Request $request, User $user): UserResource
    {
        $this->ensureManageable($user, $request->user());

        return new UserResource($user->load('roles'));
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $actor = $request->user();
        $validated = $request->validated();
        $this->ensureManageable($user, $actor);

        if ($user->is($actor) && ! empty($validated['password'])) {
            throw ValidationException::withMessages([
                'password' => ['Use the account password-change flow to update your own password.'],
            ]);
        }

        $roleNames = null;

        if (array_key_exists('roles', $validated)) {
            if (! $actor->can('users.roles.assign') || $user->is($actor) || $user->hasRole('Super Admin')) {
                abort(403);
            }

            $roleNames = $this->assignableRoleNames($validated['roles'], $actor);

        }

        DB::transaction(function () use ($user, $validated, $roleNames, $actor): void {
            $lockedUser = User::query()->with('roles')->lockForUpdate()->findOrFail($user->id);
            $this->ensureManageable($lockedUser, $actor);

            if ($roleNames !== null && $lockedUser->status === 'active' && $lockedUser->hasRole('Super Admin') && ! in_array('Super Admin', $roleNames, true) && $this->activeSuperAdminCount() <= 1) {
                throw ValidationException::withMessages(['roles' => ['The last active Super Admin role cannot be removed.']]);
            }

            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (! empty($validated['password'])) {
                $data['password'] = $validated['password'];
            }

            $lockedUser->update($data);

            if ($roleNames !== null) {
                $lockedUser->syncRoles($roleNames);
            }
        });

        return new UserResource($user->fresh()->load('roles'));
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user): UserResource
    {
        $actor = $request->user();
        $this->ensureManageable($user, $actor);

        if ($user->is($actor)) {
            throw ValidationException::withMessages([
                'status' => ['You cannot change your own account status.'],
            ]);
        }

        $status = $request->validated('status');

        DB::transaction(function () use ($user, $actor, $status): void {
            $lockedUser = User::query()->with('roles')->lockForUpdate()->findOrFail($user->id);
            $this->ensureManageable($lockedUser, $actor);

            if ($lockedUser->is($actor)) {
                throw ValidationException::withMessages(['status' => ['You cannot change your own account status.']]);
            }

            $this->protectLastActiveSuperAdmin($lockedUser, $status === 'inactive');
            $lockedUser->update(['status' => $status]);
        });

        return new UserResource($user->fresh()->load('roles'));
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($request->user()->can('users.delete'), 403);
        DB::transaction(function () use ($user, $request): void {
            $lockedUser = User::query()->with('roles')->lockForUpdate()->findOrFail($user->id);
            $this->ensureCanTrash($lockedUser, $request->user());
            $lockedUser->delete();
        });

        return response()->json(['message' => 'User moved to Trash.']);
    }

    public function restore(Request $request, int $user)
    {
        abort_unless($request->user()->can('users.restore'), 403);

        $trashedUser = User::onlyTrashed()->with('roles')->findOrFail($user);
        $this->ensureManageable($trashedUser, $request->user());
        $trashedUser->restore();

        return new UserResource($trashedUser->fresh()->load('roles'));
    }

    public function forceDelete(Request $request, int $user)
    {
        abort_unless($request->user()->can('users.force_delete'), 403);

        DB::transaction(function () use ($user, $request): void {
            $trashedUser = User::onlyTrashed()->with('roles')->lockForUpdate()->findOrFail($user);
            $this->ensureCanForceDelete($trashedUser, $request->user());
            $trashedUser->forceDelete();
        });

        return response()->json(['message' => 'User permanently deleted.']);
    }

    public function bulkAction(BulkUserActionRequest $request)
    {
        $validated = $request->validated();
        $actor = $request->user();
        $action = $validated['action'];
        $ids = $validated['ids'];

        $permission = match ($action) {
            'activate', 'deactivate' => 'users.status.update',
            'trash' => 'users.delete',
            'restore' => 'users.restore',
            'force_delete' => 'users.force_delete',
        };

        abort_unless($actor->can($permission), 403);

        DB::transaction(function () use ($action, $ids, $actor): void {
            if (in_array($action, ['activate', 'deactivate', 'trash'], true)) {
                $users = User::query()->with('roles')->whereKey($ids)->lockForUpdate()->get();
            } else {
                $users = User::onlyTrashed()->with('roles')->whereKey($ids)->lockForUpdate()->get();
            }

            if ($users->count() !== count($ids)) {
                throw ValidationException::withMessages(['ids' => ['One or more selected users are not available for this action.']]);
            }

            foreach ($users as $user) {
                if ($action === 'activate') {
                    $this->ensureManageable($user, $actor);
                    $this->ensureCanChangeStatus($user, $actor, 'active');
                    $user->update(['status' => 'active']);
                } elseif ($action === 'deactivate') {
                    $this->ensureManageable($user, $actor);
                    $this->ensureCanChangeStatus($user, $actor, 'inactive');
                    $user->update(['status' => 'inactive']);
                } elseif ($action === 'trash') {
                    $this->ensureCanTrash($user, $actor);
                    $user->delete();
                } elseif ($action === 'restore') {
                    $this->ensureManageable($user, $actor);
                    $user->restore();
                } else {
                    $this->ensureCanForceDelete($user, $actor);
                    $user->forceDelete();
                }
            }
        });

        return response()->json(['message' => 'Bulk user action completed successfully.']);
    }

    private function assignableRoleNames(array $roleNames, User $actor): array
    {
        if (! $actor->can('users.roles.assign')) {
            abort(403);
        }

        $roleNames = array_values(array_unique($roleNames));
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $roleNames)
            ->get();

        if ($roles->count() !== count($roleNames)) {
            throw ValidationException::withMessages([
                'roles' => ['One or more selected roles are invalid.'],
            ]);
        }

        if (! $actor->hasRole('Super Admin') && $roles->contains(fn (Role $role) => in_array($role->name, ['Super Admin', 'Admin'], true))) {
            throw ValidationException::withMessages([
                'roles' => ['You cannot assign an administrator or Super Admin role.'],
            ]);
        }

        return $roles->pluck('name')->all();
    }

    private function ensureManageable(User $user, User $actor): void
    {
        if ($user->hasRole('Super Admin') && ! $actor->hasRole('Super Admin')) {
            abort(403);
        }

        if ($user->hasRole('Admin') && ! $actor->hasRole('Super Admin') && ! $user->is($actor)) {
            abort(403);
        }
    }

    private function ensureCanChangeStatus(User $user, User $actor, string $status): void
    {
        if ($user->is($actor)) {
            throw ValidationException::withMessages(['ids' => ['You cannot change your own account status.']]);
        }

        $this->protectLastActiveSuperAdmin($user, $status === 'inactive');
    }

    private function ensureCanTrash(User $user, User $actor): void
    {
        $this->ensureManageable($user, $actor);

        if ($user->is($actor)) {
            throw ValidationException::withMessages(['user' => ['You cannot move your own account to Trash.']]);
        }

        $this->protectLastActiveSuperAdmin($user, true);
    }

    private function ensureCanForceDelete(User $user, User $actor): void
    {
        $this->ensureManageable($user, $actor);

        if ($user->is($actor)) {
            throw ValidationException::withMessages(['user' => ['You cannot permanently delete your own account.']]);
        }

        if ($user->hasRole('Super Admin') && $this->activeSuperAdminCount() < 1) {
            throw ValidationException::withMessages(['user' => ['At least one active Super Admin account must remain.']]);
        }

        if ($this->hasProtectedDependencies($user)) {
            throw ValidationException::withMessages(['user' => ['This account has linked academic, financial, content, profile, or media records and cannot be permanently deleted.']]);
        }
    }


    private function protectLastActiveSuperAdmin(User $user, bool $removingActiveAccess): void
    {
        if ($removingActiveAccess && $user->status === 'active' && $user->hasRole('Super Admin') && $this->activeSuperAdminCount() <= 1) {
            throw ValidationException::withMessages(['user' => ['The last active Super Admin account cannot be deactivated or moved to Trash.']]);
        }
    }

    private function activeSuperAdminCount(): int
    {
        return User::query()
            ->where('status', 'active')
            ->whereHas('roles', fn (Builder $roles) => $roles->where('name', 'Super Admin')->where('guard_name', 'web'))
            ->lockForUpdate()
            ->get(['users.id'])
            ->count();
    }

    private function hasProtectedDependencies(User $user): bool
    {
        $references = [
            'student_profiles' => 'user_id',
            'teacher_profiles' => 'user_id',
            'announcements' => 'created_by_user_id',
            'learning_materials' => 'uploaded_by_user_id',
            'attendance_records' => 'marked_by_user_id',
            'payments' => 'received_by_user_id',
        ];

        foreach ($references as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column) && DB::table($table)->where($column, $user->id)->exists()) {
                return true;
            }
        }

        return Schema::hasTable('media')
            && Schema::hasColumn('media', 'model_type')
            && Schema::hasColumn('media', 'model_id')
            && DB::table('media')
                ->whereIn('model_type', array_unique([User::class, $user->getMorphClass()]))
                ->where('model_id', $user->id)
                ->exists();
    }
}
