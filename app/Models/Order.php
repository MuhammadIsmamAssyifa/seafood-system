<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'order_table_id',
        'order_number',
        'customer_name',
        'guest_count',
        'status',
        'subtotal',
        'discount',
        'grand_total',
        'total_cogs',
        'gross_profit',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'total_cogs' => 'decimal:2',
            'gross_profit' => 'decimal:2',
            'guest_count' => 'integer',
            'status' => OrderStatus::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Order belongs to outlet
     */
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    /**
     * Order belongs to table
     */
    public function orderTable(): BelongsTo
    {
        return $this->belongsTo(OrderTable::class);
    }

    /**
     * User who created the order
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Order batches
     */
    public function batches(): HasMany
    {
        return $this->hasMany(OrderBatch::class);
    }

    /**
     * Order items through batches
     */
    public function items(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, OrderBatch::class);
    }

    /**
     * Order status histories
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    /**
     * Payments
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
