<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{

    protected $fillable = [

        'outlet_id',

        'ingredient_id',

        'inventory_batch_id',

        'type',

        'quantity',

        'reference_type',

        'reference_id',

        'note',

        'created_by',

    ];



    protected function casts(): array
    {
        return [

            'type' =>
            StockMovementType::class,


            'quantity' =>
            'decimal:3',

        ];
    }



    public function outlet(): BelongsTo
    {
        return $this->belongsTo(
            Outlet::class
        );
    }



    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(
            Ingredient::class
        );
    }



    public function inventoryBatch(): BelongsTo
    {
        return $this->belongsTo(
            InventoryBatch::class
        );
    }



    public function reference()
    {
        return $this->morphTo();
    }



    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}
