<?php

namespace App\Http\Resources\Api\V1\Permissions;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PermissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $group = Str::before($this->name, '.');
        $isProtected = in_array($group, ['users', 'roles', 'permissions'], true);
        $otherRolesCount = $this->other_roles_count ?? 0;
        $usersCount = $this->users_count ?? 0;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'group' => Str::headline($group),
            'group_key' => $group,
            'roles_count' => $this->roles_count ?? 0,
            'other_roles_count' => $otherRolesCount,
            'users_count' => $usersCount,
            'is_assigned' => ($this->roles_count ?? 0) > 0 || $usersCount > 0,
            'is_protected' => $isProtected,
            'can_rename' => ! $isProtected && ($this->roles_count ?? 0) === 0 && $usersCount === 0,
            'can_trash' => ! $isProtected && $otherRolesCount === 0 && $usersCount === 0,
            'created_at' => $this->created_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
