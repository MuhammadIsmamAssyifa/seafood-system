<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'status' => $user->status,
            'role' => $user->getRoleNames()->first() ?? null,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'restaurant' => $user->restaurant ? [
                'id' => $user->restaurant->id,
                'name' => $user->restaurant->name,
                'code' => $user->restaurant->code,
                'phone' => $user->restaurant->phone,
                'address' => $user->restaurant->address,
                'footer_message' => $user->restaurant->footer_message,
            ] : null,
        ];
    }
}
