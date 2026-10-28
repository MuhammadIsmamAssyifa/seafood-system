<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\OrderBatchStatus;

class OrderBatch extends Model
{
    use HasFactory;


    protected $fillable = [
        'order_id',
        'batch_number',
        'status',
        'started_at',
        'completed_at',
    ];


    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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
