<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
class StorePetRequest extends FormRequest
{
    /** @var array<string, array{id: int, name: string}> */
    public const CATEGORIES = [
        'dogs'  => ['id' => 1, 'name' => 'Dogs'],
        'cats'  => ['id' => 2, 'name' => 'Cats'],
        'birds' => ['id' => 3, 'name' => 'Birds'],
    ];
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'min:2', 'max:120'],
            'status'        => ['required', 'string', 'in:available,pending,sold'],
            'category'      => ['nullable', 'array'],
            'category.id'   => ['required_with:category', 'integer'],
            'category.name' => ['required_with:category', 'string', 'max:60'],
            'tags'          => ['nullable', 'array'],
            'tags.*'        => ['string', 'max:60'],
            'photo_urls'    => ['nullable', 'string'],
        ];
    }
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $category = $this->input('category');
            if (empty($category)) {
                return;
            }
            $valid = collect(self::CATEGORIES)->contains(
                fn (array $cat) =>
                    (int) ($category['id'] ?? -1) === $cat['id']
                    && ($category['name'] ?? '') === $cat['name']
            );
            if (! $valid) {
                $v->errors()->add('category', 'The selected category is invalid.');
            }
        });
    }
}
