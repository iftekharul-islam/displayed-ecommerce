<?php

namespace App\Http\Requests\ShortUrl;

use Illuminate\Foundation\Http\FormRequest;

class LatestDomainExportRequest extends FormRequest
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
            'campaignId' => ['required', 'exists:campaigns,id'],
            'fromDate' => ['required', 'date', 'date_format:Y-m-d'],
            'toDate' => ['required', 'date', 'date_format:Y-m-d'],
        ];
    }
}
