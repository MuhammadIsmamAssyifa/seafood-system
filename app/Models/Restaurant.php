<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status',
        'phone',
        'address',
        'footer_message',
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

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
