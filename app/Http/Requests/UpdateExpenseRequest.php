<?php

namespace App\Http\Requests;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateExpenseRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|exists:expenses,id',
            'name' => [
                'nullable',
                'string',
                'min:1',
                'max:255',
                Rule::unique('expenses', 'name')->ignore($this->route('expense')),
            ],
            'amount' => 'nullable|numeric|min:0',
            'comment' => 'nullable|string|min:6|max:255',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $expenseId = $this->route('expense');
            $parentId = $this->input('parent_id') ?? null;
            $name = $this->input('name') ?? null;

            // Expense
            $expense = Expense::findOrFail($expenseId);

            // Parent ID
            if ($parentId) {
                $pluckChildren = $expense->children->pluck('name', 'id');

                if ($expense->id === $parentId || !empty($pluckChildren[$parentId])) {
                    $validator->errors()->add("parent_id", "Ota xarajatni to'g'ri tanlang!");
                }
            }

            // Name
            if ($name) {
                $isExist = Expense::where('name', $name)
                    ->where('id', "<>", $expenseId)->exists();
                if ($isExist) {
                    $validator->errors()->add("name", "Bu xarajat allaqachon mavjud");
                };
            }
        });
    }
}
