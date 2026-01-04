<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:20', 'regex:/^[A-Za-zء-ي\s]+$/'],
            'last_name' => ['required', 'string', 'max:20', 'regex:/^[A-Za-zء-ي\s]+$/'],
            'phone' => 'required|string|unique:users,phone|min:10|max:20',
            'birth_date' => 'required|date|before:today',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'id_card_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
            'password' => 'required|string|min:8',
        ];
    }
}
