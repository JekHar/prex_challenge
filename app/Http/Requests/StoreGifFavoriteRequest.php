<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGifFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gif_id' => 'required|string',
            'alias' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'gif_id.required' => 'A GIF ID is required',
            'alias.required' => 'An alias is required',
            'alias.max' => 'Alias cannot be longer than 255 characters',
        ];
    }
}
