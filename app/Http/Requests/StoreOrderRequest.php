<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'order_table_id' => [
                'required',
                'exists:order_tables,id',
            ],

            'customer_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'guest_count' => [
                'required',
                'integer',
                'min:1',
            ],

        ];
    }
}
