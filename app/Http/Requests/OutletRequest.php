<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OutletRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'alpha_dash'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'restaurant_id' => ['nullable', 'integer', 'exists:restaurants,id'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama outlet wajib diisi.',
            'code.required' => 'Kode outlet wajib diisi.',
            'code.alpha_dash' => 'Kode outlet hanya boleh berisi huruf, angka, garis hubung, dan garis bawah.',
            'status.in' => 'Status outlet harus active atau inactive.',
        ];
    }
}

