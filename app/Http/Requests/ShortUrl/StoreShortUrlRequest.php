<?php

namespace App\Http\Requests\ShortUrl;

use App\Rules\Boolean;
use Illuminate\Validation\Rule;
use App\Constants\ShortUrlConstant;
use Illuminate\Foundation\Http\FormRequest;

class StoreShortUrlRequest extends FormRequest
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
            'destination_domain' => ['required', 'string', 'max:255'],
            'remarks' => 'nullable|string',
            'original_domains' => ['required', 'array'],
            'original_domains.*.domain' => ['required', 'string', 'max:255', 'unique:short_urls,original_domain'],
            'original_domains.*.expired_date' => ['required', 'date', 'date_format:Y-m-d'],
            'original_domains.*.auto_renewal' => ['required', new Boolean],
            'original_domains.*.status' => ['required', Rule::in([
                ShortUrlConstant::VALID,
                ShortUrlConstant::INVALID,
                ShortUrlConstant::EXPIRED,
            ])],
        ];
    }
}
