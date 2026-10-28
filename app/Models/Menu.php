<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;


    protected $fillable = [
        'outlet_id',
        'category_id',
        'name',
        'description',
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


    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(MenuVariant::class);
    }
}
