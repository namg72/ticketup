<?php

namespace App\Http\Requests\Users\User;

use Illuminate\Foundation\Http\FormRequest;
use Monolog\Handler\IFTTTHandler;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        $user = $this->user();

        if ($user->hasRole('admin')) {
            return true;
        }



        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // id del usuario que estamos editando (null en create)
        $userId = $this->route('id');

        $rules = [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                // en create: unique normal
                // en update: ignora el user con id = $userId
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'supervisor_id' => ['nullable', 'integer', 'exists:users,id'],

            'is_active' => ['sometimes', 'boolean'],

            'role' => ['required', 'string', Rule::in(['admin', 'supervisor', 'employee'])],
        ];

        // Password:
        // - POST (create): requerida
        // - PUT/PATCH (update): opcional, si viene se valida
        if ($this->isMethod('post')) {
            $rules['password'] = ['required', 'string', 'min:8'];
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['password'] = ['nullable', 'string', 'min:8'];
        }

        return $rules;
    }
}
