<?php

namespace App\Http\Requests\ShortUrl;

use App\Rules\Boolean;
use Illuminate\Validation\Rule;
use App\Constants\ShortUrlConstant;
use Illuminate\Foundation\Http\FormRequest;

class UpdateShortUrlRequest extends FormRequest
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
            'expired_date' => ['required', 'date', 'date_format:Y-m-d'],
            'auto_renewal' => ['required', new Boolean],
            'remarks' => 'nullable|string',
            'status' => ['required', Rule::in([
                ShortUrlConstant::VALID,
                ShortUrlConstant::INVALID,
                ShortUrlConstant::EXPIRED,
            ])],
        ];
    }
}
