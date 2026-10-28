<?php

namespace App\Http\Requests\OrderItemChange;

use Illuminate\Foundation\Http\FormRequest;

class ReplaceOrderItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'menu_variant_id' => ['required', 'integer', 'exists:menu_variants,id'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'menu_variant_id.required' => 'Menu pengganti wajib dipilih.',
            'menu_variant_id.exists' => 'Menu pengganti tidak ditemukan.',
            'quantity.required' => 'Quantity wajib diisi.',
            'quantity.min' => 'Quantity minimal 0.001.',
            'reason.required' => 'Alasan penggantian wajib diisi.',
        ];
    }
}

