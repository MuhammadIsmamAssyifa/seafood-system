<?php

namespace App\Http\Requests\OrderItemChange;

use App\Enums\OrderItemChangeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderItemChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_item_id' => ['sometimes', 'required', 'integer', 'exists:order_items,id'],
            'type' => ['required', Rule::enum(OrderItemChangeType::class)],
            'quantity' => ['nullable', 'numeric', 'min:0.001'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Tipe perubahan wajib diisi.',
            'reason.required' => 'Alasan perubahan wajib diisi.',
        ];
    }
}
