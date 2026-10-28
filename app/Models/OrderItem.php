<?php

namespace App\Models;

use App\Enums\OrderItemStatus;
use App\Enums\SalesUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_batch_id',
        'menu_variant_id',
        'menu_name_snapshot',
        'variant_name_snapshot',
        'requested_quantity',
        'confirmed_quantity',
        'price_snapshot',
        'sales_unit',
        'quantity',
        'inventory_quantity',
        'inventory_unit',
        'subtotal',
        'cogs',
        'gross_profit',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'requested_quantity' => 'decimal:3',
            'confirmed_quantity' => 'decimal:3',
            'price_snapshot' => 'decimal:2',
            'quantity' => 'decimal:3',
            'subtotal' => 'decimal:2',
            'cogs' => 'decimal:2',
            'gross_profit' => 'decimal:2',
            'inventory_quantity' => 'decimal:3',
            'sales_unit' => SalesUnit::class,
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

    public function order(): HasOneThrough
    {
        return $this->hasOneThrough(
            Order::class,
            OrderBatch::class,
            'id',
            'id',
            'order_batch_id',
            'order_id'
        );
    }

    public function menuVariant(): BelongsTo
    {
        return $this->belongsTo(MenuVariant::class);
    }

    public function changes(): HasMany
    {
        return $this->hasMany(OrderItemChange::class);
    }
}
