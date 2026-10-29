<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddOrderItemRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'menu_variant_id' => [
                'required',
                'integer',
                'exists:menu_variants,id',
            ],

            'quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'menu_variant_id.required' => 'Menu harus dipilih.',
            'menu_variant_id.exists' => 'Menu tidak ditemukan.',

            'quantity.required' => 'Quantity wajib diisi.',
            'quantity.numeric' => 'Quantity harus berupa angka.',
            'quantity.gt' => 'Quantity harus lebih dari 0.',
        ];
    }
}
