<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Outlet extends Model
{
    use HasFactory;


    protected $fillable = [
        'restaurant_id',
        'name',
        'code',
        'address',
        'phone',
        'status',
    ];


    protected function casts(): array
    {
        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */


    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_outlets');
    }


    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }


    public function orderTables(): HasMany
    {
        return $this->hasMany(OrderTable::class);
    }


    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }


    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }


    public function menuVariants(): HasMany
    {
        return $this->hasMany(MenuVariant::class);
    }
}
