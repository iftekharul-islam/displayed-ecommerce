<?php

namespace App\Http\Requests\ExcludedDomain;

use App\Rules\Boolean;
use Illuminate\Validation\Rule;
use App\Constants\ShortUrlConstant;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExcludedDomainRequest extends FormRequest
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
            'campaign_id' => ['required', 'exists:campaigns,id'],
            'domain' => ['required', 'string', 'max:255', Rule::unique('excluded_domains', 'domain')->ignore($this->excluded_domain)],
            'expired_at' => ['required', 'date', 'date_format:Y-m-d'],
            'auto_renewal' => ['required', new Boolean],
            'status' => ['required', Rule::in([
                ShortUrlConstant::VALID,
                ShortUrlConstant::INVALID,
                ShortUrlConstant::EXPIRED,
            ])],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
