<?php

namespace App\Models;

use App\Enums\OrderItemChangeType;
use App\Enums\OrderItemChangeStatus;
use Illuminate\Database\Eloquent\Model;

class OrderItemChange extends Model
{
    protected $fillable = [
        'order_item_id',
        'type',

        'old_quantity',
        'old_price',

        'new_quantity',
        'new_price',

        'replacement_item_id',

        'status',

        'reason',

        'created_by',
    ];


    protected function casts(): array
    {
        return [

            'type' =>
            OrderItemChangeType::class,

            'status' =>
            OrderItemChangeStatus::class,

            'old_quantity' =>
            'decimal:3',

            'new_quantity' =>
            'decimal:3',

            'old_price' =>
            'decimal:2',

            'new_price' =>
            'decimal:2',

        ];
    }



    public function orderItem()
    {
        return $this->belongsTo(
            OrderItem::class
        );
    }


    public function replacementItem()
    {
        return $this->belongsTo(
            OrderItem::class,
            'replacement_item_id'
        );
    }


    public function createdBy()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}
