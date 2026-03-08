<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
class UpdatePetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'id'            => ['required', 'integer', 'min:1'],
            'name'          => ['required', 'string', 'min:2', 'max:120'],
            'status'        => ['required', 'string', 'in:available,pending,sold'],
            'category'      => ['nullable', 'array'],
            'category.id'   => ['nullable', 'integer'],
            'category.name' => ['nullable', 'string', 'max:60'],
            'tags'          => ['nullable', 'array'],
            'tags.*'        => ['nullable', 'string', 'max:60'],
            'photo_urls'    => ['nullable', 'string'],
        ];
    }
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $category = $this->input('category');

            if (empty($category['id']) || empty($category['name'])) {
                return;
            }

            $valid = collect(StorePetRequest::CATEGORIES)->contains(
                fn (array $cat) =>
                    (int) $category['id'] === $cat['id']
                    && $category['name'] === $cat['name']
            );

            if (! $valid) {
                $v->errors()->add('category', 'The selected category is invalid.');
            }
        });
    }
}
