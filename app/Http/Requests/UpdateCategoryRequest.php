<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{

    public function authorize(): bool
    {
        return Auth::user()->isAdmin();
    }


    public function rules(): array
    {
        return [
            'name' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($this->route('category'))
            ],
            'parent_id' => [
                'nullable',
                'numeric',
                'exists:categories,id',
                Rule::notIn(array_merge(
                    [$this->route('category')],
                    $this->getDescendants($this->route('category'))
                )),
            ],

        ];
    }

    public function messages(): array
    {
        return [
            "name.unique" => "Bu nomli kategoriya allaqachon mavjud",
            "parent_id.exists" => "Ota kategoriyani noto'g'ri tanladingiz",
        ];
    }

    public function getDescendants(string|null $categoryId): array
    {
        if (!$categoryId) return [];

        $descendants = [];
        $this->findChildrenCategories($categoryId, $descendants);
        return $descendants;
    }

    public function findChildrenCategories($categoryId, &$descendants)
    {
        $children = Category::where('parent_id', $categoryId)->get()->pluck('id')->toArray();
        foreach ($children as $childId) {
            $descendants[] = $childId;
            $this->findChildrenCategories($childId, $descendants);
        }
    }
}
