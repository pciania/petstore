<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPetImageRequest extends FormRequest
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
            'image'               => ['required', 'file', 'image', 'max:10240', 'mimes:jpg,jpeg,png,gif'],
            'additional_metadata' => ['nullable', 'string', 'max:255'],
        ];
    }
}

