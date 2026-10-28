<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;

class Payment extends Model
{
    use HasFactory;


    protected $fillable = [
        'order_id',
        'method',
        'status',
        'amount',
        'paid_at',
        'created_by',
    ];


    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'status' => PaymentStatus::class,
            'method' => PaymentMethod::class,
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


    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
