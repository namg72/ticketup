<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        $user = $this->user();


        // Crear ticket (POST): solo empleados
        if ($this->isMethod('post')) {
            return $user->hasRole('employee');
        }

        // Actualizar ticket (PUT/PATCH): empleado, supervisor o admin
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $user->hasAnyRole(['employee', 'supervisor', 'admin']);
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
        $rules = [
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', 'exists:ticket_categories,id'],
            'image' => ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048']
        ];


        return $rules;
    }
}
