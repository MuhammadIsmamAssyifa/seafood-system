<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecipeRequest extends FormRequest
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
        $rules = [
            'name' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ingredient_id' => ['required', 'integer', 'exists:ingredients,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit' => ['required', 'string', 'in:kg,gram,liter,ml,pcs'],
        ];

        if ($this->isMethod('POST')) {
            $rules['menu_variant_id'] = ['required', 'integer', 'exists:menu_variants,id'];
        } else {
            $rules['menu_variant_id'] = ['nullable', 'integer', 'exists:menu_variants,id'];
        }

        return $rules;
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'menu_variant_id.required' => 'Varian menu wajib dipilih.',
            'menu_variant_id.exists' => 'Varian menu tidak ditemukan.',
            'items.required' => 'Daftar bahan baku wajib diisi minimal 1 bahan.',
            'items.min' => 'Daftar bahan baku wajib memiliki minimal 1 bahan.',
            'items.*.ingredient_id.required' => 'Bahan baku wajib dipilih.',
            'items.*.quantity.required' => 'Jumlah takaran bahan wajib diisi.',
            'items.*.quantity.gt' => 'Jumlah takaran bahan harus lebih besar dari 0.',
            'items.*.unit.required' => 'Satuan bahan wajib dipilih.',
            'items.*.unit.in' => 'Satuan bahan tidak valid (pilih: kg, gram, liter, ml, pcs).',
        ];
    }
}

