<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchGifsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'query' => 'required|string',
            'limit' => 'nullable|integer|min:1',
            'offset' => 'nullable|integer|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'query.required' => 'A search query is required',
            'limit.integer' => 'Limit must be a number',
            'limit.min' => 'Limit must be at least 1',
            'offset.integer' => 'Offset must be a number',
            'offset.min' => 'Offset cannot be negative'
        ];
    }
}
