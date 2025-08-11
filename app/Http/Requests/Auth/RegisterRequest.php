<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name'     => 'required|string|max:250',
            'email'    => 'required_without:phone|nullable|email|max:250|unique:users,email',
            'phone'    => 'required_without:email|nullable|string|max:50|unique:users,phone',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,technical,customer',
            'state'    => 'required|string|max:250',
            'area'     => 'required|string|max:250',
        ];


    }
}
