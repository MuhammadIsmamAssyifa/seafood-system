<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\IngredientStatus;

class Ingredient extends Model
{
    use HasFactory;


    protected $fillable = [
        'outlet_id',
        'name',
        'unit',
        'type',
        'status',
    ];


    protected function casts(): array
    {
        return ['status' => IngredientStatus::class,];
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function stockMovements(): HasMany
    {
        return $this->hasMany(
            StockMovement::class
        );
    }


    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }


    public function menuVariants(): HasMany
    {
        return $this->hasMany(MenuVariant::class, 'main_ingredient_id');
    }

    public function inventoryBatches(): HasMany
    {
        return $this->hasMany(
            InventoryBatch::class
        );
    }
}
