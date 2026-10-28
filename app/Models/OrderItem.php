<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\OrderItemStatus;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_batch_id',
        'menu_variant_id',
        'menu_name_snapshot',
        'variant_name_snapshot',
        'price_snapshot',
        'sales_unit',
        'quantity',
        'subtotal',
        'inventory_quantity',
        'inventory_unit',
        'status',
    ];


    protected function casts(): array
    {
        return [
            'price_snapshot' => 'decimal:2',
            'quantity' => 'decimal:3',
            'subtotal' => 'decimal:2',
            'inventory_quantity' => 'decimal:3',
            'status' => OrderItemStatus::class,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */


    public function orderBatch(): BelongsTo
    {
        return $this->belongsTo(OrderBatch::class);
    }


    public function menuVariant(): BelongsTo
    {
        return $this->belongsTo(MenuVariant::class);
    }

    public function changes()
    {
        return $this->hasMany(
            OrderItemChange::class
        );
    }
}
