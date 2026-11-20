<?php

namespace App\Http\Requests\Api\V1\Roles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('roles.update') ?? false;
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => ['required', 'string', 'min:2', 'max:125', Rule::unique('roles', 'name')->where(fn ($query) => $query->where('guard_name', 'web'))->ignore($role?->id)],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => [
                'required', 'string', 'distinct',
                Rule::exists('permissions', 'name')->where(fn ($query) => $query->where('guard_name', 'web')->whereNull('deleted_at')),
            ],
        ];
    }
}
