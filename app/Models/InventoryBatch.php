<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryBatch extends Model
{

    protected $fillable = [

        'outlet_id',

        'ingredient_id',

        'quantity',

        'remaining_quantity',

        'cost_per_unit',

        'source',

        'received_at',

    ];



    protected function casts(): array
    {
        return [

            'quantity' =>
            'decimal:3',


            'remaining_quantity' =>
            'decimal:3',


            'cost_per_unit' =>
            'decimal:2',


            'received_at' =>
            'datetime',

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

    public function movements(): HasMany
    {
        return $this->hasMany(
            StockMovement::class,
        );
    }
}
