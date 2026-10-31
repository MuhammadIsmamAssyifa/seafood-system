<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'address' => $this->address,
            'phone' => $this->phone,
            'status' => $this->status,
            'restaurant_id' => $this->restaurant_id,
            'restaurant_name' => $this->restaurant?->name,
            'users_count' => $this->users_count ?? $this->users?->count() ?? 0,
            'menus_count' => $this->menus_count ?? 0,
            'ingredients_count' => $this->ingredients_count ?? 0,
            'tables_count' => $this->order_tables_count ?? 0,
            'orders_today_count' => $this->orders_today_count ?? 0,
            'users' => $this->whenLoaded('users', function () {
                return $this->users->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'status' => $u->status,
                    'role' => $u->relationLoaded('roles') ? ($u->roles->first()?->name ?? 'Staff') : 'Staff',
                ]);
            }),
            'created_at' => $this->created_at?->toISOString() ?? (string) $this->created_at,
            'updated_at' => $this->updated_at?->toISOString() ?? (string) $this->updated_at,
        ];
    }
}
