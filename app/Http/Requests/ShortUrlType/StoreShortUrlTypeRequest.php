<?php

namespace App\Http\Requests\ShortUrlType;

use Illuminate\Foundation\Http\FormRequest;

class StoreShortUrlTypeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:short_url_types,name'],
            'redirect_url' => ['required', 'string', 'max:255'],
        ];
    }
}
