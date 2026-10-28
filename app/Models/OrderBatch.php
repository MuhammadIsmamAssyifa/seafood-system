<?php

namespace App\Models;

use App\Enums\OrderBatchStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'batch_number',
        'status',
        'sent_to_kitchen_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_to_kitchen_at' => 'datetime',
            'status' => OrderBatchStatus::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
