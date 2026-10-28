<?php

namespace App\Models;

use App\Enums\ActiveStatus;
use App\Enums\IngredientUnit;
use App\Enums\SalesUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MenuVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'menu_id',
        'cooking_method_id',
        'main_ingredient_id',
        'kitchen_station_id',
        'sequence',
        'name',
        'price',
        'sales_unit',
        'inventory_unit',
        'conversion_rate',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'conversion_rate' => 'decimal:3',
            'sales_unit' => SalesUnit::class,
            'inventory_unit' => IngredientUnit::class,
            'status' => ActiveStatus::class,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function cookingMethod(): BelongsTo
    {
        return $this->belongsTo(CookingMethod::class);
    }

    public function mainIngredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'main_ingredient_id');
    }

    public function kitchenStation(): BelongsTo
    {
        return $this->belongsTo(KitchenStation::class);
    }

    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
