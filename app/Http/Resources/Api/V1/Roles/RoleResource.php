<?php

namespace App\Http\Resources\Api\V1\Roles;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $systemRoles = ['Super Admin', 'Admin', 'Teacher', 'Accountant', 'Student'];

        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'permissions_count' => $this->permissions_count ?? 0,
            'users_count' => $this->users_count ?? 0,
            'permissions' => $this->whenLoaded('permissions', fn () => $this->permissions->pluck('name')->values()),
            'is_system' => in_array($this->name, $systemRoles, true),
            'is_protected' => $this->name === 'Super Admin',
            'created_at' => $this->created_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
