<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'user_id',
        'action',
        'module',
        'description',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getModuleAttribute($value): string
    {
        if (! empty($value)) {
            return $value;
        }

        $action = strtolower($this->action ?? '');
        $type = strtolower($this->auditable_type ?? '');

        if (str_contains($action, 'order') || str_contains($type, 'order')) {
            return 'Order';
        }
        if (str_contains($action, 'payment') || str_contains($type, 'payment')) {
            return 'Payment';
        }
        if (str_contains($action, 'stock') || str_contains($action, 'inventory') || str_contains($action, 'waste') || str_contains($type, 'stock') || str_contains($type, 'waste') || str_contains($type, 'ingredient')) {
            return 'Inventory';
        }
        if (str_contains($action, 'menu') || str_contains($type, 'menu')) {
            return 'Menu';
        }
        if (str_contains($action, 'user') || str_contains($action, 'employee') || str_contains($action, 'role') || str_contains($action, 'login') || str_contains($action, 'logout') || str_contains($type, 'user') || str_contains($type, 'role')) {
            return 'Employee & Auth';
        }
        if (str_contains($action, 'setting') || str_contains($type, 'setting') || str_contains($type, 'restaurant')) {
            return 'Settings';
        }

        return 'System';
    }
}
