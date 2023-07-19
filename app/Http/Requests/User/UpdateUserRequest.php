<?php

namespace App\Http\Requests\User;

use App\Rules\Boolean;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email',   Rule::unique('users')->ignore($this->user)],
            'is_active' => ['nullable', new Boolean],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'password_confirmation' => ['nullable', 'min:6'],
        ];
    }
}
