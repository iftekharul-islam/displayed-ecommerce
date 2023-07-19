<?php

namespace App\Http\Requests\Role;

use App\Rules\Boolean;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'is_all_checked' => ['nullable', new Boolean],
            'is_attach' => ['nullable', new Boolean],
            'permission_id' => ['nullable', 'exists:permissions,id'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['nullable', 'exists:permissions,id'],
        ];
    }
}
