<?php

namespace App\Http\Requests\Api\V1\Permissions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('permissions.update') ?? false;
    }

    public function rules(): array
    {
        $permission = $this->route('permission');

        return [
            'name' => [
                'required', 'string', 'max:125',
                'regex:/^[a-z][a-z0-9_]*(\\.[a-z][a-z0-9_]*)+$/',
                Rule::unique('permissions', 'name')->where(fn ($query) => $query->where('guard_name', 'web'))->ignore($permission?->id),
            ],
        ];
    }
}
