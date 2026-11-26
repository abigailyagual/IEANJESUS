<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdate extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->exists('categories')) {
            $this->merge(['categories' => []]);
        }

        if (!$this->filled('password')) {
            $this->request->remove('password');
            $this->request->remove('password_confirmation');
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'position' => [
                'nullable',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($this->route('user')),
            ],

            'password' => [
                'nullable',
                'string',
                'confirmed',
                'min:8',
            ],

            'role' => [
                'required',
                Rule::in([0, 1, 2, 3, 4]),
            ],

            'categories' => [
                Rule::requiredIf(fn () => in_array((string) $this->input('role'), ['1', '3'], true)),
                'present',
                'array',
            ],

            'categories.*' => [
                'integer',
                'distinct',
                'exists:categories,id',
            ],
        ];
    }
}
