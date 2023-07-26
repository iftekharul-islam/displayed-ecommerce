<?php

namespace App\Http\Requests\Tld;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTldRequest extends FormRequest
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
            'name'   => ['required', 'string', 'max:255', Rule::unique('tlds', 'name')->where(function ($query) {
                return $query->where('campaign_id', $this->campaign);
            })->ignore($this->tld)],
            'price' => ['required', 'string', 'max:255'],
            'last_updated_at' => ['nullable', 'date', 'date_format:Y-m-d'],
        ];
    }
}
