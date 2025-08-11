<?php

namespace App\Http\Requests\ServiceCategories;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
        ]);
    }

    public function rules(): array
    {
        return [
            'name'       => 'required|string|max:250|unique:service_categories,name',
            'image'      => 'nullable|image|max:2048', // jpg,png,webp… (2MB)
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم التصنيف مطلوب.',
            'name.unique'   => 'اسم التصنيف مستخدم من قبل.',
            'image.image'   => 'الملف يجب أن يكون صورة.',
        ];
    }
}
