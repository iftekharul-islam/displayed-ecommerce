<?php

namespace App\Http\Requests\Campaign;

use App\Rules\Boolean;
use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:campaigns,name'],
            'is_active' => ['required', new Boolean],
            'last_updated_at' => ['nullable', 'date', 'date_format:Y-m-d'],
        ];
    }
}
