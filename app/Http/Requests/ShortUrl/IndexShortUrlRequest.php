<?php

namespace App\Http\Requests\ShortUrl;

use Illuminate\Foundation\Http\FormRequest;

class IndexShortUrlRequest extends FormRequest
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
            'filterQuery' => ['nullable', 'array'],
            'filterQuery.fromDateFilter' => ['nullable', 'date', 'date_format:Y-m-d'],
            'filterQuery.toDateFilter' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:filterQuery.fromDateFilter'],
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'filterQuery.fromDateFilter.date_format' => 'The from date filter does not match the format Y-m-d.',
            'filterQuery.toDateFilter.date_format' => 'The to date filter does not match the format Y-m-d.',
            'filterQuery.toDateFilter.after_or_equal' => 'The to date filter must be a date after or equal to from date filter.',
        ];
    }
}
