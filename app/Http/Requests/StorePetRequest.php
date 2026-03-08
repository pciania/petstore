<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'min:2', 'max:120'],
            'status'     => ['required', 'string', 'in:available,pending,sold'],
            'photo_urls' => ['nullable', 'string'],
        ];
    }
}

