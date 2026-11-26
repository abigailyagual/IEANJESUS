<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserStore extends FormRequest
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
                Rule::unique('users', 'email'),
            ],

            'password' => [
                'required',
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
    public function messages(): array
{
    return [
        'name.required' => 'El nombre completo es obligatorio.',

        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email' => 'Ingrese un correo electrónico válido.',
        'email.unique' => 'El correo electrónico ya está registrado.',

        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.confirmed' => 'Las contraseñas no coinciden.',

        'role.required' => 'Seleccione un rol para el usuario.',
        'role.in' => 'El rol seleccionado no es válido.',

        'categories.required' => 'Seleccione al menos un departamento.',
        'categories.min' => 'Seleccione al menos un departamento.',
        'categories.array' => 'Los departamentos seleccionados no son válidos.',
        'categories.*.exists' => 'El departamento seleccionado no existe.',
        'categories.*.distinct' => 'No puede seleccionar departamentos duplicados.',
    ];
}
    
}
