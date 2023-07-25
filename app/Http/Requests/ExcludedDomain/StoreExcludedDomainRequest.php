<?php

namespace App\Http\Requests\ExcludedDomain;

use App\Rules\Boolean;
use Illuminate\Validation\Rule;
use App\Constants\ShortUrlConstant;
use Illuminate\Foundation\Http\FormRequest;

class StoreExcludedDomainRequest extends FormRequest
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
            'domains' => ['required', 'array', 'max:5'],
            'domains.*.domain' => ['required', 'string', 'max:255', 'unique:excluded_domains,domain'],
            'domains.*.expired_at' => ['required', 'date', 'date_format:Y-m-d'],
            'domains.*.auto_renewal' => ['required', new Boolean],
            'domains.*.status' => ['required', Rule::in([
                ShortUrlConstant::VALID,
                ShortUrlConstant::INVALID,
                ShortUrlConstant::EXPIRED,
            ])],
            'domains.*.remarks' => ['nullable', 'string'],
        ];
    }
}
